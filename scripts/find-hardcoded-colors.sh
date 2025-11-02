#!/bin/bash

###############################################################################
# LEGO Framework - Hardcoded Colors Finder
###############################################################################
#
# Este script busca colores hardcodeados en archivos CSS que necesitan
# ser migrados al nuevo sistema de theming.
#
# Uso:
#   ./scripts/find-hardcoded-colors.sh
#   ./scripts/find-hardcoded-colors.sh --detailed
#   ./scripts/find-hardcoded-colors.sh --file components/App/MyComponent/styles.css
#
###############################################################################

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# Banner
echo -e "${BLUE}${BOLD}"
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     LEGO Framework - Hardcoded Colors Finder                  ║"
echo "║     Encuentra componentes que necesitan migración              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Directorios a buscar
SEARCH_DIRS="components assets"

# Función para contar colores hardcodeados en un archivo
count_hardcoded_colors() {
    local file="$1"
    local hex_count=$(grep -o "#[0-9a-fA-F]\{3,6\}" "$file" | wc -l | tr -d ' ')
    local name_count=$(grep -oE ":\s*(white|black|gray|grey|red|blue|green|yellow|orange|purple|pink)(?![a-z-])" "$file" | wc -l | tr -d ' ')
    local rgb_count=$(grep -oE "rgb\(|rgba\(" "$file" | wc -l | tr -d ' ')

    echo $((hex_count + name_count + rgb_count))
}

# Función para mostrar detalles de un archivo
show_file_details() {
    local file="$1"
    local count="$2"

    echo -e "\n${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BOLD}File:${NC} $file"
    echo -e "${BOLD}Issues:${NC} ${RED}$count hardcoded colors${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # Hex colors
    echo -e "\n${BOLD}Hex Colors:${NC}"
    grep -n "#[0-9a-fA-F]\{3,6\}" "$file" | head -10 | while IFS=: read -r line_num content; do
        # Highlight hex colors
        highlighted=$(echo "$content" | sed -E "s/(#[0-9a-fA-F]{3,6})/$(printf "${RED}")\\1$(printf "${NC}")/g")
        echo -e "  ${BLUE}Line $line_num:${NC} $highlighted"
    done

    # Named colors
    local named=$(grep -n -E ":\s*(white|black|gray|grey|red|blue|green|yellow|orange|purple|pink)(?![a-z-])" "$file" | wc -l | tr -d ' ')
    if [ "$named" -gt 0 ]; then
        echo -e "\n${BOLD}Named Colors:${NC}"
        grep -n -E ":\s*(white|black|gray|grey|red|blue|green|yellow|orange|purple|pink)(?![a-z-])" "$file" | head -10 | while IFS=: read -r line_num content; do
            echo -e "  ${BLUE}Line $line_num:${NC} $content"
        done
    fi

    # RGB/RGBA colors
    local rgb=$(grep -n -E "rgb\(|rgba\(" "$file" | wc -l | tr -d ' ')
    if [ "$rgb" -gt 0 ]; then
        echo -e "\n${BOLD}RGB/RGBA Colors:${NC}"
        grep -n -E "rgb\(|rgba\(" "$file" | head -10 | while IFS=: read -r line_num content; do
            echo -e "  ${BLUE}Line $line_num:${NC} $content"
        done
    fi
}

# Parsear argumentos
DETAILED=false
SPECIFIC_FILE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --detailed|-d)
            DETAILED=true
            shift
            ;;
        --file|-f)
            SPECIFIC_FILE="$2"
            shift 2
            ;;
        --help|-h)
            echo "Uso:"
            echo "  $0                  # Reporte resumido"
            echo "  $0 --detailed       # Reporte detallado con líneas de código"
            echo "  $0 --file PATH      # Analizar archivo específico"
            echo ""
            echo "Opciones:"
            echo "  -d, --detailed      Mostrar líneas de código con colores hardcodeados"
            echo "  -f, --file PATH     Analizar solo un archivo específico"
            echo "  -h, --help          Mostrar esta ayuda"
            exit 0
            ;;
        *)
            echo -e "${RED}Opción desconocida: $1${NC}"
            exit 1
            ;;
    esac
done

# Si se especificó un archivo
if [ -n "$SPECIFIC_FILE" ]; then
    if [ ! -f "$SPECIFIC_FILE" ]; then
        echo -e "${RED}Error: Archivo no encontrado: $SPECIFIC_FILE${NC}"
        exit 1
    fi

    count=$(count_hardcoded_colors "$SPECIFIC_FILE")
    show_file_details "$SPECIFIC_FILE" "$count"
    exit 0
fi

# Buscar todos los archivos CSS
echo -e "${BOLD}Buscando archivos CSS...${NC}\n"

declare -a problem_files=()
declare -a problem_counts=()
total_issues=0
total_files=0
clean_files=0

# Buscar en directorios
for dir in $SEARCH_DIRS; do
    if [ ! -d "$dir" ]; then
        continue
    fi

    while IFS= read -r file; do
        total_files=$((total_files + 1))
        count=$(count_hardcoded_colors "$file")

        if [ "$count" -gt 0 ]; then
            problem_files+=("$file")
            problem_counts+=("$count")
            total_issues=$((total_issues + count))

            if [ "$DETAILED" = true ]; then
                show_file_details "$file" "$count"
            else
                echo -e "${RED}✗${NC} $file ${YELLOW}($count issues)${NC}"
            fi
        else
            clean_files=$((clean_files + 1))
            if [ "$DETAILED" = true ]; then
                echo -e "${GREEN}✓${NC} $file ${GREEN}(clean)${NC}"
            fi
        fi
    done < <(find "$dir" -name "*.css" -type f)
done

# Reporte final
echo -e "\n${BLUE}${BOLD}"
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                      REPORTE FINAL                             ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${BOLD}Estadísticas:${NC}"
echo -e "  Total de archivos CSS: ${BLUE}$total_files${NC}"
echo -e "  Archivos limpios: ${GREEN}$clean_files${NC} ✓"
echo -e "  Archivos con problemas: ${RED}${#problem_files[@]}${NC} ✗"
echo -e "  Total de colores hardcodeados: ${RED}$total_issues${NC}"

if [ ${#problem_files[@]} -gt 0 ]; then
    echo -e "\n${BOLD}Top 10 archivos con más problemas:${NC}"

    # Crear arrays paralelos y ordenar
    for i in "${!problem_files[@]}"; do
        echo "${problem_counts[$i]} ${problem_files[$i]}"
    done | sort -rn | head -10 | while read count file; do
        echo -e "  ${RED}$count${NC} issues → ${YELLOW}$file${NC}"
    done

    echo -e "\n${YELLOW}${BOLD}Próximos pasos:${NC}"
    echo -e "  1. Revisa la documentación: ${BLUE}docs/THEMING_SYSTEM_GUIDE.md${NC}"
    echo -e "  2. Mira el ejemplo: ${BLUE}docs/MIGRATION_EXAMPLE.md${NC}"
    echo -e "  3. Migra los archivos usando variables CSS"
    echo -e "  4. Ejecuta de nuevo este script para verificar"

    echo -e "\n${BOLD}Para ver detalles de un archivo específico:${NC}"
    echo -e "  ${BLUE}$0 --file ${problem_files[0]}${NC}"

    echo -e "\n${BOLD}Para ver todos los detalles:${NC}"
    echo -e "  ${BLUE}$0 --detailed${NC}"
else
    echo -e "\n${GREEN}${BOLD}¡Felicidades! No se encontraron colores hardcodeados.${NC}"
    echo -e "${GREEN}Todos los componentes usan el sistema de theming correctamente.${NC} ✓"
fi

echo ""

# Exit code
if [ ${#problem_files[@]} -gt 0 ]; then
    exit 1
else
    exit 0
fi
