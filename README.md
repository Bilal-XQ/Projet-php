# Application de Gestion Pédagogique 🎓
> Département Mathématiques & Informatique — Université Sidi Mohamed Ben Abdellah

Application web complète de gestion des notes, des modules, des filières et du suivi d'assiduité, construite en **PHP 8.3 natif (Vanilla MVC)**, **MySQL 8.0**, et **Docker**.

---

## 🚀 Comment lancer le projet sur votre machine

### Étape 1 : Télécharger et installer Docker
Si vous n'avez pas encore Docker sur votre ordinateur, téléchargez et installez **Docker Desktop** :
👉 **[Lien direct de téléchargement Docker Desktop](https://www.docker.com/products/docker-desktop/)**

*(Assurez-vous que Docker Desktop est bien lancé avant de passer à l'étape suivante).*

---

### Étape 2 : Cloner le projet
Ouvrez votre terminal (PowerShell, Git Bash ou CMD) et tapez :

```bash
git clone https://github.com/Bilal-XQ/Projet-php.git
cd Projet-php
```

---

### Étape 3 : Démarrer l'application
Dans le dossier du projet, exécutez simplement la commande suivante :

```bash
docker compose up -d
```
*(ou `docker-compose up -d` selon votre version de Docker)*

Docker va automatiquement :
1. Télécharger et configurer l'environnement PHP 8.3 & Nginx.
2. Initialiser la base de données MySQL avec les tables et les données de test.

---

### Étape 4 : Ouvrir l'application
Ouvrez votre navigateur web et rendez-vous sur :
👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🔑 Comptes de test pré-remplis

Sur la page de connexion, **vous pouvez cliquer directement sur les boutons pré-remplis**, ou saisir ces identifiants :

| Rôle | Email | Mot de passe | Description |
| :--- | :--- | :--- | :--- |
| **Administrateur** | `admin@departement.ma` | `admin123` | Gestion filières, modules, inscriptions & utilisateurs |
| **Enseignant** | `h.elbahi@departement.ma` | `teacher123` | Saisie des notes directes sur 20, registre d'appel |
| **Étudiant** | `b.elazzam@etudiant.ma` | `student123` | Consultation relevé de notes, bilan assiduité & PDF |

---

## 🛑 Comment arrêter le projet

Quand vous avez terminé, exécutez dans le terminal :

```bash
docker compose down
```

---

## 📚 Documentation Technique
Pour comprendre le fonctionnement interne du code PHP, l'architecture MVC et les dossiers, consultez le guide explicatif complet :
* 📄 **[docs/EXPLICATION_PROJET.md](docs/EXPLICATION_PROJET.md)**
