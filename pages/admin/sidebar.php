<aside class="sidebar admin-sidebar">
    <div class="sidebar-brand admin-brand">🔴 Administration</div>
    <div class="sidebar-title">Menu</div>
    <a href="/projet-voyage3/pages/admin/dashboard.php"     <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'class="active"':'' ?>>📊 Dashboard</a>
    <a href="/projet-voyage3/pages/admin/staffs.php"        <?= basename($_SERVER['PHP_SELF'])==='staffs.php'?'class="active"':'' ?>>🧑‍💼 Staffs</a>
    <a href="/projet-voyage3/pages/admin/clients.php"       <?= basename($_SERVER['PHP_SELF'])==='clients.php'?'class="active"':'' ?>>👥 Clients</a>
    <a href="/projet-voyage3/pages/admin/voyages.php"       <?= basename($_SERVER['PHP_SELF'])==='voyages.php'?'class="active"':'' ?>>🌍 Voyages</a>
    <a href="/projet-voyage3/pages/admin/reservations.php"  <?= basename($_SERVER['PHP_SELF'])==='reservations.php'?'class="active"':'' ?>>📅 Réservations</a>
    <a href="/projet-voyage3/pages/admin/statistiques.php"  <?= basename($_SERVER['PHP_SELF'])==='statistiques.php'?'class="active"':'' ?>>📈 Statistiques</a>
</aside>
