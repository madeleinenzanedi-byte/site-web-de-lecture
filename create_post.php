<?php
session_start();
require_once 'db.php';

// Rediriger vers la connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_title = trim($_POST['book_title']);
    $book_author = trim($_POST['book_author']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($book_title) && !empty($book_author) && !empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, book_title, book_author, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $book_title, $book_author, $content]);
            $success = "Votre critique a été publiée avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur lors de la publication : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Partager une critique - LecturesPartagées</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <!-- En-tête -->
    <header class="site-header">
        <a href="index.php" class="logo" style="color: var(--primary-color);">LecturesPartagées</a>
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">← Retour à l'accueil</a>
    </header>

    <!-- Formulaire de publication -->
    <div class="auth-container" style="max-width: 500px; margin: 40px auto;">
        <div class="auth-header">
            <h1>Partager une lecture</h1>
            <p>Donnez votre avis sur un livre que vous avez lu</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success" style="color: #059669; background-color: #d1fae5; padding: 10px; border-radius: 8px; text-align: center; font-size: 13px; margin-bottom: 15px;"><?= $success ?></div>
        <?php endif; ?>

        <form action="create_post.php" method="POST">
            <div class="input-group">
                <label for="book_title">Titre du livre</label>
                <input type="text" id="book_title" name="book_title" required placeholder="Ex: Les Misérables">
            </div>

            <div class="input-group">
                <label for="book_author">Auteur du livre</label>
                <input type="text" id="book_author" name="book_author" required placeholder="Ex: Victor Hugo">
            </div>

            <div class="input-group">
                <label for="content">Votre critique / Avis</label>
                <textarea id="content" name="content" rows="6" required placeholder="Qu'avez-vous pensé de cette lecture ?" style="width: 100%; padding: 12px 14px; box-sizing: border-box; border: 1px solid var(--border-color); border-radius: 10px; font-size: 14px; background-color: #fff; font-family: inherit;"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="background-color: var(--primary-color);">Publier ma critique</button>
        </form>
    </div>

</body>
</html>