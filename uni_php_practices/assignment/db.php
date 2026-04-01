<?php
  $server = "localhost";
  $db = "stocking";
  $user = "root";
  $password = "kira7!23A5";
  
  $conn = new mysqli($server, $user, $password, $db);
  
  if($conn->connect_error){
    die("Connection Error : " . $conn->connect_error);
  }
?>