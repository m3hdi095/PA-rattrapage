CREATE DATABASE IF NOT EXISTS no_more_waste
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE no_more_waste;

DROP TABLE IF EXISTS inscriptions_service;
DROP TABLE IF EXISTS plannings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS livraison_produits;
DROP TABLE IF EXISTS livraisons;
DROP TABLE IF EXISTS tournees;
DROP TABLE IF EXISTS destinataires;
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS collectes;
DROP TABLE IF EXISTS benevole_capacites;
DROP TABLE IF EXISTS capacites;
DROP TABLE IF EXISTS benevoles;
DROP TABLE IF EXISTS adherents;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom           VARCHAR(100),
    prenom        VARCHAR(100),
    role          ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB;

CREATE TABLE adherents (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(190) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    nom             VARCHAR(150) NOT NULL,
    siret           VARCHAR(14) NOT NULL UNIQUE,
    adresse         VARCHAR(190),
    code_postal     VARCHAR(10),
    ville           VARCHAR(100),
    telephone       VARCHAR(20),
    date_adhesion   DATE NOT NULL,
    date_expiration DATE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE benevoles (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    email               VARCHAR(190) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    telephone           VARCHAR(20),
    statut_candidature  ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente'
) ENGINE=InnoDB;

CREATE TABLE capacites (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE benevole_capacites (
    benevole_id INT NOT NULL,
    capacite_id INT NOT NULL,
    PRIMARY KEY (benevole_id, capacite_id),
    CONSTRAINT fk_bc_benevole FOREIGN KEY (benevole_id) REFERENCES benevoles(id) ON DELETE CASCADE,
    CONSTRAINT fk_bc_capacite FOREIGN KEY (capacite_id) REFERENCES capacites(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE collectes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id  INT NOT NULL,
    date_collecte DATETIME NOT NULL,
    statut       ENUM('planifiee', 'effectuee', 'annulee') NOT NULL DEFAULT 'planifiee',
    CONSTRAINT fk_collectes_adherent FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE produits (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    collecte_id        INT NOT NULL,
    code_barre         VARCHAR(50) NOT NULL UNIQUE,
    nom                VARCHAR(150) NOT NULL,
    quantite           INT NOT NULL DEFAULT 1,
    date_limite_conso  DATE,
    emplacement_stock  VARCHAR(100),
    statut             ENUM('en_stock', 'distribue', 'perime') NOT NULL DEFAULT 'en_stock',
    CONSTRAINT fk_produits_collecte FOREIGN KEY (collecte_id) REFERENCES collectes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE destinataires (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('association', 'particulier') NOT NULL,
    nom         VARCHAR(150) NOT NULL,
    adresse     VARCHAR(190) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    ville       VARCHAR(100) NOT NULL,
    telephone   VARCHAR(20)
) ENGINE=InnoDB;

CREATE TABLE tournees (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    benevole_id  INT,
    date_tournee DATE NOT NULL,
    statut       ENUM('planifiee', 'en_cours', 'terminee') NOT NULL DEFAULT 'planifiee',
    CONSTRAINT fk_tournees_benevole FOREIGN KEY (benevole_id) REFERENCES benevoles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE livraisons (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    tournee_id      INT NOT NULL,
    destinataire_id INT NOT NULL,
    statut          ENUM('prevue', 'livree', 'annulee') NOT NULL DEFAULT 'prevue',
    CONSTRAINT fk_livraisons_tournee FOREIGN KEY (tournee_id) REFERENCES tournees(id) ON DELETE CASCADE,
    CONSTRAINT fk_livraisons_destinataire FOREIGN KEY (destinataire_id) REFERENCES destinataires(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE livraison_produits (
    livraison_id INT NOT NULL,
    produit_id   INT NOT NULL,
    quantite     INT NOT NULL DEFAULT 1,
    PRIMARY KEY (livraison_id, produit_id),
    CONSTRAINT fk_lp_livraison FOREIGN KEY (livraison_id) REFERENCES livraisons(id) ON DELETE CASCADE,
    CONSTRAINT fk_lp_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    description TEXT,
    capacite_id INT,
    CONSTRAINT fk_services_capacite FOREIGN KEY (capacite_id) REFERENCES capacites(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE plannings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    service_id   INT NOT NULL,
    benevole_id  INT,
    date_debut   DATETIME NOT NULL,
    date_fin     DATETIME NOT NULL,
    lieu         VARCHAR(190),
    places_max   INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_plannings_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    CONSTRAINT fk_plannings_benevole FOREIGN KEY (benevole_id) REFERENCES benevoles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inscriptions_service (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    planning_id INT NOT NULL,
    adherent_id INT NOT NULL,
    statut      ENUM('inscrit', 'annule') NOT NULL DEFAULT 'inscrit',
    UNIQUE (planning_id, adherent_id),
    CONSTRAINT fk_is_planning FOREIGN KEY (planning_id) REFERENCES plannings(id) ON DELETE CASCADE,
    CONSTRAINT fk_is_adherent FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO capacites (libelle) VALUES
    ('chauffeur'), ('cuisinier'), ('plombier'), ('electricien');

-- Mot de passe pour tous les comptes de demo : test1234
-- (hash bcrypt genere une seule fois, reutilise pour tous les comptes de test)

INSERT INTO admins (email, password_hash, nom, prenom, role) VALUES
    ('super.admin@nomorewaste.fr', '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Admin', 'Super', 'super_admin'),
    ('admin@nomorewaste.fr',       '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Martin', 'Julie', 'admin');

INSERT INTO adherents (email, password_hash, nom, siret, adresse, code_postal, ville, telephone, date_adhesion, date_expiration) VALUES
    ('super.admin@nomorewaste.fr',   '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Boulangerie du Marais',   '11122233300011', '12 rue des Rosiers',        '75004', 'Paris',    '0142330011', '2025-09-01', '2026-09-01'),
    ('contact@epicerie-verte.fr',    '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Epicerie Verte',          '22233344400022', '5 avenue Jean Jaures',      '75019', 'Paris',    '0142440022', '2025-11-15', '2026-11-15'),
    ('info@primeur-bio-nantes.fr',   '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Primeur Bio Nantes',      '33344455500033', '8 rue de Strasbourg',       '44000', 'Nantes',   '0240550033', '2026-01-10', '2027-01-10'),
    ('hello@resto-phoceen.fr',       '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Restaurant Le Phoceen',   '44455566600044', '20 quai du Port',           '13002', 'Marseille','0491660044', '2026-03-01', '2027-03-01');

INSERT INTO benevoles (email, password_hash, nom, prenom, telephone, statut_candidature) VALUES
    ('super.admin@nomorewaste.fr', '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Dupont',  'Jean',   '0611111111', 'valide'),
    ('alice.martin@mail.fr',    '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Martin',  'Alice',  '0622222222', 'valide'),
    ('karim.saidi@mail.fr',     '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Saidi',   'Karim',  '0633333333', 'valide'),
    ('sofia.rossi@mail.fr',     '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Rossi',   'Sofia',  '0644444444', 'en_attente'),
    ('lucas.bernard@mail.fr',   '$2a$10$2M4LzkqwEGR9i5DoOb17YuUuaFqUYEq.gh0Lhdan7x2fxOA2Q/y.C', 'Bernard', 'Lucas',  '0655555555', 'refuse');

INSERT INTO benevole_capacites (benevole_id, capacite_id) VALUES
    (1, 1),
    (2, 1), (2, 2),
    (3, 3),
    (4, 4);

INSERT INTO collectes (adherent_id, date_collecte, statut) VALUES
    (1, '2026-08-20 09:00:00', 'effectuee'),
    (1, '2026-09-05 10:00:00', 'planifiee'),
    (2, '2026-08-22 14:00:00', 'effectuee'),
    (3, '2026-09-10 09:00:00', 'planifiee'),
    (4, '2026-08-15 11:00:00', 'annulee');

INSERT INTO produits (collecte_id, code_barre, nom, quantite, date_limite_conso, emplacement_stock, statut) VALUES
    (1, '1000000000001', 'Pain complet',       10, '2026-08-25', 'Etagere A1', 'distribue'),
    (1, '1000000000002', 'Croissants',         20, '2026-08-24', 'Etagere A2', 'distribue'),
    (3, '1000000000003', 'Legumes assortis',   15, '2026-08-26', 'Etagere B1', 'en_stock'),
    (3, '1000000000004', 'Conserves diverses', 30, NULL,         'Etagere B2', 'en_stock'),
    (1, '1000000000005', 'Yaourts nature',     12, '2026-08-23', 'Frigo 1',    'perime');

INSERT INTO destinataires (type, nom, adresse, code_postal, ville, telephone) VALUES
    ('association', 'Restos du Coeur Paris 4', '10 rue de Rivoli',     '75004', 'Paris',  '0140000001'),
    ('particulier', 'Famille Nguyen',          '22 rue de Belleville', '75020', 'Paris',  '0640000002'),
    ('association', 'Croix-Rouge Nantes',      '3 quai de la Fosse',   '44000', 'Nantes', '0240000003');

INSERT INTO tournees (benevole_id, date_tournee, statut) VALUES
    (1, '2026-08-21', 'terminee'),
    (2, '2026-09-06', 'planifiee');

INSERT INTO livraisons (tournee_id, destinataire_id, statut) VALUES
    (1, 1, 'livree'),
    (1, 2, 'livree'),
    (2, 3, 'prevue');

INSERT INTO livraison_produits (livraison_id, produit_id, quantite) VALUES
    (1, 1, 5),
    (1, 2, 10),
    (2, 1, 3),
    (3, 3, 8),
    (3, 4, 15);

INSERT INTO services (nom, description, capacite_id) VALUES
    ('Cours de cuisine anti-gaspi',      'Apprendre a cuisiner les restes et les invendus.', 2),
    ('Covoiturage collectes',            'Aider au transport des produits collectes.',        1),
    ('Petits travaux de plomberie',      'Depannage plomberie pour les adherents.',           3),
    ('Conseils anti-gaspi',              'Ateliers de sensibilisation au gaspillage.',        NULL);

INSERT INTO plannings (service_id, benevole_id, date_debut, date_fin, lieu, places_max) VALUES
    (1, 2, '2026-08-15 09:00:00', '2026-08-15 12:00:00', 'Local associatif Paris',  8),
    (1, 2, '2026-09-05 09:00:00', '2026-09-05 12:00:00', 'Local associatif Paris',  8),
    (2, 1, '2026-09-06 08:00:00', '2026-09-06 11:00:00', 'Depot Paris',             1),
    (3, 3, '2026-09-12 14:00:00', '2026-09-12 17:00:00', 'Local associatif Nantes', 3);

INSERT INTO inscriptions_service (planning_id, adherent_id, statut) VALUES
    (2, 1, 'inscrit'),
    (2, 2, 'inscrit'),
    (4, 3, 'inscrit'),
    (1, 1, 'annule');
