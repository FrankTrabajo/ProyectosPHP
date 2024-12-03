<?php
session_start();
require_once "config/dbconfig.php";

if (isset($_COOKIE['recordar'])) {
    if ($_SESSION['id'] != 1) {
        header('Location: pages/user/homeUsuarios.php');
        exit();
    } else {
        header('Location: pages/admin/homeAdmin.php');
        exit();
    }
}

function validarDato($dato)
{
    return htmlspecialchars(stripslashes(trim($dato)));
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['entrar'])) {
    if (!empty($_POST['email'])) {
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email = validarDato($_POST['email']);
        } else {
            array_push($errores, "El correo no es valido");
        }
    } else {
        array_push($errores, "El correo es obligatorio");
    }
    if (!empty($_POST['pass'])) {
        $pass = validarDato($_POST['pass']);
    } else {
        array_push($errores, "La contraseña es obligatoria");
    }

    //comprobamos si el usuario existe en la base de datos
    try {
        $result = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email");
        $result->bindValue(":email", $email);
        $result->execute();
        $usuario = $result->fetch(PDO::FETCH_ASSOC);

        //Si la contraseña coincide
        if (password_verify($pass, $usuario['pass'])) {
            //Se habrá dado e iniciado seison.
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $usuario['id_usuario'];
            if ($usuario['id_usuario'] == 1) {
                $_SESSION['admin'] = true;
            }
            //Creacion de cookie
            if (isset($_POST['recordar'])) {
                //Si le ha dado a recordar, se le asigna un token
                $token = bin2hex(random_bytes(32));
                try {
                    $result = $conexion->prepare("UPDATE usuarios SET token = :token WHERE email = :email");
                    $result->bindValue(":token", $token);
                    $result->bindValue(":email", $email);
                    $result->execute();
                } catch (PDOException $e) {
                    echo "Error al insertar token: " . $e->getMessage();
                }
                setcookie("recordar", $token, time() + 3600, "/");
            }
            if ($usuario['id'] == 1) {
                //Es administrador
                header('Location: pages/admin/homeAdmin.php');
                exit();
            } else {
                //No es administrador
                header("Location: pages/user/homeUsuarios.php");
                exit();
            }
        } else {

            array_push($errores, "La contraseña es incorrecta");
        }
    } catch (PDOException $e) {
        echo "Error de seleccion: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css" type="text/css" />
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
    <title>Login</title>
</head>

<body>
    <div class="contenedor">

        <div class="modal-content rounded-4 shadow formulario ">
            <h1>Formulario de Login</h1>
            <?php if (!empty($errores)): ?>
                <?php foreach ($errores as $error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="email">Correo: </label><br>
                <input type="text" name="email" id="email" class="form-control" placeholder="Escriba su correo..." required><br><br>
                <label for="pass">Contraseña: </label><br>
                <input type="password" name="pass" class="form-control" id="pass"  placeholder="Escriba su contraseña..." required><br><br>
                <label for="recordar">Recordar: </label>
                <input type="checkbox" name="recordar" id="recordar"><br><br>
                <input type="submit" class="btn btn-lg btn-primary boton " name="entrar" value="Entrar">
                <p><a href="register.php">¿No tienes cuenta? ¡Registrate!</a></p>
            </form>
            
        </div>
    </div>
</body>

</html>