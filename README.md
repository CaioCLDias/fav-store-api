# 🛍️ FavStore API

![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Docker](https://img.shields.io/badge/Docker-Enabled-blue)

API RESTful desenvolvida em **Laravel 12** com **PHP 8.4**, utilizando **JWT** para autenticação.  
Este projeto implementa a funcionalidade de **produtos favoritos** dos usuários, integrando-se com sistemas externos.

---

## 📋 Resumo do Projeto

API backend completa para gerenciamento de produtos favoritos com foco na segurança e isolamento de dados por usuário. Desenvolvida seguindo as melhores práticas do Laravel com arquitetura limpa e escalável, integrando com a FakeStore API para consulta de produtos.

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
- Git
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

Instale as dependências:

```bash
composer install
```

Configurações da aplicação:
Gerar Chave:
```bash
./vendor/bin/sail artisan key:generate
```
Gerar JWT Secret:
```bash
./vendor/bin/sail artisan jwt:secret
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
Cobertura de Testes
```bash
./vendor/bin/sail artisan test --coverage
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

### Autenticação

```
POST   /api/auth/register       # Registrar novo usuário
POST   /api/auth/login          # Login
GET    /api/auth/me             # Dados do usuário autenticado
POST   /api/auth/logout         # Logout
POST   /api/auth/refresh        # Refresh token
```

### Produtos (FakeStore API)

```
GET    /api/products            # Listar todos os produtos
GET    /api/products/{id}       # Visualizar produto específico
```

### Favoritos do Usuário

```
GET    /api/my-favorites        # Listar meus produtos favoritos
POST   /api/my-favorites        # Adicionar produto aos favoritos
DELETE /api/my-favorites/{id}   # Remover produto dos favoritos
GET    /api/my-favorites/{id}/check  # Verificar se produto é favorito
GET    /api/my-favorites/count  # Contar produtos favoritos
```

### Gerenciamento de Usuários (Admin)

```
GET    /api/users               # Listar usuários
POST   /api/users               # Criar usuário
GET    /api/users/trashed       # Listar usuários removidos
GET    /api/users/{id}          # Visualizar usuário específico
PUT    /api/users/{id}          # Atualizar usuário
DELETE /api/users/{id}          # Remover usuário
POST   /api/users/{id}/restore  # Restaurar usuário
```

### Favoritos por Usuário (Admin)

```
GET    /api/users/{user}/favorites           # Listar favoritos do usuário
POST   /api/users/{user}/favorites           # Adicionar favorito para usuário
DELETE /api/users/{user}/favorites/{product} # Remover favorito do usuário
GET    /api/users/{user}/favorites/{product}/check # Verificar favorito
GET    /api/users/{user}/favorites/count     # Contar favoritos do usuário
```

## 👨‍💻 Autor

Desenvolvido por **Caio Dias**  
🔗 [GitHub](https://github.com/CaioCLDias) • [LinkedIn](https://www.linkedin.com/in/caio-cesar-lorenzon-dias/) • [WebSite](https://caiocldias.github.io)

---
# Leia-me
## 📦 Entrega do Desafio

### Informações de Entrega

Este projeto foi desenvolvido como parte do desafio técnico da aiqfome e deve ser entregue conforme as seguintes especificações:

### Escolhas Técnicas e Justificativas

#### Framework e Linguagem
- **Laravel 12 + PHP 8.2**: Escolhido pela robustez, maturidade e excelente ecossistema para desenvolvimento de APIs RESTful
- **Eloquent ORM**: Facilita o mapeamento objeto-relacional e operações de banco de dados
- **Artisan CLI**: Agiliza tarefas de desenvolvimento e deployment

#### Autenticação
- **JWT (JSON Web Tokens)**: Implementado via `tymon/jwt-auth` para autenticação stateless, ideal para APIs
- **Middleware personalizado**: Garante isolamento de dados por usuário

#### Banco de Dados
- **PostgreSQL**: Escolhido pela confiabilidade, performance, recursos avançados e por ser recomendado para o desafio
- **Migrations e Seeders**: Versionamento do schema e dados de teste automatizados
- **Soft Deletes**: Preserva integridade dos dados permitindo recuperação

#### Integração Externa
- **FakeStore API**: Integração com cache inteligente para otimizar performance
- **Guzzle HTTP Client**: Cliente robusto para requisições HTTP com timeout e retry
- **Cache configurável**: TTL ajustável para balancear performance e atualização de dados

#### Documentação
- **Swagger/OpenAPI**: Documentação automática da API via `darkaonline/l5-swagger`
- **README detalhado**: Instruções completas de instalação e uso
- **Comentários no código**: Documentação inline para facilitar manutenção

#### Testes e Qualidade
- **PHPUnit**: Framework de testes unitários e de integração
- **Form Requests**: Validação centralizada e reutilizável
- **API Resources**: Controle preciso da serialização de dados

#### DevOps e Deployment
- **Docker + Laravel Sail**: Ambiente de desenvolvimento consistente e isolado
- **Docker Compose**: Orquestração de serviços (app, banco, cache)
- **Variáveis de ambiente**: Configuração flexível para diferentes ambientes

#### Arquitetura
- **Repository Pattern**: Abstração da camada de dados
- **Service Layer**: Lógica de negócio isolada dos controllers
- **Dependency Injection**: Inversão de controle para melhor testabilidade
- **RESTful Design**: Endpoints seguindo convenções REST

### Diferenciais Implementados

1. **Sistema de Permissões**: Usuários admin podem gerenciar favoritos de outros usuários
2. **Cache Inteligente**: Otimização de requisições à FakeStore API
3. **Soft Deletes**: Usuários podem ser removidos logicamente
4. **Rate Limiting**: Proteção contra abuso da API externa
5. **Documentação Swagger**: Interface interativa para testes
6. **Testes Automatizados**: Cobertura de cenários críticos
7. **Containerização Completa**: Ambiente reproduzível em qualquer máquina

### Possiveis Melhorias Futuras

- Implementação de Redis para cache distribuído
- Sistema de logs estruturados com ELK Stack
- CI/CD com GitHub Actions
- Monitoramento com Prometheus/Grafana
- Implementação de WebSockets para atualizações em tempo real
- Sistema de notificações por email
- Versionamento da API (v1, v2, etc.)

---

**Desenvolvido por Caio Dias para o desafio técnico aiqfome** 


