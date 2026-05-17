<?php
require_once __DIR__ . '/../../includes/auth.php';

if (isLoggedIn()) { redirectByRole(); }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        $result = login($email, $password);
        if ($result['success']) {
            redirectByRole();
        }
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Accueil</a></li>
            <li><a href="/projet-voyage3/pages/client/register.php" class="btn-nav">S'inscrire</a></li>
        </ul>
    </div>
</nav>
<div class="form-page">
    <div class="form-box">
        <h2>✈ Connexion</h2>
        <p class="form-sub">Accédez à votre espace</p>

        <?php if ($message): ?>
            <div class="alert alert-error"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Compte créé ! Connectez-vous.</div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required placeholder="votre@email.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary w-full" style="margin-top:.5rem">Se connecter</button>
        </form>

        <p style="text-align:center;margin-top:1.25rem;font-size:.88rem;color:var(--text-muted)">
            Pas de compte ? <a href="/projet-voyage3/pages/client/register.php" style="color:var(--primary);font-weight:600">S'inscrire</a>
        </p>

        <!-- Comptes test -->
        <div style="margin-top:1.5rem;padding:1rem;background:var(--bg);border-radius:var(--radius);font-size:.8rem;color:var(--text-muted)">
            <strong>Comptes test :</strong><br>
            🔴 Admin : admin@agence.tn / password<br>
            🟢 Staff : staff@agence.tn / password<br>
            🔵 Client : client@test.tn / password
        </div>
    </div>
</div>
<script src="/projet-voyage3/public/js/main.js"></script>
</body>
</html>
