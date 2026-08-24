-- =====================================================================
-- NO MORE WASTE - Schéma de base de données
-- Basé sur "Mission 1 : développement des applications" du rattrapage 2ESGI
-- A importer via phpMyAdmin (WAMP) ou : mysql -u root -p < 001_init_schema.sql
-- =====================================================================

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

-- ---------------------------------------------------------------------
-- Comptes
-- ---------------------------------------------------------------------

-- role : un super_admin peut créer/supprimer des comptes admin et super_admin,
-- un admin simple ne peut pas gérer les autres comptes admin.
CREATE TABLE admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom           VARCHAR(100),
    prenom        VARCHAR(100),
    role          ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB;

-- Adhérents = commerçants. date_expiration sert au rappel automatique de renouvellement.
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

-- statut_candidature : suivi du bénévole depuis sa candidature jusqu'à sa validation
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

-- ---------------------------------------------------------------------
-- Collectes et stocks
-- ---------------------------------------------------------------------

-- Une collecte = un passage de camion chez un adhérent
CREATE TABLE collectes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id  INT NOT NULL,
    date_collecte DATETIME NOT NULL,
    statut       ENUM('planifiee', 'effectuee', 'annulee') NOT NULL DEFAULT 'planifiee',
    CONSTRAINT fk_collectes_adherent FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Chaque produit rapporté est référencé par code-barre et localisé en stock
CREATE TABLE produits (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    collecte_id        INT NOT NULL,
    code_barre         VARCHAR(50) NOT NULL,
    nom                VARCHAR(150) NOT NULL,
    quantite           INT NOT NULL DEFAULT 1,
    date_limite_conso  DATE,
    emplacement_stock  VARCHAR(100),
    statut             ENUM('en_stock', 'distribue', 'perime') NOT NULL DEFAULT 'en_stock',
    CONSTRAINT fk_produits_collecte FOREIGN KEY (collecte_id) REFERENCES collectes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tournées de distribution
-- ---------------------------------------------------------------------

-- Bénéficiaire d'une tournée : association caritative ou particulier en détresse
CREATE TABLE destinataires (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('association', 'particulier') NOT NULL,
    nom         VARCHAR(150) NOT NULL,
    adresse     VARCHAR(190) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    ville       VARCHAR(100) NOT NULL,
    telephone   VARCHAR(20)
) ENGINE=InnoDB;

-- Une tournée est conduite par un bénévole (chauffeur)
CREATE TABLE tournees (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    benevole_id  INT,
    date_tournee DATE NOT NULL,
    statut       ENUM('planifiee', 'en_cours', 'terminee') NOT NULL DEFAULT 'planifiee',
    CONSTRAINT fk_tournees_benevole FOREIGN KEY (benevole_id) REFERENCES benevoles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Un arrêt de la tournée chez un destinataire = une livraison (donne lieu au récap PDF)
CREATE TABLE livraisons (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    tournee_id      INT NOT NULL,
    destinataire_id INT NOT NULL,
    statut          ENUM('prevue', 'livree', 'annulee') NOT NULL DEFAULT 'prevue',
    CONSTRAINT fk_livraisons_tournee FOREIGN KEY (tournee_id) REFERENCES tournees(id) ON DELETE CASCADE,
    CONSTRAINT fk_livraisons_destinataire FOREIGN KEY (destinataire_id) REFERENCES destinataires(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Détail des produits remis lors d'une livraison
CREATE TABLE livraison_produits (
    livraison_id INT NOT NULL,
    produit_id   INT NOT NULL,
    quantite     INT NOT NULL DEFAULT 1,
    PRIMARY KEY (livraison_id, produit_id),
    CONSTRAINT fk_lp_livraison FOREIGN KEY (livraison_id) REFERENCES livraisons(id) ON DELETE CASCADE,
    CONSTRAINT fk_lp_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Services proposés aux adhérents (conseils anti-gaspi, cours de cuisine,
-- partage de véhicules, échange de services, réparation, gardiennage...)
-- ---------------------------------------------------------------------

-- capacite_id : compétence requise pour tenir ce service (optionnelle)
CREATE TABLE services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    description TEXT,
    capacite_id INT,
    CONSTRAINT fk_services_capacite FOREIGN KEY (capacite_id) REFERENCES capacites(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Une occurrence planifiée d'un service, tenue par un bénévole (ex: plombier)
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

-- Inscription d'un adhérent à un créneau de service
CREATE TABLE inscriptions_service (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    planning_id INT NOT NULL,
    adherent_id INT NOT NULL,
    statut      ENUM('inscrit', 'annule') NOT NULL DEFAULT 'inscrit',
    UNIQUE (planning_id, adherent_id),
    CONSTRAINT fk_is_planning FOREIGN KEY (planning_id) REFERENCES plannings(id) ON DELETE CASCADE,
    CONSTRAINT fk_is_adherent FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Compétences de départ (l'admin peut en ajouter/supprimer depuis l'espace admin)
INSERT INTO capacites (libelle) VALUES
    ('chauffeur'), ('cuisinier'), ('plombier'), ('electricien');
