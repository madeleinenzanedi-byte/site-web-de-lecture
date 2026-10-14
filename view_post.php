<?php
session_start();
require_once 'db.php';

// Récupérer l'ID de la publication
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer la publication et l'auteur
$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit();
}

// Gestion de l'ajout d'un commentaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment_content = trim($_POST['comment_content']);
    if (!empty($comment_content)) {
        $insert = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $insert->execute([$post_id, $_SESSION['user_id'], $comment_content]);
        header("Location: view_post.php?id=" . $post_id);
        exit();
    } else {
        $error = "Le commentaire ne peut pas être vide.";
    }
}

// Récupérer les commentaires de cette publication
$comments_stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
$comments_stmt->execute([$post_id]);
$comments = $comments_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Critique de <?= htmlspecialchars($post['book_title']) ?> - LecturesPartagées</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <header class="site-header">
        <a href="index.php" class="logo" style="color: var(--primary-color);">LecturesPartagées</a>
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">← Retour au fil</a>
    </header>

    <div style="max-width: 600px; margin: 30px auto; padding: 0 20px;">
        
        <!-- Affichage de la publication -->
        <div class="book-card" style="padding: 20px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 35px; height: 35px; background: var(--accent-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?= strtoupper(substr($post['username'], 0, 1)) ?></div>
                <div>
                    <strong><?= htmlspecialchars($post['username']) ?></strong> 
                    <span style="font-size: 12px; color: var(--text-muted);">• <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?></span>
                </div>
            </div>

            <h2 style="margin: 0 0 5px 0; color: var(--primary-color);"><?= htmlspecialchars($post['book_title']) ?></h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Par <?= htmlspecialchars($post['book_author']) ?></p>
            
            <p style="font-size: 15px; line-height: 1.5; color: var(--text-main);">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </p>
        </div>

        <!-- Section Commentaires -->
        <h3 style="color: var(--primary-color); margin-bottom: 20px;">Commentaires (<?= count($comments) ?>)</h3>

        <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="background: #fff; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                            <strong style="color: var(--primary-color);"><?= htmlspecialchars($comment['username']) ?></strong>
                            <span style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <p style="font-size: 14px; color: var(--text-main); margin: 0; line-height: 1.4;">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 14px;">Aucun commentaire pour l'instant. Soyez le premier à réagir !</p>
            <?php endif; ?>
        </div>

        <!-- Formulaire pour ajouter un commentaire -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="view_post.php?id=<?= $post_id ?>" method="POST" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                <h4 style="margin-top: 0; margin-bottom: 12px; color: var(--text-main);">Ajouter un commentaire</h4>
                <?php if (!empty($error)): ?>
                    <div style="color: #dc2626; font-size: 13px; margin-bottom: 10px;"><?= $error ?></div>
                <?php endif; ?>
                <textarea name="comment_content" rows="3" required placeholder="Écrivez votre commentaire ici..." style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; font-family: inherit; margin-bottom: 10px;"></textarea>
                <button type="submit" class="btn-primary" style="background-color: var(--primary-color); padding: 10px 20px; border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Publier le commentaire</button>
            </form>
        <?php else: ?>
            <p style="text-align: center; font-size: 14px;"><a href="login.php" style="color: var(--primary-color); font-weight: bold;">Connectez-vous</a> pour participer à la discussion.</p>
        <?php endif; ?>

    </div>

</body>
</html>