# FavStore API

![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Docker](https://img.shields.io/badge/Docker-Enabled-blue)
![Tests](https://github.com/seu-usuario/favstore-api/actions/workflows/tests.yml/badge.svg)

API RESTful desenvolvida em **Laravel 12** com **PHP 8.4**, utilizando **JWT** para autenticação.  
Este projeto implementa a funcionalidade de **produtos favoritos** dos usuários, integrando-se com sistemas externos.

---

## 🚀 Tecnologias

- [Laravel 12](https://laravel.com/)
- [PHP 8.4](https://www.php.net/)
- [Laravel Sail](https://laravel.com/docs/master/sail) (Docker)
- [PostgreSQL](https://www.postgresql.org/)
- [JWT Auth (tymon/jwt-auth)](https://github.com/tymondesigns/jwt-auth)
- [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) para documentação da API

---

## ⚙️ Requisitos

- Docker e Docker Compose instalados
- PHP >= 8.4 (caso não use Sail)
- Composer

---

## 🔧 Instalação

Clone o repositório:

```bash
git clone https://github.com/seu-usuario/favstore-api.git
cd favstore-api
```

Copie o arquivo de configuração:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
./vendor/bin/sail artisan key:generate
```

Instale as dependências:

```bash
composer install
```

---

## 🐳 Executando com Docker (Sail)

Suba os containers:

```bash
./vendor/bin/sail up -d
```

Rode as migrations:

```bash
./vendor/bin/sail artisan migrate --seed
```

---

## 📚 Documentação da API

A documentação está disponível via Swagger em:

```
http://localhost/api/documentation
```

---

## 🔑 Autenticação

A API utiliza **JWT**.  
Após login/registro, o cliente deve enviar o token no header:

```http
Authorization: Bearer <token>
```

---

## 🧪 Testes

Para rodar os testes:

```bash
./vendor/bin/sail artisan test
```

Para rodar apenas um teste específico:

```bash
./vendor/bin/sail artisan test --filter=AuthTest
```

---

## 📂 Estrutura do Projeto

```
app/
 ├── Http/
 │   ├── Controllers/Api/   # Controllers da API
 │   ├── Requests/          # Validações
 │   ├── Resources/         # API Resources
 │   └── Services/          # Regras de negócio
 ├── Models/                # Models (Eloquent)
 └── ...
routes/
 └── api.php                # Rotas da API
tests/
 ├── Feature/               # Testes de integração (API)
 └── Unit/                  # Testes unitários
```

---

## 📌 Endpoints principais

- **POST** `/api/auth/register` – Registro de usuário  
- **POST** `/api/auth/login` – Login  
- **POST** `/api/auth/logout` – Logout  
- **POST** `/api/auth/refresh` – Renovar token  
- **GET** `/api/auth/me` – Perfil do usuário autenticado  

---

## 👨‍💻 Autor

Desenvolvido por **Caio Cesar Lorenzon Dias**  
🔗 [GitHub](https://github.com/CaioCLDias) • [LinkedIn](https://www.linkedin.com/in/caio-cesar-lorenzon-dias/)
