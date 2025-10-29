<?php

$controller = str_replace('/', '', parse_url($_SERVER['REQUEST_URI'])['path']);

if (!$controller) $controller = 'login';

if (!file_exists("../src/controllers/{$controller}.controller.php")) {
  abort(404, 'Página não encontrada.');
}

require "../src/controllers/{$controller}.controller.php";