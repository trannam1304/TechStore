@echo off
chcp 65001 >nul
title TechStore - Server Launcher
cd /d "%~dp0"

echo ========================================================
echo               TECHSTORE - SYSTEM LAUNCHER               
echo ========================================================
echo.

:: 1. Xac dinh thu muc cai dat XAMPP
set "XAMPP_DIR=C:\xampp"
if not exist "%XAMPP_DIR%" (
    if exist "D:\xampp" set "XAMPP_DIR=D:\xampp"
    if exist "E:\xampp" set "XAMPP_DIR=E:\xampp"
)

:: 2. Khoi dong MySQL neu chua chay
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if %ERRORLEVEL% equ 0 (
    echo [OK] MySQL server da san sang.
) else (
    echo [..] Dang khoi dong MySQL server...
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        ping 127.0.0.1 -n 3 >nul
        echo [OK] MySQL da khoi dong thanh cong.
    ) else (
        echo [!] Khong tim thay MySQL tai %XAMPP_DIR%\mysql.
    )
)

:: 3. Khoi dong Apache neu chua chay
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if %ERRORLEVEL% equ 0 (
    echo [OK] Apache server da san sang.
) else (
    echo [..] Dang khoi dong Apache server...
    if exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
        start "" /b "%XAMPP_DIR%\apache\bin\httpd.exe"
        ping 127.0.0.1 -n 3 >nul
        echo [OK] Apache da khoi dong thanh cong.
    ) else (
        echo [!] Khong tim thay Apache tai %XAMPP_DIR%\apache.
    )
)

:: 4. Kiem tra file .env
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [OK] Da tu dong tao file .env tu .env.example.
    )
)

:: 5. Xac dinh duong dan PHP
set "PHP_BIN=php"
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "%XAMPP_DIR%\php\php.exe" (
        set "PHP_BIN=%XAMPP_DIR%\php\php.exe"
    ) else (
        echo [!] Khong tim thay PHP trong he thong hoac XAMPP.
        pause
        exit /b 1
    )
)

:: 6. Tu dong mo trinh duyet
echo [..] Dang mo trinh duyet den TechStore...
start "" /b cmd /c "ping 127.0.0.1 -n 3 >nul & start http://localhost:8000"

:: 7. Khoi chay Web Server cho TechStore voi document root la thu muc public
echo.
echo ========================================================
echo   TechStore dang chay tai: http://localhost:8000
echo   Thu muc Document Root: %~dp0public
echo   Nhan to hop phim Ctrl + C de dung Web Server
echo ========================================================
echo.

"%PHP_BIN%" -S localhost:8000 -t public
