<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$pdo = getDB();
$msg = $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $rid    = (int)$_POST['reservation_id'];
    $statut = $_POST['statut'] ?? '';
    if (in_array($statut, ['pending','confirmed','cancelled'])) {
        $pdo->prepare("UPDATE reservations SET statut=? WHERE id=?")->execute([$statut, $rid]);
        $msg = 'Statut mis à jour.'; $type = 'success';
    }
}

$filter = $_GET['statut'] ?? 'all';
$where  = $filter !== 'all' ? "WHERE r.statut = " . $pdo->quote($filter) : '';
$reservations = $pdo->query("
    SELECT r.*, u.nom, u.prenom, u.email, v.titre, v.destination
    FROM reservations r JOIN users u ON r.user_id=u.id JOIN voyages v ON r.voyage_id=v.id
    $where ORDER BY r.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations Admin — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar admin-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/admin/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">ADMIN</small></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Voir le site</a></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>
<div class="dash-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
        <div class="page-header"><h1>Gestion des Réservations</h1></div>
        <?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>

        <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
            <?php foreach (['all'=>'Toutes','pending'=>'En attente','confirmed'=>'Confirmées','cancelled'=>'Annulées'] as $val=>$label): ?>
                <a href="?statut=<?= $val ?>" class="<?= $filter===$val?'btn-admin':'btn-secondary' ?> btn-sm"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <table class="data-table">
            <thead><tr><th>#</th><th>Client</th><th>Voyage</th><th>Pers.</th><th>Total (DT)</th><th>Statut</th><th>Changer</th></tr></thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $r['id'] ?></td>
                    <td><?= h($r['prenom'].' '.$r['nom']) ?><br><span style="font-size:.76rem;color:var(--text-muted)"><?= h($r['email']) ?></span></td>
                    <td><?= h($r['titre']) ?><br><span style="font-size:.76rem;color:var(--text-muted)">📍 <?= h($r['destination']) ?></span></td>
                    <td><?= $r['nb_personnes'] ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= formatPrix($r['prix_total']) ?></td>
                    <td><span class="badge badge-<?= $r['statut'] ?>"><?= ucfirst($r['statut']) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:.35rem;align-items:center">
                            <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                            <select name="statut" style="padding:.3rem;border:1px solid var(--border);border-radius:var(--radius);font-size:.82rem">
                                <option value="pending"   <?= $r['statut']==='pending'?'selected':'' ?>>En attente</option>
                                <option value="confirmed" <?= $r['statut']==='confirmed'?'selected':'' ?>>Confirmée</option>
                                <option value="cancelled" <?= $r['statut']==='cancelled'?'selected':'' ?>>Annulée</option>
                            </select>
                            <button type="submit" class="btn-admin btn-sm">OK</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem">Aucune réservation.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
