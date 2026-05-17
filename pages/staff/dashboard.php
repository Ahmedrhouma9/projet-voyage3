<?php
require_once __DIR__ . '/../../includes/auth.php';
requireStaff();
$pdo = getDB();

$stats = [
    'clients'   => $pdo->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn(),
    'pending'   => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='pending'")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='confirmed'")->fetchColumn(),
    'cancelled' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='cancelled'")->fetchColumn(),
];

$last = $pdo->query("
    SELECT r.*, u.nom, u.prenom, u.email, v.titre
    FROM reservations r JOIN users u ON r.user_id=u.id JOIN voyages v ON r.voyage_id=v.id
    ORDER BY r.created_at DESC LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staff — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar staff-nav">
    <div class="container nav-inner">
        <a href="/projet-voyage3/pages/staff/dashboard.php" class="logo">Tunisie<span>Voyages</span> <small style="font-size:.7rem;opacity:.8;font-weight:400">STAFF</small></a>
        <ul class="nav-links">
            <li><span style="color:rgba(255,255,255,.8);font-size:.85rem">👤 <?= h($_SESSION['prenom']) ?></span></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>
<div class="dash-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
        <div class="page-header">
            <h1>Dashboard Staff</h1>
            <span style="color:var(--text-muted);font-size:.85rem">Bienvenue, <?= h($_SESSION['prenom'].' '.$_SESSION['nom']) ?></span>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-label">Clients</div><div class="stat-value blue"><?= $stats['clients'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-label">En attente</div><div class="stat-value amber"><?= $stats['pending'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-label">Confirmées</div><div class="stat-value green"><?= $stats['confirmed'] ?></div></div>
            <div class="stat-card"><div class="stat-icon">❌</div><div class="stat-label">Annulées</div><div class="stat-value red"><?= $stats['cancelled'] ?></div></div>
        </div>

        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">Dernières réservations à traiter</h2>
        <table class="data-table">
            <thead><tr><th>Client</th><th>Voyage</th><th>Pers.</th><th>Total (DT)</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($last as $r): ?>
                <tr>
                    <td><?= h($r['prenom'].' '.$r['nom']) ?><br><span style="font-size:.76rem;color:var(--text-muted)"><?= h($r['email']) ?></span></td>
                    <td><?= h($r['titre']) ?></td>
                    <td><?= $r['nb_personnes'] ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= formatPrix($r['prix_total']) ?></td>
                    <td><span class="badge badge-<?= $r['statut'] ?>"><?= ucfirst($r['statut']) ?></span></td>
                    <td><a href="/projet-voyage3/pages/staff/reservations.php" class="btn-staff btn-sm">Gérer</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
