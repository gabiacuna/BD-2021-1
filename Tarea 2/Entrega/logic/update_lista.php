<?php
include('./../logic/funciones.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

// Se realiza el CRUD + seguir Para la lista segun lo que este seteado en el post

if (isset($_POST['new_name'])) {
    $nuevo_name = $_POST['new_name'];
    $id_lista = $_POST['id_lista'];
    

    if (mysqli_query($conn, "UPDATE Listas SET Nombre_lista = '$nuevo_name' WHERE Id_lista = '$id_lista'")) {
        echo "Nombre actualizado :)";
        header("Location: {$_SERVER["HTTP_REFERER"]}");
    }else {
        $mensaje_error = "No se pudo editar :c " . mysqli_error($conn);
    }
}


if (isset($_POST['borrar'])) {
    $id_lista = $_POST['id_lista'];
    $sql_del_list = "DELETE FROM Listas WHERE Id_lista = '$id_lista'";

    if (mysqli_query($conn,$sql_del_list )){
        echo "lista eliminada :c";
        header("location:./../vistas/mi_perfil.php");
    } else {
        echo "No se pudo eliminar la lista";
    }
}

if (isset($_POST['update_users'])) {
    $id_lista = $_POST['id_lista'];

    // Primero se eliminan todas las pertenencias de usuarios a la lista actual.

    mysqli_query($conn, "DELETE FROM User_pertenece_a_lista WHERE Id_lista = '$id_lista'");

    // ahora se agregaran todos los usuarios checkeados

    $all_users = mysqli_query($conn, "SELECT * FROM Usuarios");

    $len = mysqli_num_rows($all_users) - 1;

    foreach (range(0,$len) as $index) {
        if (isset($_POST[$index])) {
            $id_user_actual = $_POST["id_user_".$index];
            $sql_ins = "INSERT INTO User_pertenece_a_lista (Id_user, Id_lista) VALUES ('$id_user_actual', '$id_lista')";
            $sql_ins_user_list = mysqli_query($conn, $sql_ins);

            error_log('___sql ins: ___'.$sql_ins);

            if (!$sql_ins_user_list) {
                $mensaje_error = "No se pudo agregar a ".$index." a la lista :(";
            }
        }
    }

    header("Location: {$_SERVER["HTTP_REFERER"]}");

}

if (isset($_POST['seguir'])) {
    $id_lista = $_POST['id_lista'];
    if ($_POST['seguir'] == "Seguir") {
        if (mysqli_query($conn, "INSERT INTO User_sigue_lista (Id_user, Id_lista) VALUES ('$id', '$id_lista')")){
            header("Location: {$_SERVER["HTTP_REFERER"]}");
        } else {
            echo "No se puede seguir a la lista :(";
        }
    }else {
        if (mysqli_query($conn, "DELETE FROM User_sigue_lista WHERE Id_user = '$id' AND Id_lista = '$id_lista'")){
            header("Location: {$_SERVER["HTTP_REFERER"]}");
        } else {
            echo "No se puede dejar de seguir a la lista :(";
        }
    }
    

}
?>