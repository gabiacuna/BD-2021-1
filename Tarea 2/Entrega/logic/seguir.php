<?php
include('./../logic/funciones.php');
session_start();

$id = $_SESSION['id_user'];

$conn=conecta_db();

//Para seguir a un usuario

$id_user = $_GET['id_user'];

// querys que revisan si el usuario al que se quiere seguir ya sigue al usuario en session
$sql_status_query1 = "SELECT * FROM User1_sigue_User2 WHERE (Id_user_1 = '$id' AND Id_user_2='$id_user')";
$sql_status_query2 = "SELECT * FROM User1_sigue_User2 WHERE (Id_user_1 = '$id_user' AND Id_user_2='$id' AND Mutuo=1)";

error_log($sql_status_query1);
error_log($sql_status_query2);

$sql_status1 = mysqli_query($conn, $sql_status_query1);
$sql_status2 = mysqli_query($conn, $sql_status_query2);

if (mysqli_num_rows($sql_status1)){
    // $status = "Siguiendo"; -> etonces se quiere dejar de seguir al usuario
    while ($row = mysqli_fetch_array($sql_status1)) {
        if ($row['Mutuo']==1) {     //Si era mutuo se agrega la relacion inversa (con mutuo = 0 def)
            $sql_update = mysqli_query($conn, "INSERT INTO User1_sigue_User2 (Id_user_1, Id_user_2) VALUES ('$id_user', '$id)");
        }
    }
    $sql_del = mysqli_query($conn, "DELETE FROM User1_sigue_User2 WHERE Id_user_1 = '$id' AND Id_user_2='$id_user'");
}elseif (mysqli_num_rows($sql_status2)) {
    // En el caso de estar participandfo en una relacion  mutua, esta se actualiza a 0
    $sql_update = mysqli_query($conn, "UPDATE User1_sigue_User2 SET Mutuo = 0 WHERE Id_user_1 = '$id_user' AND Id_user_2='$id'");
        
} else {
    // $status = "Seguir"; -> se quiere empezar a seguir al usuario nuevo

    // Si el user 2 ya seguia al user actual, solo se debe actualizar el mutuo a 1
    $sql_rev_follow = mysqli_query($conn, "SELECT * FROM User1_sigue_User2 WHERE Id_user_1 = '$id_user' AND Id_user_2 = '$id'");

    if (mysqli_num_rows($sql_rev_follow)>0){
        $sql_update = mysqli_query($conn, "UPDATE User1_sigue_User2 SET Mutuo = 1 WHERE (Id_user_1 = '$id_user') AND (Id_user_2 = '$id')");
    } else {
        $sql_follow = mysqli_query($conn, "INSERT INTO User1_sigue_User2 (Id_user_1, Id_user_2) VALUES ('$id', '$id_user')");
    }
}

header("location:".$_GET['url']);

?>