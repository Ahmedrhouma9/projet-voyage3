<?php
require_once __DIR__ . '/../../includes/auth.php';
requireClient();

$pdo  = getDB();
$user = getCurrentUser();
$msg  = $type = '';

// Annulation — backend vérifie statut pending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_id'])) {
    $rid  = (int)$_POST['annuler_id'];
    // Sécurité backend : vérifier que c'est bien la réservation du client ET qu'elle est pending
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
    $stmt->execute([$rid, $_SESSION['user_id']]);
    $resv = $stmt->fetch();

    if (!$resv) {
        $msg = 'Réservation introuvable.'; $type = 'error';
    } elseif (!canClientModifyReservation($resv)) {
        $msg = 'Impossible d\'annuler une réservation confirmée.'; $type = 'error';
    } else {
        $pdo->prepare("UPDATE reservations SET statut='cancelled' WHERE id=?")->execute([$rid]);
        $pdo->prepare("UPDATE voyages SET places_restantes = places_restantes + ? WHERE id=?")
            ->execute([$resv['nb_personnes'], $resv['voyage_id']]);
        $msg = 'Réservation annulée.'; $type = 'success';
    }
}

$stmt = $pdo->prepare("
    SELECT r.*, v.titre, v.destination, v.duree, v.image, v.date_depart
    FROM reservations r
    JOIN voyages v ON r.voyage_id = v.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();

$statut_labels = ['pending'=>'En attente','confirmed'=>'Confirmée','cancelled'=>'Annulée'];
$statut_badges = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','cancelled'=>'badge-cancelled'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes réservations — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Accueil</a></li>
            <li><a href="/projet-voyage3/pages/client/voyages.php">Destinations</a></li>
            <li><a href="/projet-voyage3/pages/client/profil.php">Mon profil</a></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>

<div class="section">
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Mes réservations</h1>
                <p style="color:var(--text-muted)">Bonjour, <?= h($user['prenom']) ?> <?= h($user['nom']) ?> 👋</p>
            </div>
            <a href="/projet-voyage3/pages/client/reserver.php" class="btn-accent">+ Nouveau voyage</a>
        </div>

        <?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>

        <?php if (empty($reservations)): ?>
            <div style="text-align:center;padding:4rem;background:var(--white);border-radius:16px;box-shadow:var(--shadow)">
                <div style="font-size:4rem;margin-bottom:1rem">✈️</div>
                <p style="font-size:1.1rem;font-weight:600;margin-bottom:1rem">Aucune réservation pour le moment</p>
                <a href="/projet-voyage3/pages/client/voyages.php" class="btn-primary">Découvrir nos voyages</a>
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $r): ?>
            <div class="resv-card">
                <?php $img = imageUrl($r['image']); ?>
                <?php if ($img): ?>
                    <img src="<?= h($img) ?>" class="resv-thumb" alt="">
                <?php else: ?>
                    <div class="resv-thumb-ph">🌍</div>
                <?php endif; ?>

                <div style="flex:1">
                    <div style="font-size:.76rem;color:var(--primary);font-weight:700;text-transform:uppercase">📍 <?= h($r['destination']) ?></div>
                    <h3 style="font-size:1rem;font-weight:700;margin:.2rem 0"><?= h($r['titre']) ?></h3>
                    <div style="font-size:.83rem;color:var(--text-muted)">
                        🗓 <?= $r['date_depart'] ? date('d/m/Y', strtotime($r['date_depart'])) : 'Sur demande' ?>
                        · <?= $r['duree'] ?> jours
                        · <?= $r['nb_personnes'] ?> personne<?= $r['nb_personnes']>1?'s':'' ?>
                    </div>
                </div>

                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:1.15rem;font-weight:800;color:var(--accent)"><?= formatPrix($r['prix_total']) ?></div>
                    <span class="badge <?= $statut_badges[$r['statut']] ?>" style="margin:.4rem 0;display:inline-block">
                        <?= $statut_labels[$r['statut']] ?>
                    </span><br>

                    <?php if (canClientModifyReservation($r)): ?>
                        <!-- Pending : bouton annuler actif -->
                        <form method="POST" onsubmit="return confirm('Annuler ce voyage ?')" style="margin-top:.3rem">
                            <input type="hidden" name="annuler_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Annuler</button>
                        </form>
                    <?php elseif ($r['statut'] === 'confirmed'): ?>
                        <!-- Confirmed : bouton désactivé -->
                        <button class="btn-disabled" disabled title="Réservation confirmée — modification impossible">
                            🔒 Confirmée
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<footer class="footer"><div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p></div></footer>
<script src="/projet-voyage3/public/js/main.js"></script>
</body>
</html>
