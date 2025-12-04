@echo off
TITLE Iniciando SGEN-Support...
echo Verificando servicios de XAMPP...

:: 1. Ir a la ruta de XAMPP (Ajusta si tu XAMPP no está en C:\xampp)
cd /d C:\xampp

:: 2. Iniciar Apache y MySQL de forma silenciosa
:: El comando 'start /min' minimiza la ventana negra que se abre
start /min apache_start.bat
start /min mysql_start.bat

echo Servicios iniciados. Abriendo el sistema...

:: 3. Esperar 4 segundos para asegurar que la base de datos esté lista
timeout /t 4 /nobreak >nul

:: 4. Abrir el navegador predeterminado en tu sistema
:: Nota: Ajusté la URL basándome en la carpeta que subiste
start http://localhost/sgen-support/public

exit