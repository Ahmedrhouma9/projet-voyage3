<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ============================================================
// Helpers de base
// ============================================================

function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
function getRole(): string  { return $_SESSION['role'] ?? ''; }
function isAdmin(): bool    { return getRole() === 'admin'; }
function isStaff(): bool    { return getRole() === 'staff'; }
function isClient(): bool   { return getRole() === 'client'; }

// ============================================================
// Middleware de protection des routes
// ============================================================

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/client/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        redirectByRole();
    }
}

function requireStaff(): void {
    requireLogin();
    if (!isStaff()) {
        redirectByRole();
    }
}

function requireClient(): void {
    requireLogin();
    if (!isClient()) {
        redirectByRole();
    }
}

function requireAdminOrStaff(): void {
    requireLogin();
    if (!isAdmin() && !isStaff()) {
        redirectByRole();
    }
}

// Redirection automatique selon le rôle
function redirectByRole(): void {
    switch (getRole()) {
        case 'admin':
            header('Location: ' . BASE_URL . '/pages/admin/dashboard.php'); break;
        case 'staff':
            header('Location: ' . BASE_URL . '/pages/staff/dashboard.php'); break;
        case 'client':
            header('Location: ' . BASE_URL . '/index.php'); break;
        default:
            header('Location: ' . BASE_URL . '/pages/client/login.php'); break;
    }
    exit;
}

// ============================================================
// Auth
// ============================================================

function login(string $email, string $password): array {
    $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom']     = $user['nom'];
    $_SESSION['prenom']  = $user['prenom'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    return ['success' => true, 'role' => $user['role']];
}

function logout(): void {
    session_destroy();
    header('Location: ' . BASE_URL . '/pages/client/login.php');
    exit;
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $stmt = getDB()->prepare("SELECT id,nom,prenom,email,telephone,role FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function register(string $nom, string $prenom, string $email, string $password, string $tel = ''): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return ['success' => false, 'message' => 'Email déjà utilisé.'];

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users (nom,prenom,email,mot_de_passe,telephone,role) VALUES (?,?,?,?,?,'client')")
        ->execute([$nom, $prenom, $email, $hash, $tel]);
    return ['success' => true];
}

// ============================================================
// Upload image
// ============================================================

function uploadImage(array $file): array {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed))
        return ['success' => false, 'message' => 'Format non autorisé.'];
    if ($file['size'] > MAX_FILE_SIZE)
        return ['success' => false, 'message' => 'Fichier trop volumineux (max 5MB).'];
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename))
        return ['success' => false, 'message' => 'Erreur upload.'];
    return ['success' => true, 'filename' => $filename];
}

// ============================================================
// Reservation helpers
// ============================================================

// Vérifie si une réservation peut être modifiée/supprimée par le client
function canClientModifyReservation(array $reservation): bool {
    return $reservation['statut'] === 'pending';
}
