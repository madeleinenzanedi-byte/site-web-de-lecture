<?php
session_start();
$_SESSION=array();
session_destroy();

// Rediriger vers la page d'où l'on vient (ou l'accueil)

header('Location: index.php');
exit();
?>

