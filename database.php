<?php
class DB{
    
    public function estrategias($pesquisa = null){
        $user = 'root';
        $pass = "";
        $dsn = "mysql:host=localhost;dbname=valorant-strathub_db";
        $db = new PDO($dsn, $user, $pass);

        $prepare = $db->prepare("select * from estrategia where titulo like :pesquisa");

        $prepare->bindValue('pesquisa', "%$pesquisa%");

        $prepare->execute();

        $items = $prepare->fetchAll();

        $retorno = [];

        foreach($items as $item){
            $estrategia = new Estrategia;

            $estrategia->id = $item['id'];
            $estrategia->titulo = $item['titulo'];
            $estrategia->descricao = $item['descricao'];
            $estrategia->fk_categoria_id = $item['fk_categoria_id'];
            $estrategia->fk_mapas_id = $item['fk_mapas_id'];
            $estrategia->fk_usuario_id = $item['fk_usuario_id'];

            $retorno []= $estrategia;
        }
        return $retorno;
    }
    public function estrategia($id){
        $user = 'root';
        $pass = "";
        $dsn = "mysql:host=localhost;dbname=valorant-strathub_db";
        $db = new PDO($dsn, $user, $pass);

        $prepare = $db->prepare("select * from estrategia where id = :id");

        $prepare->bindValue('id', "$id");

        $prepare->execute();

        $items = $prepare->fetchAll();

        $retorno = [];

        foreach($items as $item){
            $estrategia = new Estrategia;

            $estrategia->id = $item['id'];
            $estrategia->titulo = $item['titulo'];
            $estrategia->descricao = $item['descricao'];
            $estrategia->fk_categoria_id = $item['fk_categoria_id'];
            $estrategia->fk_mapas_id = $item['fk_mapas_id'];
            $estrategia->fk_usuario_id = $item['fk_usuario_id'];

            $retorno []= $estrategia;
        }
        return $retorno[0];
    }
}