# Guia de Deploy para Produção

## Visão Geral

Este guia apresenta orientações para realizar o deploy da API de Controle de Despesas em ambiente de produção. O objetivo é fornecer um passo a passo com boas práticas de configuração, segurança e otimização de desempenho.

As recomendações podem variar conforme o provedor de hospedagem e a infraestrutura disponível.

## Pré-requisitos

### Infraestrutura Mínima
- **Servidor**: Linux (Ubuntu 20.04+ ou CentOS 8+)
- **CPU**: 2 vCPUs
- **RAM**: 4GB RAM mínimo, 8GB recomendado
- **Storage**: 50GB SSD
- **PHP**: 8.1 ou superior
- **MySQL**: 8.0 ou superior
- **Nginx**: 1.18 ou superior

### Dependências do Sistema
```bash
# Ubuntu/Debian
# Ubuntu/Debian
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-curl php8.3-gd php8.3-bcmath \
    php8.3-intl nginx mysql-server composer git curl
```

## Configuração do Ambiente de Produção

### 1. Configuração do PHP

**Ajustes principais em: `/etc/php/8.3/fpm/php.ini`**

- Limites de memória e tempo de execução adequados
- Upload configurado conforme necessidade
- OPcache ativado para melhor performance
- Logs de erro habilitados e display_errors desativado

**Configuração de pool em : `/etc/php/8.3/fpm/pool.d/www.conf`**
- Gerenciamento de processos com pm = dynamic
- Restrições de extensões para maior segurança

### 2. Configuração do Nginx

**Arquivo: `/etc/nginx/sites-available/expenses-api`**
```nginx
server {
    listen 443 ssl http2;
    server_name api.expenses.com;

    root /var/www/expenses-api/web;
    index index.php;

    # SSL (ajustar caminhos dos certificados)
    ssl_certificate /etc/letsencrypt/live/api.expenses.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.expenses.com/privkey.pem;

    # Headers de segurança
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}

```

### 3. Configuração do MySQL

**Ajustes básicos em (`/etc/mysql/mysql.conf.d/mysqld.cnf`):**
- `bind-address = 127.0.0.1` (segurança)
- `innodb_buffer_pool_size` configurado conforme memória disponível
- Logs de queries lentas habilitados para diagnóstico

## Deploy da Aplicação

### 1. Clonar o repositório e definir permissões

```bash
# Clone do repositório
cd /var/www
git clone <repository-url> expenses-api
chown -R www-data:www-data expenses-api
```

### 2. Instalar dependências 
```bash
cd expenses-api
sudo -u www-data composer install --no-dev --optimize-autoloader
```
### 3. Configuração do Banco de Dados

```bash
# Criar banco e usuário
mysql -u root -p
CREATE DATABASE expenses_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'expenses_prod'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON expenses_prod.* TO 'expenses_prod'@'localhost';
FLUSH PRIVILEGES;
```
### 4. Rodar migrations
```bash
php yii migrate --interactive=0
```

### 3. Configurar variáveis sensíveis

**Arquivo: `config/db.php`** → conexão com banco
**Arquivo: `config/params.php`** → chave JWT e credenciais de e-mail



## Segurança
- SSL (Let's Encrypt) configurado com auto-renovação
- Firewall (UFW) permitindo apenas portas necessárias
- Fail2Ban para bloquear acessos maliciosos repetidos
- Permissões de arquivos restritas ao usuário do servidor web

## Otimizações de Performance
- OPcache habilitado no PHP
- Cache de aplicação via Redis ou arquivos
- Paginação e índices no banco para consultas mais rápidas
- Compressão Gzip habilitada no Nginx


## Checklist de Deploy

### Antes do Deploy
- [ ] Backup completo do sistema atual
- [ ] Testes em ambiente de staging
- [ ] Validação de configurações de produção
- [ ] Certificados SSL configurados
- [ ] Monitoramento ativo

### Durante o Deploy
- [ ] Ativar página de manutenção (se aplicável)
- [ ] Executar migrations
- [ ] Verificar permissões de arquivos
- [ ] Testar conectividade com banco
- [ ] Validar configurações de cache

### Após o Deploy
- [ ] Testes de smoke
- [ ] Verificar logs de erro
- [ ] Monitorar performance
- [ ] Validar SSL e headers de segurança
- [ ] Confirmar backup automático funcionando

---

Este guia reúne recomendações práticas para deploy seguro e estável da API. Cada ambiente pode ter particularidades, portanto ajustes adicionais podem ser necessários. O ideal é sempre validar em staging antes de aplicar em produção e manter um processo contínuo de monitoramento, backup e atualização.