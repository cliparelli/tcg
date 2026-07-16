#!/usr/bin/env python3
"""
Sobe o viewer PHP localmente, abre cada carta de uma colecao no navegador
(via Playwright) e tira um screenshot do preview renderizado (#card-preview),
salvando em EXPANSIONS/<colecao>/. So captura cartas cujo arquivo de arte
(coluna "Arte", ou "IMAGEM" no caso de Itens) existe de fato em LIB/<colecao>/.

Uso:
    python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso"
    python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso" --somente PERSONAGENS
    python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso" --porta 8010

Requer Playwright:
    pip3 install playwright
    python3 -m playwright install chromium
"""

import argparse
import csv
import re
import subprocess
import sys
import time
import unicodedata
import urllib.request
from pathlib import Path
from typing import Optional

REPO_DIR = Path(__file__).resolve().parent.parent
LIB_DIR = REPO_DIR / "LIB"
VIEWER_DIR = REPO_DIR / "viewer"
EXPANSIONS_DIR = REPO_DIR / "EXPANSIONS"

COLUNA_ARTE = "Arte"
COLUNA_IMAGEM = "IMAGEM"
COLUNA_NOME = "Nome"

TIPO_POR_ARQUIVO = {
    "personagem": re.compile(r"PERSONAGENS", re.IGNORECASE),
    "item": re.compile(r"ITENS", re.IGNORECASE),
    "energia": re.compile(r"ENERGIAS", re.IGNORECASE),
}


def detectar_tipo(nome_arquivo: str) -> Optional[str]:
    for tipo, padrao in TIPO_POR_ARQUIVO.items():
        if padrao.search(nome_arquivo):
            return tipo
    return None


def eh_url(valor: str) -> bool:
    return bool(re.match(r"^https?://", valor.strip(), re.IGNORECASE))


def slugify(nome: str) -> str:
    sem_acento = unicodedata.normalize("NFKD", nome).encode("ascii", "ignore").decode("ascii")
    slug = re.sub(r"[^a-zA-Z0-9]+", "-", sem_acento).strip("-")
    return slug or "sem-nome"


def carregar_cartas_com_arte(csv_path: Path, colecao_dir: Path, tipo: str):
    """Le um CSV ';' e retorna as linhas cujo arquivo de arte existe em disco."""
    cartas = []
    with csv_path.open("r", encoding="utf-8-sig", newline="") as f:
        leitor = csv.DictReader(f, delimiter=";")
        campos = {c.strip(): c for c in (leitor.fieldnames or [])}

        col_arte = campos.get(COLUNA_ARTE)
        col_imagem = campos.get(COLUNA_IMAGEM)
        col_nome = campos.get(COLUNA_NOME)
        if not col_nome or (not col_arte and not col_imagem):
            return []

        for indice, linha in enumerate(leitor):
            nome = (linha.get(col_nome) or "").strip()
            if not nome:
                continue

            arte = (linha.get(col_arte) or "").strip() if col_arte else ""
            imagem = (linha.get(col_imagem) or "").strip() if col_imagem else ""

            arquivo_arte = None
            if arte and not eh_url(arte):
                arquivo_arte = arte
            elif imagem and not eh_url(imagem):
                arquivo_arte = imagem

            if not arquivo_arte:
                continue

            if not (colecao_dir / arquivo_arte).is_file():
                print(f"   [AVISO] {nome}: arte '{arquivo_arte}' referenciada mas nao encontrada em disco. Pulando.")
                continue

            cartas.append({"id": f"{tipo}-{indice}", "nome": nome, "tipo": tipo})

    return cartas


def encontrar_csvs(colecao_dir: Path, filtro_arquivo: Optional[str]):
    csvs = sorted(colecao_dir.glob("*.csv"))
    if filtro_arquivo:
        csvs = [c for c in csvs if filtro_arquivo.lower() in c.stem.lower()]
    return csvs


def aguardar_servidor(url: str, tentativas: int = 30, intervalo: float = 0.3) -> bool:
    for _ in range(tentativas):
        try:
            urllib.request.urlopen(url, timeout=1)
            return True
        except Exception:
            time.sleep(intervalo)
    return False


def main():
    parser = argparse.ArgumentParser(description="Captura screenshots do preview de cartas (viewer/) para EXPANSIONS/<colecao>/")
    parser.add_argument("colecao", help="Nome da pasta dentro de LIB/ (ex.: 'Fratura do Multiverso')")
    parser.add_argument("--somente", help="Filtra so os CSVs cujo nome contem este texto (ex.: PERSONAGENS)")
    parser.add_argument("--porta", type=int, default=8000, help="Porta do servidor PHP embutido (padrao: 8000)")
    args = parser.parse_args()

    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("Playwright nao esta instalado. Rode:")
        print("  pip3 install playwright")
        print("  python3 -m playwright install chromium")
        sys.exit(1)

    colecao_dir = LIB_DIR / args.colecao
    if not colecao_dir.is_dir():
        print(f"Pasta nao encontrada: {colecao_dir}")
        sys.exit(1)

    csvs = encontrar_csvs(colecao_dir, args.somente)
    if not csvs:
        print("Nenhum CSV encontrado nessa colecao.")
        sys.exit(1)

    fila = []
    for csv_path in csvs:
        tipo = detectar_tipo(csv_path.name)
        if tipo is None:
            continue
        rel_path = f"{args.colecao}/{csv_path.name}"
        cartas = carregar_cartas_com_arte(csv_path, colecao_dir, tipo)
        for carta in cartas:
            fila.append({**carta, "rel_path": rel_path})

    if not fila:
        print("Nenhuma carta com arte existente em disco foi encontrada.")
        sys.exit(0)

    print(f"{len(fila)} carta(s) na fila de captura.\n")

    destino_dir = EXPANSIONS_DIR / args.colecao
    destino_dir.mkdir(parents=True, exist_ok=True)

    servidor = subprocess.Popen(
        ["php", "-S", f"localhost:{args.porta}", "-t", str(VIEWER_DIR)],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )

    base_url = f"http://localhost:{args.porta}"

    try:
        if not aguardar_servidor(base_url + "/index.php"):
            print("Nao foi possivel iniciar o servidor PHP embutido.")
            sys.exit(1)

        with sync_playwright() as p:
            navegador = p.chromium.launch()
            pagina = navegador.new_page(device_scale_factor=2)
            pagina.goto(base_url + "/index.php", wait_until="networkidle")

            arquivo_atual = None
            sucesso, falhas = 0, 0
            for i, item in enumerate(fila, start=1):
                print(f"[{i}/{len(fila)}] ({item['rel_path']}) {item['nome']}")

                if item["rel_path"] != arquivo_atual:
                    pagina.select_option("#file-select", item["rel_path"])
                    arquivo_atual = item["rel_path"]

                item_li = pagina.locator("#card-list li", has_text=item["nome"]).first
                try:
                    item_li.wait_for(state="visible", timeout=15_000)
                except Exception:
                    print("   [AVISO] Carta nao encontrada na lista renderizada. Pulando.")
                    falhas += 1
                    continue

                item_li.click()
                pagina.wait_for_selector("#card-preview img.card-preview__frame")
                pagina.wait_for_load_state("networkidle")
                pagina.wait_for_function(
                    "Array.from(document.querySelectorAll('#card-preview img'))"
                    ".every(img => img.complete && img.naturalWidth > 0)"
                )

                nome_arquivo = f"{item['tipo']}-{slugify(item['nome'])}.png"
                destino = destino_dir / nome_arquivo

                try:
                    pagina.locator("#card-preview").screenshot(path=str(destino))
                    print(f"   OK: salvo em {destino}")
                    sucesso += 1
                except Exception as e:
                    print(f"   [AVISO] Falha ao capturar: {e}")
                    falhas += 1

            navegador.close()

        print(f"\nConcluido: {sucesso} capturada(s), {falhas} falha(s) de {len(fila)}.")

    finally:
        servidor.terminate()
        servidor.wait(timeout=5)


if __name__ == "__main__":
    main()
