@echo off

set xampp=C:\xampp
set PASTA=C:\xampp\htdocs\precio
set BANCO=C:\xampp\mysql\data\precio_db
set pastaAtual=%cd%

cd %xampp%

start /min "" "apache\bin\httpd.exe"
start /min "" "mysql\bin\mysqld" --defaults-file=mysql\bin\my.ini --standalone

echo Criando o projeto > log.txt

if exist "%PASTA%" (
    echo A pasta ja existe.
) else (
    echo A pasta nao existe. Criando agora...
    cd C:\xampp\htdocs
    git clone https://github.com/vinimarc/precio
    
    cd %pastaAtual%
    echo Pasta criada com sucesso! >> log.txt
    date /t >> log.txt
    time /t >> log.txt	
)

if exist "%BANCO%" (
    echo O banco ja existe.
) else (
    echo Esperando o SQL iniciar.

    timeout /t 10 /nobreak > nul

    echo Criando banco de dados
    cd C:\xampp\mysql\bin
    mysql.exe -h localhost -P 3306 -u root < C:\xampp\htdocs\precio\back-end\setup.sql
    echo Banco criado
)

start http://localhost:8080/precio/front-end/

timeout 10
