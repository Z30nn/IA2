<?php
// $conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database)or die('Cannot open database');	
// $con=mysqli_connect("localhost", "id13019632freeprojectscodes.com", "PASS=word@freeprojectscodes.com", "id13019632_attendance");

$con=mysqli_connect("localhost", "root", "", "resultgrading");
if(mysqli_connect_errno()){
    echo "Connection Fail".mysqli_connect_error(); 
}

    // $con=mysqli_connect("localhost", "root", "freeprojectscodes.com", "amsys");
    // if(mysqli_connect_errno()){
    // echo "Connection Fail".mysqli_connect_error();
    // }

?><!-- Log on to freeprojectscodes.com for more projects! -->
