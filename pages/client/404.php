<?php
require_once __DIR__ . '/../../includes/auth.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page introuvable — TunisieVoyages</title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<div style="text-align:center;padding:5rem">
    <div style="font-size:5rem">🌍</div>
    <h1 style="font-size:3rem;color:var(--primary)">404</h1>
    <p style="font-size:1.1rem;color:var(--text-muted);margin:1rem 0">Page introuvable</p>
    <a href="/projet-voyage3/index.php" class="btn-primary">Retour à l'accueil</a>
</div>
</body>
</html>
