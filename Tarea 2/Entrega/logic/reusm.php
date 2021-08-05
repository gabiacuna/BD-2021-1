<?php
include('./../database/connection.php');
session_start();
$id = $_SESSION['id_user'];
$id_usmito = trim($_POST['id_usmito']);

$conn=conecta_db();

// para Reusmear!

// Revisa si esta seteado 'privado' en el post
if (filter_has_var(INPUT_POST,'privado')) {
    $es_priv = 1;
}else {
    $es_priv = 0;
}

// Se revisa si se esta reusmeando o desreusmeando
if ($_POST['reusm'] == 'Reusmear') {
    $sql_reus = "INSERT INTO User_reusmea_usmito (Id_user, Id_usmito, Es_privado) VALUES ('$id', '$id_usmito', '$es_priv')";


    if (mysqli_query($conn,$sql_reus)) {
        echo "Reusmeado!";
        error_log("llega al Reusmeado");
        header("Location: {$_SERVER["HTTP_REFERER"]}");
    }else {
        $mensaje_error = "No se pudo reusmear :c " . mysqli_error($conn);
    }
} else {
    $sql_desreus = "DELETE FROM User_reusmea_usmito WHERE Id_user = '$id' AND Id_usmito = '$id_usmito'";
    error_log("llega al desreus  ".$sql_desreus);
    if (mysqli_query($conn,$sql_desreus)) {
        echo "Ya no está reusmeado :'(";
        header("Location: {$_SERVER["HTTP_REFERER"]}");
    }else {
        $mensaje_error = "No se pudo aplicar el desreusmear :c " . mysqli_error($conn);
    }
}

?>