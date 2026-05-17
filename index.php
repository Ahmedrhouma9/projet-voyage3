<?php
require_once __DIR__ . '/includes/auth.php';
// Si connecté et pas client → rediriger vers son dashboard
if (isLoggedIn() && !isClient()) { redirectByRole(); }

$pdo     = getDB();
$voyages = $pdo->query("SELECT * FROM voyages WHERE disponible=1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> — Agence de Voyage</title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/index.php">Accueil</a></li>
            <li><a href="/projet-voyage3/pages/client/voyages.php">Destinations</a></li>
            <?php if (isLoggedIn() && isClient()): ?>
                <li><a href="/projet-voyage3/pages/client/mes-reservations.php">Mes réservations</a></li>
                <li><a href="/projet-voyage3/pages/client/profil.php">Mon profil</a></li>
                <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="/projet-voyage3/pages/client/login.php">Connexion</a></li>
                <li><a href="/projet-voyage3/pages/client/register.php" class="btn-nav">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="hero-badge">✈ Agence de voyage certifiée</div>
        <h1>Explorez le monde<br>depuis la Tunisie</h1>
        <p>Des voyages inoubliables aux meilleurs prix en Dinar Tunisien.</p>
        <a href="/projet-voyage3/pages/client/voyages.php" class="btn-accent">Voir les destinations</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Nos destinations</h2>
        <p class="section-sub">Prix tout compris en DT</p>
        <div class="cards-grid">
            <?php foreach ($voyages as $v): ?>
            <div class="card">
                <div class="card-img" style="position:relative">
                    <?php $img = imageUrl($v['image']); ?>
                    <?php if ($img): ?>
                        <img src="<?= h($img) ?>" alt="<?= h($v['titre']) ?>">
                    <?php else: ?>
                        <div class="card-img-placeholder">🌍</div>
                    <?php endif; ?>
                    <div class="card-badge"><?= $v['duree'] ?> jours</div>
                </div>
                <div class="card-body">
                    <div class="card-dest">📍 <?= h($v['destination']) ?></div>
                    <h3><?= h($v['titre']) ?></h3>
                    <p><?= h(mb_substr($v['description']??'',0,100)) ?>...</p>
                    <div class="card-meta">
                        <div>
                            <span class="price"><?= formatPrix($v['prix']) ?></span>
                            <span class="price-sub">par personne</span>
                        </div>
                        <span style="font-size:.82rem;color:var(--text-muted)"><?= $v['places_restantes'] ?> places</span>
                    </div>
                    <?php if (isLoggedIn() && isClient()): ?>
                        <a href="/projet-voyage3/pages/client/reserver.php?voyage_id=<?= $v['id'] ?>" class="btn-primary w-full">Réserver</a>
                    <?php else: ?>
                        <a href="/projet-voyage3/pages/client/login.php" class="btn-secondary w-full">Connexion pour réserver</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.</p></div>
</footer>
<script src="/projet-voyage3/public/js/main.js"></script>
</body>
</html>
