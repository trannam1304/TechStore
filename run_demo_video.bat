@echo off
chcp 65001 > nul
title TechStore - Auto Demo Video Recording Runner
echo ======================================================================
echo    TECHSTORE - KỊCH BẢN VIDEO DEMO TỰ ĐỘNG CHIA ĐÔI MÀN HÌNH
echo ======================================================================
echo.
echo [1] Kịch bản sẽ tự động mở 2 cửa sổ Chrome song song:
echo     - Nửa bên trái: Giao diện người dùng TechStore (Khách hàng & Admin)
echo     - Nửa bên phải: Bảng điều khiển API Live Inspector (401, 403, 200, JWT)
echo.
echo [2] Mọi thao tác click, đăng nhập, chặn quyền và hiển thị API
echo     sẽ chạy đồng bộ 100%% hoàn toàn tự động!
echo.
echo [3] Bạn hãy chuẩn bị sẵn phần mềm quay màn hình (OBS hoặc Win + Alt + R).
echo.
echo ===> Sau khi bấm phím bất kỳ, hệ thống sẽ ĐẾM NGƯỢC 10 GIÂY trước khi mở trình duyệt! <===
echo.
pause
python tests/auto_demo_video.py
echo.
echo Đã hoàn tất kịch bản quay video demo!
pause

