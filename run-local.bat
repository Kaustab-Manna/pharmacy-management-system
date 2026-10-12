@echo off
title INFOSOF Pharmacy Management System - Localhost Server
echo =====================================================================
echo  INFOSOF TECHNOLOGIES - Pharmacy Management Software v3.0
echo  Starting Localhost Development Server...
echo =====================================================================
echo.
echo  Access URL   : http://localhost:8080
echo  Default Login:
echo    Username   : admin
echo    Password   : admin123
echo    (Login credentials for all roles available on login page)
echo.
echo  Press Ctrl+C in this window to stop the server.
echo =====================================================================
echo.
php -S localhost:8080 -t public
pause
