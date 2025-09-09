<?php

require_once 'database.php';
require_once 'functions.php';
require_once 'src/models/Agent.php';
require_once 'src/models/Map.php';

echo "🔍 Testando página de criação de estratégia...\n\n";

try {
    // Testar Agent::all()
    echo "🎯 Testando Agent::all()...\n";
    $agents = Agent::all();
    echo "✅ Agentes carregados: " . count($agents) . "\n";
    
    if (!empty($agents)) {
        echo "   Primeiro agente: " . $agents[0]->name . "\n";
    }
    
    // Testar Map::all()
    echo "\n🗺️  Testando Map::all()...\n";
    $maps = Map::all();
    echo "✅ Mapas carregados: " . count($maps) . "\n";
    
    if (!empty($maps)) {
        echo "   Primeiro mapa: " . $maps[0]->name . "\n";
    }
    
    echo "\n🎉 Página de criação de estratégia funcionando!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
