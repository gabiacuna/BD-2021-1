<?php
include('./../logic/funciones.php');
session_start();

//Logica + Vista de la busqueda 

$conn=conecta_db();

$busqueda_in = trim($_GET['busqueda']);     //Extraccion del txt que se este buscando

$sql_find_tag = mysqli_query($conn, "SELECT * FROM Tags WHERE Tag='$busqueda_in'");
$sql_find_user = mysqli_query($conn, "SELECT * FROM Usuarios WHERE Username='$busqueda_in' OR Nombre_cuenta='$busqueda_in'");

function Busqueda($busqueda)
{
    $conn=conecta_db();

    if(!$conn){
        die('Connection error: '. mysqli_connect_error());
    }

    $sql_find_tag = mysqli_query($conn, "SELECT * FROM Tags WHERE Tag='$busqueda'");
    $sql_find_user = mysqli_query($conn, "SELECT * FROM Usuarios WHERE Username='$busqueda' OR Nombre_cuenta='$busqueda'");
    $sql_find_usmito = mysqli_query($conn, "SELECT * FROM Usmitos WHERE Texto='$busqueda'");

    $res = array();

    //contador, si llega a 3 es por que no se encontro resultado a la busqueda
    $not_f = 0;

    if (mysqli_num_rows($sql_find_tag) > 0){
        $res[] = "tag";
        while ($row = mysqli_fetch_array($sql_find_tag)) {
            $res[] = $row['Tag'];
        }
    }else {
        $not_f += 1;
    }

    if (mysqli_num_rows($sql_find_user) > 0) {
        $res[] = "user";
        while ($row = mysqli_fetch_array($sql_find_user)) {
            $res[] = $row['Username'];
            $res[] = $row['Nombre_cuenta'];
            $res[] = $row['Id_user'];
        }        
    }else {
        $not_f += 1;
    }

    if (mysqli_num_rows($sql_find_usmito) > 0) {
        $res[] = "usmito";
        while ($row = mysqli_fetch_array($sql_find_usmito)) {
            $id_user_usmito = $row['Id_user'];
            $res[] = $row['Id_usmito'];
        }
    }else {
        $not_f += 1;
    }

    if($not_f == 3){
        $res[] = "no";
    }
    return $res;
}

$resultado = Busqueda($busqueda_in);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busqueda</title>
</head>
<body>
<form id="nav_bar">
        <input type="button" value="atras" onclick="history.back()">
        <input type='button' value='recargar' onclick='window.location.reload()'>
        <input type="button" value="adelante" onclick="history.back()">
</form>
    <div class="headder">
        <a href="./../vistas/home.php"><img src="./../imgs/logoUSMwer.png" alt="logo" style="width: 8%;"></a>
        <h1>Los resultados de la busqueda:</h1>
    </div>
    
    <?php
    $i = 0;
    if ($resultado[$i] == "tag") {
        foreach ($resultado as $res) {
            if ($res != "tag") {
                ?><h2><?php echo '#'.$res;?></h2> <?php
                while ($row = mysqli_fetch_array($sql_find_tag)) {
                    $id_tag = $row['Id_tag'];
                    display_usmitos_from_id_tag($id_tag);
                }
            } elseif ($res == "user" or $res == "usmito") {
                break;
            }
            $i += 1;
        }
    }
    if (count($resultado)>$i) {
        if($resultado[$i] == "user"){
            while ($row = mysqli_fetch_array($sql_find_user)) {
                $id_user_res = $row['Id_user'];
                ?>
                <div class="post_header">
                    <h3><?php echo $row['Nombre_cuenta']; ?></h5>
                    <a href="./../vistas/perfil_user.php?id_user=<?php echo $id_user_res; ?>"> @<?php echo $row['Username']; ?></a>
                </div><?php
            }
        }
        foreach ($resultado as $res) {
            if ($res == "usmito") {
                break;
            }
            $i += 1;}
    }
    if (count($resultado)>$i){
        if($resultado[$i] == "usmito"){
            ?> <h3>Usmito:</h3><?php
            foreach ($resultado as $res) {
                display_usmito($res);
            }
        }
    }
    

    if($resultado[0] == 'no'){
        echo "No se encontro match para tu busqueda :(";
    }    
    ?>
    <div class="buscador">
        <form action="buscar.php">
            <input type="text" name="busqueda" placeholder="Username, tag, usmito....">
            <input type="submit" value="Buscar">
        </form>
    </div>
    <p>Usuario Actual : <a href="./../vistas/mi_perfil.php" > <?php session_start(); echo $_SESSION['nombre_cuenta']?>  </a></p>
    
</body>
</html>