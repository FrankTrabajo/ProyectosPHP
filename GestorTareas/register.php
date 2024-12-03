<?php
session_start();
require_once "config/dbconfig.php";

function validarDato($dato){
    return htmlspecialchars(stripslashes(trim($dato)));
}

$errores = [];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])){
    if(!empty($_POST['nombre'])){
        $nombre = validarDato($_POST['nombre']);
    }else{
        array_push($errores, "El nombre es obligatorio");
    }
    if(!empty($_POST['email'])){
        if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $email = validarDato($_POST['email']);
        }else{
            array_push($errores, "Error en el correo electrónico"); 
        }
    }else{
        array_push($errores, "El correo es obligatorio");
    }
    if(!empty($_POST['pass'])){
        $pass = validarDato($_POST['pass']);
    }else{
        array_push($errores, "La contraseña es obligatorio");
    }
    if(!empty($_POST['pass2'])){
        $pass2 = validarDato($_POST['pass2']);
    }else{
        array_push($errores, "La confirmacion de contraseña es obligatorio");
    }

    if($nombre && $email && $pass == $pass2){
        
        //Comprobamos si el usuario existe
        try{
            $sql = 'SELECT * FROM usuarios WHERE email = :email';
            $result = $conexion->prepare($sql);
            $result->bindValue(":email", $email);
            $result->execute();
            $usuario = $result->fetch(PDO::FETCH_OBJ);
            if($usuario){
                array_push($errores, "El usuario " . $usuario->email . " ya existe");
            }else{
                try{
                    $result = $conexion->prepare('INSERT INTO usuarios (nombre, email, pass) VALUES (:nombre, :email, :pass)');
                    $result->bindValue(":nombre", $nombre);
                    $result->bindValue(":email",$email);
                    $result->bindValue(":pass", password_hash($pass, PASSWORD_DEFAULT));
                    $result->execute();
                    // header('Location: index.php');
                    // exit();
                }catch(PDOException $e){
                    echo "Error de insercion de usuario";
                }
            }
        }catch(PDOException $e){
            echo "Error de selección";
        }
        
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        .contenedor {
            border: solid grey 3px;
            border-radius: 10px;
            text-align: center;
            width: 700px;
            margin:auto;
        }
        .formulario h1{
            border-radius: 10px;
            padding: 20px;
        }
        .formulario form{
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            text-align: left;
            align-items: flex-start;
        }  
        .boton{
            margin-left: 300px;;
        }
    </style>

    <title>Registrarse</title>
</head>
<body>
    <div class="contenedor">
        <div class="modal-content rounded-4 shadow formulario ">
            <h1>Formulario de Registro</h1>
            <?php if(!empty($errores)): ?>
                <?php foreach($errores as $error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="nombre">Nombre: </label>
                <input type="text" name="nombre" class="form-control" id="nombre" placeholder="Escriba su nombre..." required><br><br>
                <label for="email">Correo: </label>
                <input type="email" name="email" class="form-control" id="email" placeholder="Escriba su correo..." required><br><br>
                <label for="pass">Contraseña: </label>
                <input type="password" name="pass" class="form-control" id="pass"  placeholder="Escriba su contraseña..." required><br><br>
                <label for="pass2">Confirmar contraseña: </label>
                <input type="password" name="pass2" class="form-control" id="pass2"  placeholder="Escriba de nuevo su contraseña..." required><br><br>
                <input type="submit" name="registrar" class="btn btn-lg btn-primary boton " value="Registrar">
                <p><a href="index.php">Ya tengo una cuenta</a></p>
            </form>
        </div>
    </div>
</body>
</html>