<?php
include('database/connection.php');
session_start();
$usuario=$_POST['usuario'];
$contraseña=$_POST['contraseña'];

// Login para usmwer :)

if ($usuario != "")
{

  $conn=conecta_db();

  $consulta="SELECT * FROM Usuarios where Username='$usuario' and Clave='$contraseña'";

  //Hacer query y conseguir resultado
  $resultado=mysqli_query($conn,$consulta);

  if (!$resultado) {
    echo 'Invalid query: ' . mysqli_connect_error();
    die('Invalid query: ' . mysqli_connect_error());
  }

  //Fetch las filas resultantes
  $filas=mysqli_fetch_all($resultado, MYSQLI_ASSOC);
  $count = mysqli_num_rows($resultado);

  if ($count == 1) {

    $consulta_id="SELECT Id_user, Nombre_cuenta FROM Usuarios WHERE Username='$usuario'";

    $resultado_id=mysqli_query($conn,$consulta_id);


    //esxtraer el id de acá!
    while($row = $resultado_id->fetch_assoc()) {
      $_SESSION['id_user']= $row["Id_user"];

      $_SESSION['nombre_cuenta']= $row["Nombre_cuenta"];

      $_SESSION['usuario'] = $usuario;

      error_log("______usuario en session:________".$_SESSION['usuario']);

    }


    header("location:vistas/home.php");
  }else {
    $mensaje_error = "Usuario o Clave invalida!!!";
  }

  //Liberar el resultado
  mysqli_free_result($resultado);
}

// mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>USMwer - Login</title>
  <link rel = "stylesheet" href="css/loginStyle.css">
</head>

<body>
  <div class="logoimg">
    <img src="/imgs/logoUSMwer.png" alt="logo USMwer" class="image" style="width:400px">
  </div>
  <div class="login">
    <form action="index.php" method="post" id="form_login">
    <h1> Login USMwer</h1>
    <p>Username <input type="text" placeholder="ingrese su username" name="usuario"></p>
    <p>Contraseña <input type="password" placeholder="ingrese su contraseña" name="contraseña"></p>

    <p class='warning' ><b><?php echo $mensaje_error?></b></p>

    <input type="submit" value="Ingresar">
  </div>
  <div style="text-align:center" >
  <br><br>
    <a href="vistas/signup.php" > Registrarme! </a>
  </div>

</body>
</html>
