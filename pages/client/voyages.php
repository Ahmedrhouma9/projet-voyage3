<?php /* voyages.php */ ?>
<?php
require_once __DIR__ . '/../../includes/auth.php';
if (isLoggedIn() && !isClient()) { redirectByRole(); }
$pdo     = getDB();
$voyages = $pdo->query("SELECT * FROM voyages WHERE disponible=1 ORDER BY prix ASC")->fetchAll();
$dests   = $pdo->query("SELECT DISTINCT destination FROM voyages WHERE disponible=1 ORDER BY destination")->fetchAll(PDO::FETCH_COLUMN);
$filter  = trim($_GET['dest']??'');
if ($filter) {
    $stmt = $pdo->prepare("SELECT * FROM voyages WHERE disponible=1 AND destination LIKE ? ORDER BY prix ASC");
    $stmt->execute(["%$filter%"]); $voyages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Accueil</a></li>
            <?php if (isLoggedIn() && isClient()): ?>
                <li><a href="/projet-voyage3/pages/client/mes-reservations.php">Mes réservations</a></li>
                <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="/projet-voyage3/pages/client/login.php">Connexion</a></li>
                <li><a href="/projet-voyage3/pages/client/register.php" class="btn-nav">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="section">
    <div class="container">
        <h1 class="section-title">Toutes nos destinations</h1>
        <p class="section-sub">Prix en DT — tout compris</p>
        <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;margin-bottom:2rem">
            <a href="/projet-voyage3/pages/client/voyages.php" class="<?= !$filter?'btn-primary':'btn-secondary' ?> btn-sm">Toutes</a>
            <?php foreach ($dests as $d): ?>
                <a href="?dest=<?= urlencode($d) ?>" class="<?= $filter===$d?'btn-primary':'btn-secondary' ?> btn-sm"><?= h($d) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="cards-grid">
            <?php foreach ($voyages as $v): ?>
            <div class="card">
                <div class="card-img" style="position:relative">
                    <?php $img=imageUrl($v['image']); ?>
                    <?php if ($img): ?><img src="<?= h($img) ?>" alt=""><?php else: ?><div class="card-img-placeholder">🌍</div><?php endif; ?>
                    <div class="card-badge"><?= $v['duree'] ?> jours</div>
                </div>
                <div class="card-body">
                    <div class="card-dest">📍 <?= h($v['destination']) ?></div>
                    <h3><?= h($v['titre']) ?></h3>
                    <p><?= h(mb_substr($v['description']??'',0,110)) ?>...</p>
                    <div class="card-meta">
                        <div><span class="price"><?= formatPrix($v['prix']) ?></span><span class="price-sub">par personne</span></div>
                        <span style="font-size:.82rem;color:var(--text-muted)"><?= $v['places_restantes'] ?> places</span>
                    </div>
                    <?php if ($v['places_restantes']==0): ?>
                        <button disabled class="btn-secondary w-full" style="opacity:.5">Complet</button>
                    <?php elseif (isLoggedIn() && isClient()): ?>
                        <a href="/projet-voyage3/pages/client/reserver.php?voyage_id=<?= $v['id'] ?>" class="btn-primary w-full">Réserver</a>
                    <?php else: ?>
                        <a href="/projet-voyage3/pages/client/login.php" class="btn-secondary w-full">Connexion pour réserver</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<footer class="footer"><div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p></div></footer>
</body>
</html>
