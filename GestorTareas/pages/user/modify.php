<?php
session_start();
require_once "../../config/dbconfig.php";
if (isset($_SESSION['admin'])) {
    header("Location: homeAdmin.php");
    exit();
}

function validarDato($dato)
{
    return htmlspecialchars(stripslashes(trim($dato)));
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['modificar'])){
        $id_tarea = validarDato($_POST['id_tarea']);
        $titulo = validarDato($_POST['titulo']);
        $descripcion = validarDato($_POST['descripcion']);
        $estado = validarDato($_POST['estado']);
        if($estado == 3){
            try{
                $sql = "UPDATE tareas SET titulo = :titulo, descripcion = :descripcion, estado = :estado, fecha_hecha = :fecha_hecha WHERE id_tarea = :id_tarea";
                $result = $conexion->prepare($sql);
                $result->bindValue(":titulo", $titulo);
                $result->bindValue(":descripcion", $descripcion);
                $result->bindValue(":estado", $estado);
                $result->bindValue(":fecha_hecha", date('Y-m-d H:i:s'));
                $result->bindValue(":id_tarea", $id_tarea);
                $result->execute();
                header('Location: homeUsuarios.php');
                exit();
            }catch(PDOException $e){
                echo "Error al modificar tarea: " . $e->getMessage();
            } 
        }else{
            try{
                $sql = "UPDATE tareas SET titulo = :titulo, descripcion = :descripcion, estado = :estado WHERE id_tarea = :id_tarea";
                $result = $conexion->prepare($sql);
                $result->bindValue(":titulo", $titulo);
                $result->bindValue(":descripcion", $descripcion);
                $result->bindValue(":estado", $estado);
                $result->bindValue(":id_tarea", $id_tarea);
                $result->execute();
                header('Location: homeUsuarios.php');
                exit();
            }catch(PDOException $e){
                echo "Error al modificar tarea: " . $e->getMessage();
            }
        }
        
        
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id_tarea = $_GET['id_tarea'];
    //Obtener la tareas de ese usuario
    try {
        $result = $conexion->prepare("SELECT * FROM tareas WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE email = :email) AND id_tarea = :id_tarea");
        $result->bindValue(":email", $_SESSION['email']);
        $result->bindValue(":id_tarea", $id_tarea);
        $result->execute();
        $tarea = $result->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al sacar las tareas: " . $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .hidden {
            display: none;
        }
    </style>
    <title>Modificar tarea</title>
</head>

<body>
    <header>
        <h1>Bienvenido <?= $_SESSION['email'] ?></h1>
        <p><a href="logoff.php">Cerrar sesión</a></p>
        <hr>
    </header>
    <main>
        <h1>Modificar tarea</h1>
        <form action="" method="POST">
                <label>Titulo: </label><br>
                <input type="hidden" name="id_tarea" id="id_tarea" value="<?= $tarea['id_tarea']?>">
                <input type="text" name="titulo" id="titulo" value="<?= $tarea['titulo']?>"><br><br>
                <label for="descripcion">Descripcion de la tarea: </label><br>
                <textarea name="descripcion" id="descripcion"><?= $tarea['descripcion']?></textarea><br><br>
                <select name="estado" id="estado">
                    <option value="0" <?php if($tarea['estado'] == 0){echo "selected";} ?>>Sin hacer</option>
                    <option value="1" <?php if($tarea['estado'] == 1){echo "selected";} ?>>En curso</option>
                    <option value="2" <?php if($tarea['estado'] == 2){echo "selected";} ?>>En pausa</option>
                    <option value="3" <?php if($tarea['estado'] == 3){echo "selected";} ?>>Finalizado</option>
                </select>
                <input type="submit" id="modificar" name="modificar" value="Modificar"><br><br>
            </form>

    </main>
</body>


</html>