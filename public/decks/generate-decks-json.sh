#!/bin/bash

# Script para gerar decks.json a partir dos arquivos .md

cat > decks.json << 'JSON'
{
  "decks": [
JSON

# Função para extrair informações do arquivo
extract_deck_info() {
    local file="$1"
    local filename=$(basename "$file" .md)
    
    # Extrai o título (primeira linha que começa com #)
    local title=$(head -1 "$file" | sed 's/^# \[.*\] //' | sed 's/ — /|/')
    
    # Extrai o estilo (MONO, DUAL, TRIPLE, RAINBOW)
    local style=""
    if [[ "$filename" == MONO-* ]]; then
        style="MONO"
    elif [[ "$filename" == DUAL-* ]]; then
        style="DUAL"
    elif [[ "$filename" == TRIPLE-* ]]; then
        style="TRIPLE"
    elif [[ "$filename" == RAINBOW-* ]]; then
        style="RAINBOW"
    fi
    
    # Extrai os tipos da combinação no nome do arquivo
    local types=""
    if [[ "$filename" =~ MONO-([A-Za-z]+)- ]]; then
        local tipo="${BASH_REMATCH[1]}"
        case "$tipo" in
            Silenteia|Magia) types="Magia" ;;
            TecSci) types="TecSci" ;;
            Fisico|Físico) types="Físico" ;;
            Divino) types="Divino" ;;
            Cosmico|Cósmico) types="Cósmico" ;;
            *) types="$tipo" ;;
        esac
    else
        # Para DUAL, TRIPLE, RAINBOW - extrai do filename
        if [[ "$filename" =~ DUAL-([^-]+)-([^-]+)- ]] || [[ "$filename" =~ DUAL-([^-]+)\+([^-]+)- ]]; then
            local t1="${BASH_REMATCH[1]}"
            local t2="${BASH_REMATCH[2]}"
            types="${t1}|${t2}"
        elif [[ "$filename" =~ TRIPLE ]]; then
            types="Triple-Type"
        elif [[ "$filename" =~ RAINBOW ]]; then
            types="Rainbow"
        fi
    fi
    
    echo "{\"file\":\"$filename.md\",\"title\":\"$title\",\"style\":\"$style\",\"types\":\"$types\"}"
}

# Loop através de todos os arquivos .md ordenados
first=true
for file in $(ls -1 *.md | sort); do
    if [ "$first" = true ]; then
        first=false
    else
        echo ","
    fi
    extract_deck_info "$file"
done

cat >> decks.json << 'JSON'

  ]
}
JSON

echo "decks.json gerado com sucesso!"
}

extract_deck_info() {
    local file="$1"
    local filename=$(basename "$file" .md)
    
    # Extrai o título (primeira linha que começa com #)
    local title=$(head -1 "$file" | sed 's/^# \[.*\] //' | sed 's/ — /|/')
    
    # Extrai o estilo (MONO, DUAL, TRIPLE, RAINBOW)
    local style=""
    if [[ "$filename" == MONO-* ]]; then
        style="MONO"
    elif [[ "$filename" == DUAL-* ]]; then
        style="DUAL"
    elif [[ "$filename" == TRIPLE-* ]]; then
        style="TRIPLE"
    elif [[ "$filename" == RAINBOW-* ]]; then
        style="RAINBOW"
    fi
    
    # Extrai os tipos
    local types="Misto"
    if [[ "$filename" =~ MONO-([A-Za-z]+)- ]]; then
        local tipo="${BASH_REMATCH[1]}"
        case "$tipo" in
            Silenteia|Magia) types="Magia" ;;
            TecSci) types="TecSci" ;;
            Fisico) types="Físico" ;;
            Divino) types="Divino" ;;
            Cosmico) types="Cósmico" ;;
            *) types="$tipo" ;;
        esac
    fi
    
    echo "{\"file\":\"$filename.md\",\"title\":\"$title\",\"style\":\"$style\",\"types\":\"$types\"}"
}

# Gerar JSON
cat > decks.json << 'JSON'
{
  "decks": [
JSON

first=true
for file in $(ls -1 *.md | sort); do
    if [ "$first" = true ]; then
        first=false
    else
        echo ","
    fi
    extract_deck_info "$file"
done >> decks.json

echo "  ]" >> decks.json
echo "}" >> decks.json

echo "✓ decks.json gerado!"
