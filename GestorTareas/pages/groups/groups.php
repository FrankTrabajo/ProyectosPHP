<?php
session_start();
require_once "../../config/dbconfig.php";

if(!isset($_SESSION['email'])){
    header("Location: ../../index.php");
    exit();
}else if(isset($_SESSION['admin'])){
    header('Location: ../admin/homeAdmin.php');
    exit();
}

function validarDato($dato){
    return htmlspecialchars(stripslashes(trim($dato)));
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['crear'])){
        $nombre = validarDato($_POST['nombre']);
        try{
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $result = $conexion->prepare($sql);
            $result->bindValue(":email", $_SESSION['email']);
            $result->execute();
            $usuario = $result->fetch(PDO::FETCH_ASSOC);

            $sql = "INSERT INTO grupos (nombre_grupo, id_usuario) VALUES (:nombre, :id_usuario)";
            $result = $conexion->prepare($sql);
            $result->bindValue(":nombre", $nombre);
            $result->bindValue(":id_usuario", $usuario['id_usuario']);
            $result->execute();


            $id_grupo = $conexion->lastInsertId();

            $sql = "INSERT INTO usuarios_grupos (id_usuario, id_grupo) VALUES (:id_usuario,:id_grupo)";
            $result = $conexion->prepare($sql);
            $result->bindValue(":id_grupo", $id_grupo);
            $result->bindValue(":id_usuario", $usuario['id_usuario']);
            $result->execute();

            header("Location: ../user/homeUsuarios.php");
            exit();

        }catch(PDOException $e){
            echo "Error al crear grupo: " . $e->getMessage();
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creacion de grupo</title>
</head>
<body>
    <header>

    </header>
    <main>
        <h1>Formulario de creacin de grupo</h1>
        <form action="" method="POST">
            <label for="nombre">Nombre del grupo</label>
            <input type="text" name="nombre" id="nombre">
            <input type="submit" name="crear" value="Crear">
        </form>
        <p><a href="../user/homeUsuarios.php">Volver</a></p>
    </main>
</body>
</html>