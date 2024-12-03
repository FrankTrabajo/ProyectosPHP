<?php
session_start();
require_once "../../config/dbconfig.php";
if (!isset($_SESSION['email'])) {
    header("Location: ../../index.php");
    exit();
} else if (!isset($_SESSION['admin'])) {
    header("Location: ../user/homeUsuarios.php");
    exit();
}

function validarDato($dato)
{
    return htmlspecialchars(stripslashes(trim($dato)));
}
$modificar = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['modificar'])) {
        $modificar = true;
        $id_usuario = validarDato($_POST['id_usuario']);
        try {
            $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
            $result->bindValue(":id_usuario", $id_usuario);
            $result->execute();
            $usuarioMod = $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al seleccionar usuario: " . $e->getMessage();
        }
    }
    if (isset($_POST['guardar'])) {
        $modificar = false;
        $nombreMod = validarDato($_POST['nombre']);
        $emailMod = validarDato($_POST['email']);
        $id_mod = validarDato($_POST['id_usuario']);
        try {
            $result = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, email = :email WHERE id_usuario = :id_usuario");
            $result->bindValue(":nombre", $nombreMod);
            $result->bindValue(":email", $emailMod);
            $result->bindValue(":id_usuario", $id_mod);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al modificar usuario: " . $e->getMessage();
        }
    }
    if (isset($_POST['eliminar'])) {
        $id_usuario = validarDato($_POST['id_usuario']);
        try {
            $result = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
            $result->bindValue(":id_usuario", $id_usuario);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar usuario: " . $e->getMessage();
        }
    }
    if (isset($_POST['eliminarGrupo'])) {
        $id_grupo = validarDato($_POST['id_grupo']);
        try {
            $result = $conexion->prepare("DELETE FROM grupos WHERE id_grupo = :id_grupo");
            $result->bindValue(":id_grupo", $id_grupo);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar grupo: " . $e->getMessage();
        }
    }
}


try {
    $result = $conexion->query("SELECT g.* , COUNT(ug.id_usuario) AS num_miembros FROM grupos g LEFT JOIN usuarios_grupos ug ON g.id_grupo = ug.id_grupo GROUP BY g.id_grupo;");
    $grupos = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al seleccionar los grupos: " . $e->getMessage();
}

try {
} catch (PDOException $e) {
    echo "Error al recoger el contrador";
}


try {
    $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario != :id_usuario");
    $result->bindValue(":id_usuario", 1);
    $result->execute();
    $usuarios = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la seleccion de usuarios: " . $e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .hide {
            display: none;
        }

        .tabla {
            width: 50%;
        }

        .tablaUsuarios {
            margin-left: 500px;
        }
        .filtro{
            float:right;    
            margin-right: 40px;
        }
        .tablaGrupos{
            margin-left: 500px;
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Bienvenido <?= $_SESSION['email'] ?></h1>
        <p><a href="../../logoff.php">Cerrar sesión</a></p>
        <hr>
        <div class="filtro">
            <h3>Filtrar por nombre</h3>
            <form>
                <input type="text" id="buscar" name="nombre" placeholder="Escriba un nombre...">
            </form>
        </div>

    </header>
    <main>
        <div class="tablaUsuarios">
            <h1>Lista de todos los usuarios del programa</h1>
            <table border="1" class="table table-dark table-striped tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th colspan="3">Opciones</th>
                </tr>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['nombre'] ?></td>
                        <td><?= $usuario['email'] ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                <input type="submit" name="modificar" value="Modificar">
                            </form>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                <input type="submit" name="eliminar" value="Eliminar">
                            </form>
                        </td>
                        <td>
                            <form action="show.php" method="GET">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                <input type="submit" name="ver" value="Ver">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php if ($modificar): ?>
                <div id="formModificar">
                    <h2>Modificar usuario</h2>
                    <form action="" method="POST">
                        <input type="hidden" name="id_usuario" value="<?= $usuarioMod['id_usuario'] ?>">
                        <label>Nombre: </label><br>
                        <input type="text" name="nombre" id="nombre" value="<?= $usuarioMod['nombre'] ?>"><br><br>
                        <label for="descripcion">Correo: </label><br>
                        <input type="email" name="email" id="email" value="<?= $usuarioMod['email'] ?>"><br><br>
                        <input type="submit" id="guardar" name="guardar" value="Guardar"><br><br>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="tablaGrupos">
            <h1>Lista de todos los grupos del programa</h1>
            <table border="1" class="table table-dark table-striped tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Numero miembros</th>
                    <th colspan="2">Opciones</th>
                </tr>
                <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td><?= $grupo['nombre_grupo'] ?></td>
                        <td><?= $grupo['num_miembros'] ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id_grupo" value="<?= $grupo['id_grupo'] ?>">
                                <input type="submit" name="eliminarGrupo" value="Eliminar">
                            </form>
                        </td>
                        <td>
                            <form action="showGroup.php" method="GET">
                                <input type="hidden" name="id_grupo" value="<?= $grupo['id_grupo'] ?>">
                                <input type="submit" name="verGrupo" value="Ver Grupo">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>


    </main>

</body>
<script>
    let trs = document.getElementsByTagName('tr');
    let inputBuscar = document.getElementById('buscar');
    inputBuscar.addEventListener('keyup', function() {
        let filtro = inputBuscar.value.toLowerCase();
        for (let i = 0; i < trs.length; i++) {
            let celdas = trs[i].getElementsByTagName('td');
            if (celdas.length > 0) {
                let nombre = celdas[0].textContent.toLowerCase();
                if (nombre.includes(filtro)) {
                    trs[i].classList.remove('hide');
                } else {
                    trs[i].classList.add('hide');
                }
            }

        }
    });
</script>

</html>