# 🐘 Como Instalar Extensão PostgreSQL no Laravel Herd

## 📍 Seu Ambiente Atual
- **Sistema**: Windows com Laravel Herd
- **Vantagem**: Herd já vem com PostgreSQL! 🎉

## 🚀 Método 1: Usar PostgreSQL do Herd (Recomendado)

### 1. Verificar se PostgreSQL está disponível
```bash
php -m | grep pgsql
```

### 2. Se não estiver, ativar no Herd
1. Abra o **Laravel Herd**
2. Vá em **Settings** → **PHP**
3. Procure por **PostgreSQL** e ative
4. Reinicie o Herd

## 🔧 Método 2: Instalação Manual (se necessário)

### 1. Encontrar o php.ini do Herd
```bash
php --ini
```

### 2. Editar php.ini
1. Abra o arquivo php.ini mostrado no comando acima
2. Procure por `extension=pgsql` e descomente (remova o `;`)
3. Procure por `extension=pdo_pgsql` e descomente (remova o `;`)

### 3. Reiniciar Herd
1. Feche o Herd
2. Abra novamente

## 🚀 Alternativa Rápida (SQLite)

Se quiser testar rapidamente, posso configurar para usar SQLite temporariamente:

1. Alterar `config.php` para usar SQLite
2. Executar migração
3. Depois instalar PostgreSQL

## ✅ Verificar Instalação
```bash
php -m | grep pgsql
```

Deve mostrar:
- `pgsql`
- `pdo_pgsql`
