<?php
require_once __DIR__ . '/../../includes/auth.php';
requireStaff();
$pdo = getDB();

$ca        = $pdo->query("SELECT COALESCE(SUM(prix_total),0) FROM reservations WHERE statut='confirmed'")->fetchColumn();
$top       = $pdo->query("SELECT v.titre, v.destination, COUNT(r.id) AS nb, COALESCE(SUM(r.prix_total),0) AS ca FROM reservations r JOIN voyages v ON r.voyage_id=v.id GROUP BY r.voyage_id ORDER BY nb DESC LIMIT 5")->fetchAll();
$par_statut= $pdo->query("SELECT statut, COUNT(*) AS nb FROM reservations GROUP BY statut")->fetchAll();
$total_r   = array_sum(array_column($par_statut,'nb'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques — Admin</title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar staff-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/staff/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">STAFF</small></a>
        <ul class="nav-links"><li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li></ul>
    </div>
</nav>
<div class="dash-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
        <div class="page-header"><h1>Statistiques Globales</h1></div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-label">CA confirmé (DT)</div><div class="stat-value green" style="font-size:1.4rem"><?= number_format((float)$ca,3) ?></div></div>
            <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-label">Total réservations</div><div class="stat-value blue"><?= $total_r ?></div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">
            <div style="background:var(--white);border:1px solid var(--border);border-radius:14px;padding:1.5rem">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem">🏆 Top destinations</h2>
                <?php $max=max(array_column($top,'nb')?:[1]); foreach($top as $t): ?>
                <div style="margin-bottom:.9rem">
                    <div style="display:flex;justify-content:space-between;font-size:.87rem;margin-bottom:.3rem">
                        <span style="font-weight:600"><?= h($t['titre']) ?></span>
                        <span><?= $t['nb'] ?> · <?= formatPrix((float)$t['ca']) ?></span>
                    </div>
                    <div style="background:var(--border);border-radius:20px;height:7px">
                        <div style="width:<?= $max?round($t['nb']/$max*100):0 ?>%;background:var(--admin);height:7px;border-radius:20px"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="background:var(--white);border:1px solid var(--border);border-radius:14px;padding:1.5rem">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem">📊 Par statut</h2>
                <?php foreach($par_statut as $ps): $pct=$total_r?round($ps['nb']/$total_r*100):0; ?>
                <div style="margin-bottom:.9rem">
                    <div style="display:flex;justify-content:space-between;font-size:.87rem;margin-bottom:.3rem">
                        <span class="badge badge-<?= $ps['statut'] ?>"><?= ucfirst($ps['statut']) ?></span>
                        <span><?= $ps['nb'] ?> (<?= $pct ?>%)</span>
                    </div>
                    <div style="background:var(--border);border-radius:20px;height:7px">
                        <div style="width:<?= $pct ?>%;background:var(--primary);height:7px;border-radius:20px"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
