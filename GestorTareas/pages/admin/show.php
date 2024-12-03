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
    if(isset($_GET['ver'])){
        $id_usuario = validarDato($_GET['id_usuario']);
        //Mostramos todos los datos de ese usuario, tanto las tareas que tiene como sus datos de usuario
        try{
            $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
            $result->bindValue(":id_usuario", $id_usuario);
            $result->execute();
            $usuarioShow = $result->fetch(PDO::FETCH_ASSOC);

            $result = $conexion->prepare("SELECT * FROM tareas WHERE id_usuario = :id_usuario");
            $result->bindValue(":id_usuario", $id_usuario);
            $result->execute();
            $tareasShow = $result->fetchAll(PDO::FETCH_ASSOC);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        
        .datosUsuario, .tareasUsuario{
            margin-left: 500px;
            margin-top: 50px;
        }
        .tabla{
            width: 400px;
        }
        .tabla2{
            width: 700px;
        }
        a{
            text-decoration: none;
            color: black;
        }
        .redirect{
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>Vista de datos y tareas del usuario: <?= $usuarioShow['nombre'] ?></h1>
    <div class="datosUsuario">
        <h2>Datos del usuario</h2>
        <table border="1" class="table table-dark table-striped tabla">
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
            </tr>
            <?php if(!empty($usuarioShow)):?>
                <tr>
                    <td><?= $usuarioShow['nombre'] ?></td>
                    <td><?= $usuarioShow['email'] ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="tareasUsuario">
        <h2>Datos del usuario</h2>
        <table border="1" class="table table-dark table-striped tabla2">
            <tr>
                <th>Titulo</th>
                <th>Descripcion</th>
                <th>Fecha de creacion</th>
                <th>Fecha de finalizacion</th>
                <th>Estado</th>
            </tr>
            <?php if(!empty($tareasShow)):?>
                <?php foreach($tareasShow as $tarea):?>
                    <?php
                    $estado = '';
                    if ($tarea['estado'] == 0) {
                        $estado = "sin hacer";
                    } elseif ($tarea['estado'] == 1) {
                        $estado = "en curso";
                    } elseif ($tarea['estado'] == 2) {
                        $estado = "en pausa";
                    } elseif ($tarea['estado'] == 3) {
                        $estado = "finalizado";
                    }
                    ?>
                    <tr>
                        <td><?= $tarea['titulo'] ?></td>
                        <td><?= $tarea['descripcion'] ?></td>
                        <td><?= $tarea['fecha_creacion'] ?></td>
                        <td><?= $tarea['fecha_hecha'] ?></td>
                        <td><?= $estado ?></td>
                    </tr>
                <?php endforeach;?>
            <?php endif; ?>
        </table>
    </div>
    <div class="redirect">
        <button class="btn btn-success"><a href="homeAdmin.php">Volver</a></button>
    </div>
</body>
</html>