<?php
session_start();
require_once "../../config/dbconfig.php";
if (isset($_SESSION['admin'])) {
    header("Location: ../admin/homeAdmin.php");
    exit();
} else if (!isset($_SESSION['email'])) {
    header("Location: ../../index.php");
    exit();
}

function validarDato($dato)
{
    return htmlspecialchars(stripslashes(trim($dato)));
}
$errores = [];

/**
 * Los estados de las tareas van del 0 al 3
 * 0 -> sin asignar o no empezada (color blanco)
 * 1 -> en curso (color verde)
 * 2 -> en pausa (se le puede poner en pausa por si tienes una tarea mas importante) (color amarillo)
 * 3 -> finalizada (color rojo) - Se le asignara la fecha de cuando se haya finalizado la tarea
 */
$descripcion = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['agregar'])) {
        if (!empty($_POST['titulo'])) {
            $titulo = validarDato($_POST['titulo']);
        } else {
            array_push($errores, "El titulo es obligatorio");
        }
        $descripcion = validarDato($_POST['descripcion']);

        //Id usuario
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $result = $conexion->prepare($sql);
            $result->bindValue(":email", $_SESSION['email']);
            $result->execute();
            $usuario = $result->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Error al insertar tarea: " . $e->getMessage();
        }


        //Agregar tarea
        try {
            $sql = "INSERT INTO tareas (titulo, descripcion, id_usuario, estado) VALUES (:titulo, :descripcion, :id_usuario, 0)";
            $result = $conexion->prepare($sql);
            $result->bindValue(":titulo", $titulo);
            $result->bindValue(":descripcion", $descripcion);
            $result->bindValue(":id_usuario", $usuario->id_usuario);
            $result->execute();
        } catch (PDOException $e) {
            echo "Error al insertar tarea: " . $e->getMessage();
        }
    }
    if(isset($_POST['eliminarTarea'])){
        $id_tarea = validarDato($_POST['id_tarea']);
        try{
            $sql = "DELETE FROM tareas WHERE id_tarea = :id_tarea";
            $result = $conexion->prepare($sql);
            $result->bindValue(":id_tarea", $id_tarea);
            $result->execute();
        }catch(PDOException $e){
            echo "Error al eliminar tarea: " . $e->getMessage();
        }
    }
}

//Obtener los grupos a los que pertenece el usuario
try {

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $result = $conexion->prepare($sql);
    $result->bindValue(":email", $_SESSION['email']);
    $result->execute();
    $usuario = $result->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM grupos WHERE id_usuario = :id_usuario";
    $result = $conexion->prepare($sql);
    $result->bindValue(":id_usuario", $usuario['id_usuario']);
    $result->execute();
    $grupos = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener los grupos: " . $e->getMessage();
}


//Obtener todas las tareas de ese usuario
try {
    $result = $conexion->prepare("SELECT * FROM tareas WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE email = :email)");
    $result->bindValue(":email", $_SESSION['email']);
    $result->execute();
    $tareas = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al sacar las tareas: " . $e->getMessage();
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

        .menu {
            background-color: grey;
        }

        .menu ul {
            list-style: none;
            display: flex;
        }

        .menu li {
            padding: 10px;
            margin-right: 10px;
        }

        .menu li:hover {
            background-color: whitesmoke;
        }

         a {
            text-decoration: none;
            color: inherit;
        }

        .contenedor1 {
            float: left;
            width: 50%;
        }

        .contenedor2{
            float: right;
            width: 50%;
        }
        .contenido2{
            border-radius: 10px;
            background-color: grey;
            border: solid #585858  1px;
            padding-left: 30px;;
        }
        .contenido2 h2{
            text-align: center;
        }
    </style>
    <link rel="stylesheet" href="../../css/styles.css">
    <title>Home usuarios</title>
</head>

<body>
    <header>
        <h1>Bienvenido <?= $_SESSION['email'] ?></h1>
        <p></p>
        <hr>
    </header>
    <nav>
        <div class="menu">
            <ul>
                <li><a href="../groups/groups.php">Crear un grupo</a></li>
                <li><a href="../../logoff.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>
    <main>
        <div class="contenedor1">
            <?php if ($tareas): ?>
                <h2>Lista de tareas</h2>
                <ul>

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
                        ?>
                        <li>
                            <p><?= $tarea['titulo'] . " - " . $tarea['descripcion'] . " - " . $tarea['fecha_creacion'] . " - Estado: " ?><span class="<?= $estado ?>"><?= $estado ?><p>
                                
                                    <form action="modify.php" method="GET">
                                        <input type="hidden" name="id_tarea" value="<?= $tarea['id_tarea'] ?>">
                                        <input type="submit" name="modificar" href="modify?'<?= $tarea['id_tarea'] ?>'" value="Modificar">
                                    </form>
                                <?php if($tarea['id_grupo'] == 0): ?>
                                    <form  method="POST">
                                        <input type="hidden" name="id_tarea" value="<?= $tarea['id_tarea'] ?>">
                                        <input type="submit" name="eliminarTarea"  value="Eliminar">
                                    </form>
                                
                                <?php endif;?>


                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <h2>No tienes tareas por hacer</h2>
            <?php endif; ?>
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
            <div class="contenido2">
                <?php if ($grupos): ?>
                    <h2>Grupos</h2>
                    <?php foreach ($grupos as $grupo): ?>
                        <hr>
                        <p><a href="../groups/homeGroups.php?id_grupo=<?=$grupo['id_grupo']?>"><?= $grupo['nombre_grupo'] ?></a></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>


    </main>
</body>
<script>
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