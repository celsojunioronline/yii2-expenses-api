# Arquitetura do Sistema

## Visão Geral

A API de Controle de Despesas foi desenvolvida com foco em simplicidade, clareza e boas práticas, aplicando princípios de arquitetura limpa e padrões que facilitam manutenção, testes e evolução futura.

## Arquitetura em Camadas

### 1. Camada de Apresentação (Controllers)
- **AuthController**: Gerencia autenticação e registro
- **ExpenseController**: CRUD de despesas

**Responsabilidades:**
- Validação básica de entradas
- Encaminhar chamadas para a camada de serviços
- Padronização das respostas da API

### 2. Camada de Serviços (Services)
- **AuthService**: Autenticação e emissão de tokens JWT
- **ExpenseService**: Regras de negócio relacionadas a despesas

**Responsabilidades:**
- Implementação da lógica de negócio
- Orquestração de operações entre entidades
- Validações específicas do domínio

### 3. Camada de Domínio (Models)
- **User**: Usuários da aplicação
- **Expense**: Entidade de despesas
- **Category**: Categorias de despesas
- **ExpenseAudit**: Registros de auditoria

**Responsabilidades:**
- Representar entidades do sistema
- Definir validações de dados
- Mapear relacionamentos entre entidades

### 4. Camada de Infraestrutura
- **Middlewares**: Autenticação JWT, Rate Limiting
- **Helpers**: Padronização de respostas e status
- **Database**: Active Record para persistência

## Padrões de Design Implementados

### Service Layer Pattern
```php
// Exemplo de uso
$service = (new ExpenseService())
    ->comUsuario($userId)
    ->comFiltros($filters);

$result = $service->listar();
```

**Benefícios:**
- Regras de negócio centralizadas
- Reuso de lógica
- Controllers mais enxutos e testáveis

### ActiveRecord (Yii2)
```php
// Encapsulamento de consultas complexas
$query = Expense::find()
    ->where(['deleted_at' => null])
    ->andWhere(['user_id' => $userId]);
```
- Consultas encapsuladas nos modelos
- Integração direta com o ORM do Yii2
### Middleware
```php
// Pipeline de middleware para autenticação
public function behaviors()
{
    return [
        'authenticator' => [
            'class' => JwtAuthMiddleware::class,
        ]
    ];
}
```
- Proteção de rotas via pipeline de autenticação

### Builder/Fluent Interface
```php
// Construção fluente de serviços
$service = (new ExpenseService())
    ->comUsuario($userId)
    ->comDespesa($expense)
    ->comDados($data);
```
- Interface fluente para melhor legibilidade
-
## Decisões Arquiteturais

### 1. Service Layer
- Mantém controllers enxutos
- Facilita testes unitários
- Centraliza regras de negócio

### 2. Soft Delete
- Preserva histórico de dados
- Permite recuperação de registros
- Facilita auditoria e compliance

### 3. JWT Stateless
- Independência de sessões
- Facilidade de integração com SPAs e mobile
- Suporte natural a escalabilidade horizontal

### 4. Auditoria Automática
- Histórico de operações sempre disponível
- Auxilia em conformidade e segurança
- Facilita debugging e análise de uso

## Estrutura de Banco de Dados

### Schema Principal

```sql
-- Usuários
users (id, name, email, password_hash, auth_key, role)

-- Categorias
categories (id, name, created_at, updated_at)

-- Despesas (com soft delete)
expenses (id, user_id, category_id, description, amount, expense_date, created_at, updated_at, deleted_at)

-- Auditoria
expenses_audit (id, expense_id, user_id, action, old_data, new_data, created_at)
```

### Relacionamentos

- `expenses.user_id` → `users.id` (CASCADE)
- `expenses.category_id` → `categories.id` (RESTRICT)
- `expenses_audit.expense_id` → `expenses.id` (CASCADE)
- `expenses_audit.user_id` → `users.id` (CASCADE)

### Índices Estratégicos

```sql
-- Performance em consultas frequentes
INDEX idx_expenses_date ON expenses(expense_date)
INDEX idx_expenses_deleted ON expenses(deleted_at)
INDEX idx_expenses_user_category ON expenses(user_id, category_id)
INDEX idx_audit_action ON expenses_audit(action)
INDEX idx_audit_created_at ON expenses_audit(created_at)
```

## Fluxo de Dados

### 1. Autenticação
```
Request → AuthController → AuthService → User Model → JWT Token → Response
```

### 2. Operação em Despesa
```
Request → ExpenseController → JwtMiddleware → ExpenseService → Expense Model → ExpenseAudit → Response
```

### 3. Listagem com Filtros
```
Request → ExpenseController → JwtMiddleware → ExpenseService → Query Builder → Pagination → Response
```

## Segurança

### Camadas de Segurança

1. **Rate Limiting** contra força bruta
2. **JWT com expiração**
3. **Controle de permissões** por recurso
4. **Validações de entrada** em múltiplas camadas
5. **Auditoria completa**: das operações

### Princípios Aplicados

- **Defense in Depth**
- **Least Privilege**
- **Fail Secure**
- **Complete Mediation**

## Escalabilidade
- 
- JWT facilita múltiplas instâncias em paralelo
- Banco preparado para replicação
- Índices estratégicos em consultas frequentes
- Paginação e soft delete otimizam performance

## Testabilidade
- Unit Tests para serviços
- Integration Tests para API
- Database Tests para models e migrations
- Dependency Injection em serviços para facilitar mocking

## Manutenibilidade
- Namespaces organizados
- Princípios SOLID aplicados
- Padrões consistentes de código
- Tratamento de erros com exceções específicas e respostas padronizadas

## Futuras Melhorias

### 1. Cache Layer
```php
// Implementação futura de cache
class ExpenseService {
    public function listarComCache(array $filtros): array {
        $cacheKey = 'expenses:' . md5(serialize($filtros));
        return Cache::remember($cacheKey, 3600, function() use ($filtros) {
            return $this->listar($filtros);
        });
    }
}
```

### 2. Event-Driven Architecture
```php
// Eventos para desacoplamento
class ExpenseService {
    public function criar(): array {
        $expense = new Expense();
        // ... lógica de criação
        
        // Dispara evento
        Event::dispatch(new ExpenseCreated($expense));
        
        return $result;
    }
}
```

### 3. CQRS (Command Query Responsibility Segregation)
```php
// Separação entre comandos e queries
class CreateExpenseCommand { /* ... */ }
class ListExpensesQuery { /* ... */ }

class ExpenseCommandHandler { /* ... */ }
class ExpenseQueryHandler { /* ... */ }
```

## Considerações de Deploy
- Load balancer e replicação de banco em cenários de alta demanda
- CI/CD com testes automatizados e análise de qualidade
- Monitoramento e alertas em produção

---

A arquitetura atual cobre bem os requisitos de um sistema de despesas pessoais, com uma base sólida para evolução. As decisões foram tomadas visando clareza, segurança e facilidade de manutenção, sem perder de vista a possibilidade de escalar e incorporar práticas mais avançadas conforme a necessidade do projeto.