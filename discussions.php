<?php
session_start();
require_once 'db.php';

// Récupérer les livres qui ont au moins un commentaire avec le nombre de commentaires
$query = $pdo->query("
    SELECT posts.id, posts.book_title, posts.book_author, COUNT(comments.id) AS total_comments, MAX(comments.created_at) AS last_comment_at
    FROM posts
    JOIN comments ON posts.id = comments.post_id
    GROUP BY posts.id
    ORDER BY last_comment_at DESC
");
$discussions = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Discussions en cours - LecturesPartagées</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <!-- En-tête -->
    <header class="site-header">
        <a href="index.php" class="logo" style="color: var(--primary-color);">LecturesPartagées</a>
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">← Accueil</a>
    </header>

    <div style="max-width: 600px; margin: 30px auto; padding: 0 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: var(--primary-color); margin-bottom: 5px;">Discussions de la communauté</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Rejoignez les échanges en cours sur ces ouvrages</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php if (count($discussions) > 0): ?>
                <?php foreach ($discussions as $disc): ?>
                    <div style="background: #fff; padding: 18px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0 0 4px 0; color: var(--primary-color); font-size: 16px;"><?= htmlspecialchars($disc['book_title']) ?></h3>
                            <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Par <?= htmlspecialchars($disc['book_author']) ?></p>
                            <span style="font-size: 12px; color: var(--accent-color); font-weight: bold; display: inline-block; margin-top: 6px;">
                                💬 <?= $disc['total_comments'] ?> commentaire<?= $disc['total_comments'] > 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <a href="view_post.php?id=<?= $disc['id'] ?>" class="btn-primary" style="background-color: var(--primary-color); padding: 8px 14px; text-decoration: none; color: white; border-radius: 8px; font-size: 13px; font-weight: 600;">Participer</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background: #fff; padding: 25px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                    <p style="color: var(--text-muted); margin: 0;">Aucune discussion active pour le moment. Soyez le premier à commenter une critique sur l'accueil !</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barre de navigation fixe en bas -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-item">
            <span class="icon">🏠</span>
            <span>Accueil</span>
        </a>
        <a href="create_post.php" class="nav-item">
            <span class="icon">✍️</span>
            <span>Écrire</span>
        </a>
        <a href="discussions.php" class="nav-item active" style="color: var(--primary-color);">
            <span class="icon">💬</span>
            <span>Discussions</span>
        </a>
        <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>" class="nav-item">
            <span class="icon">👤</span>
            <span>Profil</span>
        </a>
    </nav>

</body>
</html>