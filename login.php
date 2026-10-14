<?php
session_start();
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $message = "Connexion réussie ! Bienvenue, " . htmlspecialchars($user['username']) . ".";
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Communauté de Lecture</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-container">
        <!-- En-tête de la page -->
        <div class="auth-header">
            <h1>Bon retour parmi nous</h1>
            <p>Connectez-vous pour profiter de toutes les fonctionnalités du site</p>
        </div>

        <!-- Bouton Connexion rapide (ex: Google) -->
        <button type="button" class="btn-social">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.2v3.15C3.16 21.32 7.22 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.2C.44 8.12 0 9.87 0 12s.44 3.88 1.2 5.42l4.08-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.22 0 3.16 2.68 1.2 6.58l4.08 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
            Google
        </button>

        <div class="separator">
            <span>Ou connectez-vous avec vos identifiants</span>
        </div>

        <!-- Formulaire principal -->
        <form method="POST">
            <?php if (!empty($message)): ?>
                <p class="<?= strpos($message, 'réussie') !== false ? 'success' : 'error-msg' ?>"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            
            <div class="input-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur" required>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>

        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
        </div>
    </div>

</body>
</html>