<?php
include('./../database/connection.php');
session_start();
$in_text=trim($_POST['in_text']);
$tags_in=trim($_POST['tag']);
$id_user=$_SESSION['id_user'];

//Si se ingresó texto, agrega el el usmito asociado al usuario actual
if ($in_text != "")
{
  $conn=conecta_db();

  if(filter_has_var(INPUT_POST,'privado')) {
    $es_priv=1;
  } else {
    $es_priv=0;
  }

  $ins="INSERT INTO Usmitos (Id_user, Es_privado, Texto) VALUES ('$id_user','$es_priv','$in_text')";

  if (mysqli_query($conn,$ins)) {
    $id_usmito = mysqli_insert_id($conn);

    header("location:./../vistas/home.php");

  }else {
    $mensaje_error = "No se pudo crear este usmito :( ".mysqli_error($conn);
  }
}

// Agrega los tags en caso de ser necesario
if ($tags_in!=""){
  
  $tags[]= explode("#", $tags_in);
  $tags = array_slice($tags[0], 1);

  foreach($tags as $key => $tag){
    $consulta="SELECT * FROM Tags where Tag='$tag'";

    $resultado=mysqli_query($conn,$consulta);
    $count = mysqli_num_rows($resultado);

    error_log("Llegó para ak!", 0);

    if($count!=1){
      $ins_tag="INSERT INTO Tags (Tag) VALUES ('$tag')";
      if (mysqli_query($conn,$ins_tag)) {
        $consulta="SELECT * FROM Tags where Tag='$tag'";
        $resultado=mysqli_query($conn,$consulta);    
      }else {
        $mensaje_error = "No se pudo crear este tag :( ".mysqli_error($conn);
      }
    }
    while($row = $resultado->fetch_assoc()) {
      $id_tag = $row["Id_tag"];
    }
    $poseen = "INSERT INTO Usmitos_poseen_tags (Id_tag, Id_usmito) VALUES ('$id_tag', '$id_usmito')";
    if (mysqli_query($conn,$poseen)) {
      $mensaje_error = "No se pudo crear este tag :( ".mysqli_error($conn);

    }
  }
}
?>