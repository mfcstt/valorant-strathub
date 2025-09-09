# 🚀 Configurando Laravel Herd para PostgreSQL

## 🔍 Situação Atual
- **XAMPP**: Instalado (PHP em `C:\Users\gui03\Documents\xampp\php\`)
- **Herd**: Instalado mas não sendo usado no terminal
- **Problema**: Terminal usando PHP do XAMPP

## 🎯 Soluções

### Opção 1: Usar Herd (Recomendado)

#### 1. Configurar PATH do Herd
1. Abra o **Laravel Herd**
2. Vá em **Settings** → **PHP**
3. Clique em **"Add to PATH"** ou **"Use Herd PHP"**
4. Reinicie o terminal

#### 2. Verificar se mudou
```bash
which php
# Deve mostrar algo como: C:\Users\gui03\AppData\Local\Programs\Herd\bin\php.exe
```

#### 3. Verificar extensões
```bash
php -m | grep pgsql
```

### Opção 2: Instalar PostgreSQL no XAMPP

#### 1. Baixar Extensões
1. Acesse: https://windows.php.net/downloads/pecl/releases/pgsql/
2. Baixe a versão compatível com seu PHP XAMPP

#### 2. Instalar
1. Extraia os arquivos `.dll`
2. Copie para: `C:\Users\gui03\Documents\xampp\php\ext\`
3. Edite: `C:\Users\gui03\Documents\xampp\php\php.ini`
4. Descomente: `extension=pgsql` e `extension=pdo_pgsql`

### Opção 3: Continuar com SQLite (Mais Simples)

Seu projeto já está funcionando perfeitamente com SQLite! Você pode:
1. Continuar desenvolvendo com SQLite
2. Migrar para Supabase depois quando quiser

## 🎯 Recomendação

**Use o Herd** - é mais moderno e já vem com PostgreSQL:
1. Configure o PATH do Herd
2. Reinicie o terminal
3. Execute: `php migrate_supabase.php`

## 🔧 Comandos para Testar

```bash
# Verificar qual PHP está sendo usado
which php

# Verificar extensões
php -m | grep pgsql

# Testar conexão
php test_connection.php
```
