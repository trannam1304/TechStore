@echo off
chcp 65001 >nul
title TechStore - Server Stopper
cd /d "%~dp0"

echo ========================================================
echo               TECHSTORE - SYSTEM STOPPER                
echo ========================================================
echo.

echo [..] Dang tat PHP Development Server tren port 8000...
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING 2^>nul') do (
    taskkill /F /PID %%a >nul 2>&1
)

echo [OK] Da dung Web Server TechStore thanh cong.
echo.
pause
