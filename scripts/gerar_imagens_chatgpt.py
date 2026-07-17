#!/usr/bin/env python3
"""
Le os CSVs de uma colecao dentro de LIB/, extrai a coluna "Prompt Arte"
de cada carta e usa o ChatGPT (via navegador, sessao logada manualmente)
para gerar a imagem correspondente, salvando-a ao lado do CSV com o nome
indicado na coluna "Arte".

Uso:
    python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso"
    python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --pular-existentes
    python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --somente PERSONAGENS
    python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --somente ITENS --pular-existentes
Requer Playwright:
    pip3 install playwright
    python3 -m playwright install chromium

Na primeira execucao (ou quando a sessao expirar), uma janela do Chromium
abre em chatgpt.com pedindo login. Faca o login manualmente e pressione
ENTER no terminal para o script continuar. A sessao fica salva em
scripts/.chatgpt-profile/ e no futuro deve pular o login.
"""

import argparse
import csv
import sys
import time
from pathlib import Path
from typing import Optional

LIB_DIR = Path(__file__).resolve().parent.parent / "LIB"
PROFILE_DIR = Path(__file__).resolve().parent / ".chatgpt-profile"

COLUNA_PROMPT = "Prompt Arte"
COLUNA_ARQUIVO = "Arte"
COLUNA_NOME = "Nome"

CHATGPT_URL = "https://chatgpt.com/"
TIMEOUT_GERACAO_MS = 180_000  # 3 minutos por imagem
TIMEOUT_LOGIN_MS = 5_000


def carregar_cartas(csv_path: Path):
    """Le um CSV ';' e retorna as linhas que tem Prompt Arte e Arte preenchidos."""
    cartas = []
    with csv_path.open("r", encoding="utf-8-sig", newline="") as f:
        leitor = csv.DictReader(f, delimiter=";")
        campos = {c.strip(): c for c in (leitor.fieldnames or [])}
        if COLUNA_PROMPT not in campos or COLUNA_ARQUIVO not in campos:
            return []
        for linha in leitor:
            prompt = (linha.get(campos[COLUNA_PROMPT]) or "").strip()
            arquivo = (linha.get(campos[COLUNA_ARQUIVO]) or "").strip()
            nome = (linha.get(campos.get(COLUNA_NOME, ""), "") or "").strip()
            if prompt and arquivo:
                cartas.append({"nome": nome or arquivo, "prompt": prompt, "arquivo": arquivo})
    return cartas


def encontrar_csvs(colecao_dir: Path, filtro_arquivo: Optional[str]):
    csvs = sorted(colecao_dir.glob("*.csv"))
    if filtro_arquivo:
        csvs = [c for c in csvs if filtro_arquivo.lower() in c.stem.lower()]
    return csvs


def aguardar_login(page):
    """Espera o usuario logar manualmente no ChatGPT, com confirmacao no terminal."""
    print("\n>> Verifique a janela do navegador.")
    print(">> Se estiver pedindo login, faca login manualmente no ChatGPT.")
    input(">> Quando estiver logado e pronto na tela de conversa, pressione ENTER aqui... ")


def esta_logado(page) -> bool:
    try:
        page.wait_for_selector(
            "#prompt-textarea, textarea[placeholder]",
            timeout=TIMEOUT_LOGIN_MS,
        )
        return True
    except Exception:
        return False


def enviar_prompt_e_baixar(page, prompt: str, destino: Path) -> bool:
    """Envia um prompt de geracao de imagem no chat e salva o resultado em destino."""
    campo = page.locator("#prompt-textarea, textarea[placeholder]").first
    campo.click()
    campo.fill(prompt)
    campo.press("Enter")

    print("   Aguardando geracao da imagem...")
    seletor_imagem = "article img[src*='oaiusercontent']"
    try:
        page.wait_for_selector(seletor_imagem, timeout=TIMEOUT_GERACAO_MS)
    except Exception:
        print("   [AVISO] Tempo esgotado esperando a imagem. Pulando esta carta.")
        return False

    # Da uma folga para o ChatGPT terminar de renderizar/estabilizar a imagem.
    time.sleep(3)

    imagens = page.locator(seletor_imagem)
    ultima = imagens.nth(imagens.count() - 1)

    # O menu de contexto nativo do navegador nao e controlavel via Playwright;
    # baixamos a imagem diretamente pela URL do <img> usando o contexto de
    # requisicoes da propria pagina (reaproveita cookies/sessao).
    src = ultima.get_attribute("src")
    if not src:
        print("   [AVISO] Nao foi possivel obter a URL da imagem gerada.")
        return False

    resposta = page.request.get(src)
    if not resposta.ok:
        print(f"   [AVISO] Falha ao baixar imagem ({resposta.status}).")
        return False

    destino.write_bytes(resposta.body())
    return True


def iniciar_nova_conversa(page):
    try:
        page.goto(CHATGPT_URL, wait_until="domcontentloaded")
        page.wait_for_selector("#prompt-textarea, textarea[placeholder]", timeout=15_000)
    except Exception:
        pass


def main():
    parser = argparse.ArgumentParser(description="Gera imagens de cartas via ChatGPT a partir dos prompts em LIB/<colecao>/*.csv")
    parser.add_argument("colecao", help="Nome da pasta dentro de LIB/ (ex.: 'Fratura do Multiverso')")
    parser.add_argument("--somente", help="Filtra so os CSVs cujo nome contem este texto (ex.: PERSONAGENS)")
    parser.add_argument("--pular-existentes", action="store_true", help="Nao regera imagens cujo arquivo de destino ja existe")
    parser.add_argument("--headless", action="store_true", help="Roda o navegador sem interface grafica (nao recomendado para o login inicial)")
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
        print("Nenhum CSV encontrado (ou nenhum tem colunas 'Prompt Arte' / 'Arte').")
        sys.exit(1)

    fila = []
    for csv_path in csvs:
        cartas = carregar_cartas(csv_path)
        for carta in cartas:
            destino = colecao_dir / carta["arquivo"]
            if args.pular_existentes and destino.exists():
                continue
            fila.append({**carta, "csv": csv_path.name, "destino": destino})

    if not fila:
        print("Nada para gerar (todas as imagens ja existem ou nenhum prompt encontrado).")
        sys.exit(0)

    print(f"{len(fila)} imagem(ns) na fila de geracao.\n")

    PROFILE_DIR.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        contexto = p.chromium.launch_persistent_context(
            user_data_dir=str(PROFILE_DIR),
            headless=args.headless,
            accept_downloads=True,
        )
        page = contexto.pages[0] if contexto.pages else contexto.new_page()
        page.goto(CHATGPT_URL, wait_until="domcontentloaded")

        if not esta_logado(page):
            aguardar_login(page)
            if not esta_logado(page):
                print("Nao foi possivel confirmar o login. Encerrando.")
                contexto.close()
                sys.exit(1)

        sucesso, falhas = 0, 0
        for i, item in enumerate(fila, start=1):
            print(f"[{i}/{len(fila)}] ({item['csv']}) {item['nome']} -> {item['destino'].name}")

            iniciar_nova_conversa(page)

            ok = enviar_prompt_e_baixar(page, item["prompt"], item["destino"])
            if ok:
                print(f"   OK: salvo em {item['destino']}")
                sucesso += 1
            else:
                falhas += 1

            if i < len(fila):
                time.sleep(2)

        contexto.close()

    print(f"\nConcluido: {sucesso} gerada(s), {falhas} falha(s) de {len(fila)}.")


if __name__ == "__main__":
    main()
