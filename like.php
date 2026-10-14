<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && isset($_GET['post_id'])) {
    $user_id = $_SESSION['user_id'];
    $post_id = (int)$_GET['post_id'];

    // Vérifier si l'utilisateur a déjà liké ce post
    $check = $pdo->prepare("SELECT id 

FROM likes WHERE post_id = ? AND user_id = ?");
    $check->execute([$post_id, $user_id]);

    if ($check->rowCount() > 0) {
        // Déjà liké -> on supprime le like (unlike)
        $delete = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $delete->execute([$post_id, $user_id]);
    } else {
        // Pas encore liké -> on l'ajoute
        $insert = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $insert->execute([$post_id, $user_id]);
    }
}

// Rediriger vers la page d'où l'on vient (ou l'accueil)

header('Location: index.php');
exit();

