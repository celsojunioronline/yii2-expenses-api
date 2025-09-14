# API de Controle de Despesas

API RESTful para gerenciamento de despesas pessoais, construída com Yii2 Framework.

## Sumário

- [Autenticação](#autenticação)
- [Rate Limiting](#rate-limiting)
- [Formato de Resposta](#formato-de-resposta)
- [Códigos de Status](#códigos-de-status)
- [Endpoints](#endpoints)
    - [Autenticação](#endpoints-de-autenticação)
    - [Despesas](#endpoints-de-despesas)
- [Modelos de Dados](#modelos-de-dados)
- [Auditoria](#auditoria)
- [Exemplos de Uso](#exemplos-de-uso)

## Autenticação

A API utiliza autenticação JWT (JSON Web Token). Após o login, inclua o token no header de todas as requisições protegidas:

```
Authorization: Bearer {seu_jwt_token}
```

### Usuários Padrão

A API vem com usuários pré-cadastrados para testes:

- **Admin**: `admin@teste.com` / `admin123`
- **Demo**: `demo@teste.com` / `123456`

## Rate Limiting

O endpoint de login possui proteção contra força bruta:
- **Limite**: 5 tentativas por IP/email
- **Janela de tempo**: 15 minutos (900 segundos)
- **Reset**: Automático após login bem-sucedido

## Formato de Resposta

Todas as respostas seguem o padrão JSON:

```json
{
  "success": true,
  "data": {},
  "errors": [],
  "message": "Mensagem informativa",
  "meta": {}
}
```

### Campos de Resposta

- `success`: Boolean indicando sucesso da operação
- `data`: Dados da resposta (objeto ou array)
- `errors`: Array de erros de validação
- `message`: Mensagem descritiva
- `meta`: Metadados (paginação, totais, etc.)

## Códigos de Status

| Código | Descrição |
|--------|-----------|
| 200 | OK - Sucesso |
| 201 | Created - Recurso criado |
| 400 | Bad Request - Dados inválidos |
| 401 | Unauthorized - Token inválido/ausente |
| 403 | Forbidden - Sem permissão |
| 404 | Not Found - Recurso não encontrado |
| 429 | Too Many Requests - Rate limit atingido |
| 500 | Internal Server Error - Erro interno |

## Endpoints

### Endpoints de Autenticação

#### Registrar Usuário

```http
POST /auth/register
```

**Body:**
```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "minhasenha123"
}
```

**Resposta de Sucesso (201):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "João Silva",
    "email": "joao@email.com"
  },
  "message": "Usuário registrado com sucesso"
}
```

**Resposta de Erro (400):**
```json
{
  "success": false,
  "message": "Email já cadastrado",
  "errors": {
    "email": ["Este email já está em uso"]
  }
}
```

#### Login

```http
POST /auth/login
```

**Body:**
```json
{
  "email": "joao@email.com",
  "password": "minhasenha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "token": "jwt_token_aqui",
    "user": {
      "id": 1,
      "name": "Administrador",
      "email": "admin@exemple.com"
    }
  },
  "message": "Login realizado com sucesso."
}
```

**Resposta de Erro (401):**
```json
{
  "success": false,
  "message": "Credenciais inválidas",
  "errors": {
    "login": ["Email ou senha incorretos"]
  }
}
```

### Endpoints de Despesas

> **Nota:** Todos os endpoints de despesas requerem autenticação JWT.

#### Criar Despesa

```http
POST /expense/create
Authorization: Bearer {token}
```

**Body:**
```json
{
  "description": "Supermercado",
  "category_id": 1,
  "amount": 120.50,
  "expense_date": "2024-03-15"
}
```

**Resposta de Sucesso (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "description": "Supermercado",
    "category_id": 1,
    "amount": "120.50",
    "expense_date": "2024-03-15"
  }
}
```

#### Listar Despesas

```http
GET /expense/index
Authorization: Bearer {token}
```

**Parâmetros de Query:**

| Parâmetro | Tipo | Descrição | Padrão |
|-----------|------|-----------|---------|
| `page` | integer | Página atual | 1 |
| `per_page` | integer | Itens por página (max: 100) | 10 |
| `category_id` | integer | Filtrar por categoria | - |
| `start_date` | string | Data inicial (YYYY-MM-DD) | - |
| `end_date` | string | Data final (YYYY-MM-DD) | - |
| `month` | integer | Filtrar por mês (1-12) | - |
| `year` | integer | Filtrar por ano | - |
| `sort` | string | Ordenação: `asc` ou `desc` | desc |

**Exemplo:**
```http
GET /expense/index?page=1&per_page=5&category_id=1&month=3&year=2024&sort=asc
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "description": "Supermercado",
      "category_id": 1,
      "amount": "120.50",
      "expense_date": "2025-09-12"
    }
  ],
  "meta": {
    "total": 10,
    "page": 1,
    "per_page": 5,
    "total_pages": 2
  }
}
```

#### Visualizar Despesa

```http
GET /expense/view/{id}
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "description": "Supermercado",
    "category_id": 1,
    "amount": "120.50",
    "expense_date": "2024-03-15"
  }
}
```

**Resposta de Erro (404):**
```json
{
  "success": false,
  "message": "Despesa não encontrada ou acesso negado"
}
```

#### Atualizar Despesa

```http
PUT /expense/update/{id}
Authorization: Bearer {token}
```

**Body:**
```json
{
  "description": "Supermercado Atualizado",
  "amount": 150.75
}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "description": "Supermercado Atualizado",
    "category_id": 1,
    "amount": "150.75",
    "expense_date": "2025-09-12"
  }
}
```

#### Excluir Despesa

```http
DELETE /expense/delete/{id}
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Despesa excluída com sucesso"
}
```

**Resposta de Erro (403):**
```json
{
  "success": false,
  "message": "Você não tem permissão para excluir esta despesa"
}
```

## Modelos de Dados

### Usuário (User)

```json
{
  "id": 1,
  "name": "João Silva",
  "email": "joao@email.com",
  "role": "user|admin",
  "created_at": "2024-03-15 10:30:00",
  "updated_at": "2024-03-15 10:30:00"
}
```

### Categoria (Category)

As categorias padrão incluem:

| ID | Nome |
|----|------|
| 1 | Alimentação |
| 2 | Transporte |
| 3 | Lazer |
| 4 | Moradia |
| 5 | Saúde |
| 6 | Outros |

### Despesa (Expense)

```json
{
  "id": 1,
  "user_id": 1,
  "category_id": 1,
  "description": "Supermercado",
  "amount": "120.50",
  "expense_date": "2025-09-12",
  "created_at": "2025-09-12 14:00:00",
  "updated_at": null,
  "deleted_at": null
}
```

## Auditoria

Todas as operações CRUD em despesas são auditadas automaticamente na tabela `expenses_audit`:

- **create**: Registra criação de despesa
- **update**: Registra alterações (dados antigos e novos)
- **delete**: Registra exclusão (soft delete)

## Permissões

### Usuário Regular (`role: user`)
- Pode gerenciar apenas suas próprias despesas
- Acesso limitado aos próprios dados

### Administrador (`role: admin`)
- Pode visualizar todas as despesas de todos os usuários
- Pode gerenciar despesas de qualquer usuário

## Exemplos de Uso

### 1. Fluxo Completo de Autenticação

```bash
# 1. Registrar novo usuário
curl -X POST http://api.example.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@email.com",
    "password": "minhasenha123"
  }'

# 2. Fazer login
curl -X POST http://api.example.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@email.com",
    "password": "minhasenha123"
  }'
```

### 2. Gerenciar Despesas

```bash
# Criar despesa
curl -X POST http://api.example.com/expense/create \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Compras do supermercado",
    "category_id": 1,
    "amount": 89.90,
    "expense_date": "2024-03-15"
  }'

# Listar despesas do mês atual
curl -X GET "http://api.example.com/expense/index?month=3&year=2024&per_page=20" \
  -H "Authorization: Bearer {seu_token}"

# Atualizar despesa
curl -X PUT http://api.example.com/expense/update/1 \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 95.50
  }'
```

### 3. Filtros Avançados

```bash
# Despesas de alimentação entre duas datas
curl -X GET "http://api.example.com/expense/index?category_id=1&start_date=2024-03-01&end_date=2024-03-31&sort=asc" \
  -H "Authorization: Bearer {seu_token}"

# Despesas de março de 2024, paginadas
curl -X GET "http://api.example.com/expense/index?month=3&year=2024&page=2&per_page=25" \
  -H "Authorization: Bearer {seu_token}"
```

## Estrutura do Banco de Dados

### Tabelas Principais

- `users`: Usuários do sistema
- `categories`: Categorias de despesas
- `expenses`: Despesas registradas
- `expenses_audit`: Log de auditoria

### Relacionamentos

- `expenses.user_id` → `users.id` (CASCADE)
- `expenses.category_id` → `categories.id` (RESTRICT)
- `expenses_audit.expense_id` → `expenses.id` (CASCADE)
- `expenses_audit.user_id` → `users.id` (CASCADE)

## Configuração JWT

Para configurar o JWT, defina os parâmetros no arquivo de configuração:

```php
'params' => [
    'jwt_key' => 'sua_chave_secreta_aqui',
    'jwt_expire' => 3600, // 1 hora
]
```

---

**Desenvolvido com Yii2 Framework** | **Versão da API: 1.0**
