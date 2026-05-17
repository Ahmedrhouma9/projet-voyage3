<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$pdo = getDB();
$msg = $type = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='client'")->execute([(int)$_GET['delete']]);
    header('Location: /projet-voyage3/pages/admin/clients.php?deleted=1'); exit;
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='client'");
    $s->execute([(int)$_GET['edit']]); $editing = $s->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id=$_POST['id']??0; $nom=trim($_POST['nom']??''); $prenom=trim($_POST['prenom']??'');
    $email=trim($_POST['email']??''); $tel=trim($_POST['telephone']??''); $pwd=$_POST['password']??'';

    if (!$nom||!$prenom||!$email) { $msg='Remplissez tous les champs.'; $type='error'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $msg='Email invalide.'; $type='error'; }
    else {
        if ($id) {
            if ($pwd && strlen($pwd)<6) { $msg='Mot de passe min. 6 caractères.'; $type='error'; }
            else {
                if ($pwd) $pdo->prepare("UPDATE users SET nom=?,prenom=?,email=?,telephone=?,mot_de_passe=? WHERE id=? AND role='client'")->execute([$nom,$prenom,$email,$tel,password_hash($pwd,PASSWORD_BCRYPT),$id]);
                else $pdo->prepare("UPDATE users SET nom=?,prenom=?,email=?,telephone=? WHERE id=? AND role='client'")->execute([$nom,$prenom,$email,$tel,$id]);
                $msg='Client mis à jour.'; $type='success'; $editing=null;
            }
        } else {
            if (!$pwd||strlen($pwd)<6) { $msg='Mot de passe obligatoire (min. 6 car.).'; $type='error'; }
            else {
                $s=$pdo->prepare("SELECT id FROM users WHERE email=?"); $s->execute([$email]);
                if ($s->fetch()) { $msg='Email déjà utilisé.'; $type='error'; }
                else { $pdo->prepare("INSERT INTO users (nom,prenom,email,mot_de_passe,telephone,role) VALUES (?,?,?,?,?,'client')")->execute([$nom,$prenom,$email,password_hash($pwd,PASSWORD_BCRYPT),$tel]); $msg='Client ajouté.'; $type='success'; }
            }
        }
    }
}

$clients = $pdo->query("SELECT u.*,COUNT(r.id) AS nb_res,COALESCE(SUM(r.prix_total),0) AS total FROM users u LEFT JOIN reservations r ON r.user_id=u.id WHERE u.role='client' GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clients — Admin | TunisieVoyages</title>
<link rel="stylesheet" href="/projet-voyage3/public/css/style.css"></head>
<body>
<nav class="navbar admin-nav"><div class="container nav-inner">
<a href="/projet-voyage3/pages/admin/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">ADMIN</small></a>
<ul class="nav-links"><li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li></ul>
</div></nav>
<div class="dash-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="dash-content">
<div class="page-header"><h1>Gestion des Clients</h1><button class="btn-admin btn-sm" onclick="toggleForm()">+ Ajouter</button></div>
<?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Client supprimé.</div><?php endif; ?>

<div id="form-section" style="<?= ($editing||($msg&&$type==='error'))?'':'display:none' ?>;background:var(--white);border:1px solid var(--border);border-radius:14px;padding:1.75rem;margin-bottom:1.75rem">
<h2 style="font-size:1.05rem;font-weight:700;margin-bottom:1.25rem"><?= $editing?'Modifier le client':'Ajouter un client' ?></h2>
<form method="POST">
<input type="hidden" name="id" value="<?= $editing?$editing['id']:'' ?>">
<div class="form-2col">
<div class="form-group"><label>Nom *</label><input type="text" name="nom" value="<?= h($editing['nom']??'') ?>" required></div>
<div class="form-group"><label>Prénom *</label><input type="text" name="prenom" value="<?= h($editing['prenom']??'') ?>" required></div>
<div class="form-group"><label>Email *</label><input type="email" name="email" value="<?= h($editing['email']??'') ?>" required></div>
<div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= h($editing['telephone']??'') ?>"></div>
</div>
<div class="form-group"><label>Mot de passe <?= $editing?'(vide = inchangé)':'*' ?></label><input type="password" name="password" <?= $editing?'':'required' ?> minlength="6"></div>
<div style="display:flex;gap:1rem;margin-top:.75rem">
<button type="submit" class="btn-admin"><?= $editing?'Enregistrer':'Ajouter' ?></button>
<a href="/projet-voyage3/pages/admin/clients.php" class="btn-secondary">Annuler</a>
</div></form></div>

<table class="data-table">
<thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Réservations</th><th>Total dépensé</th><th>Inscrit le</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($clients as $c): ?>
<tr>
<td style="font-weight:600"><?= h($c['prenom'].' '.$c['nom']) ?></td>
<td><?= h($c['email']) ?></td><td><?= h($c['telephone']?:'—') ?></td>
<td><?= $c['nb_res'] ?></td>
<td style="font-weight:700;color:var(--accent)"><?= formatPrix((float)$c['total']) ?></td>
<td><?= date('d/m/Y',strtotime($c['created_at'])) ?></td>
<td style="display:flex;gap:.4rem">
<a href="?edit=<?= $c['id'] ?>" class="btn-secondary btn-sm">Modifier</a>
<a href="?delete=<?= $c['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Supprimer ce client ?')">Supprimer</a>
</td></tr>
<?php endforeach; ?>
</tbody></table>
</main></div>
<script>function toggleForm(){const f=document.getElementById('form-section');f.style.display=f.style.display==='none'?'':'none';}</script>
</body></html>
