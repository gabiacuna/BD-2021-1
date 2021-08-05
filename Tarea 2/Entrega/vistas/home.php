<?php
include('./../logic/funciones.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

// Esta es la pagina principal :)

//Extraer el id de los usuarios a los que sigue el user actual
$sql_following = mysqli_query($conn, "SELECT *  FROM User1_sigue_User2 WHERE Id_user_1 = '$id' OR( Id_user_2='$id' AND Mutuo = 1)");

$sql_list_following = mysqli_query($conn, "SELECT * FROM User_sigue_lista WHERE Id_user = '$id'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USMwer - Home</title>
    <link rel = "stylesheet" href="./../css/home.css">
    <link rel="icon" href="./../imgs/logoUSMwer.png">
</head>
<body>
    <form id="nav_bar">
        <input type="button" value="atras" onclick="history.back()">
        <input type='button' value='recargar' onclick='window.location.reload()'>
        <input type="button" value="adelante" onclick="history.back()">
    </form>
    
    <div class="grid">
        <div class="col-1-of-3">
            <h1> Bienvenid_<3</h1>
            <div id="mi_perfil_but">
              <a href="mi_perfil.php" >  <?php session_start(); echo $_SESSION['nombre_cuenta']?>  </a>
            </div>
            <h2>Listas que sigues:</h2>
                <?php
                if (mysqli_num_rows($sql_list_following)) {
                while($row_l = mysqli_fetch_array($sql_list_following)){
                    $id_lista_act = $row_l['Id_lista'];

                    $sql_name_lista = mysqli_query($conn, "SELECT * FROM Listas WHERE Id_lista = '$id_lista_act'");
                    while ($temp = mysqli_fetch_array($sql_name_lista)) {
                        $name_lista_act = $temp['Nombre_lista'];
                    }

                    $sql_followers_lista = mysqli_query($conn, "SELECT 1 FROM User_sigue_lista WHERE Id_lista = '$id_lista_act'");
                    ?>
                    <div class="listas">
                    <h4><a href="mostrar_lista.php?id_lista=<?php echo $id_lista_act; ?>"><?php echo $name_lista_act;?></a></h4>
                    <p> seguidores : <?php echo mysqli_num_rows($sql_followers_lista) - 1;?> </p>
                    </div>

                <?php }
              }else {
                  echo "No sigues a ninguna lista :'c";
              }
              ?>            
        </div>
        <div class="col-2-of-3">
            <!--Inicio Tweet Box-->
            <div class="tweetbox">
                <form action="./../logic/public_usmito.php" method="post">
                    <div class="tweetbox_input">
                      <p><textarea name="in_text" cols="48" rows="3" maxlength="279" placeholder="Qué está pasando?" required></textarea> </p>
                    </div>
                    <p>Tags: <input type="textbox" id="tag" name="tag"></p>
                    <p><input type="checkbox" name="privado" id="amigos"><label for="privado" class="cb_lb">privado</label> </p>
                    <input type="submit" value="Usmear">
                    <div class='warning'>
                      <p><b><?php echo $mensaje_error?></b></p>
                  </div>
                </form>
            </div>
            <!-- Inicio Posts -->
            <div>
                <?php
                if (mysqli_num_rows($sql_following) > 0){
                    while ($row = mysqli_fetch_array($sql_following)) {
                        if ($row['Mutuo'] == 1 and $row['Id_user_1'] != $id) {
                            $id_user_2 = $row['Id_user_1'];
                        } else {
                            $id_user_2 = $row['Id_user_2'];
                        }
                        if($row['Mutuo']==1){
                            $son_cf = true;
                        }else {
                            $son_cf = false;
                        }
                        $cons_datos_user=mysqli_query($conn,"SELECT Username, Nombre_cuenta FROM Usuarios WHERE Id_user='$id_user_2'");
                            
                        while($datos = $cons_datos_user->fetch_assoc()) {
                            $username_2 = $datos['Username'];
                            $n_cuenta_2 = $datos['Nombre_cuenta'];
                            }

                        $usmitos_user_2 = mysqli_query($conn, "SELECT * FROM Usmitos WHERE Id_user='$id_user_2'");
                        
                        if (mysqli_num_rows($usmitos_user_2) > 0){
                            
                            while($usmito_user_2 = mysqli_fetch_array($usmitos_user_2)){
                                if($usmito_user_2['Es_privado']==0 or $son_cf){
                                    
                                    ?>
                                    <div class="post_body">
                                        <div class="post_header">
                                            <h3><?php echo $n_cuenta_2; ?></h3>
                                            <a href="perfil_user.php?id_user=<?php echo $id_user_2; ?>"> @<?php echo $username_2; ?></a>
                                        </div>
                                        <div class="textou">
                                            <p><?php echo $usmito_user_2['Texto'];?></p>
                                        </div>
                                        <div class="fecha">
                                            <p style="float: right;"><?php echo $usmito_user_2['Fecha'];?></p>
                                        </div>
                                        <div class="tags">
                                            <?php 
                                            $id_usmito_actual = $usmito_user_2['Id_usmito'];
                                            $tags_actuales= usmitoTags($id_usmito_actual);
                                            if($tags_actuales != array(false)){
                                                foreach($tags_actuales as $d_tag){
                                                    echo "#".$d_tag."   ";
                                            }}
                                            if($son_cf and $usmito_user_2['Es_privado']==1){
                                                ?><img src="https://image.flaticon.com/icons/png/512/2107/2107957.png" alt="" width="18px" style="float: right;"><?php
                                            }
                                            if (user_likes_usmito($id, $id_usmito_actual)) {
                                                $status_like = "Dislike";
                                            }else {
                                                $status_like = "Like";
                                            }
                                            if(user_ha_reusmeado($id, $id_usmito_actual)){
                                                $status_reus = "DesReusmear";
                                            }else {
                                                $status_reus = "Reusmear";
                                            }                          
                                            ?>
                                        </div>
                                        <div class="post_options">
                                            <form action="./../logic/like.php" method="post">
                                                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito_actual; ?>">
                                                <input type="submit" name="like" id="btn_like" value="<?php echo $status_like; ?>">
                                                <p><?php echo cant_likes_usmitos($id_usmito_actual)?></p>
                                            </form>
                                            <form action="./../logic/reusm.php" method="post">
                                                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito_actual; ?>">
                                                <input type="submit" name="reusm" id="btn_reusm" value="<?php echo $status_reus; ?>">
                                                <p><input type="checkbox" name="privado"><label for="privado" class="cb_lb">privado (reus)</label> </p>
                                                <p><?php echo cant_reusmeado_usmito($id_usmito_actual)?></p>
                                            </form>
                                            <form action="./../logic/comment.php" method="post">
                                                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito_actual; ?>">
                                                <p>Comentar <input type="text" name="comment" id="btn_coment" maxlength="279" required> </p>
                                                <input type="submit" name="comm" value="Subir comentario">
                                            </form>
                                            <div class="comentarios">
                                                <?php display_comments($id_usmito_actual)?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }

                        display_reusmeados($id_user_2, $username_2);
                    }
                } else {
                    echo "Las personas que sigues no han publicado nada aun :c";
                }
                ?>
                    
            </div>
                
        </div>
        <div class="col-3-of-3">
            <div class="buscador">
                <form action="./../logic/buscar.php">
                    <input type="text" name="busqueda" placeholder="Username, tag, usmito....">
                    <input type="submit" value="Buscar">
                </form>
            </div>
            <div class="tendencia">
                <h2>Tendencia de Tags:</h2>
                <?php
                $sql_view_top = mysqli_query($conn,  "SELECT * FROM tendencia_tags WHERE ROWNUM() <= 10");
                if(mysqli_num_rows($sql_view_top)>0){
                    while($row = mysqli_fetch_array($sql_view_top)){
                        ?>
                        <div class="tag">
                            <h4>#<?php echo $row['Tag']; ?> </h4>
                            <p> <?php echo $row['count']; ?> </p>
                        </div>
                        <?php
                    }
                }else {
                    ?> <h4>Aún no hay tendencia, crea un post con #Tags y empieza una!</h4> <?php
                }
                ?>

            </div>
        </div>
    </div>
</body>
</html>
