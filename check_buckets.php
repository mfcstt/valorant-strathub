<?php

require_once 'config.php';

function checkAndCreateBucket($supabaseUrl, $supabaseKey, $bucketName, $isPublic = true) {
    // Verificar se o bucket existe
    $url = "{$supabaseUrl}/storage/v1/bucket/{$bucketName}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'apikey: ' . $supabaseKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Verificando bucket '{$bucketName}': HTTP {$httpCode}\n";
    
    if ($httpCode === 200) {
        echo "✓ Bucket '{$bucketName}' já existe\n";
        return true;
    }
    
    if ($httpCode === 404) {
        echo "Bucket '{$bucketName}' não existe. Criando...\n";
        
        // Criar o bucket
        $createUrl = "{$supabaseUrl}/storage/v1/bucket";
        
        $data = json_encode([
            'id' => $bucketName,
            'name' => $bucketName,
            'public' => $isPublic
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $createUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabaseKey,
            'apikey: ' . $supabaseKey,
            'Content-Type: application/json'
        ]);
        
        $createResponse = curl_exec($ch);
        $createHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Criação do bucket '{$bucketName}': HTTP {$createHttpCode}\n";
        echo "Resposta: {$createResponse}\n";
        
        if ($createHttpCode >= 200 && $createHttpCode < 300) {
            echo "✓ Bucket '{$bucketName}' criado com sucesso\n";
            return true;
        } else {
            echo "✗ Erro ao criar bucket '{$bucketName}'\n";
            return false;
        }
    }
    
    echo "✗ Erro ao verificar bucket '{$bucketName}': HTTP {$httpCode}\n";
    echo "Resposta: {$response}\n";
    return false;
}

echo "=== Verificação dos Buckets do Supabase ===\n\n";

$supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
$supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';

if (empty($supabaseUrl) || empty($supabaseKey)) {
    echo "✗ Erro: Configurações do Supabase não encontradas no .env\n";
    exit(1);
}

echo "URL do Supabase: {$supabaseUrl}\n";
echo "Usando chave: " . (strlen($supabaseKey) > 10 ? substr($supabaseKey, 0, 10) . "..." : $supabaseKey) . "\n\n";

// Verificar/criar bucket de imagens
$imagesBucketOk = checkAndCreateBucket($supabaseUrl, $supabaseKey, 'strategy-covers', true);
echo "\n";

// Verificar/criar bucket de vídeos
$videosBucketOk = checkAndCreateBucket($supabaseUrl, $supabaseKey, 'strategy-videos', true);
echo "\n";

if ($imagesBucketOk && $videosBucketOk) {
    echo "🎉 Todos os buckets estão configurados corretamente!\n";
} else {
    echo "❌ Alguns buckets não puderam ser verificados/criados.\n";
    echo "Verifique as permissões da sua chave do Supabase.\n";
}