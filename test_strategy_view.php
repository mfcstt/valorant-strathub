<?php

require_once 'database.php';
require_once 'functions.php';
require_once 'src/models/Estrategia.php';

echo "🔍 Testando visualização de estratégia...\n\n";

try {
    // Buscar estratégias existentes
    $database = new Database(config('database'));
    $estrategias = $database->query("SELECT id FROM estrategias LIMIT 1");
    $estrategia = $estrategias->fetch(PDO::FETCH_ASSOC);
    
    if ($estrategia) {
        $estrategia_id = $estrategia['id'];
        echo "📝 Testando estratégia ID: $estrategia_id\n";
        
        // Testar Estrategia::get()
        $estrategiaObj = Estrategia::get($estrategia_id);
        
        if ($estrategiaObj) {
            echo "✅ Estratégia encontrada!\n";
            echo "   Título: " . $estrategiaObj->title . "\n";
            echo "   Categoria: " . $estrategiaObj->category . "\n";
            echo "   Agente: " . $estrategiaObj->agent_name . "\n";
            echo "   Cover: " . $estrategiaObj->cover . "\n";
            echo "   Rating: " . $estrategiaObj->rating_average . "\n";
        } else {
            echo "❌ Estratégia não encontrada!\n";
        }
        
        // Testar avaliações
        $ratings = $database->query(
            "SELECT 
                r.*,
                u.name as user_name,
                u.avatar as user_avatar,
                (SELECT COUNT(*) FROM ratings countR WHERE countR.user_id = r.user_id) AS rated_movies
             FROM ratings r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.estrategia_id = :estrategia_id",
            null,
            compact('estrategia_id')
        );
        
        $ratingCount = 0;
        while ($rating = $ratings->fetch(PDO::FETCH_ASSOC)) {
            $ratingCount++;
        }
        
        echo "✅ Avaliações encontradas: $ratingCount\n";
        
    } else {
        echo "⚠️  Nenhuma estratégia encontrada no banco\n";
        echo "   Crie uma estratégia primeiro em /strategy-create\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
