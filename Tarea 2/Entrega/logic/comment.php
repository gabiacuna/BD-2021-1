<?php
include('./../database/connection.php');
session_start();
$id = $_SESSION['id_user'];
$id_usmito = trim($_POST['id_usmito']);
$comment_text = trim($_POST['comment']);

// Se lllegara a este php cuando se realize un comentario en una publicación.

$conn=conecta_db();


$sql_comm = "INSERT INTO Usuarios_responden_usmitos (Id_user, Id_usmito, Texto_respuesta) VALUES ('$id', '$id_usmito', '$comment_text')";

error_log("_____sql_comm:_______".$sql_comm);

if (mysqli_query($conn,$sql_comm)) {
    echo "Comentado!";
    header("Location: {$_SERVER["HTTP_REFERER"]}");
}else {
    $mensaje_error = "No se pudo comentar :c " . mysqli_error($conn);
}


?>