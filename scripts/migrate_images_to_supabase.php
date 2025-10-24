<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'src/models/Image.php';
require_once 'src/services/SupabaseStorageService.php';

echo "Iniciando migração das imagens para o Supabase...\n";

try {
    $database = new Database(config('database')['database']);

    // 1. Criar tabela de imagens se não existir
    echo "1. Criando tabela de imagens...\n";
    // Verificar se a tabela images já existe
    $tableExists = $database->query("SELECT name FROM sqlite_master WHERE type='table' AND name='images'")->fetchColumn();

    if (!$tableExists) {
        $database->query("
            CREATE TABLE images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INTEGER NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // 2. Adicionar coluna cover_image_id na tabela estrategias se não existir
    echo "2. Adicionando coluna cover_image_id na tabela estrategias...\n";
    // Verificar se a coluna cover_image_id já existe na tabela estrategias
    $columnExists = $database->query("PRAGMA table_info(estrategias)")->fetchAll(PDO::FETCH_ASSOC);
    $hasColumn = false;

    foreach ($columnExists as $column) {
        if ($column['name'] === 'cover_image_id') {
            $hasColumn = true;
            break;
        }
    }

    if (!$hasColumn) {
        $database->query("ALTER TABLE estrategias ADD COLUMN cover_image_id INTEGER REFERENCES images(id) ON DELETE SET NULL");
    }

    // 3. Migrar imagens existentes
    echo "3. Migrando imagens existentes...\n";

    // Verificar se a coluna 'cover' existe na tabela estrategias
    $columnExists = $database->query("PRAGMA table_info(estrategias)")->fetchAll(PDO::FETCH_ASSOC);
    $hasCoverColumn = false;

    foreach ($columnExists as $column) {
        if ($column['name'] === 'cover') {
            $hasCoverColumn = true;
            break;
        }
    }

    if (!$hasCoverColumn) {
        echo "A coluna 'cover' não existe na tabela estrategias. Adicionando...\n";
        $database->query("ALTER TABLE estrategias ADD COLUMN cover VARCHAR(255)");
        echo "Coluna 'cover' adicionada com sucesso.\n";
        $estrategias = [];
    } else {
        $estrategias = $database->query("
            SELECT id, cover, user_id 
            FROM estrategias 
            WHERE cover IS NOT NULL AND cover != ''
        ");
    }

    $storageService = new SupabaseStorageService();
    $migratedCount = 0;
    $errorCount = 0;

    foreach ($estrategias as $estrategia) {
        $localPath = __DIR__ . '/public/assets/images/covers/' . $estrategia->cover;

        if (file_exists($localPath)) {
            echo "   Migrando: {$estrategia->cover}...\n";

            // Criar arquivo temporário para upload
            $tempFile = [
                'name' => $estrategia->cover,
                'type' => mime_content_type($localPath),
                'size' => filesize($localPath),
                'tmp_name' => $localPath,
                'error' => UPLOAD_ERR_OK
            ];

            // Fazer upload para o Supabase
            $uploadedImage = $storageService->uploadImage($tempFile, $estrategia->user_id);

            if ($uploadedImage) {
                // Atualizar estratégia com o novo ID da imagem
                $database->query(
                    "UPDATE estrategias SET cover_image_id = :cover_image_id WHERE id = :id",
                    null,
                    ['cover_image_id' => $uploadedImage->id, 'id' => $estrategia->id]
                );

                $migratedCount++;
                echo "   ✓ Migrado com sucesso\n";
            } else {
                $errorCount++;
                echo "   ✗ Erro no upload\n";
            }
        } else {
            echo "   ⚠ Arquivo não encontrado: {$estrategia->cover}\n";
            $errorCount++;
        }
    }

    echo "\nMigração concluída!\n";
    echo "Imagens migradas: $migratedCount\n";
    echo "Erros: $errorCount\n";

    if ($migratedCount > 0) {
        echo "\n⚠ IMPORTANTE: Após verificar que tudo está funcionando corretamente,\n";
        echo "você pode remover a coluna 'cover' da tabela estrategias e\n";
        echo "deletar a pasta public/assets/images/covers/ se desejar.\n";
    }

} catch (Exception $e) {
    echo "Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}
