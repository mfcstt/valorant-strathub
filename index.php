<?php

require 'dados.php';


$view = 'index';

if ($uri = str_replace('/', '', $_SERVER['PATH_INFO'])){
    $view = $uri;
}



require "views/template/app.php";