<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$pdo = getDB();

$stats = [
    'clients'      => $pdo->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn(),
    'staffs'       => $pdo->query("SELECT COUNT(*) FROM users WHERE role='staff'")->fetchColumn(),
    'voyages'      => $pdo->query("SELECT COUNT(*) FROM voyages")->fetchColumn(),
    'reservations' => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
    'pending'      => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='pending'")->fetchColumn(),
    'ca'           => $pdo->query("SELECT COALESCE(SUM(prix_total),0) FROM reservations WHERE statut='confirmed'")->fetchColumn(),
];

$last_reserv = $pdo->query("
    SELECT r.*, u.nom, u.prenom, v.titre, v.destination
    FROM reservations r
    JOIN users u ON r.user_id=u.id
    JOIN voyages v ON r.voyage_id=v.id
    ORDER BY r.created_at DESC LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar admin-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/admin/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">ADMIN</small></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Voir le site</a></li>
            <li><span style="color:rgba(255,255,255,.7);font-size:.85rem">👤 <?= h($_SESSION['prenom']) ?></span></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>

<div class="dash-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
        <div class="page-header">
            <h1>Dashboard Admin</h1>
            <span style="color:var(--text-muted);font-size:.85rem"><?= date('d/m/Y H:i') ?></span>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-label">Clients</div><div class="stat-value blue"><?= $stats['clients'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">🧑‍💼</div><div class="stat-label">Staffs</div><div class="stat-value green"><?= $stats['staffs'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">🌍</div><div class="stat-label">Voyages</div><div class="stat-value blue"><?= $stats['voyages'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-label">Réservations</div><div class="stat-value blue"><?= $stats['reservations'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-label">En attente</div><div class="stat-value amber"><?= $stats['pending'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-label">CA confirmé (DT)</div><div class="stat-value green" style="font-size:1.3rem"><?= number_format((float)$stats['ca'],3) ?></div></div>
        </div>

        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">Dernières réservations</h2>
        <table class="data-table">
            <thead><tr><th>Client</th><th>Voyage</th><th>Personnes</th><th>Total</th><th>Statut</th></tr></thead>
            <tbody>
                <?php foreach ($last_reserv as $r): ?>
                <tr>
                    <td><?= h($r['prenom'].' '.$r['nom']) ?></td>
                    <td><?= h($r['titre']) ?><br><span style="font-size:.76rem;color:var(--text-muted)">📍 <?= h($r['destination']) ?></span></td>
                    <td><?= $r['nb_personnes'] ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= formatPrix($r['prix_total']) ?></td>
                    <td><span class="badge badge-<?= $r['statut'] ?>"><?= ucfirst($r['statut']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
