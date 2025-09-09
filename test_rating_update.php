<?php

require_once 'database.php';
require_once 'functions.php';

echo "🔍 Testando atualização de avaliações...\n\n";

try {
    $database = new Database(config('database'));
    
    $user_id = 2; // Usuário teste
    $estrategia_id = 1; // Estratégia existente
    $rating = 4; // Nova avaliação
    $comment = 'Avaliação atualizada!';
    
    echo "📝 Testando atualização de avaliação:\n";
    echo "   Usuário ID: $user_id\n";
    echo "   Estratégia ID: $estrategia_id\n";
    echo "   Rating: $rating\n";
    echo "   Comentário: $comment\n\n";
    
    // Verificar avaliação existente
    $existing = $database->query(
        "SELECT * FROM ratings WHERE user_id = :user_id AND estrategia_id = :estrategia_id",
        null,
        compact('user_id', 'estrategia_id')
    );
    
    $existingRating = $existing->fetch(PDO::FETCH_ASSOC);
    
    if ($existingRating) {
        echo "✅ Avaliação existente encontrada:\n";
        echo "   Rating atual: " . $existingRating['rating'] . "\n";
        echo "   Comentário atual: " . $existingRating['comment'] . "\n\n";
    } else {
        echo "⚠️  Nenhuma avaliação existente encontrada\n\n";
    }
    
    // Testar INSERT ... ON CONFLICT
    $database->query(
        "INSERT INTO ratings (user_id, estrategia_id, rating, comment)
        VALUES (:user_id, :estrategia_id, :rating, :comment)
        ON CONFLICT (user_id, estrategia_id) 
        DO UPDATE SET 
            rating = EXCLUDED.rating,
            comment = EXCLUDED.comment,
            created_at = CURRENT_TIMESTAMP",
        null,
        compact('user_id', 'estrategia_id', 'rating', 'comment')
    );
    
    echo "✅ Avaliação inserida/atualizada com sucesso!\n\n";
    
    // Verificar resultado
    $updated = $database->query(
        "SELECT * FROM ratings WHERE user_id = :user_id AND estrategia_id = :estrategia_id",
        null,
        compact('user_id', 'estrategia_id')
    );
    
    $updatedRating = $updated->fetch(PDO::FETCH_ASSOC);
    
    if ($updatedRating) {
        echo "📊 Avaliação atualizada:\n";
        echo "   Rating: " . $updatedRating['rating'] . "\n";
        echo "   Comentário: " . $updatedRating['comment'] . "\n";
        echo "   Data: " . $updatedRating['created_at'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
