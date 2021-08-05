<?php
include('./../database/connection.php');
session_start();

// Vista para registrarse en usmwer :)

$usuario_n=$_POST['usuario_n'];
$name_cuenta_n = $_POST['name_cuenta_n'];
$contraseña_n=$_POST['contraseña_n'];
$contraseña_rep=$_POST['contraseña_rep'];

if ($usuario_n != "")
{

  $conn=conecta_db();

  

  if($contraseña_n != $contraseña_rep){
    $mensaje_error = "Las contaseñas no coinciden!, intentalo de nuevo";
  } else {
    $consulta="INSERT INTO Usuarios (Username, Nombre_cuenta, Clave) VALUES ('$usuario_n','$name_cuenta_n','$contraseña_n')";

    $_SESSION['usuario'] = $usuario_n;
    $_SESSION['nombre_cuenta']= $name_cuenta_n;
    

    if (mysqli_query($conn,$consulta)) {

        $_SESSION['id_user']=  mysqli_insert_id($conn); //esxtraer el id del usr recien incertado 

        //$_SESSION['login_user'] = $myusername;
        echo "Usuario ingresado exitosamente!! :D";

        header("location:home.php");

    }else {
        $mensaje_error = "No se pudo registrar el usuario :c " . mysqli_error($conn);
    }
}
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USMwer - SignUp</title>
    <link rel = "stylesheet" href="../css/loginStyle.css">
</head>
<body>
<form action="signup.php" method="post">
    <h1> Ingresa tus datos :)</h1>
    <p>Username (Este debe ser único)<input type="text" placeholder="ingresa tu username" name="usuario_n" required></p>
    <p>Nombre de Cuenta <input type="text" placeholder="ingresa tu nombre de cuenta" name="name_cuenta_n" required></p>
    <p>Contraseña <input type="password" placeholder="ingresa tu contraseña" name="contraseña_n" required></p>
    <p>Repite tu contraseña <input type="password" placeholder="repite tu contraseña" name="contraseña_rep" required></p>
    <div class='warning'>
        <p><b><?php echo $mensaje_error?></b></p>
    </div>
    <input type="submit" value="Listo!">
</body>
</html>
