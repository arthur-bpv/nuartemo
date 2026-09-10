<?php

$host = 'localhost';
$usuario = 'nuartemo';
$senha = 'TkMMd311Vx415K';
$database = 'nuartemo';


$mysqli = new mysqli($host, $usuario, $senha, $database);

if($mysqli->error) {
    die("Falha ao conectar ao banco de dados: " . $mysqli->error);
}