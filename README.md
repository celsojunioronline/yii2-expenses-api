<h1 align="center">
  <br>
  <img src="https://destrosp.digital/logo1.png" alt="Expenses API" width="100">
  <br>
  API de Controle de Despesas
  <br>
</h1>

<h4 align="center">Sistema RESTful para gerenciamento de despesas pessoais desenvolvido em Yii2 + Docker</h4>

<p align="center">
  <a href="https://www.php.net/">
    <img src="https://img.shields.io/badge/PHP-8.3-blue.svg" alt="PHP">
  </a>
  <a href="https://www.yiiframework.com/">
    <img src="https://img.shields.io/badge/Yii2-2.0.45-green.svg" alt="Yii2">
  </a>
  <a href="https://www.mysql.com/">
    <img src="https://img.shields.io/badge/MySQL-8.0-orange.svg" alt="MySQL">
  </a>
  <a href="https://jwt.io/">
    <img src="https://img.shields.io/badge/Auth-JWT-red.svg" alt="JWT">
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-lightgrey.svg" alt="License">
  </a>
</p>

<p align="center">
  <a href="#✨-funcionalidades">Funcionalidades</a> •
  <a href="#🧰-tecnologias">Tecnologias</a> •
  <a href="#🚀-instalação-rápida">Instalação</a> •
  <a href="#🔑-uso-da-api">Uso da API</a> •
  <a href="#🧪-testes">Testes</a> •
  <a href="#📄-licença">Licença</a>
</p>

## 📖 Visão Geral

Esta API foi desenvolvida para gestão de despesas pessoais, incorporando funcionalidades normalmente encontradas em sistemas de nível empresarial, como auditoria, rate limiting, permissionamento granular e ambiente Docker completo.

### Principais Funcionalidades

- 🔐 Autenticação JWT com middleware customizado
- 📂 CRUD completo de despesas (soft delete)
- 📜 Auditoria automática de operações
- ⚡ Rate limiting contra força bruta
- 🔎 Filtros avançados por categoria, mês/ano, período
- 📑 Paginação inteligente com metadados
- 👥 Permissionamento granular (usuário/admin)
- 🐳 Ambiente Docker containerizado
- ✅ Testes automatizados com Codeception

## 🧰 Tecnologias

| Back-end | Banco de Dados | Infraestrutura | Testes |
|----------|----------------|----------------|--------|
| Yii2 2.0.45 | MySQL 8.0 | Docker & Compose | Codeception |
| PHP 8.3 | phpMyAdmin | Nginx | Task (Taskfile) |
| Firebase JWT | | | |

## 🗂️ Estrutura do Projeto

```
projeto/
├── config/                 # Configurações do Yii2
│   ├── db.php             # Configuração do banco
│   └── web.php            # Configuração principal
├── controllers/           # Controllers da API
│   ├── AuthController.php # Autenticação
│   └── ExpenseController.php # Despesas
├── models/               # Modelos ActiveRecord
│   ├── User.php         # Usuário
│   ├── Expense.php      # Despesa
│   ├── Category.php     # Categoria
│   └── ExpenseAudit.php # Auditoria
├── services/            # Camada de serviços
│   ├── AuthService.php  # Serviços de autenticação
│   └── ExpenseService.php # Serviços de despesas
├── middleware/          # Middlewares customizados
│   ├── JwtAuthMiddleware.php # Autenticação JWT
│   └── RateLimiter.php      # Rate limiting
├── helpers/            # Classes auxiliares
│   ├── ApiResponse.php # Padronização de respostas
│   └── ApiStatus.php   # Códigos de status
├── migrations/         # Migrations do banco
├── tests/             # Testes automatizados
├── docker/            # Configurações Docker
└── docs/              # Documentação
```

## 🚀 Instalação Rápida

> Pré-requisitos: **Docker + Docker Compose + Git**

### Comando para uma instalação rápida

1. **Clone o repositório:**
```bash
git clone https://github.com/celsojunioronline/yii2-expenses-api.git
cd expenses-api
```

2. **Suba o ambiente:**
```bash
docker-compose up -d
```

3. **Instale as dependências e configure:**
```bash
docker exec -it expenses_app task install
```

4. **Acesse a aplicação:**
- API: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### Configuração Manual

Se preferir configurar manualmente:

```bash
# Entre no container
docker exec -it expenses_app bash

# Instale dependências
composer install

# Rode as migrations
php yii migrate --interactive=0

# Ajuste permissões
chmod -R 777 runtime/ web/assets/
```

## 🔑 Credenciais Padrão

O sistema vem com usuários pré-cadastrados para testes:

| Tipo | Email | Senha | Descrição |
|------|-------|-------|-----------|
| Admin | admin@teste.com | admin123 | Acesso total ao sistema |
| Demo | demo@teste.com | 123456 | Usuário regular |

## 🗄️ Banco de Dados

### Configuração Local

- **Host:** 127.0.0.1
- **Porta:** 8236
- **Database:** expenses_db
- **Usuário:** expenses_user
- **Senha:** expenses_pass

### Estrutura das Tabelas

- `users` - Usuários do sistema
- `categories` - Categorias de despesas
- `expenses` - Despesas registradas
- `expenses_audit` - Log de auditoria

## 📡 Uso da API

### Autenticação

1. **Faça login para obter o token:**
```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@teste.com", "password": "admin123"}'
```

2. **Use o token nas requisições:**
```bash
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  http://localhost:8080/expense/index
```

### Exemplos Básicos

**Criar despesa:**
```bash
curl -X POST http://localhost:8080/expense/create \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Supermercado",
    "category_id": 1,
    "amount": 120.50,
    "expense_date": "2024-03-15"
  }'
```

**Listar despesas com filtros:**
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8080/expense/index?month=3&year=2024&category_id=1&page=1"
```

Para documentação completa da API, consulte [API.md](API.md).

## 🧪 Testes

### Executar Testes Automatizados

```bash
# Testes completos
docker exec -it expenses_app task test

# Ou manualmente
docker exec -it expenses_app vendor/bin/codecept run api
```

### Cobertura de Testes

- Autenticação (registro, login, validação de token)
- CRUD de despesas (criar, listar, visualizar, atualizar, excluir)
- Filtros e paginação
- Validações e erros
- Permissões de acesso

## ⚙️ Comandos Úteis

O projeto utiliza [Task](https://taskfile.dev/) para automação:

```bash
# Instalação completa
docker exec -it expenses_app task install

# Rodar migrations
docker exec -it expenses_app task migrate

# Resetar banco de dados
docker exec -it expenses_app task reset-db

# Executar testes
docker exec -it expenses_app task test
```

## 🧭 Decisões Técnicas

### Arquitetura

**Service Layer:** Foi implementada uma camada de serviços para separar a lógica de negócio dos controllers, seguindo princípios SOLID e facilitando manutenibilidade.

**Middleware Customizado:** Criado um middleware próprio para autenticação JWT e rate limiting, proporcionando maior controle e flexibilidade.


### Segurança

**JWT com Expiração:** Tokens com tempo de vida limitado e validação robusta.

**Rate Limiting:** Proteção contra ataques de força bruta no endpoint de login.

**Validação em Múltiplas Camadas:** Validações tanto no modelo quanto no serviço.

### Funcionalidades Extras

**Sistema de Auditoria:** Registro automático de todas as operações CRUD.

**Permissionamento:** Diferenciação entre usuários regulares e administradores.

**Filtros Avançados:** Múltiplas opções de filtro para facilitar consultas específicas.

**Paginação Inteligente:** Metadados completos para facilitar implementação de interfaces.

### Docker

**Ambiente Completo:** Configuração com PHP, Nginx, MySQL e phpMyAdmin para desenvolvimento e testes.

**Volume Mapping:** Código mapeado para desenvolvimento em tempo real.

**Networks Isoladas:** Comunicação segura entre containers.

## 🚧 Melhorias Futuras

Possíveis expansões identificadas:

- **Cache Redis** para otimização de performance
- **Upload de Comprovantes** para despesas
- **Relatórios PDF** automatizados
- **Notificações** por email/webhook
- **API de Categorias** customizáveis
- **Dashboard Analytics** com métricas
- **Backup Automático** do banco de dados

## 🏗️ Estrutura de Desenvolvimento

### Padrões Seguidos

- **PSR-4** para autoloading
- **RESTful** para design da API
- **MVC** com Service Layer
- **Repository Pattern** através do ActiveRecord
- **Dependency Injection** via Yii2 DI Container

### Code Style

- Nomenclatura em inglês para código
- Documentação em português brasileiro
- Tipagem forte quando possível
- Comentários explicativos em lógicas complexas

## 📝 Nota do Desenvolvedor

Este projeto foi desenvolvido para demonstrar a aplicação de boas práticas no desenvolvimento de APIs com Yii2, incluindo autenticação JWT, auditoria, permissionamento e containerização em Docker, com foco em segurança, escalabilidade e organização do código.

---

## 📄 Licença

MIT © 2025  
