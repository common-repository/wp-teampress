@setlocal enableextensions
@cd /d "%~dp0"
sass --watch main.scss:../tpress-style.css -r ./bourbon/lib/bourbon.rb
