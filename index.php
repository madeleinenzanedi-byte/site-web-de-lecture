<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Communauté de Lecture - Partagez vos avis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <!-- En-tête -->
    <header class="site-header">
    <a href="index.php" class="logo" style="color: var(--primary-color);">LecturesPartagées</a>
        <div class="header-icons">
            <button class="icon-btn">🔍</button>
            <?php if (isset($_SESSION['username'])): ?>
    <a href="profile.php" class="profile-icon" style="background-color: var(--primary-color); text-decoration: none; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></a>
<?php else: ?>
    <a href="login.php" class="profile-icon" style="background-color: var(--primary-color); text-decoration: none; display: flex; align-items: center; justify-content: center; color: white;">👤</a>
<?php endif; ?>
            
        </div>
    </header>

    <!-- Section de Bienvenue avec les images penchées -->
    <section class="hero-section">
        <div class="hero-subtitle" style="color: var(--accent-color);">ESPACE COMMUNAUTAIRE</div>
        <h1>Que lisez-vous en ce moment ?</h1>
        <p class="hero-desc">Partagez vos avis sur vos dernières lectures et découvrez les coups de cœur des autres.</p>

        <!-- Cartes en cascade (vos images locales) -->
        <div class="cards-cascade">
            <div class="cascade-card" style="background-image: url('images/l1.jpg');"></div>
            <div class="cascade-card" style="background-image: url('images/l2.jpg');"></div>
            <div class="cascade-card" style="background-image: url('images/l3.jpg');"></div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="cta-container" style="margin-top: 20px;">
                <a href="create_post.php" class="btn-main-red" style="background-color: var(--primary-color); text-decoration: none; display: inline-block;">+ Partager une critique de livre</a>
            </div>
        <?php else: ?>
            <div class="cta-container" style="margin-top: 20px;">
                <a href="register.php" class="btn-main-red" style="background-color: var(--primary-color); text-decoration: none; display: inline-block;">Rejoindre la communauté</a>
                <a href="login.php" class="btn-outline">Se connecter</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Fil d'actualité des publications (dynamique) -->
    <section class="featured-section">
        <span class="section-tag" style="color: var(--accent-color);">Fil d'actualité</span>
        <h2 class="section-title">Les derniers avis de la communauté</h2>

        <div class="posts-container" style="max-width: 600px; margin: 20px auto; display: flex; flex-direction: column; gap: 20px;">
            
            <?php
            // Récupérer les publications avec le nom des utilisateurs associés
            $query = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
            $posts = $query->fetchAll();

            if (count($posts) > 0) {
                foreach ($posts as $post) {
                    $firstLetter = strtoupper(substr($post['username'], 0, 1));

                    // Compter les likes de ce post
                    $like_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
                    $like_count_stmt->execute([$post['id']]);
                    $like_count = $like_count_stmt->fetchColumn();

                    // Vérifier si l'utilisateur connecté a déjà liké
                    $user_liked = false;
                    if (isset($_SESSION['user_id'])) {
                        $check_like = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
                        $check_like->execute([$post['id'], $_SESSION['user_id']]);
                        $user_liked = $check_like->rowCount() > 0;
                    }
            ?>
                    <!-- Carte de publication individuelle -->
                    <div class="book-card" style="padding: 20px; text-align: left; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            <div style="width: 35px; height: 35px; background: var(--accent-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?= $firstLetter ?></div>
                            <div>
                                <strong><?= htmlspecialchars($post['username']) ?></strong> 
                                <span style="font-size: 12px; color: var(--text-muted);">a publié une critique • <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>

                        <h3 style="margin: 0 0 5px 0; color: var(--primary-color);"><?= htmlspecialchars($post['book_title']) ?></h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px;">Par <?= htmlspecialchars($post['book_author']) ?></p>
                        
                        <p style="font-size: 15px; line-height: 1.5; color: var(--text-main);">
                            <?= nl2br(htmlspecialchars($post['content'])) ?>
                        </p>

                        <!-- Boutons J'aime, Commentaires, Partager -->
                        <div style="display: flex; gap: 20px; border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 15px; font-size: 14px; color: var(--text-muted); align-items: center;">
                            <a href="like.php?post_id=<?= $post['id'] ?>" style="text-decoration: none; color: <?= $user_liked ? 'var(--accent-color)' : 'var(--text-muted)' ?>; font-weight: 600;">
                                <?= $user_liked ? '❤️' : '🤍' ?> <?= $like_count ?> J'aime
                            </a>
                            <a href="view_post.php?id=<?= $post['id'] ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">💬 Commentaires</a>
                            
                            <button onclick="sharePost(<?= $post['id'] ?>)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 14px; font-weight: 600; padding: 0;">
                                🔗 Partager
                            </button>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo '<p style="text-align: center; color: var(--text-muted);">Aucune critique pour le moment. Soyez le premier à en partager une !</p>';
            }
            ?>

        </div>
    </section>

    <!-- Barre de navigation fixe en bas -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-item active" style="color: var(--primary-color);">
            <span class="icon">🏠</span>
            <span>Accueil</span>
        </a>
        <a href="create_post.php" class="nav-item">
            <span class="icon">✍️</span>
            <span>Écrire</span>
        </a>
        <a href="discussions.php" class="nav-item">
    <span class="icon">💬</span>
    <span>Discussions</span>
</a>
        <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>" class="nav-item">
            <span class="icon">👤</span>
            <span>Profil</span>
        </a>
    </nav>

    <!-- Script JavaScript pour le bouton Partager -->
    <script>
    function sharePost(postId) {
        const url = window.location.origin + window.location.pathname.replace('index.php', '') + 'view_post.php?id=' + postId;
        navigator.clipboard.writeText(url).then(() => {
            alert("Lien de la critique copié dans le presse-papier !");
        }).catch(err => {
            console.error('Erreur lors de la copie : ', err);
        });
    }
    </script>

</body>
</html>