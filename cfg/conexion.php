<?php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db = "cpt3db";

   $conexion = new mysqli($host,$user,$pass,$db);
   $conexion->set_charset("utf8");

   if (!$conexion) {
    echo 'Conexion Fallida';
   }