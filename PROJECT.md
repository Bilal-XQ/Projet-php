# Spécifications Pédagogiques & Techniques — Application de Gestion Pédagogique

## 1. Contexte & Périmètre du Projet
L'application Web centralise la gestion pédagogique du département Mathématiques-Informatique. Elle élimine les doublons et les pertes de données en orchestrant les filières, modules, enseignants, étudiants, inscriptions, évaluations et présences.

Ce document est le référentiel unique d'implémentation pour l'équipe et les agents IA de développement. Aucun framework PHP ne doit être utilisé.

---

## 2. Architecture & Environnement Technique
- **Backend :** PHP natif 8.3 (Orienté Objet strict, typage fort, séparation MVC sans framework).
- **Base de données :** MySQL 8.4 LTS via l'extension native **PHP PDO** (requêtes préparées obligatoires).
- **Serveur Web :** Nginx Alpine reverse-proxy vers PHP-FPM.
- **Frontend :** HTML5 sémantique, CSS3 moderne (Responsive, Flexbox/Grid, variables CSS natives), JavaScript Vanilla (ES6+, DOM natif, Fetch API).
- **Conteneurisation :** Docker & Docker Compose (environnement identique pour toute l'équipe).
- **Bibliothèque tierce minimale :** FPDF (ou équivalent natif sans dépendance externe lourde) pour le rendu PDF.

---

## 3. Rôles & Cas d'Utilisation Métier
1. **Administrateur (Chef de Département / Scolarité) :**
   * Authentification et gestion de profil.
   * Gestion complète des utilisateurs (Création, mise à jour, suppression logique).
   * Configuration de la structure pédagogique (Filières, semestres, modules).
   * Affectation des enseignants aux modules.
   * Inscription des étudiants aux filières par année universitaire.
   * Publication des avis et annonces pédagogiques globales.
   * Consultation du journal d'audit des modifications de notes.
2. **Enseignant :**
   * Authentification et gestion de profil.
   * Consultation des modules attribués et des listes d'étudiants inscrits.
   * Saisie, modification et validation des notes (Contrôle Continu, Examen) accompagnées d'une **remarque pédagogique**.
   * Planification des séances et **déclaration des absences** par séance (avec motif / justification).
   * Publication d'annonces ciblées pour ses modules.
   * Génération et export des relevés de notes en PDF.
3. **Étudiant :**
   * Authentification et consultation de son profil.
   * Consultation de sa filière, de ses modules inscrits et des annonces le concernant.
   * Consultation en direct de ses notes, **remarques de l'enseignant** et statuts de validation.
   * Consultation de son **bilan d'absences** par séance et par module.
   * Téléchargement de son relevé de notes officiel au format PDF.

---

## 4. Schéma Relationnel MySQL (`schema.sql`)

```sql
CREATE DATABASE IF NOT EXISTS gestion_pedagogique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_pedagogique;

-- 1. Table Utilisateurs (Héritage POO)
CREATE TABLE utilisateurs (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'ENSEIGNANT', 'ETUDIANT') NOT NULL,
    dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table Enseignants
CREATE TABLE enseignants (
    idUtilisateur INT PRIMARY KEY,
    matricule VARCHAR(50) NOT NULL UNIQUE,
    specialite VARCHAR(100) NOT NULL,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Table Étudiants
CREATE TABLE etudiants (
    idUtilisateur INT PRIMARY KEY,
    cne VARCHAR(50) NOT NULL UNIQUE,
    dateNaissance DATE NOT NULL,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table Filières
CREATE TABLE filieres (
    idFiliere INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    intitule VARCHAR(150) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB;

-- 5. Table Modules
CREATE TABLE modules (
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
CREATE TABLE inscriptions (
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
CREATE TABLE seances (
    idSeance INT AUTO_INCREMENT PRIMARY KEY,
    idModule INT NOT NULL,
    dateSeance DATE NOT NULL,
    heureDebut TIME NOT NULL,
    heureFin TIME NOT NULL,
    typeSeance ENUM('COURS', 'TD', 'TP') NOT NULL DEFAULT 'COURS',
    FOREIGN KEY (idModule) REFERENCES modules(idModule) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Table Absences
CREATE TABLE absences (
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
CREATE TABLE notes (
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
CREATE TABLE annonces (
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
CREATE TABLE journal_audit_notes (
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
```

---

## 5. Infrastructure Docker & Configuration

### 5.1 `docker-compose.yml` (Racine)
```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: gestion_pedagogique_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - app_network

  webserver:
    image: nginx:alpine
    container_name: gestion_pedagogique_web
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    depends_on:
      - app
    networks:
      - app_network

  db:
    image: mysql:8.4
    container_name: gestion_pedagogique_db
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: gestion_pedagogique
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_USER: app_user
      MYSQL_PASSWORD: app_password
    volumes:
      - dbdata:/var/lib/mysql
      - ./schema.sql:/docker-entrypoint-initdb.d/schema.sql
    networks:
      - app_network

networks:
  app_network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

### 5.2 `Dockerfile` (Racine)
```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

WORKDIR /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

### 5.3 Configuration Nginx (`docker/nginx/conf.d/default.conf`)
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 6. Classe de Connexion PDO (`config/database.php`)
```php
<?php
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = 'db';
            $db   = 'gestion_pedagogique';
            $user = 'app_user';
            $pass = 'app_password';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, $user, $pass, $options);
        }
        return self::$instance;
    }
}
```

---

## 7. Structure de l'Arborescence MVC
```text
gestion_pedagogique/
├── config/
│   └── database.php
├── docker/
│   └── nginx/
│       └── conf.d/
│           └── default.conf
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   ├── assets/
│   └── index.php            # Front Controller & Routage
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── AdminController.php
│   │   ├── EnseignantController.php
│   │   └── EtudiantController.php
│   ├── Models/
│   │   ├── Utilisateur.php  # Classe abstraite
│   │   ├── Administrateur.php
│   │   ├── Enseignant.php
│   │   ├── Etudiant.php
│   │   ├── Filiere.php
│   │   ├── Module.php
│   │   ├── Seance.php
│   │   ├── Absence.php
│   │   ├── Note.php
│   │   ├── Annonce.php
│   │   └── JournalAudit.php
│   └── Views/
│       ├── layout/          # header.php, footer.php, nav.php
│       ├── auth/
│       ├── admin/
│       ├── enseignant/
│       └── etudiant/
├── vendor/                  # FPDF pour la génération PDF
├── Dockerfile
├── docker-compose.yml
├── schema.sql
└── PROJECT.md
```

---

## 8. Règles de Calcul et Workflow Métier
* **Formule de la Note Finale :**
  $$\text{Note Finale} = (\text{Note CC} \times 0.40) + (\text{Note Exam} \times 0.60)$$
* **Barème de Validation :**
  * $\text{Note Finale} \ge 10.00 \implies$ Statut **Validé (V)**.
  * $\text{Note Finale} < 10.00$ en session normale $\implies$ Statut **Rattrapage (RATT)**.
  * $\text{Note Finale} < 10.00$ après rattrapage $\implies$ Statut **Non Validé (NV)**.
* **Audit automatique :** Chaque mise à jour ou insertion dans la table `notes` consigne automatiquement l'identifiant de l'enseignant, la valeur précédente, la nouvelle valeur et l'horodatage dans `journal_audit_notes`.
