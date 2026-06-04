@echo off
REM ============================================================
REM  One-click local launcher for the meds_management_project
REM  - Starts Laragon's MySQL if it isn't already running
REM  - Serves the app from the PROJECT ROOT (so /public/... CSS
REM    and JS resolve) via serve-local.php
REM  Open http://127.0.0.1:8000 once it's up. Ctrl+C to stop.
REM ============================================================
setlocal
set "PHP=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
set "MYSQLD=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe"
set "MYSQLDATA=C:\laragon\data\mysql-8.4"

REM --- Start MySQL only if nothing is listening on 3306 ---
netstat -an | findstr ":3306 " >nul 2>&1
if errorlevel 1 (
  echo [start-local] Starting MySQL...
  start "MySQL" /B "%MYSQLD%" --datadir=%MYSQLDATA%
  timeout /t 4 /nobreak >nul
) else (
  echo [start-local] MySQL already running.
)

cd /d "%~dp0"
echo [start-local] Starting Vite (frontend) in a separate window - keep it open...
start "Vite (keep open)" cmd /k npm run dev
echo [start-local] App at http://127.0.0.1:8000   ^(Ctrl+C here stops the web server^)
start "" http://127.0.0.1:8000
"%PHP%" -S 127.0.0.1:8000 serve-local.php
