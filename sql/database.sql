-- ============================================================
-- TunisieVoyages — Base de données v2
-- Rôles : admin, staff, client
-- ============================================================

CREATE DATABASE IF NOT EXISTS tunis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tunis;

DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS voyages;
DROP TABLE IF EXISTS users;

-- ============================================================
-- TABLE : users (3 rôles)
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('admin', 'staff', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : voyages
-- ============================================================
CREATE TABLE voyages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(10,3) NOT NULL,
    duree INT NOT NULL,
    places_total INT NOT NULL DEFAULT 20,
    places_restantes INT NOT NULL DEFAULT 20,
    date_depart DATE,
    image VARCHAR(500),
    disponible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : reservations
-- ============================================================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voyage_id INT NOT NULL,
    nb_personnes INT NOT NULL DEFAULT 1,
    prix_total DECIMAL(10,3) NOT NULL,
    statut ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (voyage_id) REFERENCES voyages(id) ON DELETE CASCADE
);

-- ============================================================
-- Données de test
-- ============================================================

-- Admin : admin@agence.tn / password
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Principal', 'admin@agence.tn',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
-- Staff : staff@agence.tn / password
('Ben Salem', 'Sami', 'staff@agence.tn',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
-- Client : client@test.tn / password
('Ben Ali', 'Ahmed', 'client@test.tn',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client');

-- Voyages
INSERT INTO voyages (titre, destination, description, prix, duree, places_total, places_restantes, date_depart, image, disponible) VALUES
('Séjour à Djerba', 'Djerba, Tunisie', 'Plages de sable blanc, eaux turquoise et médina historique. Hôtel 4★ all-inclusive inclus.', 1450.000, 7, 20, 20, '2025-07-10', 'https://images.unsplash.com/photo-1539650116574-75c0c6d73d0e?w=800&q=80', 1),
('Aventure Sahara', 'Douz, Tunisie', 'Nuit sous les étoiles en campement berbère, balade à dos de chameau au lever du soleil.', 980.000, 4, 15, 15, '2025-08-05', 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80', 1),
('Sidi Bou Said & Carthage', 'Tunis, Tunisie', 'Village bleu et blanc de Sidi Bou Said, ruines de Carthage, musée du Bardo.', 520.000, 3, 25, 25, '2025-06-15', 'https://images.unsplash.com/photo-1548013146-72479768bada?w=800&q=80', 1),
('Tabarka — Plongée & Nature', 'Tabarka, Tunisie', 'Fonds marins exceptionnels, forêts de chênes-lièges. Hôtel 3★ en bord de mer.', 750.000, 5, 18, 18, '2025-07-20', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80', 1),
('Istanbul', 'Istanbul, Turquie', 'Sainte-Sophie, Topkapi, croisière sur le Bosphore. Vol + hôtel 4★.', 3200.000, 6, 20, 20, '2025-09-10', 'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=800&q=80', 1),
('Dubaï — Luxe & Modernité', 'Dubaï, Émirats Arabes Unis', 'Burj Khalifa, safari 4x4 dans les dunes. Vol + hôtel 5★.', 5800.000, 7, 12, 12, '2025-10-05', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800&q=80', 1),
('Paris — Ville Lumière', 'Paris, France', 'Tour Eiffel, Louvre, Montmartre, Versailles. Vol direct depuis Tunis, hôtel 4★.', 4500.000, 5, 20, 20, '2025-11-01', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&q=80', 1),
('Cappadoce — Montgolfière', 'Cappadoce, Turquie', 'Vol en montgolfière au lever du soleil, villages troglodytes.', 3800.000, 5, 15, 15, '2025-08-20', 'https://images.unsplash.com/photo-1570939274717-7eda259b50ed?w=800&q=80', 1),
('Marrakech — Magie du Maroc', 'Marrakech, Maroc', 'Jemaa el-Fna, souks, Palais Bahia, jardins Majorelle. Riad 4★.', 2100.000, 4, 20, 20, '2025-09-25', 'https://images.unsplash.com/photo-1597212618440-806262de4f6b?w=800&q=80', 1),
('Rome — Éternelle', 'Rome, Italie', 'Colisée, Vatican, Fontaine de Trévi. Vol depuis Tunis, hôtel 4★.', 3950.000, 5, 18, 18, '2025-10-15', 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800&q=80', 1);
