<?php

//-------------------------Score Letter Grade -------------------------------

function getScoreLetterGrade($score){

    $letterGrade = "";
     if($score >= 75)
     {
        $letterGrade = "AA";
     }
     else if($score >= 70){
        $letterGrade = "A";
     }
     else if($score >= 65){
        $letterGrade = "AB";
     }
     else if($score >= 60){
         $letterGrade = "B";
     }
     else if($score >= 55){
        $letterGrade = "BC";
     }
      else if($score >= 50){
        $letterGrade = "C";
     }
      else if($score >= 45){
        $letterGrade = "CD";
     }
     else if($score >= 40){
        $letterGrade = "D";
     }
     else if($score <= 39){
         $letterGrade = "F";
     }

     return $letterGrade;
}

// -------------------------- Score Grade Point -------------------------

function getScoreGradePoint($score){

    $gradePoint = "";

     if($score >= 75)
     {
        $gradePoint = 4.00;
     }
     else if($score >= 70){
        $gradePoint = 3.50;
     }
     else if($score >= 65){
        $gradePoint = 3.25;
     }
     else if($score >= 60){
         $gradePoint = 3.00;
     }
     else if($score >= 55){
        $gradePoint = 2.75;
     }
      else if($score >= 50){
        $gradePoint = 2.50;
     }
      else if($score >= 45){
        $gradePoint = 2.25;
     }
     else if($score >= 40){
        $gradePoint = 2.00;
     }
     else if($score <= 39){
         $gradePoint = 0.00;
     }

     return $gradePoint;
}

// -------------------------- Class of Diploma -------------------------

function getClassOfDiploma($gpa){

    $classOfDiploma = "";

     if($gpa >= 3.50)
     {
        $classOfDiploma = "Distinction";
     }
     else if($gpa >= 3.00){
        $classOfDiploma = "Upper Credit";
     }
     else if($gpa >= 2.50){
       $classOfDiploma = "Lower Credit";
     }
     else if($gpa >= 2.00){
         $classOfDiploma = "Pass";
     }
     else if($gpa < 2.00){
        $classOfDiploma = "Fail";
     }

     return $classOfDiploma;
}

// Log an event to the audit_logs table
function log_audit_event($con, $user, $role, $event_type, $event_details) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = mysqli_prepare($con, "INSERT INTO audit_logs (user, role, event_type, event_details, ip_address) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $user, $role, $event_type, $event_details, $ip_address);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

?>
<!-- Log on to freeprojectscodes.com for more projects! -->