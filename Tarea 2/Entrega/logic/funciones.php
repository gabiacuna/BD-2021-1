<?php
include('./../database/connection.php');
session_start();

//Para mostrar los tags que tiene un usmito
function usmitoTags($id_usmito){
    $conn=conecta_db();

    $tags = mysqli_query($conn, "SELECT Id_tag FROM Usmitos_poseen_tags WHERE Id_usmito='$id_usmito'");

    if (mysqli_num_rows($tags) > 0) {

        $array_id_tags=array();

        while ($row = mysqli_fetch_array($tags)) {
            $array_id_tags[] = $row['Id_tag'];
        }

        $array_tags = array();  //se retorna un arreglo con los id de los tags del usmito

        foreach($array_id_tags as $id_tag){
            $ext_tag = mysqli_query($conn, "SELECT Tag FROM Tags WHERE Id_tag='$id_tag'");
            while ($row = mysqli_fetch_array($ext_tag) ) {
                $array_tags[]= $row['Tag'];
            }
        }
        return $array_tags;
    } else {
        return array(false);
    }
}

//Retorna true si dos usuarios son mejores amigos
function son_cf($id_user){
    $conn=conecta_db();

    $id = $_SESSION['id_user'];

    $sql_mutuo = mysqli_query($conn, "SELECT Mutuo FROM User1_sigue_User2 WHERE (Id_user_1 = '$id' AND Id_user_2 = '$id_user') OR (Id_user_2 = '$id' AND Id_user_1 = '$id_user')");

    if (mysqli_num_rows($sql_mutuo) > 0) {
        while ($row = mysqli_fetch_array($sql_mutuo)) {
            if ($row['Mutuo'] == 1) {
                return true;
            }else {
                return false;
            }
        }
    }
    return false;
}

// Para mostrar cualquier usmito, incluye a los reusmeados
function display_usmito($id_usmito, $msg_reus = "")
{
    $conn=conecta_db();

    $res = mysqli_query($conn, "SELECT * FROM Usmitos WHERE Id_usmito='$id_usmito'");

    if (mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_array($res)) {
            $id_user = $row['Id_user'];
            if ($row['Es_privado'] == 1) {
                $res_son_cf = son_cf($id_user);
                $puede_ver = $res_son_cf;
            }else {
                $puede_ver = true;
                $res_son_cf = false;
            }

            if ($puede_ver) {
            $sql_data_usr = mysqli_query($conn, "SELECT Username, Nombre_cuenta FROM Usuarios WHERE Id_user='$id_user'");
            while($datos = $sql_data_usr->fetch_assoc()) {
                $username = $datos['Username'];
                $n_cuenta = $datos['Nombre_cuenta'];
                }
            ?>
            <style>
                .post {
                    background-color: #BDE4A8;
                    border:1px solid #14213d;
                    border-radius: 20px;
                    margin-top: 10px;
                    margin-bottom: 10px;
                    padding: 10px;
                    position: relative;
                }
                p{
                    color: #14213d;
                }
            </style>
            <div class="post">
                <p><?php echo $msg_reus; ?></p>
                <h3><?php echo $n_cuenta; ?></h5>
                <a href="./../vistas/perfil_user.php?id_user=<?php echo $id_user; ?>"> @<?php echo $username; ?></a>
                <div class="textou">
                    <p><?php echo $row['Texto'];?></p>
                </div>
                <p style="float: right;">likes : <?php echo cant_likes_usmitos($id_usmito)."  "?></p>
                <p style="float: right;">Reusmeado : <?php echo cant_reusmeado_usmito($id_usmito)."&emsp;"?></p>
            <?php
            $tags_actuales= usmitoTags($id_usmito);
            if($tags_actuales != array(false)){
                foreach($tags_actuales as $d_tag){
                    echo "#".$d_tag."   ";
            }}
            if($res_son_cf and $row['Es_privado']==1){
                ?><img src="https://image.flaticon.com/icons/png/512/2107/2107957.png" alt="" width="18px" style="float: right;"><?php
            }
            ?>
            <p><?php echo $row['Fecha'];?></p>
            <?php $id = $_SESSION['id_user'];
            if (user_likes_usmito($id, $id_usmito)) {
                $status_like = "Dislike";
            }else {
                $status_like = "Like";
            }
            if(user_ha_reusmeado($id, $id_usmito)){
                $status_reus = "DesReusmear";
            }else {
                $status_reus = "Reusmear";
            }  
            ?>
            <form action="./../logic/like.php" method="post">
                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito; ?>">
                <input type="submit" name="like" id="btn_like" value="<?php echo $status_like; ?>">
            </form>
            <form action="./../logic/reusm.php" method="post">
                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito; ?>">
                <input type="submit" name="reusm" id="btn_reusm" value="<?php echo $status_reus; ?>">
                <input type="checkbox" name="privado"><label for="privado" class="cb_lb">privado (reus)</label>
            </form>
            <form action="./../logic/comment.php" method="post">
                <input type="hidden" name='id_usmito' value="<?php echo $id_usmito; ?>">
                Comentar <input type="text" name="comment" id="btn_coment" maxlength="279" required>
                <input type="submit" name="comm" value="Subir comentario">
            </form>
            
            <?php display_comments($id_usmito); ?>

            </div><?php
            }
        }
    }
}

//Muestra todos los usmitos asociados a un tag que el usuario actual pueda ver
function display_usmitos_from_id_tag($id_tag)
{

    $conn=conecta_db();

    $usmitos = mysqli_query($conn, "SELECT Id_usmito FROM Usmitos_poseen_tags WHERE Id_tag='$id_tag'");
    
    if (mysqli_num_rows($usmitos) > 0) {
        while ($row = mysqli_fetch_array($usmitos)) {
            $id_usmito = $row['Id_usmito'];
            display_usmito($id_usmito);
        }
    }

}

//Retorna la cantidad de likes que posee un usmito
function cant_likes_usmitos($id_usmito)
{
    $conn=conecta_db();

    $sql_query = mysqli_query($conn, "SELECT * FROM User_like_usmito WHERE Id_usmito='$id_usmito'");

    return mysqli_num_rows($sql_query);
}

//Retorna true si al user actual le gusta un usmito
function user_likes_usmito($id_user, $id_usmito){
    
    $conn=conecta_db();

    $sql_query = mysqli_query($conn, "SELECT 1 FROM User_like_usmito WHERE Id_user = '$id_user' AND Id_usmito = '$id_usmito'");

    if (mysqli_num_rows($sql_query) > 0) {
        return true;
    }
    return false;
}

//Retorna la cantida de veces que ha sido reusmeado un usmito
function cant_reusmeado_usmito($id_usmito)
{
    $conn=conecta_db();

    $sql_c_reus = mysqli_query($conn, "SELECT 1 FROM User_reusmea_usmito WHERE Id_usmito = '$id_usmito'");

    return mysqli_num_rows($sql_c_reus);
}

//retorna si el user actual reusmeo un usmito
function user_ha_reusmeado($id_user, $id_usmito)
{
    $conn=conecta_db();

    $sql_c_reus = mysqli_query($conn, "SELECT * FROM User_reusmea_usmito WHERE Id_user = '$id_user' AND Id_usmito = '$id_usmito'");

    if( mysqli_num_rows($sql_c_reus) == 1){
        return true;
    }
    return false;
}

// Muestra las respuestas a un usmito
function display_comments($id_usmito)
{
    ?>
    <h5>Respuestas:</h5>
    <?php
    $conn=conecta_db();

    
    
    $sql_find_comment = mysqli_query($conn, "SELECT * FROM Usuarios_responden_usmitos WHERE Id_usmito = '$id_usmito'");

    if (mysqli_num_rows($sql_find_comment) > 0) {
        while ($row = mysqli_fetch_array($sql_find_comment)) {
            $id_user = $row['Id_user'];
            $txt = $row['Texto_respuesta'];
            
            $sql_name_user = mysqli_query($conn, "SELECT Username, Nombre_cuenta FROM Usuarios WHERE Id_user = '$id_user'");

            while ($res = mysqli_fetch_array($sql_name_user) ) {
                $username = $res['Username'];
                $n_cuenta = $res['Nombre_cuenta'];
            }

            ?>
            <div class="un_comment">
                <h4> <?php echo $n_cuenta."  @".$username; ?> </h4>
                <p> <?php echo $txt; ?> </p>
            </div>
            <?php
        }
    }else {
        ?>
        <div class="un_comment">
            <p> <i> Aún no tiene respuestas <br> </i> </p>
        </div>
        <?php
    }
}

// Muestra los usmitos reusmeados por un usuario
function display_reusmeados($id_user, $username)
{
    $conn=conecta_db();

    

    $sql_reus = mysqli_query($conn, "SELECT * FROM User_reusmea_usmito WHERE Id_user = '$id_user'");

    if (mysqli_num_rows($sql_reus)) {
        while ($data_reus = mysqli_fetch_array($sql_reus)) {
            $id_usmito_og = $data_reus['Id_usmito'];
            display_usmito($id_usmito_og, "reusmeado por @$username");
        }
        return true;
    } else {
        return false;
    }


}
?>