<?php
include('./../database/connection.php');
session_start();
$id = $_SESSION['id_user'];

$conn=conecta_db();

if(!$conn){
    die('Connection error: '. mysqli_connect_error());
}

$res = mysqli_query($conn, "SELECT * FROM Usmitos WHERE Id_user='$id'");

function usmitoTags($id_usmito){
    $conn=conecta_db();

    $tags = mysqli_query($conn, "SELECT Id_tag FROM Usmitos_poseen_tags WHERE Id_usmito='$id_usmito'");

    if (mysqli_num_rows($tags) > 0) {

        $array_id_tags=array();

        while ($row = mysqli_fetch_array($tags)) {
            $array_id_tags[] = $row['Id_tag'];
        }

        $array_tags = array();

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

?>