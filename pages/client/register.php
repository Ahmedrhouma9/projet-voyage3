<?php
require_once __DIR__ . '/../../includes/auth.php';
if (isLoggedIn()) { redirectByRole(); }

$message = $type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? ''); $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? ''); $tel = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? ''; $confirm = $_POST['confirm'] ?? '';

    if (!$nom || !$prenom || !$email || !$password) { $message = 'Remplissez tous les champs obligatoires.'; $type = 'error'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $message = 'Email invalide.'; $type = 'error'; }
    elseif (strlen($password) < 6) { $message = 'Mot de passe minimum 6 caractères.'; $type = 'error'; }
    elseif ($password !== $confirm) { $message = 'Les mots de passe ne correspondent pas.'; $type = 'error'; }
    else {
        $result = register($nom, $prenom, $email, $password, $tel);
        if ($result['success']) { header('Location: ' . BASE_URL . '/pages/client/login.php?registered=1'); exit; }
        $message = $result['message']; $type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Accueil</a></li>
            <li><a href="/projet-voyage3/pages/client/login.php">Connexion</a></li>
        </ul>
    </div>
</nav>
<div class="form-page">
    <div class="form-box" style="max-width:500px">
        <h2>🌍 Créer un compte</h2>
        <p class="form-sub">Rejoignez TunisieVoyages</p>
        <?php if ($message): ?><div class="alert alert-<?= $type === 'error' ? 'error' : 'success' ?>"><?= h($message) ?></div><?php endif; ?>
        <form method="POST" id="registerForm">
            <div class="form-2col">
                <div class="form-group"><label>Nom *</label><input type="text" name="nom" value="<?= h($_POST['nom'] ?? '') ?>" required></div>
                <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" value="<?= h($_POST['prenom'] ?? '') ?>" required></div>
            </div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required></div>
            <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= h($_POST['telephone'] ?? '') ?>"></div>
            <div class="form-2col">
                <div class="form-group"><label>Mot de passe *</label><input type="password" name="password" required minlength="6"></div>
                <div class="form-group"><label>Confirmer *</label><input type="password" name="confirm" required></div>
            </div>
            <button type="submit" class="btn-primary w-full">Créer mon compte</button>
        </form>
        <p style="text-align:center;margin-top:1.25rem;font-size:.88rem;color:var(--text-muted)">
            Déjà inscrit ? <a href="/projet-voyage3/pages/client/login.php" style="color:var(--primary);font-weight:600">Se connecter</a>
        </p>
    </div>
</div>
<script src="/projet-voyage3/public/js/main.js"></script>
</body>
</html>
