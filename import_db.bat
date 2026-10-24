@echo off
chcp 65001 >nul
title TechStore - MySQL Database Auto Importer
cd /d "%~dp0"

echo ========================================================
echo           TECHSTORE - TỰ ĐỘNG IMPORT CSDL MYSQL          
echo ========================================================
echo.

:: 1. Xác định PHP Binary (ưu tiên PHP hệ thống, sau đó đến XAMPP)
set "PHP_BIN=php"
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    set "XAMPP_DIR=C:\xampp"
    if not exist "%XAMPP_DIR%" (
        if exist "D:\xampp" set "XAMPP_DIR=D:\xampp"
        if exist "E:\xampp" set "XAMPP_DIR=E:\xampp"
    )
    if exist "%XAMPP_DIR%\php\php.exe" (
        set "PHP_BIN=%XAMPP_DIR%\php\php.exe"
    )
)

:: 2. Kiểm tra MySQL Container hoặc MySQL Service
echo [..] Đang kiểm tra trạng thái MySQL...
docker ps --format "{{.Names}}" 2>nul | findstr /i "techstore_mysql" >nul
if %ERRORLEVEL% equ 0 (
    echo [OK] Phát hiện MySQL đang chạy qua Docker container (techstore_mysql).
) else (
    tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
    if %ERRORLEVEL% equ 0 (
        echo [OK] Phát hiện MySQL server đang chạy cục bộ (mysqld.exe).
    ) else (
        echo [!] Cảnh báo: Chưa phát hiện MySQL đang chạy!
        echo [*] Đang thử khởi động Docker container MySQL...
        where docker-compose >nul 2>nul
        if %ERRORLEVEL% equ 0 (
            docker-compose up -d mysql
        ) else (
            where docker >nul 2>nul
            if %ERRORLEVEL% equ 0 (
                docker compose up -d mysql
            ) else (
                echo [!] Không tìm thấy Docker hoặc MySQL cục bộ.
            )
        )
        echo [*] Đang chờ MySQL khởi động sẵn sàng (10 giây)...
        ping 127.0.0.1 -n 11 >nul
    )
)

echo.
:: 3. Thực thi import qua script PHP PDO (Đảm bảo chạy được trên 100%% máy Windows)
"%PHP_BIN%" -v >nul 2>nul
if %ERRORLEVEL% equ 0 (
    echo [*] Thực thi import CSDL thông qua PDO Migration script...
    echo.
    "%PHP_BIN%" "%~dp0database\import.php"
    goto :FINISHED
)

:: 4. Fallback: Nếu không tìm thấy PHP, thử import qua docker exec
docker ps --format "{{.Names}}" 2>nul | findstr /i "techstore_mysql" >nul
if %ERRORLEVEL% equ 0 (
    echo [*] Thực thi import trực tiếp vào Docker MySQL container...
    docker exec -i techstore_mysql mysql -u root techstore < "%~dp0database\schema.sql"
    docker exec -i techstore_mysql mysql -u root techstore < "%~dp0database\seed.sql"
    echo [OK] Đã import schema.sql và seed.sql vào container thành công!
    goto :FINISHED
)

echo [ERROR] Không tìm thấy PHP hoặc công cụ thực thi SQL! Vui lòng cài đặt PHP hoặc khởi động Docker.

:FINISHED
echo.
echo ========================================================
echo Quá trình hoàn tất. Nhấn phím bất kỳ để đóng cửa sổ.
echo ========================================================
pause >nul
