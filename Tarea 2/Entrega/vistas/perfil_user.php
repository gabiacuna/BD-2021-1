<?php
include('./../logic/funciones.php');
session_start();

$id = $_SESSION['id_user'];

$conn=conecta_db();

// Para mostrar el perfil de un usuario desde el punto de vista del usuario logeado (revisa si son cf y si lo sigue)

$id_user = $_GET['id_user'];

$sql_datos_user = mysqli_query($conn, "SELECT * FROM Usuarios WHERE Id_user='$id_user'");
//Esta todo en version mi perfil, cachar como pasar los datos del perfil clckeado para aca

while($datos = $sql_datos_user->fetch_assoc()){
    $username = $datos['Username'];
    $n_cuenta = $datos['Nombre_cuenta'];
    // echo "username ".$datos['Username'];
}

$sql_seguidores = mysqli_query($conn, "SELECT * FROM User1_sigue_User2 WHERE Id_user_2 = '$id_user' OR (Id_user_1 = '$id_user' AND Mutuo = 1)");

$seguidores = mysqli_num_rows($sql_seguidores);

$sql_seguidos = mysqli_query($conn, "SELECT * FROM User1_sigue_User2 WHERE Id_user_1 = '$id_user' OR (Id_user_2 = '$id_user' AND Mutuo = 1)");

$seguidos = mysqli_num_rows($sql_seguidos);

$sql_status_query = "SELECT * FROM User1_sigue_User2 WHERE (Id_user_1 = '$id' AND Id_user_2='$id_user') OR (Id_user_1 = '$id_user' AND Id_user_2='$id' AND Mutuo=1)";

$sql_status = mysqli_query($conn, $sql_status_query );

$sql_posts_user = mysqli_query($conn, "SELECT Id_usmito FROM Usmitos WHERE Id_user='$id_user'");

$usmitos = mysqli_num_rows($sql_posts_user);

if (mysqli_num_rows($sql_status)){
    $status = "Siguiendo";
} else {
    $status = "Seguir";
}


$sql_sel_listas = mysqli_query($conn, "SELECT * FROM Listas WHERE Id_user_creador = '$id_user'");

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil @<?php echo $username ?></title>
    <link rel="icon" href="./../imgs/logoUSMwer.png">
    <link rel = "stylesheet" href="./../css/mi_perfil.css">
</head>
<body>
<form id="nav_bar">
        <input type="button" value="atras" onclick="history.back()">
        <input type='button' value='recargar' onclick='window.location.reload()'>
        <input type="button" value="adelante" onclick="history.back()">
</form>
    <div class="grid">
        <div class="box header">
            <a href="home.php"><img src="./../imgs/logoUSMwer.png" alt="logo" style="width: 8%;"></a>
            <h1> <?php echo $n_cuenta;?> </h1>
            <p>@ <?php echo $username ?></p>
            <div class="seguidores">
                <p>Seguidores:</p>
                <p><?php echo $seguidores;?></p>
            </div>
            <div class="seguidos">
                <p>Seguidos:</p>
                <p><?php echo $seguidos; ?></p>
            </div>
            <div class="usmitos">
                <p>Usmitos:</p>
                <p><?php echo $usmitos; ?></p>
            </div>
            <form action="./../logic/seguir.php">
                <input type="hidden" name="id_user" value="<?php echo $id_user;?>">
                <input type="hidden" name="url" value="<?php echo "./../vistas/perfil_user.php?id_user=".$id_user?>">
                <input type="submit" value="<?php echo $status;?>">
            </form>
        </div>
        <div class="box listas">
            <h4>Listas creadas por el usuario:</h4>
            <?php
                if (mysqli_num_rows($sql_sel_listas) > 0) {
                    while ($row_l = mysqli_fetch_array($sql_sel_listas)) {
                        $id_lista_act = $row_l['Id_lista'];
                        $cant_seguid = mysqli_query($conn, "SELECT * FROM User_sigue_lista WHERE Id_lista = '$id_lista_act'");?>
                    <h3><a href="mostrar_lista.php?id_lista=<?php echo $id_lista_act; ?>"><?php echo $row_l['Nombre_lista'];?></a></h3>
                    <p> Seguidores : <?php echo mysqli_num_rows($cant_seguid); ?> </p>
                    <?php }
                }?>
        </div>
        <div class="box usmitos">
            <h4>Usmitos publicados:</h4>
            <div class="post_content">
                <?php
                while($datos = mysqli_fetch_array($sql_posts_user)){
                   display_usmito($datos['Id_usmito']);
                }
                ?>
            </div>
            <h4>Usmitos reusmeados:</h4>
            <?php display_reusmeados($id_user, $username); ?>
        </div>
    </div>
    <p>Usuario Actual : <a href="./../vistas/mi_perfil.php" > <?php session_start(); echo $_SESSION['nombre_cuenta']?>  </a></p>
</body>
</html>