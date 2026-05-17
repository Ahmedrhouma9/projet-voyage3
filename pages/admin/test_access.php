<?php
// Ce fichier teste que les redirections fonctionnent
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
echo "Accès admin OK - " . $_SESSION['email'];
