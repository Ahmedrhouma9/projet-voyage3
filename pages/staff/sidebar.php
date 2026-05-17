<aside class="sidebar staff-sidebar">
    <div class="sidebar-brand staff-brand">🟢 Espace Staff</div>
    <div class="sidebar-title">Menu</div>
    <a href="/projet-voyage3/pages/staff/dashboard.php"    <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'class="active"':'' ?>>📊 Dashboard</a>
    <a href="/projet-voyage3/pages/staff/reservations.php" <?= basename($_SERVER['PHP_SELF'])==='reservations.php'?'class="active"':'' ?>>📅 Réservations</a>
    <a href="/projet-voyage3/pages/staff/clients.php"      <?= basename($_SERVER['PHP_SELF'])==='clients.php'?'class="active"':'' ?>>👥 Clients</a>
    <a href="/projet-voyage3/pages/staff/voyages.php"      <?= basename($_SERVER['PHP_SELF'])==='voyages.php'?'class="active"':'' ?>>🌍 Voyages</a>
    <a href="/projet-voyage3/pages/staff/statistiques.php" <?= basename($_SERVER['PHP_SELF'])==='statistiques.php'?'class="active"':'' ?>>📈 Statistiques</a>
    <a href="/projet-voyage3/pages/staff/profil.php"       <?= basename($_SERVER['PHP_SELF'])==='profil.php'?'class="active"':'' ?>>👤 Mon profil</a>
</aside>
