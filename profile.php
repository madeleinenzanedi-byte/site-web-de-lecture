<?php
session_start();
require_once 'db.php';

// Récupérer l'ID de l'utilisateur dont on veut voir le profil (par défaut, l'utilisateur connecté)
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if ($profile_id <= 0) {
    header('Location: login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$user_stmt = $pdo->prepare("SELECT username, created_at FROM users WHERE id = ?");
$user_stmt->execute([$profile_id]);
$profile_user = $user_stmt->fetch();

if (!$profile_user) {
    header('Location: index.php');
    exit();
}

$firstLetter = strtoupper(substr($profile_user['username'], 0, 1));

// Récupérer les critiques publiées par cet utilisateur
$posts_stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$posts_stmt->execute([$profile_id]);
$user_posts = $posts_stmt->fetchAll();

// Récupérer les livres sur lesquels cet utilisateur a laissé des commentaires
$comments_stmt = $pdo->prepare("
    SELECT DISTINCT posts.id, posts.book_title, posts.book_author 
    FROM comments 
    JOIN posts ON comments.post_id = posts.id 
    WHERE comments.user_id = ?
");
$comments_stmt->execute([$profile_id]);
$user_commented_posts = $comments_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= htmlspecialchars($profile_user['username']) ?> - LecturesPartagées</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <!-- En-tête -->
    <header class="site-header">
        <a href="index.php" class="logo" style="color: var(--primary-color);">LecturesPartagées</a>
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">← Retour à l'accueil</a>
    </header>

    <div style="max-width: 600px; margin: 30px auto; padding: 0 20px;">
        
        <!-- En-tête du profil -->
        <div style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid var(--border-color); text-align: center; margin-bottom: 30px;">
            <div style="width: 70px; height: 70px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin: 0 auto 15px auto;"><?= $firstLetter ?></div>
            <h1 style="margin: 0 0 5px 0; color: var(--primary-color);"><?= htmlspecialchars($profile_user['username']) ?></h1>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Membre depuis le <?= date('d/m/Y', strtotime($profile_user['created_at'])) ?></p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_id): ?>
                <div style="margin-top: 20px;">
                    <a href="logout.php" style="background-color: #fee2e2; color: #dc2626; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">Se déconnecter</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section : Les critiques de l'utilisateur -->
        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Ses critiques publiées (<?= count($user_posts) ?>)</h3>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
            <?php if (count($user_posts) > 0): ?>
                <?php foreach ($user_posts as $post): ?>
                    <div style="background: #fff; padding: 20px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <h4 style="margin: 0 0 5px 0; color: var(--primary-color);"><?= htmlspecialchars($post['book_title']) ?></h4>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">Par <?= htmlspecialchars($post['book_author']) ?> • <?= date('d/m/Y', strtotime($post['created_at'])) ?></p>
                        <p style="font-size: 14px; color: var(--text-main); margin-bottom: 15px;"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                        <a href="view_post.php?id=<?= $post['id'] ?>" style="font-size: 13px; color: var(--primary-color); text-decoration: none; font-weight: 600;">Voir la critique complète →</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 14px;">Cet utilisateur n'a encore publié aucune critique.</p>
            <?php endif; ?>
        </div>

        <!-- Section : Livres commentés -->
        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Livres commentés (<?= count($user_commented_posts) ?>)</h3>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
            <?php if (count($user_commented_posts) > 0): ?>
                <?php foreach ($user_commented_posts as $book): ?>
                    <div style="background: #fff; padding: 12px 15px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--text-main);"><?= htmlspecialchars($book['book_title']) ?></strong>
                            <span style="font-size: 12px; color: var(--text-muted);"> (<?= htmlspecialchars($book['book_author']) ?>)</span>
                        </div>
                        <a href="view_post.php?id=<?= $book['id'] ?>" style="font-size: 13px; color: var(--primary-color); text-decoration: none; font-weight: 600;">Voir</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 14px;">Aucun commentaire laissé sur des livres pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>