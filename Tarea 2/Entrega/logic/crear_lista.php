<?php 
include('./../database/connection.php');
session_start();

$id = $_SESSION['id_user'];

$conn=conecta_db();

// En este archivo se mostrará el paso intermedio para crear una lista (Seleccionar los usuarios que perteneceran a ella).

if (isset($_POST['crear'])) {

    $name_lista =  $_POST['name_lista'];

    $sql_crear_list = "INSERT INTO Listas (Id_user_creador, Nombre_lista) VALUES ('$id', '$name_lista')";

    if (mysqli_query($conn,$sql_crear_list)) {
        $id_lista = mysqli_insert_id($conn);
        $mensaje_error = "Lista creada! :)";
    }else {
        $mensaje_error = "No se pudo crear la lista :(";
    }

    error_log("______id_lista____________".$id_lista);

    $all_users = mysqli_query($conn, "SELECT * FROM Usuarios");

    $len = mysqli_num_rows($all_users) - 1;

    // Si esta seteado el index de un usuario, quiere decir que este fue checkeado y debe ser agregado a la lista
    foreach (range(0,$len) as $index) {
        if (isset($_POST[$index])) {
            $id_user_actual = $_POST["id_user_".$index];
            $sql_ins = "INSERT INTO User_pertenece_a_lista (Id_user, Id_lista) VALUES ('$id_user_actual', '$id_lista')";
            $sql_ins_user_list = mysqli_query($conn, $sql_ins);

            error_log('___sql ins: ___'.$sql_ins);

            if (!$sql_ins_user_list) {
                $mensaje_error = "No se pudo agregar a ".$index." a la lista :(";
            }
        }
    }

    header("location:./../vistas/mi_perfil.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Lista</title>
</head>
<body>
<form id="nav_bar">
        <input type="button" value="atras" onclick="history.back()">
        <input type='button' value='recargar' onclick='window.location.reload()'>
        <input type="button" value="adelante" onclick="history.back()">
        <a href="./../vistas/home.php"><img src="./../imgs/logoUSMwer.png" alt="logo" style="width: 8%;"></a>
</form>
<p class='warning' ><b><?php echo $mensaje_error?></b></p>
    <form action="crear_lista.php" method="post">
        <div class="headder">
            <h2>Dale nombre a tu nueva lista:</h2>
            <input type="text" name="name_lista" placeholder="Nombre de la lista" required>
            <h2>Agrega a los usuarios a tu lista!</h2>
            <p>Solo debes chequear a los que quieres que pertenezcan a la nueva lista</p>
        </div>
        <?php 
        $i = 0;
        $all_users = mysqli_query($conn, "SELECT * FROM Usuarios");
        while ($user = mysqli_fetch_array($all_users)) {
            $n_cuenta = $user['Nombre_cuenta'];
            $username = $user['Username'];
            $id_user = $user['Id_user'];
            ?> 
            <input type="hidden" name="id_user_<?php echo $i ; ?>" value="<?php echo $id_user ; ?>">
            <p><input type="checkbox" name="<?php echo $i ; ?>"><?php echo $n_cuenta ; ?><a href="./../vistas/perfil_user.php?id_user=<?php echo $id_user; ?>"> @<?php echo $username; ?></a></p>
            <?php
            $i +=1;
        }
        ?>
        <input type="submit" value="Crear!" name="crear">
    </form>

    <p>Usuario Actual : <a href="./../vistas/mi_perfil.php" > <?php session_start(); echo $_SESSION['nombre_cuenta']?>  </a></p>
</body>
</html>