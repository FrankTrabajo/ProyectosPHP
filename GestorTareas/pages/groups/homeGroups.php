<?php
session_start();
require_once "../../config/dbconfig.php";

if (!isset($_SESSION['email'])) {
    header("Location: ../../index.php");
    exit();
} else if (isset($_SESSION['admin'])) {
    header('Location: ../admin/homeAdmin.php');
    exit();
}

function validarDato($dato)
{
    return htmlspecialchars(stripslashes(trim($dato)));
}

//Es un if abreviado, es decir, if isset id_grupo tomara el valor que reciba de get, sino, no toma nada como valor
$id_grupo = isset($_GET['id_grupo']) ? validarDato($_GET['id_grupo']) : '';


//Obtener todos los datos del grupo cuyo id sea el que pasamos en la url
try {
    $sql = "SELECT * FROM grupos WHERE id_grupo = :id_grupo";
    $result = $conexion->prepare($sql);
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $grupo = $result->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al seleccionar grupo: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['anadir'])) {
        //Ahora añadimos a la tabla usuarios_grupos el id del usuario seleccionado
        $id_usuario = validarDato($_POST['id_usuarioA']);
        $id_grupo = validarDato($_POST['id_grupoA']);
        try {
            $sql = "INSERT INTO usuarios_grupos (id_usuario, id_grupo) VALUES (:id_usuario, :id_grupo)";
            $result = $conexion->prepare($sql);
            $result->bindValue(":id_usuario", $id_usuario);
            $result->bindValue(":id_grupo", $id_grupo);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al insertar usuario a grupo: " . $e->getMessage();
        }
    }
    if (isset($_POST['eliminar'])) {
        $id_usuario = validarDato($_POST['id_usuario']);
        try {
            $sql = "DELETE FROM usuarios_grupos WHERE id_usuario = :id_usuario";
            $result = $conexion->prepare($sql);
            $result->bindValue(":id_usuario", $id_usuario);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar usuario del grupo: " . $e->getMessage();
        }
    }
    if (isset($_POST['agregar'])) {
        $titulo = validarDato($_POST['titulo']);
        $descripcion = validarDato($_POST['descripcion']);
        try {
            $sql = "INSERT INTO tareas (titulo, descripcion,id_grupo) VALUES (:titulo,:descripcion,:id_grupo)";
            $result = $conexion->prepare($sql);
            $result->bindValue(":titulo", $titulo);
            $result->bindValue(":descripcion", $descripcion);
            $result->bindValue(":id_grupo", $id_grupo);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al crear una nueva tarea: " . $e->getMessage();
        }
    }
    if (isset($_POST['agregarTarea'])) {
        $id_usuario = validarDato($_POST['usuarioGrupo']);
        $id_tarea = validarDato($_POST['tarea']);
        try {
            $sql = "UPDATE tareas SET id_usuario = :id_usuario WHERE id_tarea = :id_tarea";
            $result = $conexion->prepare($sql);
            $result->bindValue(":id_usuario", $id_usuario);
            $result->bindValue(":id_tarea", $id_tarea);
            $result->execute();
            $tarea = $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al asignar tarea: " . $e->getMessage();
        }
    }
    if(isset($_POST['eliminarTarea'])){
        $id_tarea = validarDato($_POST['id_tarea']);
        try{
            $result = $conexion->prepare("DELETE FROM tareas WHERE id_tarea = :id_tarea");
            $result->bindValue(":id_tarea",$id_tarea);
            $result->execute();
        }catch(PDOException $e){
            echo "Error al eliminar tarea: " . $e->getMessage();
        }
    }
}


//Obtener todas las tareas que sean del grupo
try {
    $sql = "SELECT * FROM tareas WHERE id_grupo = :id_grupo";
    $result = $conexion->prepare($sql);
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $tareas = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al sacar las tareas del grupo: " . $e->getMessage();
}

//Obtener todos los uduarios que pertenezcan al grupo
try {
    $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario IN (SELECT id_usuario FROM usuarios_grupos WHERE id_grupo = :id_grupo)");
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $usuariosGrupo = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al sacar los usuarios del grupo: " . $e->getMessage();
}

//Obtener al administrador del grupo
try {
    $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario IN (SELECT id_usuario FROM grupos WHERE id_grupo = :id_grupo)");
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $admin = $result->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al sacar al administrador del grupo: " . $e->getMessage();
}

//Obtenemos los datos del grupo
try {
    $sql = "SELECT * FROM grupos WHERE id_grupo = :id_grupo";
    $result = $conexion->prepare($sql);
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $grupo = $result->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al seleccionar grupo: " . $e->getMessage();
}


//Obtenemos todos los usuarios excepto el administrador para poder añadir usuarios al grupo
try {
    $result = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario NOT IN (SELECT id_usuario FROM usuarios_grupos WHERE id_grupo = :id_grupo) AND id_usuario != :id_usuario ");
    $result->bindValue(":id_usuario", 1);
    $result->bindValue(":id_grupo", $id_grupo);
    $result->execute();
    $usuarios = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al sacar los usuarios: " . $e->getMessage();
}





?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Grupos</title>
    <style>
        .cabecera {
            background-color: grey;
            height: 90px;
            ;
        }

        .cabecera h1 {
            text-align: center;
            padding-top: 20px;
            ;
        }

        .menu {
            background-color: grey;
        }

        .menu ul {
            list-style: none;
            display: flex;
        }

        .menu li {
            width: 80px;
            text-align: center;
            margin-right: 10px;
        }

        .menu li:hover {
            background-color: whitesmoke;
        }

        .main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 30px;
            height: 100px;
        }

        .contenedor1 {
            grid-column: 1;
            grid-row: 1 / 4;
            padding: 20px;
            background-color: grey;
            border-radius: 10px;
            overflow-y: auto;
        }

        .contenedor2,
        .contenedor3,
        .contenedor4 {
            grid-column: 2;
            padding: 20px;
            background-color: grey;
            border-radius: 10px;
            width: 95%;
        }

        .hidden {
            display: none;
        }

        li {
            list-style: none;
        }

        table {
            border: solid black 1px;
        }

        td {
            padding: 5px
        }
        h3{
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <div class="cabecera">
            <h1><?= $grupo['nombre_grupo'] ?></h1>

        </div>
    </header>
    <nav>
        <div class="menu">
            <ul>
                <li>
                    <p><a href="../user/homeUsuarios.php">Volver</a></p>
                </li>
            </ul>
        </div>
    </nav>
    <main>
        <div class="main">
            <div class="contenedor1">
                <h3>Tareas del grupo</h3>
                <?php if (!empty($tareas)): ?>
                    <table border="1">
                        <tr>
                            <th>Titulo</th>
                            <th>Descripcion</th>
                            <th>Fecha Creacion</th>
                            <th>Estado</th>
                            <th>Asignado</th>
                            <th></th>
                        </tr>
                        <?php foreach ($tareas as $tarea): ?>
                            <?php
                            $estado = '';
                            if ($tarea['estado'] == 0) {
                                $estado = "sinHacer";
                            } elseif ($tarea['estado'] == 1) {
                                $estado = "enCurso";
                            } elseif ($tarea['estado'] == 2) {
                                $estado = "enPausa";
                            } elseif ($tarea['estado'] == 3) {
                                $estado = "finalizado";
                            }
                            foreach ($usuariosGrupo as $usuario) {
                                if ($usuario['id_usuario'] == $tarea['id_usuario']) {
                                    $propietario = $usuario['nombre'];
                                }
                            }
                            ?>
                            <tr>
                                <td><?= $tarea['titulo'] ?></td>
                                <td><?= $tarea['descripcion'] ?></td>
                                <td><?= $tarea['fecha_creacion'] ?></td>
                                <td><?= $estado ?></td>
                                <td><?php if($tarea['id_usuario'] != NULL){ echo $propietario;}else{echo "Sin asignar";}  ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="id_tarea" value="<?= $tarea['id_tarea']?>">
                                        <input type="submit" name="eliminarTarea" value="Eliminar">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <h3>Aún no teneis ninguna tarea creada</h3>
                    <?php endif; ?>
                    </table>
                    <br>
                    <button name="agregarTarea" class="btnAddTask" id="btnAddTask">Agrega una nueva tarea</button>
                    <div id="formularioNuevaTarea" class="hidden">
                        <form action="" method="POST">
                            <label>Titulo: </label><br>
                            <input type="text" name="titulo" id="titulo"><br><br>
                            <label for="descripcion">Descripcion de la tarea: </label><br>
                            <textarea name="descripcion" id="descripcion"></textarea><br><br>
                            <input type="submit" id="agregar" name="agregar" value="Agregar"><br><br>
                        </form>
                    </div>
            </div>
            <div class="contenedor2">
                <h3>Miembros del grupo</h3>
                <?php if (!empty($usuariosGrupo)): ?>
                    <?php foreach ($usuariosGrupo as $usuGrupo): ?>
                        <?php if ($admin['id_usuario'] == $usuGrupo['id_usuario']): ?>
                            <p><?= $usuGrupo['nombre'] ?> (Administrador)</p>
                        <?php endif; ?>
                        <?php if ($admin['id_usuario'] != $usuGrupo['id_usuario']): ?>
                            <p><?= $usuGrupo['nombre'] ?></p>
                            <form action="" method="POST">
                                <input type="hidden" name="id_usuario" value="<?= $usuGrupo['id_usuario'] ?>">
                                <input type="submit" name="eliminar" value="Eliminar del grupo">
                            </form>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="contenedor3">
                <h3>Agregar persona al grupo</h3>
                <form method="POST" id="formBusca">
                    <label>Escribe el correo de la persona</label>
                    <input type="text" name="email" id="email" placeholder="Correo electronico">
                </form>
                <ul id="listaCorreos">
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuarioA): ?>
                            <li id="correos" class="hidden">
                                <?= $usuarioA['email'] ?>
                                <form method="POST">
                                    <input type="hidden" name="id_usuarioA" value="<?= $usuarioA['id_usuario'] ?>">
                                    <input type="hidden" name="id_grupoA" value="<?= $grupo['id_grupo'] ?>">
                                    <input type="submit" name="anadir" value="Añadir">
                                </form>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="contenedor4">
                <h3>Asignar tarea</h3>
                    <?php if (!empty($tareas) && !empty($usuariosGrupo)): ?>
                    <form action="" method="POST">
                        
                            <select name="usuarioGrupo" id="usuarioGrupo">
                                <?php foreach ($usuariosGrupo as $usuGrupo): ?>
                                    <option value="<?= $usuGrupo['id_usuario'] ?>"><?= $usuGrupo['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="tarea" id="tarea">
                                <?php foreach ($tareas as $tarea): ?>
                                    <option value="<?= $tarea['id_tarea'] ?>"><?= $tarea['titulo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="submit" name="agregarTarea" value="Agregar Tarea">
                        
                    </form>
                    <?php else:?>
                        <h4>No tienes tareas pendientes</h4>
                    <?php endif; ?>
            </div>
        </div>
    </main>
</body>
<script>
    document.getElementById('email').addEventListener('keyup', function() {
        let input = document.getElementById('email').value.toLowerCase();
        let correos = document.getElementsByTagName('li');
        for (let i = 0; i < correos.length; i++) {
            let textoCorreo = correos[i].textContent.toLowerCase();
            if (textoCorreo.includes(input) && input.length > 0) {
                correos[i].classList.remove('hidden');
            } else {
                correos[i].classList.add('hidden');
            }
        }
    });



    document.getElementById('btnAddTask').addEventListener('click', function() {
        let formulario = document.getElementById('formularioNuevaTarea');
        formulario.classList.remove('hidden');
    });

    document.getElementById('agregar').addEventListener('click', function() {
        let formulario = document.getElementById('formularioNuevaTarea');
        formulario.classList.add('hidden');
    });
</script>

</html>