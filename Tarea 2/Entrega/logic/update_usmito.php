<?php
include('./../logic/funciones.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

// CRUD para el usmito

if (isset($_GET['cambio_en_txt'])){
    $new_txt = $_GET['cambio_txt'];
    $id_usmito_act = $_GET['id_usmito_act'];

    if (mysqli_query($conn, "UPDATE Usmitos SET Texto = '$new_txt' WHERE Id_usmito = '$id_usmito_act'")){
        echo "Usmito actualizado";
        header("location:./../vistas/mi_perfil.php");
    }else {
        echo "No se puede actualizar :c";
    }
}


if (isset($_GET['cambio_en_tags'])){
    $tags_in = $_GET['cambio_tags'];
    $id_usmito = $_GET['id_usmito_act'];
    $tags[]= explode("#", $tags_in);
    $tags = array_slice($tags[0], 1);
    
    //Primero borrar todas las apariciones de este usmito en la tabla Usmitos_poseen_tags
    $del_app = mysqli_query($conn, "DELETE FROM Usmitos_poseen_tags WHERE Id_usmito = '$id_usmito'");

    foreach($tags as $key => $tag){
        // Primero, extaer el id tag
        
        $consulta="SELECT * FROM Tags where Tag='$tag'";
        error_log($consulta);

        $resultado=mysqli_query($conn,$consulta);
        while($row = $resultado->fetch_assoc()) {
            $id_tag = $row["Id_tag"];
        }

        $count = mysqli_num_rows($resultado);
        
        // Si el tag no existe, este se agrega a tags

        if($count==0){
            $ins_tag="INSERT INTO Tags (Tag) VALUES ('$tag')";
            if (mysqli_query($conn,$ins_tag)) {
                $sql_new_tag="SELECT * FROM Tags where Tag='$tag'";
                $resultado=mysqli_query($conn,$sql_new_tag); 
                while($row = $resultado->fetch_assoc()) {
                    $id_tag = $row["Id_tag"];
                }
            } else {
                $mensaje_error = "No se pudo crear este tag :( ".mysqli_error($conn);
            }
        }

        //Se agrega el tag correspondiente de nuevo a la relación

        $poseen = "INSERT INTO Usmitos_poseen_tags (Id_tag, Id_usmito) VALUES ('$id_tag', '$id_usmito')";

        if (mysqli_query($conn,$poseen)) {
            header("location:./../vistas/mi_perfil.php");
        } else {
            $mensaje_error = "No se pudo crear este tag :( ".mysqli_error($conn);
        }
    }
}

if (isset($_GET['cambio_en_priv'])){
    $id_usmito = $_GET['id_usmito_act'];
    
    if($_GET['privado']) {
        $es_priv=1;
    } else {
        $es_priv=0;
    }

    $sql_cambio = "UPDATE Usmitos SET Es_privado='$es_priv' WHERE Id_usmito='$id_usmito'";

    error_log($sql_cambio);

    if (mysqli_query($conn, $sql_cambio)) {
        header("location:./../vistas/mi_perfil.php");
    } else {
        $mensaje_error = "No se pudo modificar la privacidad :( ".mysqli_error($conn);
    }
}

?>