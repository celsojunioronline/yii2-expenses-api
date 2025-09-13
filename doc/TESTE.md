# Documentação de Testes

## Visão Geral

O projeto utiliza **Codeception** como framework de testes, com foco em validar endpoints e fluxos críticos da API de forma automatizada.
A suíte atual cobre autenticação e operações de despesas, garantindo estabilidade nas funcionalidades principais e criando base para expansão contínua.
## Estrutura de Testes

### Hierarquia de Arquivos

```
tests/
├── api/                          # Testes de API (end-to-end)
│   ├── AuthCest.php             # Autenticação e registro
│   └── ExpenseCest.php          # CRUD de despesas
├── _support/                     # Classes de suporte
│   ├── ApiTester.php            # Helper principal
│   └── _generated/              # Arquivos gerados automaticamente
└── api.suite.yml               # Configuração da suite de API
```

### Tipos de Teste

1. **API Tests** → validam endpoints completos via HTTP
2. **Integration Tests** → cobrem fluxos de negócio ponta a ponta
3. **Functional Tests** → verificam comportamentos isolados

## Configuração de Testes

### Ambiente de Teste

Os testes são executados no mesmo ambiente Docker da aplicação, garantindo consistência.

```yaml
# api.suite.yml
actor: ApiTester
modules:
  enabled:
    - Asserts
    - REST:
        url: http://nginx
        depends: PhpBrowser
```

### Base URL

- **Desenvolvimento**: http://nginx (interno ao Docker)
- **Local**: http://localhost:8080

## Executando Testes

### Comandos Básicos

```bash
# Todos os testes
docker exec -it expenses_app task test

# Apenas testes de API
docker exec -it expenses_app vendor/bin/codecept run api

# Teste específico
docker exec -it expenses_app vendor/bin/codecept run api AuthCest

# Com output detalhado
docker exec -it expenses_app vendor/bin/codecept run api --debug

# Com steps visíveis
docker exec -it expenses_app vendor/bin/codecept run api --steps
```


## Casos de Teste

### Autenticação

- Registro de usuário → valida criação, resposta correta e status 201
- Login válido → token JWT gerado, dados corretos, status 200
- Login inválido → rejeição adequada, mensagem clara, status 401

### Despesas

- Criar despesa → autenticação obrigatória, status 201, validação de campos
- Listar despesas → paginação e metadados corretos, status 200
- Visualizar despesa → acesso restrito ao dono, status 200
- Atualizar despesa → atualização válida, resposta correta, status 200
- Excluir despesa → soft delete, validação de propriedade, status 200

### Padrões e Boas Práticas

- Cada teste é independente (não depende de execução prévia de outro)
- Uso consistente de status codes e estrutura JSON
- Dados de teste pré-configurados para garantir reprodutibilidade
- Nomenclatura descritiva, ex.:

```php
public function testLoginFailsWithInvalidPassword(ApiTester $I): void
```
## Cobertura de Testes

### Endpoints Cobertos

| Endpoint | Método | Teste | Status |
|----------|--------|-------|--------|
| /auth/register | POST | ✅ | Completo |
| /auth/login | POST | ✅ | Completo |
| /expense/create | POST | ✅ | Completo |
| /expense/index | GET | ✅ | Completo |
| /expense/view/{id} | GET | ✅ | Completo |
| /expense/update/{id} | PUT | ✅ | Completo |
| /expense/delete/{id} | DELETE | ✅ | Completo |

### Cenários cobertos

- ✅ Autenticação válida e inválida
- ✅ CRUD completo de despesas
- ✅ Paginação e propriedade de dados
- ✅ Acesso negado sem token

### Cenários planejados

- ⏳ Rate limiting
- ⏳ Token expirado/inválido
- ⏳ Filtros por categoria/data
- ⏳ Permissões de admin

## Adicionando Novos Testes

### 1. Criando Nova Suite de Testes

```bash
# Gerar nova suite
docker exec -it expenses_app vendor/bin/codecept generate:suite unit

# Gerar novo teste
docker exec -it expenses_app vendor/bin/codecept generate:cest api NewFeatureCest
```

### 2. Template de Teste

```php
<?php

declare(strict_types=1);

namespace Api;

use ApiTester;

final class NewFeatureCest
{
    public function testNewFunctionality(ApiTester $I): void
    {
        // Arrange - preparar dados
        $I->sendPOST('auth/login', [
            'email' => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        $testData = [
            'field1' => 'value1',
            'field2' => 'value2'
        ];

        // Act - executar ação
        $I->sendPOST('new-endpoint/action', $testData);

        // Assert - validar resultado
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'integer',
                'field1' => 'string',
                'field2' => 'string'
            ]
        ]);
    }

    public function testValidationError(ApiTester $I): void
    {
        // Teste de validação
        $I->sendPOST('auth/login', [
            'email' => 'admin@teste.com',
            'password' => 'admin123'
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.data.token')[0];
        $I->amBearerAuthenticated($token);

        // Dados inválidos
        $invalidData = ['field1' => ''];

        $I->sendPOST('new-endpoint/action', $invalidData);

        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['success' => false]);
        $I->seeResponseMatchesJsonType([
            'errors' => 'array',
            'message' => 'string'
        ]);
    }
}
```

## Debugging de Testes

### 1. Debug Detalhado

```bash
# Com debug completo
docker exec -it expenses_app vendor/bin/codecept run api --debug

# Com steps visíveis
docker exec -it expenses_app vendor/bin/codecept run api --steps

# Parar no primeiro erro
docker exec -it expenses_app vendor/bin/codecept run api --fail-fast
```

### Técnicas

- `codecept_debug()` para logs customizados
- `pauseExecution()` para inspeção manual durante execução

### Integração Contínua
Exemplo no GitHub Actions `(.github/workflows/tests.yml):`

```php
name: Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - run: docker-compose up -d
      - run: docker exec expenses_app task install
      - run: docker exec expenses_app task test
      - run: docker-compose down
```
## Roadmap de Testes
### Próximos passos

- Cobrir filtros de despesas (categoria, período)
- Testes de rate limiting e expiração de token
- Casos de edge validation (campos obrigatórios)
- Testes de autorização diferenciando usuário e admin
- Início de testes de carga/performance básicos

### Evoluções futuras

- Unit tests para services/helpers
- Database tests para migrations e models
- Load tests para cenários de maior volume
- Security tests (SQL injection, XSS, etc.)

## Melhores Práticas

### 1. Nomenclatura

```php
// ✅ Bom - descritivo e específico
public function testCreateExpenseWithValidData(ApiTester $I): void
public function testLoginFailsWithInvalidPassword(ApiTester $I): void

// ❌ Evitar - genérico
public function test1(ApiTester $I): void
public function testAPI(ApiTester $I): void
```

### 2. Independência

```php
// ✅ Cada teste deve ser independente
public function testCreateExpense(ApiTester $I): void
{
    // Setup próprio
    $token = $this->loginAndGetToken($I);
    // Test logic
}

// ❌ Evitar dependência entre testes
private static $expenseId; // Estado compartilhado
```

### 3. Dados de Teste

```php
// ✅ Dados únicos para evitar conflitos
$email = 'user_' . uniqid() . '@example.com';

// ✅ Usar dados pré-configurados quando apropriado  
$email = 'admin@teste.com'; // Para testes que precisam de admin
```

## Troubleshooting

### Problemas Comuns

**1. Testes falhando por timeout:**
```bash
# Aumentar timeout no codeception.yml
timeout: 120
```

**2. Dados inconsistentes:**
```bash
# Resetar banco e reexecutar seeds
docker exec -it expenses_app task reset-db
docker exec -it expenses_app task install
```

**3. Container não responde:**
```bash
# Verificar status dos containers
docker-compose ps

# Logs dos containers
docker-compose logs -f app
docker-compose logs -f nginx
```

---


A suíte de testes já garante qualidade nos fluxos essenciais da API, reduzindo risco de regressões.
O foco agora deve ser expandir cobertura para cenários de segurança, filtros avançados e performance, acompanhando a evolução natural do sistema.