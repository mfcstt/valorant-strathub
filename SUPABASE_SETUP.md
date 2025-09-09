# 🚀 Configuração do Supabase para Valorant Strategy Hub

Este guia te ajudará a conectar seu projeto ao Supabase (PostgreSQL).

## 📋 Pré-requisitos

1. Conta no [Supabase](https://supabase.com)
2. Projeto criado no Supabase
3. PHP com extensão PDO PostgreSQL habilitada

## 🔧 Passo a Passo

### 1. Obter Credenciais do Supabase

1. Acesse seu projeto no [Supabase Dashboard](https://app.supabase.com)
2. Vá em **Settings** → **Database**
3. Copie as seguintes informações:
   - **Host**: `db.xxxxxxxxxxxxx.supabase.co`
   - **Database name**: `postgres`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: (sua senha do banco)

### 2. Configurar Variáveis de Ambiente

1. Crie um arquivo `.env` na raiz do projeto:
```bash
cp supabase_config_example.txt .env
```

2. Edite o arquivo `.env` com suas credenciais:
```env
DB_HOST=db.xxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=sua-senha-aqui
```

### 3. Executar Migração

Execute o script de migração:
```bash
php migrate_supabase.php
```

Este script irá:
- ✅ Testar a conexão com o Supabase
- 📊 Criar todas as tabelas necessárias
- 🔧 Configurar índices e triggers
- 👥 Inserir agentes padrão do Valorant

### 4. Verificar Conexão

Para testar se tudo está funcionando, você pode criar um arquivo de teste:

```php
<?php
require_once 'database.php';
require_once 'config.php';

try {
    $database = new Database(config('database'));
    $result = $database->query("SELECT COUNT(*) as total FROM agents")->fetch();
    echo "✅ Conexão OK! Total de agentes: " . $result->total;
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

## 📊 Estrutura do Banco

O script criará as seguintes tabelas:

- **users**: Usuários do sistema
- **agents**: Agentes do Valorant
- **maps**: Mapas do jogo
- **estrategias**: Estratégias criadas pelos usuários
- **ratings**: Avaliações das estratégias

## 🔄 Alternando entre Bancos

Para alternar entre SQLite (desenvolvimento) e PostgreSQL (produção):

### SQLite (Desenvolvimento Local)
```php
// Em config.php, descomente:
'driver' => 'sqlite',
'database' => __DIR__ . '/database.sqlite',
```

### PostgreSQL (Supabase)
```php
// Em config.php, mantenha:
'driver' => 'pgsql',
'host' => $_ENV['DB_HOST'] ?? 'localhost',
// ... outras configurações
```

## 🛠️ Solução de Problemas

### Erro de Conexão
- ✅ Verifique se as credenciais estão corretas
- ✅ Confirme se o banco está acessível
- ✅ Teste a conexão no painel do Supabase

### Erro de Permissão
- ✅ Verifique se o usuário tem permissão para criar tabelas
- ✅ Confirme se o banco está no modo correto

### Erro de Extensão PHP
```bash
# Ubuntu/Debian
sudo apt-get install php-pgsql

# CentOS/RHEL
sudo yum install php-pgsql

# Windows (XAMPP)
# Descomente a linha no php.ini:
extension=pgsql
```

## 📝 Comandos Úteis

```bash
# Executar migração
php migrate_supabase.php

# Verificar tabelas criadas
php -r "
require_once 'database.php';
require_once 'config.php';
\$db = new Database(config('database'));
\$tables = \$db->query(\"SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'\")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', \$tables);
"
```

## 🔐 Segurança

- ⚠️ **NUNCA** commite o arquivo `.env` no Git
- ✅ Use variáveis de ambiente em produção
- ✅ Configure Row Level Security (RLS) no Supabase se necessário

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs do Supabase
2. Teste a conexão diretamente no painel
3. Confirme se todas as extensões PHP estão instaladas
