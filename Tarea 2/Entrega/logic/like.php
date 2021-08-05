<?php
include('./../database/connection.php');
session_start();
$id = $_SESSION['id_user'];
$id_usmito = trim($_POST['id_usmito']);

$conn=conecta_db();

// Para agregar y quitar de la tabla User_like_usmito

if ($_POST['like'] == 'Like') {
    $sql_like = "INSERT INTO User_like_usmito (Id_user, Id_usmito) VALUES ('$id', '$id_usmito')";


    if (mysqli_query($conn,$sql_like)) {
        echo "Te encanta!";
        error_log("llega al like");
        header("Location: {$_SERVER["HTTP_REFERER"]}");
    }else {
        $mensaje_error = "No se pudo aplicar el like :c " . mysqli_error($conn);
    }
} else {
    $sql_dislike = "DELETE FROM User_like_usmito WHERE Id_user = '$id' AND Id_usmito = '$id_usmito'";
    error_log("llega al dislike  ".$sql_dislike);
    if (mysqli_query($conn,$sql_dislike)) {
        echo "Ya no te encanta :'(";
        error_log("hace al dislike");
        header("Location: {$_SERVER["HTTP_REFERER"]}");
    }else {
        $mensaje_error = "No se pudo aplicar el dislike :c " . mysqli_error($conn);
    }
}

?>