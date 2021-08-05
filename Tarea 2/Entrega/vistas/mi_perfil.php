<?php
include('./../logic/funciones.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

// Se muestra el perfil del usuario que esta logeado en esta sesion

$sql_sel_usmitos = mysqli_query($conn, "SELECT * FROM Usmitos WHERE Id_user='$id'");
$sql_sel_listas = mysqli_query($conn, "SELECT * FROM Listas WHERE Id_user_creador = '$id'");
$sql_seguidores = mysqli_query($conn, "SELECT * FROM User1_sigue_User2 WHERE Id_user_2 = '$id' OR (Id_user_1 = '$id' AND Mutuo = 1) ");
$sql_siguiendo = mysqli_query($conn, "SELECT * FROM User1_sigue_User2 WHERE Id_user_1 = '$id' OR (Id_user_2 = '$id' AND Mutuo = 1) ");


if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['id_usmito_borrar'];
    
    $sql_del = "DELETE FROM Usmitos WHERE Id_usmito = '$id_borrar'";
    
    if (mysqli_query($conn,$sql_del)){
        echo "Usmito eliminado °n°";
    }else {
        echo "No se pudo eliminar x.x";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil</title>
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
            <h1> <?php session_start(); echo $_SESSION['nombre_cuenta']?> </h1>
            <form action="./../logic/update_user.php" method="post">
                <input type="submit" value="Borrar Usuario ESTO ES IRREVERSIBLE" name="borrar">
            </form>
            <br>
            <form action="./../logic/update_user.php" method="post">
                Nuevo nombre cuenta: <input type="text" name="new_n_cuenta" value="<?php echo $_SESSION['nombre_cuenta'] ;?>" required>
                Nueva contraseña: <input type="password" name="new_clave" placeholder="ingresa tu nueva contraseña">
                <input type="submit" value="Editar usuario" name="edit">
            </form>

            <p>Seguidores : <?php echo mysqli_num_rows($sql_seguidores); ?></p>
            <p>Siguiendo : <?php echo mysqli_num_rows($sql_siguiendo); ?></p>
            <p>Usmitos publicados : <?php echo mysqli_num_rows($sql_sel_usmitos); ?></p>
        </div>
        <div class="box listas">
            <h4>Listas creadas por el usuario:</h4>
            <form action="./../logic/crear_lista.php" method="post">
                <input type="submit" value="Crear Lista :)">
            </form>
            
            <?php
                if (mysqli_num_rows($sql_sel_listas) > 0) {
                    while ($row_l = mysqli_fetch_array($sql_sel_listas)) {
                        $id_lista_act = $row_l['Id_lista'];?>
                    <div class="lista">
                    <h3><a href="mostrar_lista.php?id_lista=<?php echo $id_lista_act; ?>"><?php echo $row_l['Nombre_lista'];?></a></h3>
                    <?php
                    $cant_seguid = mysqli_query($conn, "SELECT * FROM User_sigue_lista WHERE Id_lista = '$id_lista_act'");?>
                    <p> seguidores : <?php echo mysqli_num_rows($cant_seguid) -1; ?> </p></div>
                    <?php }
                }?>
            <h4>Sigues a:</h4>
            <ul><?php
            while ($row_f = mysqli_fetch_array($sql_siguiendo)) {
                if ($row_f['Mutuo'] == 1 ){
                    $id_user_seg = $row_f['Id_user_1'];
                } else {
                    $id_user_seg = $row_f['Id_user_2'];
                }

                $sql_datos_siguiendo = mysqli_query($conn, "SELECT * FROM Usuarios WHERE Id_user = '$id_user_seg'");
                
                while ($row_u_f = mysqli_fetch_array($sql_datos_siguiendo)) {
                    ?><li> <?php echo $row_u_f['Nombre_cuenta']?> <a href="perfil_user.php?id_user=<?php echo $id_user_seg; ?>"> @<?php echo $row_u_f['Username']; ?></a></li>
                    <?php
                }
            }
            ?><ul>
        </div>
        <div class="box usmitos">
            <h4>Usmitos publicados:</h4>
            <div class="post_content">
                <?php
                if (mysqli_num_rows($sql_sel_usmitos) > 0) {
                    while ($row_u = mysqli_fetch_array($sql_sel_usmitos)) {?>
                    <div class="post_body">
                        <div class="post_header">
                            <h3><?php echo $_SESSION['nombre_cuenta']?></h5>
                            <p>@<?php echo $_SESSION['usuario']?></p>
                        </div>
                        <div class="textou">
                            <p><?php echo $row_u['Texto'];?></p>
                        </div>
                        <div class="fecha">
                            <p style="float: right;"><?php echo $row_u['Fecha'];?></p>
                        </div>
                        <?php if($row_u['Es_privado']==1){?>
                            <img src="https://image.flaticon.com/icons/png/512/2107/2107957.png" alt="" width="18px" style="float: right;">
                            <?php 
                            $es_priv = "checked";
                            }else {
                                $es_priv = "";
                            }; ?>
                        <div class="tags">
                            <?php 
                            $id_usmito_actual = $row_u['Id_usmito'];
                            $tags_actuales= usmitoTags($id_usmito_actual);
                            if($tags_actuales != array(false)){
                            foreach($tags_actuales as $d_tag){
                                echo "#".$d_tag."   ";
                            }}
                            echo "<br><br> Reusmeado : ".cant_reusmeado_usmito($id_usmito_actual)."  Likes : ".cant_likes_usmitos($id_usmito_actual)."<br><br>";
                            display_comments($id_usmito_actual);
                            ?>
                        
                        </div>
                        <div class="posat_options">
                            <form action="./../logic/update_usmito.php">
                                <input type="text" name="cambio_txt" value="<?php echo $row_u['Texto']?>" required>
                                <input type="hidden" name='id_usmito_act' value="<?php echo $id_usmito_actual; ?>">
                                <input type="submit" name="cambio_en_txt" value="cambiar este usmito">
                            </form>
                            <form action="./../logic/update_usmito.php">
                                <input type="text" name="cambio_tags" value="#<?php echo implode("#", $tags_actuales)?>" required>
                                <input type="hidden" name='id_usmito_act' value="<?php echo $id_usmito_actual; ?>">
                                <input type="submit" name="cambio_en_tags" value="cambiar los tags">
                            </form>
                            <form action="./../logic/update_usmito.php">
                                <p><input type="checkbox" name="privado" id="amigos" <?php echo $es_priv; ?>><label for="privado" class="cb_lb">privado</label> </p>
                                <input type="hidden" name='id_usmito_act' value="<?php echo $id_usmito_actual; ?>">
                                <input type="submit" name="cambio_en_priv" value="Modificar privacidad">
                            </form>
                            <form action="mi_perfil.php">
                                <input type="hidden" name='id_usmito_borrar' value="<?php echo $id_usmito_actual; ?>">
                                <input type="submit" name="borrar" value="Borrar Usmito">
                            </form>
                        </div>
                    </div>
                        <?php } }else {
                            echo "Aún no publicas tu primer usmito! :c";}?>
            </div>
            <h4>Usmitos reusmeados:</h4>
            <?php display_reusmeados($id, $_SESSION['usuario']) ;?>
        </div>
    </div>
</body>
</html>