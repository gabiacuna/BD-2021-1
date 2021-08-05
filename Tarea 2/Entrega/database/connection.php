<?php
// Coneccion a la db

function conecta_db()
{
   $conn=mysqli_connect("localhost","root","Deadpool","USMwer");

   if(!$conn){
      error_log('Error en la conección: '. mysqli_connect_error());
      echo 'Error en la conección: '. mysqli_connect_error();
   }
   
   return $conn;
}

?>
