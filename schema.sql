-- =============================================================================
-- Schéma Relationnel & Données Initiales — Gestion Pédagogique
-- Base de données : MySQL 8.4 LTS
-- =============================================================================

CREATE DATABASE IF NOT EXISTS gestion_pedagogique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_pedagogique;

-- 1. Table Utilisateurs (Table mère - Héritage POO)
CREATE TABLE IF NOT EXISTS utilisateurs (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'ENSEIGNANT', 'ETUDIANT') NOT NULL,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table Enseignants (Spécialisation d'Utilisateurs)
CREATE TABLE IF NOT EXISTS enseignants (
    idUtilisateur INT PRIMARY KEY,
    matricule VARCHAR(50) NOT NULL UNIQUE,
    specialite VARCHAR(100) NOT NULL,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Table Étudiants (Spécialisation d'Utilisateurs)
CREATE TABLE IF NOT EXISTS etudiants (
    idUtilisateur INT PRIMARY KEY,
    cne VARCHAR(50) NOT NULL UNIQUE,
    dateNaissance DATE NOT NULL,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table Filières
CREATE TABLE IF NOT EXISTS filieres (
    idFiliere INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    intitule VARCHAR(150) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB;

-- 5. Table Modules
CREATE TABLE IF NOT EXISTS modules (
    idModule INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    intitule VARCHAR(150) NOT NULL,
    semestre ENUM('S1', 'S2', 'S3', 'S4') NOT NULL,
    coefficient FLOAT NOT NULL DEFAULT 1.0,
    idFiliere INT NOT NULL,
    idEnseignant INT NULL,
    FOREIGN KEY (idFiliere) REFERENCES filieres(idFiliere) ON DELETE CASCADE,
    FOREIGN KEY (idEnseignant) REFERENCES enseignants(idUtilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Table Inscriptions (Année universitaire)
CREATE TABLE IF NOT EXISTS inscriptions (
    idInscription INT AUTO_INCREMENT PRIMARY KEY,
    idEtudiant INT NOT NULL,
    idFiliere INT NOT NULL,
    anneeUniversitaire VARCHAR(20) NOT NULL, -- Ex: '2026-2027'
    dateInscription DATE NOT NULL,
    statut ENUM('EN_COURS', 'VALIDE', 'AJOURNE') DEFAULT 'EN_COURS',
    UNIQUE KEY uq_etudiant_annee (idEtudiant, anneeUniversitaire),
    FOREIGN KEY (idEtudiant) REFERENCES etudiants(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (idFiliere) REFERENCES filieres(idFiliere) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Table Séances (Gestion d'assiduité)
CREATE TABLE IF NOT EXISTS seances (
    idSeance INT AUTO_INCREMENT PRIMARY KEY,
    idModule INT NOT NULL,
    dateSeance DATE NOT NULL,
    heureDebut TIME NOT NULL,
    heureFin TIME NOT NULL,
    typeSeance ENUM('COURS', 'TD', 'TP') NOT NULL DEFAULT 'COURS',
    FOREIGN KEY (idModule) REFERENCES modules(idModule) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Table Absences
CREATE TABLE IF NOT EXISTS absences (
    idAbsence INT AUTO_INCREMENT PRIMARY KEY,
    idEtudiant INT NOT NULL,
    idSeance INT NOT NULL,
    statut ENUM('ABSENT', 'RETARD', 'PRESENT') DEFAULT 'ABSENT',
    justifiee BOOLEAN DEFAULT FALSE,
    motif TEXT NULL,
    UNIQUE KEY uq_etudiant_seance (idEtudiant, idSeance),
    FOREIGN KEY (idEtudiant) REFERENCES etudiants(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (idSeance) REFERENCES seances(idSeance) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Table Notes & Évaluations
CREATE TABLE IF NOT EXISTS notes (
    idNote INT AUTO_INCREMENT PRIMARY KEY,
    idEtudiant INT NOT NULL,
    idModule INT NOT NULL,
    noteCC FLOAT NULL CHECK (noteCC BETWEEN 0 AND 20),
    noteExam FLOAT NULL CHECK (noteExam BETWEEN 0 AND 20),
    noteFinale FLOAT NULL CHECK (noteFinale BETWEEN 0 AND 20),
    session ENUM('NORMALE', 'RATTRAPAGE') DEFAULT 'NORMALE',
    statutValidation ENUM('V', 'RATT', 'NV') DEFAULT 'NV',
    remarque TEXT NULL,
    UNIQUE KEY uq_etudiant_module_session (idEtudiant, idModule, session),
    FOREIGN KEY (idEtudiant) REFERENCES etudiants(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (idModule) REFERENCES modules(idModule) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Table Annonces Pédagogiques
CREATE TABLE IF NOT EXISTS annonces (
    idAnnonce INT AUTO_INCREMENT PRIMARY KEY,
    idAuteur INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    datePublication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    portee ENUM('GLOBALE', 'MODULE') NOT NULL DEFAULT 'GLOBALE',
    idModule INT NULL,
    FOREIGN KEY (idAuteur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (idModule) REFERENCES modules(idModule) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Table Journal d'Audit des Notes
CREATE TABLE IF NOT EXISTS journal_audit_notes (
    idAudit INT AUTO_INCREMENT PRIMARY KEY,
    idNote INT NOT NULL,
    idUtilisateur INT NOT NULL,
    typeAction ENUM('CREATION', 'MODIFICATION', 'SUPPRESSION') NOT NULL,
    ancienneValeur FLOAT NULL,
    nouvelleValeur FLOAT NULL,
    dateAction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idNote) REFERENCES notes(idNote) ON DELETE CASCADE,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================================
-- Données Initiales / Seeds de Test (Mots de passe hashés via password_hash)
-- Mot de passe admin : admin123
-- Mot de passe enseignant : teacher123
-- Mot de passe étudiant : student123
-- =============================================================================

-- Insertion de l'Administrateur
INSERT INTO utilisateurs (idUtilisateur, nom, prenom, email, motDePasse, role)
VALUES (1, 'Benjelloun', 'Karim', 'admin@departement.ma', '$2y$12$o0IN7mzVjKb7/toAINT/geNXH.qYatVQJbnN/k4Xg1wcNEoOJJYGy', 'ADMIN')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Insertion de l'Enseignant
INSERT INTO utilisateurs (idUtilisateur, nom, prenom, email, motDePasse, role)
VALUES (2, 'Alami', 'Ahmed', 'a.alami@departement.ma', '$2y$12$lK9ruyrotAPQHkkI6BxgyOdJMEd7NqfHzsAluiKpCXsANnuL06u/O', 'ENSEIGNANT')
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO enseignants (idUtilisateur, matricule, specialite)
VALUES (2, 'ENS-2026-001', 'Génie Logiciel & Systèmes d\'Information')
ON DUPLICATE KEY UPDATE specialite=VALUES(specialite);

-- Insertion de l'Étudiant
INSERT INTO utilisateurs (idUtilisateur, nom, prenom, email, motDePasse, role)
VALUES (3, 'Idrissi', 'Youssef', 'y.idrissi@etudiant.ma', '$2y$12$7.Nb/F3fbq5yh.5zCi7EjOAhCKMCgy61M3tfNnoho6ppXmXF69oKq', 'ETUDIANT')
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO etudiants (idUtilisateur, cne, dateNaissance)
VALUES (3, 'M134098765', '2004-03-15')
ON DUPLICATE KEY UPDATE cne=VALUES(cne);

-- Insertion d'une Filière
INSERT INTO filieres (idFiliere, code, intitule, description)
VALUES (1, 'INFO-L3', 'Licence Informatique et Ingénierie des Systèmes', 'Formation axée sur le développement logiciel, les bases de données et les architectures web modernes.')
ON DUPLICATE KEY UPDATE code=VALUES(code);

-- Insertion d'un Module rattaché à la Filière et à l'Enseignant
INSERT INTO modules (idModule, code, intitule, semestre, coefficient, idFiliere, idEnseignant)
VALUES (1, 'DEV-WEB', 'Développement Web & Architecture Backend', 'S1', 2.0, 1, 2)
ON DUPLICATE KEY UPDATE code=VALUES(code);
