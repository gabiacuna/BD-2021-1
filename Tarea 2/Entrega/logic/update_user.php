<?php
include('./../database/connection.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

// CRUD para el usuario

if (isset($_POST['borrar'])) {
    $query = "DELETE FROM Usuarios WHERE Id_user = '$id'";

    mysqli_query($conn, $query);

    header("location:./../index.php");
}

if (isset($_POST['edit'])) {
    $new_n_c = $_POST['new_n_cuenta'];
    $_SESSION['nombre_cuenta'] = $new_n_c;
    if (isset($_POST['new_clave'])) {
        $new_clave = $_POST['new_clave'];
        if (mysqli_query($conn, "UPDATE Usuarios SET Nombre_cuenta = '$new_n_c', Clave = '$new_clave' WHERE Id_user = '$id'")) {
            echo "Datos actualizados correctamente :)";
            header("location:./../vistas/mi_perfil.php");
        }
    }else {
        $sql_update = "UPDATE Usuarios SET Nombre_cuenta = '$new_n_c'";
        error_log("_______sql_up:______ ".$sql_update);

        if (mysqli_query($conn, $sql_update)) {
            echo "Datos actualizados correctamente :)";
            header("location:./../vistas/mi_perfil.php");
        }
    }
}

?>