<?php
require_once __DIR__ . '/../../includes/auth.php';
requireClient();
$pdo  = getDB();
$user = getCurrentUser();
$msg  = $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']??''); $prenom = trim($_POST['prenom']??'');
    $tel = trim($_POST['telephone']??''); $pwd = $_POST['password']??'';
    if (!$nom||!$prenom) { $msg='Nom et prénom obligatoires.'; $type='error'; }
    else {
        if ($pwd && strlen($pwd)<6) { $msg='Mot de passe minimum 6 caractères.'; $type='error'; }
        else {
            if ($pwd) {
                $pdo->prepare("UPDATE users SET nom=?,prenom=?,telephone=?,mot_de_passe=? WHERE id=?")
                    ->execute([$nom,$prenom,$tel,password_hash($pwd,PASSWORD_BCRYPT),$_SESSION['user_id']]);
            } else {
                $pdo->prepare("UPDATE users SET nom=?,prenom=?,telephone=? WHERE id=?")
                    ->execute([$nom,$prenom,$tel,$_SESSION['user_id']]);
            }
            $_SESSION['nom']=$nom; $_SESSION['prenom']=$prenom;
            $msg='Profil mis à jour.'; $type='success'; $user=getCurrentUser();
        }
    }
}
$nb_res = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id=?"); $nb_res->execute([$_SESSION['user_id']]); $nb_res=$nb_res->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/pages/client/mes-reservations.php">Mes réservations</a></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>
<div class="section">
    <div class="container" style="max-width:600px">
        <div class="page-header"><h1>Mon Profil</h1></div>
        <?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>

        <div class="profile-card" style="margin-bottom:1.5rem">
            <div class="profile-avatar" style="background:var(--primary)">
                <?= strtoupper(mb_substr($user['prenom'],0,1).mb_substr($user['nom'],0,1)) ?>
            </div>
            <span class="badge badge-client" style="margin-bottom:1.25rem;display:inline-block">Client</span>
            <p style="font-size:.88rem;color:var(--text-muted);margin-bottom:1.25rem">Total réservations : <strong><?= $nb_res ?></strong></p>

            <form method="POST">
                <div class="form-2col">
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" value="<?= h($user['nom']) ?>" required></div>
                    <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" value="<?= h($user['prenom']) ?>" required></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" value="<?= h($user['email']) ?>" disabled style="opacity:.6"></div>
                <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= h($user['telephone']?:'') ?>"></div>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="password" placeholder="Laisser vide pour ne pas changer">
                    <span class="form-hint">Minimum 6 caractères</span>
                </div>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
</div>
<footer class="footer"><div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p></div></footer>
</body>
</html>
