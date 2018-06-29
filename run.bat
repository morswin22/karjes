@echo off
IF "%1"=="--mysql" start ..\mysql_start.bat 
IF "%1"=="-m" start ..\mysql_start.bat 
code . && ..\apache_start.bat 