@echo off
REM Set plugin name
set PLUGIN_NAME=wpdispatchforge

REM Create main plugin folder
mkdir %PLUGIN_NAME%

REM Create admin subfolders
mkdir %PLUGIN_NAME%\admin
mkdir %PLUGIN_NAME%\admin\css
mkdir %PLUGIN_NAME%\admin\js
mkdir %PLUGIN_NAME%\admin\partials
mkdir %PLUGIN_NAME%\admin\templates

REM Create includes subfolders
mkdir %PLUGIN_NAME%\includes
mkdir %PLUGIN_NAME%\includes\api
mkdir %PLUGIN_NAME%\includes\classes
mkdir %PLUGIN_NAME%\includes\helpers
mkdir %PLUGIN_NAME%\includes\interfaces
mkdir %PLUGIN_NAME%\includes\settings

REM Create assets subfolders
mkdir %PLUGIN_NAME%\assets
mkdir %PLUGIN_NAME%\assets\css
mkdir %PLUGIN_NAME%\assets\js
mkdir %PLUGIN_NAME%\assets\images

REM Create other main folders
mkdir %PLUGIN_NAME%\languages
mkdir %PLUGIN_NAME%\tests

REM Create main plugin files
echo /* Main Plugin File */ > %PLUGIN_NAME%\wpdispatchforge.php
echo /* Uninstall Script */ > %PLUGIN_NAME%\uninstall.php
echo /* Plugin README */ > %PLUGIN_NAME%\README.md

REM Create admin files
echo /* Admin Panel Class */ > %PLUGIN_NAME%\admin\class-wpdf-admin.php

REM Create includes files
echo /* Core Plugin Class */ > %PLUGIN_NAME%\includes\class-wpdispatchforge.php

REM Create Git repository
cd %PLUGIN_NAME%
git init
echo "# WPDispatchForge Plugin" > README.md
git add .
git commit -m "Initial commit with folder structure"

@echo Folder structure and Git repository for %PLUGIN_NAME% have been created!
pause
