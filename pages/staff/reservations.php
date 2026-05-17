<?php
require_once __DIR__ . '/../../includes/auth.php';
requireStaff();
$pdo = getDB();
$msg = $type = '';

// Staff peut confirmer ou annuler — pas accès admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $rid    = (int)$_POST['reservation_id'];
    $statut = $_POST['statut'] ?? '';
    // Staff ne peut que confirmer ou annuler
    if (in_array($statut, ['pending','confirmed','cancelled'])) {
        $pdo->prepare("UPDATE reservations SET statut=? WHERE id=?")->execute([$statut, $rid]);
        $msg = 'Statut mis à jour.'; $type = 'success';
    }
}

$filter = $_GET['statut'] ?? 'all';
$where  = $filter !== 'all' ? "WHERE r.statut = " . $pdo->quote($filter) : '';
$reservations = $pdo->query("
    SELECT r.*, u.nom, u.prenom, u.email, u.telephone, v.titre, v.destination, v.prix
    FROM reservations r JOIN users u ON r.user_id=u.id JOIN voyages v ON r.voyage_id=v.id
    $where ORDER BY r.statut='pending' DESC, r.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations — Staff</title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar staff-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/staff/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">STAFF</small></a>
        <ul class="nav-links">
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
                <a href="?statut=<?= $val ?>" class="<?= $filter===$val?'btn-staff':'btn-secondary' ?> btn-sm"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <table class="data-table">
            <thead><tr><th>Client</th><th>Contact</th><th>Voyage</th><th>Pers.</th><th>Total</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr style="<?= $r['statut']==='pending'?'background:#fffbeb':'' ?>">
                    <td style="font-weight:600"><?= h($r['prenom'].' '.$r['nom']) ?></td>
                    <td style="font-size:.82rem"><?= h($r['email']) ?><br><?= h($r['telephone']?:'—') ?></td>
                    <td><?= h($r['titre']) ?><br><span style="font-size:.76rem;color:var(--text-muted)">📍 <?= h($r['destination']) ?></span></td>
                    <td><?= $r['nb_personnes'] ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= formatPrix($r['prix_total']) ?></td>
                    <td><span class="badge badge-<?= $r['statut'] ?>"><?= ucfirst($r['statut']) ?></span></td>
                    <td>
                        <?php if ($r['statut'] === 'pending'): ?>
                            <form method="POST" style="display:flex;gap:.35rem;flex-wrap:wrap">
                                <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="statut" value="confirmed" class="btn-staff btn-sm">✅ Confirmer</button>
                                <button type="submit" name="statut" value="cancelled" class="btn-danger btn-sm" onclick="return confirm('Annuler cette réservation ?')">❌ Annuler</button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                                <select name="statut" onchange="this.form.submit()" style="padding:.3rem;border:1px solid var(--border);border-radius:var(--radius);font-size:.82rem">
                                    <option value="pending"   <?= $r['statut']==='pending'?'selected':'' ?>>En attente</option>
                                    <option value="confirmed" <?= $r['statut']==='confirmed'?'selected':'' ?>>Confirmée</option>
                                    <option value="cancelled" <?= $r['statut']==='cancelled'?'selected':'' ?>>Annulée</option>
                                </select>
                            </form>
                        <?php endif; ?>
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
