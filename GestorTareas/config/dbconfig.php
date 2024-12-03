<?php


define("CAD_DB", "mysql:host=localhost;dbname=gestor_tareas");
define("US_DB", "root");
define("PASS_DB", "");

try{
    $conexion = new PDO(CAD_DB,US_DB,PASS_DB);
    $conexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo "Error de conexion: " . $e->getMessage();
}