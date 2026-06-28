@echo off
REM Script de instalación automática para Sistema Admin EEST2 (Windows)
REM Este script configura automáticamente el entorno de desarrollo en Windows

echo 🚀 Instalando Sistema Admin EEST2...
echo ==================================

REM Verificar si estamos en el directorio correcto
if not exist "composer.json" (
    echo ✗ No se encontró composer.json. Ejecuta este script desde la raíz del proyecto.
    pause
    exit /b 1
)

REM 1. Verificar dependencias
echo.
echo 📋 Verificando dependencias...

REM Verificar PHP
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ PHP encontrado
) else (
    echo ✗ PHP no está instalado. Instala XAMPP o PHP 8.1+
    pause
    exit /b 1
)

REM Verificar Composer
composer --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ Composer encontrado
) else (
    echo ✗ Composer no está instalado. Instala Composer desde https://getcomposer.org/
    pause
    exit /b 1
)

REM 2. Configurar archivo .env
echo.
echo ⚙️ Configurando variables de entorno...

if not exist ".env" (
    if exist "env.example" (
        copy env.example .env >nul
        echo ✓ Archivo .env creado desde env.example
        
        REM Configurar valores por defecto para XAMPP
        powershell -Command "(Get-Content .env) -replace 'DB_HOST=database', 'DB_HOST=localhost' | Set-Content .env"
        powershell -Command "(Get-Content .env) -replace 'DB_USER=sistema_admin', 'DB_USER=root' | Set-Content .env"
        powershell -Command "(Get-Content .env) -replace 'DB_PASS=SecurePassword123!', 'DB_PASS=' | Set-Content .env"
        echo ✓ Configurado para XAMPP (Windows)
        echo ⚠ IMPORTANTE: Edita el archivo .env con tus credenciales de base de datos
    ) else (
        echo ✗ No se encontró env.example. Creando .env básico...
        (
            echo # Configuración de Base de Datos
            echo DB_HOST=localhost
            echo DB_PORT=3306
            echo DB_NAME=sistema_admin_eest2
            echo DB_USER=root
            echo DB_PASS=
            echo.
            echo # Configuración de Aplicación
            echo APP_ENV=development
            echo APP_DEBUG=true
            echo APP_KEY=your-secret-key-here
            echo.
            echo # Configuración de Seguridad
            echo SESSION_LIFETIME=120
            echo MAX_LOGIN_ATTEMPTS=5
        ) > .env
        echo ✓ Archivo .env básico creado
    )
) else (
    echo ✓ Archivo .env ya existe
)

REM 3. Instalar dependencias de Composer
echo.
echo 📦 Instalando dependencias de Composer...

if exist "composer.json" (
    composer install --no-dev --optimize-autoloader
    echo ✓ Dependencias de Composer instaladas
) else (
    echo ✗ No se encontró composer.json
    pause
    exit /b 1
)

REM 4. Crear directorios necesarios
echo.
echo 📁 Configurando estructura de directorios...

if not exist "logs" mkdir logs
if not exist "backups" mkdir backups
if not exist "public\logs" mkdir public\logs
if not exist "admin\logs" mkdir admin\logs

REM Crear archivos .gitkeep
echo. > logs\.gitkeep
echo. > backups\.gitkeep
echo. > public\logs\.gitkeep
echo. > admin\logs\.gitkeep

echo ✓ Estructura de directorios configurada

REM 5. Verificar instalación
echo.
echo 🔍 Verificando instalación...

REM Verificar archivos críticos
set "critical_files=index.php config\database.php src\EnvLoader.php .env"
for %%f in (%critical_files%) do (
    if exist "%%f" (
        echo ✓ Archivo crítico encontrado: %%f
    ) else (
        echo ✗ Archivo crítico faltante: %%f
    )
)

REM 6. Mostrar información final
echo.
echo 🎉 ¡Instalación completada!
echo ==========================
echo.
echo 📋 Próximos pasos:
echo 1. Edita el archivo .env con tus credenciales de base de datos
echo 2. Importa la base de datos: mysql -u root -p sistema_admin_eest2 ^< database\sistema_admin_eest2.sql
echo 3. Accede al sistema en: http://localhost/SistemaAdmin
echo.
echo 👤 Usuarios por defecto:
echo    Admin: admin / admin123
echo    Director: director / director123
echo    Preceptor: preceptor / preceptor123
echo.
echo ⚠  IMPORTANTE: Cambia las contraseñas después del primer login
echo.
echo 📚 Documentación:
echo    - README.md: Información general
echo    - docs\: Documentación completa
echo    - Sistema integrado: Accede desde el login
echo.

echo ✓ Instalación exitosa! 🚀
pause
