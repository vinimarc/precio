# Precio — Setup

## Requisitos
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.4+
- Servidor web (Apache/Nginx) ou `php -S localhost:8000`

## Instalação

### 1. Banco de dados
Execute o script SQL para criar o banco e a tabela:
```bash
mysql -u root -p < setup.sql
```

### 2. Configuração do banco
Edite `includes/db.php` com suas credenciais:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'precio_db');
define('DB_USER', 'root');       // seu usuário MySQL
define('DB_PASS', '');           // sua senha MySQL
```

### 3. Iniciar o servidor
```bash
cd precio
php -S localhost:8000
```
Acesse: http://localhost:8000

## Estrutura de arquivos
```
precio/
├── index.php          ← Página de login / cadastro
├── home.php           ← Home protegida (requer login)
├── api.php            ← Endpoints: login, register, logout
├── logout.php         ← Atalho de logout via GET
├── setup.sql          ← Script de criação do banco
├── css/
│   └── style.css      ← Estilos globais
└── includes/
    ├── db.php          ← Conexão PDO
    └── auth.php        ← Funções de sessão
```

## Segurança implementada
- Senhas com `password_hash()` (bcrypt) e `password_verify()`
- PDO com prepared statements (proteção contra SQL injection)
- `session_regenerate_id(true)` no login (proteção contra session fixation)
- Redirecionamento automático: usuários não autenticados → index.php
