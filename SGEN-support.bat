@echo off
TITLE Iniciando SGEN-Support...
COLOR 0A
cls

echo ====================================================
echo      SGEN-Support - Iniciador Automatico
echo ====================================================
echo.

echo [1/4] Verificando instalacion de XAMPP...

:: Intentar detectar XAMPP en rutas comunes
if exist "C:\xampp\apache_start.bat" (
    set XAMPP_PATH=C:\xampp
) else (
    echo [!] No se encontro XAMPP en C:\xampp.
    echo Por favor, asegurese de tener XAMPP instalado.
    pause
    exit
)

echo [OK] XAMPP detectado en %XAMPP_PATH%
echo.

echo [2/4] Iniciando Apache y MySQL...
cd /d %XAMPP_PATH%
start /min apache_start.bat
start /min mysql_start.bat
echo [OK] Servicios iniciados en segundo plano.
echo.

echo [3/4] Esperando a que la base de datos este lista...
timeout /t 5 /nobreak >nul
echo [OK] Servicios listos.
echo.

echo [3.5/4] Sincronizando esquema de base de datos...
php scripts/sync_schema.php
echo [OK] Esquema actualizado.
echo.

echo [4/4] Abriendo el sistema en el navegador...
start http://localhost/sgen-support/public
echo.

echo ====================================================
echo      El sistema se ha iniciado correctamente.
echo      Puede cerrar esta ventana si lo desea.
echo ====================================================
timeout /t 5
exit