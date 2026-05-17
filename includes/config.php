<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'tunis');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME',  'TunisieVoyages');
define('DEVISE',     'DT');
define('BASE_URL',   '/projet-voyage3');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', '/projet-voyage3/public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Connexion BDD impossible']));
        }
    }
    return $pdo;
}

function formatPrix(float $prix): string {
    return number_format($prix, 3, '.', ' ') . ' ' . DEVISE;
}

function imageUrl(?string $image): string {
    if (!$image) return '';
    if (str_starts_with($image, 'http')) return $image;
    return UPLOAD_URL . $image;
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
