<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$pdo = getDB();
$msg = $type = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $s=$pdo->prepare("SELECT image FROM voyages WHERE id=?"); $s->execute([(int)$_GET['delete']]); $v=$s->fetch();
    if ($v && $v['image'] && !str_starts_with($v['image'],'http') && file_exists(UPLOAD_DIR.$v['image'])) unlink(UPLOAD_DIR.$v['image']);
    $pdo->prepare("DELETE FROM voyages WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: /projet-voyage3/pages/admin/voyages.php?deleted=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id=$_POST['id']??0; $titre=trim($_POST['titre']??''); $dest=trim($_POST['destination']??'');
    $desc=trim($_POST['description']??''); $prix=(float)($_POST['prix']??0);
    $duree=(int)($_POST['duree']??0); $places=(int)($_POST['places_total']??20);
    $date=$_POST['date_depart']??null; $dispo=isset($_POST['disponible'])?1:0;
    $img=$_POST['old_image']??null;

    if (!$titre||!$dest||$prix<=0||$duree<=0) { $msg='Remplissez tous les champs.'; $type='error'; }
    else {
        if (!empty($_FILES['image']['name'])) {
            $up=uploadImage($_FILES['image']);
            if (!$up['success']) { $msg=$up['message']; $type='error'; }
            else { if($img&&!str_starts_with($img,'http')&&file_exists(UPLOAD_DIR.$img)) unlink(UPLOAD_DIR.$img); $img=$up['filename']; }
        }
        if (!$msg) {
            if ($id) {
                $pdo->prepare("UPDATE voyages SET titre=?,destination=?,description=?,prix=?,duree=?,places_total=?,date_depart=?,image=?,disponible=? WHERE id=?")
                    ->execute([$titre,$dest,$desc,$prix,$duree,$places,$date?:null,$img,$dispo,$id]);
            } else {
                $pdo->prepare("INSERT INTO voyages (titre,destination,description,prix,duree,places_total,places_restantes,date_depart,image,disponible) VALUES (?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$titre,$dest,$desc,$prix,$duree,$places,$places,$date?:null,$img,$dispo]);
            }
            $msg=$id?'Voyage mis à jour.':'Voyage ajouté.'; $type='success';
        }
    }
}

$editing=null;
if (isset($_GET['edit'])&&is_numeric($_GET['edit'])) { $s=$pdo->prepare("SELECT * FROM voyages WHERE id=?"); $s->execute([(int)$_GET['edit']]); $editing=$s->fetch(); }
$voyages=$pdo->query("SELECT * FROM voyages ORDER BY titre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyages — Admin</title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar admin-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/admin/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">ADMIN</small></a>
        <ul class="nav-links"><li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li></ul>
    </div>
</nav>
<div class="dash-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
        <div class="page-header">
            <h1>Gestion des Voyages</h1>
            <?php if (!$editing): ?><button class="btn-admin btn-sm" onclick="toggleForm()">+ Ajouter</button><?php endif; ?>
        </div>
        <?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Voyage supprimé.</div><?php endif; ?>

        <div id="form-section" style="<?= ($editing||($msg&&$type==='error'))?'':'display:none' ?>;background:var(--white);border:1px solid var(--border);border-radius:14px;padding:1.75rem;margin-bottom:1.75rem">
            <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:1.25rem"><?= $editing?'Modifier':'Nouveau voyage' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editing?$editing['id']:'' ?>">
                <input type="hidden" name="old_image" value="<?= h($editing['image']??'') ?>">
                <div class="form-2col">
                    <div class="form-group"><label>Titre *</label><input type="text" name="titre" value="<?= h($editing['titre']??'') ?>" required></div>
                    <div class="form-group"><label>Destination *</label><input type="text" name="destination" value="<?= h($editing['destination']??'') ?>" required></div>
                    <div class="form-group"><label>Prix (DT) *</label><input type="number" name="prix" step="0.001" value="<?= h($editing['prix']??'') ?>" required></div>
                    <div class="form-group"><label>Durée (jours) *</label><input type="number" name="duree" value="<?= h($editing['duree']??'') ?>" required></div>
                    <div class="form-group"><label>Places</label><input type="number" name="places_total" value="<?= h($editing['places_total']??20) ?>"></div>
                    <div class="form-group"><label>Date départ</label><input type="date" name="date_depart" value="<?= h($editing['date_depart']??'') ?>"></div>
                </div>
                <div class="form-group"><label>Description</label><textarea name="description"><?= h($editing['description']??'') ?></textarea></div>
                <div class="form-group"><label>Photo / URL</label><input type="file" name="image" accept="image/*"><?php if($editing&&$editing['image']): ?><span class="form-hint">Actuelle : <?= h(basename($editing['image'])) ?></span><?php endif; ?></div>
                <div class="form-group" style="display:flex;align-items:center;gap:.5rem"><input type="checkbox" name="disponible" id="dispo" <?= ($editing?$editing['disponible']:1)?'checked':'' ?>><label for="dispo" style="margin:0">Disponible</label></div>
                <div style="display:flex;gap:1rem;margin-top:.75rem">
                    <button type="submit" class="btn-admin"><?= $editing?'Enregistrer':'Ajouter' ?></button>
                    <a href="/projet-voyage3/pages/admin/voyages.php" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <table class="data-table">
            <thead><tr><th>Photo</th><th>Titre</th><th>Destination</th><th>Prix</th><th>Places</th><th>Dispo.</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($voyages as $v): ?>
                <tr>
                    <td><?php $img=imageUrl($v['image']); if($img): ?><img src="<?= h($img) ?>" style="width:55px;height:42px;object-fit:cover;border-radius:6px"><?php else: ?>🌍<?php endif; ?></td>
                    <td style="font-weight:600"><?= h($v['titre']) ?></td>
                    <td>📍 <?= h($v['destination']) ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= formatPrix($v['prix']) ?></td>
                    <td><?= $v['places_restantes'] ?>/<?= $v['places_total'] ?></td>
                    <td><span class="badge <?= $v['disponible']?'badge-confirmed':'badge-cancelled' ?>"><?= $v['disponible']?'Oui':'Non' ?></span></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?edit=<?= $v['id'] ?>" class="btn-secondary btn-sm">Modifier</a>
                        <a href="?delete=<?= $v['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
<script>function toggleForm(){const f=document.getElementById('form-section');f.style.display=f.style.display==='none'?'':'none';}</script>
</body>
</html>
