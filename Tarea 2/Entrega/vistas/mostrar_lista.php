<?php
include('./../logic/funciones.php');
session_start();
$id = $_SESSION['id_user'];
$id_lista = $_GET['id_lista'];

$conn=conecta_db();

// Para mostrar las listas, ya sean del uruario logeado o otro

$sql_info_lista = mysqli_query($conn, "SELECT * FROM Listas WHERE Id_lista='$id_lista'");

while ($row = mysqli_fetch_array($sql_info_lista)) {
    $name_lista = $row['Nombre_lista'];
    $id_creador = $row['Id_user_creador'];
    $id_lista = $row['Id_lista'];
}

$sql_pert_lista = mysqli_query($conn, "SELECT * FROM User_pertenece_a_lista WHERE Id_lista = '$id_lista'");

$id_usuarios_lista = []
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista <?php echo $name_lista; ?></title>
    <link rel = "stylesheet" href="./../css/listas.css">
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
            <h1> <?php echo $name_lista; ?> </h1>
            <?php 
            if ($id_creador == $id) {?>
                <form action="./../logic/update_lista.php" method="post">
                <input type="hidden" name="id_lista" value="<?php echo $id_lista ; ?>">
                    <input type="text" name="new_name" placeholder="Nuevo nombre para la lista" value="<?php echo $name_lista; ?>" required>
                <input type="submit" value="Cambiar nombre lista">
                </form>
                <form action="./../logic/update_lista.php" method="post">
                <input type="hidden" name="id_lista" value="<?php echo $id_lista ; ?>">
                <input type="submit" value="Borrar esta lista (irreversible)" name="borrar">
                </form>
                <?php
            }else{
                $sql_user_sigue_lista = mysqli_query($conn, "SELECT * FROM User_sigue_lista WHERE Id_user = '$id' AND Id_lista = '$id_lista'");
                if (mysqli_num_rows($sql_user_sigue_lista)>0) {
                    $estado_follow = "Siguiendo";
                } else {
                    $estado_follow = "Seguir";
                }
                ?>
                <form action="./../logic/update_lista.php" method="post">
                <input type="hidden" name="id_lista" value="<?php echo $id_lista ; ?>">
                <input type="submit" value="<?php echo $estado_follow; ?>" name="seguir">
                </form>
            <?php }
            ?>
        </div>
        <div class="box usuarios">
        <h2> Usuarios que pertenecen a la lista: </h2>
<?php
if (mysqli_num_rows($sql_pert_lista) > 0) {
    while ($user = mysqli_fetch_array($sql_pert_lista)) {
        $user_id = $user['Id_user'];

        $sql_datos_user=mysqli_query($conn,"SELECT * FROM Usuarios WHERE Id_user='$user_id'");
                            
        while($datos = mysqli_fetch_array($sql_datos_user)) {
            $username = $datos['Username'];
            $n_cuenta = $datos['Nombre_cuenta'];
            $id_usuarios_lista[] = $datos['Id_user'];
            }
        ?> 
           <h3> <?php echo $n_cuenta ?> </h3>
           <a href="perfil_user.php?id_user=<?php echo $user_id; ?>"> @<?php echo $username; ?></a>
        <?php
    } 
    if ($id_creador == $id) {?>
        <form action="mostrar_lista.php?id_lista=<?php echo $id_lista; ?>" method="post">
        <input type="hidden" name="id_lista" value="<?php echo $id_lista ; ?>">
        <input type="submit" value="Editar los usuarios que pertenecen a la lista" name="update_users_list">
        </form>
        <?php
    }
    if (isset($_POST['update_users_list'])) {
        $i = 0;
        $all_users = mysqli_query($conn, "SELECT * FROM Usuarios");
        ?> <form action="./../logic/update_lista.php" method="post"><?php
        while ($user = mysqli_fetch_array($all_users)) {
            $n_cuenta_ed = $user['Nombre_cuenta'];
            $username_ed = $user['Username'];
            $id_user_ed = $user['Id_user'];

            if(in_array($id_user_ed, $id_usuarios_lista)){
                $check = "checked";
            } else {
                $check = "";
            }
            ?> 
            <input type="hidden" name="id_user_<?php echo $i ; ?>" value="<?php echo $id_user_ed ; ?>">
            <p><input type="checkbox" name="<?php echo $i ; ?>" <?php echo $check ; ?>> <?php echo $n_cuenta_ed ; ?><a href="./../vistas/perfil_user.php?id_user=<?php echo $id_user_ed; ?>"> @<?php echo $username_ed; ?></a></p>
            <?php
            $i +=1;
        }
        ?> 
        <input type="hidden" name="id_lista" value="<?php echo $id_lista ; ?>">
        <input type="submit" value="Modificar usuarios pertenecientes a la lista" name="update_users">
        <?php

    }
}
?>
</div>
<div class="box usmitos">
    <h2> Usmitos: </h2>
    <?php 
    foreach ($id_usuarios_lista as $id_user) {
        $sql_usmitos = mysqli_query($conn, "SELECT Id_usmito FROM Usmitos WHERE Id_user = '$id_user'");
        if (mysqli_num_rows($sql_usmitos) > 0) {
            while ($row = mysqli_fetch_array($sql_usmitos)) {
                ?>
                    <?php display_usmito($row['Id_usmito']);?>
                <?php
            }
        }
    }
    
    ?>
</div>
</div>  
<p>Usuario Actual : <a href="mi_perfil.php" > <?php session_start(); echo $_SESSION['nombre_cuenta']?>  </a></p>
</body>
</html>