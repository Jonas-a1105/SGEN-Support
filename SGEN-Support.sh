#!/bin/bash

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}     SGEN-Support - Iniciador Automático (Linux)${NC}"
echo -e "${BLUE}====================================================${NC}"
echo ""

# 1. Intentar iniciar XAMPP (requiere sudo)
if [ -d "/opt/lampp" ]; then
    echo -e "${GREEN}[1/3] Detectado XAMPP en /opt/lampp${NC}"
    echo "Intentando iniciar servicios (puede pedir contraseña)..."
    sudo /opt/lampp/lampp start
else
    echo -e "${RED}[!] No se encontró XAMPP en /opt/lampp${NC}"
    echo "Usando servidor integrado de PHP como alternativa..."
    
    # Comprobar si PHP está instalado
    if ! command -v php &> /dev/null; then
        echo -e "${RED}[ERROR] PHP no está instalado. Instálalo para continuar.${NC}"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}[2/3] Esperando servicios...${NC}"
sleep 3

# 2. Abrir navegador
TARGET_URL="http://localhost/sgen-support/public"

# Si usamos PHP built-in server
if [ ! -d "/opt/lampp" ]; then
    TARGET_URL="http://localhost:8000"
    echo -e "${GREEN}[3/3] Iniciando servidor PHP en $TARGET_URL${NC}"
    
    # Abrir navegador antes de bloquear la terminal con el servidor
    xdg-open $TARGET_URL 2>/dev/null || open $TARGET_URL 2>/dev/null &
    
    cd public
    php -S localhost:8000
else
    echo -e "${GREEN}[3/3] Abriendo navegador en $TARGET_URL${NC}"
    xdg-open $TARGET_URL 2>/dev/null || open $TARGET_URL 2>/dev/null &
fi

echo ""
echo -e "${BLUE}¡Listo!${NC}"