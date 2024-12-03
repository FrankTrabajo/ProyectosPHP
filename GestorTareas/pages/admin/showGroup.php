<?php
session_start();
require_once "../../config/dbconfig.php";

if(!isset($_SESSION['admin']) && !isset($_SESSION['email'])){
    header("Location: ../../index.php");
    exit();
}

function validarDato($dato){
    return htmlspecialchars(stripslashes(trim($dato)));
}

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(isset($_GET['verGrupo'])){
        $id_grupo = validarDato($_GET['id_grupo']);
        //Mostramos todos los datos de ese usuario, tanto las tareas que tiene como sus datos de usuario
        try{
            $result = $conexion->prepare("SELECT * FROM grupos WHERE id_grupo = :id_grupo");
            $result->bindValue(":id_grupo", $id_grupo);
            $result->execute();
            $grupoShow = $result->fetch(PDO::FETCH_ASSOC);

            $result = $conexion->prepare("SELECT * FROM tareas WHERE id_grupo = :id_grupo");
            $result->bindValue(":id_grupo", $id_grupo);
            $result->execute();
            $tareasGrupoShow = $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            echo "Error al seleccionar usuario y tareas: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver usuario</title>
</head>
<body>
    <h1>Vista de datos y tareas del grupo: <?= $grupoShow['nombre_grupo'] ?></h1>
    <div class="datosUsuario">
        <h2>Datos del grupo</h2>
        <ul>
            <li>Nombre
                <ul>
                    <li><?=$grupoShow['nombre_grupo']?></li>
                </ul>
            </li>
            <li>Tareas
                <ol>
                    <?php foreach($tareasGrupoShow as $tarea): ?>
                        <li><?=$tarea['titulo']?></li>
                    <?php endforeach; ?>
                </ol>
            </li>
        </ul>
    </div>
    <div>
        <p><a href="homeAdmin.php">Volver</a></p>
    </div>
</body>
</html>