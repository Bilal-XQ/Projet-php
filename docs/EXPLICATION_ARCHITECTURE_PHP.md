# 🎓 Guide de Compréhension du Projet PHP : Architecture & Fonctionnement

> **Ce guide a été spécialement conçu pour vous permettre de comprendre l'ensemble du projet sans avoir de connaissances préalables en PHP, et de répondre en toute confiance aux questions de votre professeur lors de la soutenance.**

---

## 📌 1. Vue d'Ensemble & Défi Technique

### Qu'est-ce que cette application ?
C'est une plateforme web complète de **Gestion Pédagogique Universitaire**. Elle permet de gérer les départements, filières, modules d'enseignement, étudiants, présences/absences et saisie des notes avec calcul automatique de la moyenne générale et génération de relevés officiels en PDF.

L'application comporte **3 espaces bien distincts** selon le rôle de l'utilisateur :
1. **Administrateur** : Création et gestion des filières, modules, inscriptions des étudiants, gestion des comptes utilisateurs, publication d'annonces globales et consultation du journal d'audit de sécurité.
2. **Enseignant** : Consultation de ses modules affectés, saisie directe des notes sur 20 (évaluation directe sans coefficient), planification des séances de cours et pointage de la feuille d'émargement des présences/absences.
3. **Étudiant** : Tableau de bord personnel, relevé de notes détaillé avec statut de validation (Validé si $\ge 10/20$, Rattrapage si $< 10/20$), calcul automatique de la moyenne générale, bilan analytique de l'assiduité (absences justifiées, non-justifiées, retards), annonces ciblées et téléchargement du relevé officiel au format PDF.

---

### ⭐ Le Grand Point Fort face au Professeur : Le "Pure Vanilla PHP 8.3"
Si votre professeur vous demande :  
> *"Quel framework avez-vous utilisé ? Laravel ? Symfony ?"*

**Votre réponse :**
> *"Nous n'avons volontairement utilisé **aucun framework** (ni Laravel, ni Symfony, ni même Composer). Tout a été conçu en **PHP 8.3 pur ('Vanilla')** selon les principes d'une architecture MVC professionnelle. Nous avons écrit nous-mêmes le routeur HTTP, le moteur d'autoloading PSR-4, la gestion des sessions/sécurité, et même un générateur natif de documents PDF 1.4 !"*

C'est un argument pédagogique majeur : cela prouve que vous maîtrisez les fondations mêmes du langage PHP et du protocole HTTP sans dépendre d'une boîte noire.

---

## 📁 2. Structure des Dossiers Expliquée Simplement

Voici la carte de l'application :

```text
projet/
├── config/                  # ⚙️ Configurations globales
│   ├── database.php         # Connexion à MySQL avec PDO
│   └── autoload.php         # Charge automatiquement les fichiers de classes PHP
│
├── public/                  # 🌐 La SEULE racine accessible depuis le web (Sécurité)
│   ├── index.php            # Le point d'entrée unique ("Front Controller")
│   ├── css/                 # Feuilles de style (design moderne shadcn Zinc)
│   └── js/                  # Scripts JavaScript (thème clair/sombre, alertes)
│
├── src/                     # 🧠 Le CŒUR de l'application (Code PHP)
│   ├── Controllers/         # Les chefs d'orchestre (gèrent les clics et formulaires)
│   ├── Models/              # Les spécialistes de la base de données MySQL
│   ├── Views/               # Les pages d'affichage HTML avec injection PHP
│   └── Utils/               # Les outils réutilisables (Routeur, Sécurité, PDF, Icônes)
│
├── database/                # 🗄️ Base de données
│   └── schema.sql           # Tables MySQL et données de démonstration
│
├── screenshots/             # 📸 Captures d'écran de toutes les pages de l'application
│
├── docker/                  # 🐳 Configuration de l'environnement Docker
│   └── nginx/               # Serveur Web Nginx
├── docker-compose.yml       # Fichier pour démarrer App + Web + DB en 1 clic
└── Dockerfile               # Recette de l'image PHP 8.3 FPM
```

### Détail des dossiers clés :

#### 1. Le dossier `public/` (Pourquoi est-il séparé ?)
Dans une application moderne et sécurisée, le serveur Web (Nginx ou Apache) pointe **uniquement** sur le dossier `public/`.
- **Pourquoi ?** Parce qu'un visiteur externe ne doit **jamais** pouvoir accéder directement à un fichier sensible comme `config/database.php` ou `src/Models/Utilisateur.php`.
- `public/index.php` est le **Front Controller** : 100% des requêtes passent d'abord par ce fichier.

#### 2. Le dossier `config/`
- **`database.php`** : Contient les paramètres de connexion à MySQL (hôte, nom de la base, identifiant, mot de passe) et utilise l'objet natif **PDO** (`PHP Data Objects`).
- **`autoload.php`** : Permet à PHP de charger automatiquement une classe dès qu'on en a besoin. Par exemple, si on écrit `new EtudiantController()`, l'autoloader cherche et inclut automatiquement le fichier `src/Controllers/EtudiantController.php` sans avoir besoin d'écrire des dizaines de `require_once`.

#### 3. Le dossier `src/` (L'architecture MVC)
Ce dossier est divisé en 3 parties fondamentales : **M**odèles, **V**ues, **C**ontrôleurs.

---

## 🏛️ 3. L'Architecture MVC : Comment ça marche ?

L'architecture **MVC** sépare clairement le code en 3 responsabilités :

```
             ┌─────────────────────────────┐
             │       Navigateur Web        │
             │ (L'utilisateur clique/tape) │
             └──────────────┬──────────────┘
                            │ URL: /etudiant/notes
                            ▼
             ┌─────────────────────────────┐
             │     public/index.php        │
             │   & src/Utils/Router.php    │
             └──────────────┬──────────────┘
                            │ Appelle la méthode
                            ▼
             ┌─────────────────────────────┐
             │         CONTROLLER          │ ───► Vérifie le rôle (Auth)
             │  (EtudiantController.php)   │
             └──────┬───────────────▲──────┘
Demande les         │               │ Renvoie les données
notes de l'étudiant │               │ sous forme de tableaux PHP
                    ▼               │
             ┌──────────────┐       │
             │    MODEL     │ ──────┘
             │  (Note.php)  │ ◄──► Exécute le SQL sécurisé dans MySQL
             └──────────────┘
                    │
            Passe les données
            à afficher
                    ▼
             ┌──────────────┐
             │     VIEW     │ ───► Génère le HTML final avec
             │  (notes.php) │      le layout/main.php et les icônes
             └──────────────┘
```

### A. Le Modèle (Model) : Le spécialiste de la Base de Données
- **Rôle** : Exécuter les requêtes SQL (SELECT, INSERT, UPDATE, DELETE) et manipuler les données.
- **Exemples** :
  - `Filiere.php` : récupère la liste des filières et le nombre d'étudiants inscrits.
  - `Note.php` : enregistre la note directe sur 20 et calcule la moyenne générale.
  - `Seance.php` et `Presence.php` : gèrent les cours et l'émargement des étudiants.
  - `Model.php` : la classe mère qui partage la connexion PDO avec tous les modèles.

### B. La Vue (View) : L'Affichage pour l'utilisateur
- **Rôle** : Contient le code HTML, enrichi de petites balises PHP simples (`<?= $variable ?>` ou `<?php foreach(...): ?>`) pour afficher les informations.
- **Règle d'or** : Une vue ne doit **jamais** faire de requêtes SQL directement. Elle se contente d'afficher ce que le contrôleur lui a préparé.
- **`src/Views/layout/main.php`** : C'est le "gabarit" (ou coquille globale). Il contient le `<!DOCTYPE html>`, le `<head>`, la barre latérale de navigation (Sidebar) et la barre supérieure (Topbar). Le contenu spécifique de chaque page vient s'insérer au centre.

### C. Le Contrôleur (Controller) : Le Chef d'Orchestre
- **Rôle** : Reçoit la demande de l'utilisateur, vérifie s'il a le droit d'être là, demande les informations aux modèles, puis appelle la bonne vue pour afficher le résultat.
- **Exemples** :
  - `AuthController.php` : Gère le formulaire de connexion (`/login`) et la déconnexion (`/logout`).
  - `AdminController.php` : Coordonne la création des filières, modules et utilisateurs.
  - `EnseignantController.php` : Traite la saisie des notes et l'enregistrement de l'appel (présences).
  - `EtudiantController.php` : Affiche les notes, les absences et génère le relevé PDF.

---

## 📝 4. La Gestion des Notes : Évaluation Directe sans Coefficient

Dans notre application :
- **La note est directement la note sur 20** : L'enseignant saisit directement une note unique pour chaque étudiant (ex: `15.00/20`). Il n'y a pas de pondération complexe ni de coefficients artificiels.
- **Validation immédiate** :
  - Si $\text{Note} \ge 10.00$ : le module est **Validé** (Badge vert).
  - Si $\text{Note} < 10.00$ : l'étudiant est orienté en **Session de Rattrapage** (Badge orange), puis déclaré **Non Validé** (Badge rouge) si la note reste inférieure à 10.
- **Moyenne Générale Directe** :
  La moyenne générale de l'étudiant est la moyenne arithmétique directe des notes obtenues dans ses modules :
  $$\text{Moyenne Générale} = \frac{\sum \text{Notes}}{\text{Nombre de Modules}}$$

---

## 🔒 5. La Sécurité Implémentée dans le Projet

Les enseignants adorent poser des questions sur la **sécurité**. Voici les 4 boucliers que nous avons construits :

### 1. Protection contre les Injections SQL (PDO et Requêtes Préparées)
- **Le Danger** : Un pirate injecte du code SQL malveillant dans un formulaire pour voler ou effacer la base de données.
- **Notre Solution** : Nous utilisons systématiquement les requêtes préparées avec des paramètres liés (`prepare()` et `execute([$param])`). Les données saisies par l'utilisateur ne sont jamais concaténées directement dans la chaîne SQL.

### 2. Hachage Sécurisé des Mots de Passe
- **Le Danger** : Si la base de données est dérobée, les pirates voient les mots de passe de tout le monde s'ils sont écrits en clair.
- **Notre Solution** : Les mots de passe sont hachés avec l'algorithme natif de PHP **BCrypt** (`password_hash($mdp, PASSWORD_BCRYPT)`). Même l'administrateur de la base ne peut pas lire le vrai mot de passe. Lors de la connexion, PHP compare l'empreinte avec `password_verify()`.

### 3. Contrôle d'Accès par Rôle (RBAC - Role-Based Access Control)
- **Le Danger** : Un étudiant malin modifie l'URL pour accéder à `/admin/utilisateurs` ou `/enseignant/modules/1/notes`.
- **Notre Solution** : Au tout début de chaque méthode sensible dans les contrôleurs, nous appelons :
  ```php
  Auth::requireRole('ADMIN');      // Dans AdminController
  Auth::requireRole('ENSEIGNANT'); // Dans EnseignantController
  Auth::requireRole('ETUDIANT');   // Dans EtudiantController
  ```
  Si le rôle en session ne correspond pas, l'utilisateur est immédiatement expulsé.

### 4. Protection contre les failles CSRF (Cross-Site Request Forgery)
- **Le Danger** : Un site malveillant force le navigateur d'un administrateur déjà connecté à exécuter une action à son insu (ex: supprimer un étudiant).
- **Notre Solution** : Chaque formulaire de modification ou suppression contient un jeton secret unique :
  ```html
  <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
  ```
  À la réception, le serveur vérifie `Auth::verifyCsrfToken($_POST['csrf_token'])`. Si le jeton ne correspond pas, l'action est rejetée.

---

## 📄 6. La Prouesse Technique : Le Générateur PDF 1.4 Natif

Dans la plupart des projets, les étudiants installent des bibliothèques externes lourdes comme FPDF, TCPDF ou Dompdf via Composer.

Dans notre projet :
- Nous avons créé **`src/Utils/PdfGenerator.php`**.
- Ce fichier écrit **directement les octets de la norme Adobe PDF 1.4** en pur PHP (dictionnaires d'objets `/Type /Page`, flux de données compressés avec `gzcompress`, polices vectorielles standard Helvetica, en-têtes et table de références croisées `xref`).
- **Pourquoi c'est impressionnant ?** Cela permet de générer des relevés de notes officiels avec tableur, métadonnées, filigranes et signatures numériques sans installer aucune dépendance externe !

---

## 🎯 7. L'Anti-Sèche : 15 Questions Types du Professeur & Réponses

Voici exactement ce que le professeur peut vous demander pendant la soutenance et ce qu'il faut lui répondre :

---

#### ❓ Question 1 : Qu'est-ce que le fichier `public/index.php` et à quoi sert-il ?
> **Réponse à donner :**  
> *"C'est le **Front Controller** de notre application. Toutes les requêtes HTTP passent obligatoirement par lui. Il initialise la session utilisateur, configure l'autoloader PSR-4, et confie l'URL au Routeur pour savoir quel contrôleur et quelle action exécuter."*

---

#### ❓ Question 2 : Comment fonctionne votre Routeur (`src/Utils/Router.php`) ?
> **Réponse à donner :**  
> *"Notre Routeur enregistre les routes (GET ou POST) associées à un contrôleur et une méthode. Il utilise des expressions régulières (Regex) pour gérer les paramètres dynamiques dans les URLs, par exemple `/enseignant/modules/{id}/notes` où `{id}` est extrait et passé automatiquement en argument à la méthode du contrôleur."*

---

#### ❓ Question 3 : Qu'est-ce qu'un Autoloader et comment fonctionne le vôtre sans Composer ?
> **Réponse à donner :**  
> *"Un autoloader évite d'écrire des dizaines de `require` ou `include` manuels. Notre fichier `config/autoload.php` utilise la fonction native `spl_autoload_register()`. Dès qu'une classe du namespace `App\` est appelée, l'autoloader remplace le préfixe `App\` par le dossier `src/` et charge le fichier `.php` correspondant à la volée selon le standard PSR-4."*

---

#### ❓ Question 4 : Pourquoi avez-vous utilisé PDO plutôt que MySQLi ?
> **Réponse à donner :**  
> *"PDO (PHP Data Objects) est une interface orientée objet moderne, sécurisée et indépendante du système de gestion de base de données. Il permet d'utiliser des requêtes préparées avec des paramètres nommés et lève des exceptions `PDOException` précises en cas d'erreur SQL."*

---

#### ❓ Question 5 : Comment empêchez-vous les injections SQL dans votre application ?
> **Réponse à donner :**  
> *"En ne concaténant jamais de variables reçues de l'utilisateur directement dans la requête SQL. Nous utilisons systématiquement la méthode `prepare()` suivie de `execute()` en passant les valeurs sous forme de paramètres sécurisés."*

---

#### ❓ Question 6 : Comment gérez-vous la connexion et le maintien de la session ?
> **Réponse à donner :**  
> *"Lorsqu'un utilisateur se connecte, nous vérifions ses identifiants avec `password_verify()`. Si c'est valide, nous stockons ses informations essentielles (ID, nom, email, rôle) dans le tableau superglobal `$_SESSION`. À chaque nouvelle page, la classe `Auth` lit `$_SESSION['user']` pour savoir qui est connecté et quel est son rôle."*

---

#### ❓ Question 7 : Comment fonctionne l'évaluation et le calcul des notes ?
> **Réponse à donner :**  
> *"L'enseignant saisit directement la note sur 20 de chaque étudiant. Il n'y a pas de coefficients. Si la note est supérieure ou égale à 10/20, le module est validé. La moyenne générale de l'étudiant est la moyenne arithmétique directe des notes obtenues dans ses modules."*

---

#### ❓ Question 8 : Qu'est-ce que le journal d'audit (`JournalAudit.php` / `journal_audit_notes`) ?
> **Réponse à donner :**  
> *"C'est une exigence réglementaire dans la gestion des examens : chaque fois qu'une note est modifiée, nous insérons une ligne d'historique enregistrant qui a modifié la note, pour quel étudiant, quel module, l'ancienne note, la nouvelle note et l'horodatage exact."*

---

#### ❓ Question 9 : Comment fonctionne le système de thèmes Clair / Sombre ?
> **Réponse à donner :**  
> *"Nous utilisons des variables CSS (Tokens shadcn Zinc) définies dans `public/css/style.css`. Un script JavaScript léger dans `public/js/theme.js` écoute le clic sur le bouton de thème, modifie l'attribut `data-theme` sur la balise `<html>`, et sauvegarde le choix de l'utilisateur dans le `localStorage` de son navigateur afin que le thème reste mémorisé lors des visites suivantes."*

---

#### ❓ Question 10 : Que se passe-t-il si un utilisateur tente de taper une URL qui n'existe pas ?
> **Réponse à donner :**  
> *"Le Routeur parcourt toutes les routes déclarées. Si aucune ne correspond à la méthode HTTP et à l'URL demandée, il envoie un code HTTP 404 (`http_response_code(404)`) et affiche une page d'erreur 404 soignée, intégrée au design de l'application."*

---

#### ❓ Question 11 : Pourquoi avoir utilisé Docker ?
> **Réponse à donner :**  
> *"Docker nous permet d'isoler l'application dans des conteneurs légers : un conteneur pour le serveur web Nginx, un pour PHP 8.3-FPM et un pour la base de données MySQL 8.4. Cela garantit que le projet tourne exactement de la même manière sur n'importe quel ordinateur, sans problème de version ou de dépendance locale."*

---

#### ❓ Question 12 : Qu'est-ce qu'une classe abstraite dans votre code (comme `Model.php` ou `Utilisateur.php`) ?
> **Réponse à donner :**  
> *"Une classe abstraite est une classe qui ne peut pas être instanciée directement avec `new`. Elle sert de modèle de base pour factoriser le code commun. Par exemple, `Model.php` contient la méthode de connexion à la base de données que tous les modèles enfants (`Filiere`, `Module`, `Note`...) partagent sans avoir à réécrire la connexion."*

---

#### ❓ Question 13 : Pourquoi les mots de passe ne sont-ils jamais enregistrés en clair ?
> **Réponse à donner :**  
> *"Pour des raisons élémentaires de sécurité et de conformité RGPD. En cas de fuite de la base de données, personne ne peut retrouver les mots de passe car `password_hash()` utilise un sel cryptographique aléatoire et une fonction de hachage à sens unique (BCrypt)."*

---

#### ❓ Question 14 : Comment avez-vous créé les icônes sans emojis ?
> **Réponse à donner :**  
> *"Nous avons créé une classe utilitaire `src/Utils/Icon.php` qui génère des balises SVG vectorielles propres de style Lucide. Cela offre un rendu net, professionnel, parfaitement dimensionné et adaptable aux couleurs du thème sombre ou clair."*

---

#### ❓ Question 15 : Qu'avez-vous appris en réalisant ce projet en pur PHP ?
> **Réponse à donner :**  
> *"Nous avons compris comment fonctionnent réellement les frameworks sous le capot : le cycle de vie d'une requête HTTP, la gestion des sessions sans état, le routage d'URLs par expressions régulières, la séparation des responsabilités avec le patron d'architecture MVC, et la sécurisation méthodique d'une application contre les failles courantes du web."*

---

## 💡 Résumé Express pour le Jour J
- **Langage :** PHP 8.3 Vanilla (Pur, 0 framework tiers).
- **Architecture :** MVC (Modèle - Vue - Contrôleur) avec Front Controller (`public/index.php`).
- **Notes :** Note directe sur 20 sans coefficient, seuil de validation à 10/20, moyenne arithmétique simple.
- **Base de données :** MySQL 8.4 géré via PDO en requêtes préparées.
- **Sécurité :** RBAC (Rôles), BCrypt (Mots de passe), jetons anti-CSRF, échappement HTML (`htmlspecialchars`).
- **Design :** shadcn Zinc (Mode Sombre / Mode Clair avec tokens CSS et icônes vectorielles SVG).
- **Rôles :** Admin, Enseignant, Étudiant.
