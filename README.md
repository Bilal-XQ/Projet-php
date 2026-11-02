# Application de Gestion Pédagogique
> Département Mathématiques & Informatique — Université Sidi Mohamed Ben Abdellah

Application web de gestion des notes, des modules, des filières, du suivi d'assiduité et des réclamations, développée en PHP 8.3 (Architecture MVC), Composer, MySQL 8.4 et Docker.

---

## 1. Prérequis
Installez Docker Desktop sur votre machine si ce n'est pas encore fait :
* [Lien de téléchargement Docker Desktop](https://www.docker.com/products/docker-desktop/)

Lancez Docker Desktop avant d'exécuter les commandes ci-dessous.

---

## 2. Cas 1 : Première installation du projet

Ouvrez un terminal dans le dossier où vous souhaitez placer le projet et exécutez :

```bash
# 1. Cloner le dépôt
git clone https://github.com/Bilal-XQ/Projet-php.git
cd Projet-php

# 2. Démarrer les conteneurs Docker (PHP, Nginx, MySQL)
docker compose up -d

# 3. Installer les dépendances Composer dans le conteneur
docker compose exec app composer install
```

Ouvrez ensuite votre navigateur sur : **http://localhost:8080**

---

## 3. Cas 2 : Vous avez déjà le projet et vous devez récupérer les mises à jour

Si de nouveaux commits ou des modifications de la base de données ont été publiés sur `main`, exécutez ces 3 commandes dans votre terminal :

```bash
# 1. Récupérer les nouveaux fichiers
git pull origin main

# 2. Recharger la base de données complète (le flag -v supprime l'ancien cache local pour charger le nouveau schema.sql)
docker compose down -v
docker compose up -d

# 3. Mettre à jour les dépendances Composer
docker compose exec app composer install
```

---

## 4. Cas 3 : Vous voulez modifier ou ajouter du code (Pull Request)

Ne travaillez jamais directement sur la branche `main`. Vous devez créer une branche dédiée, apporter vos changements, puis ouvrir une Pull Request pour relecture et fusion.

### Prompt prêt à copier pour votre assistant IA :

Copiez et collez le texte suivant à votre IA (ChatGPT, Claude, Cursor, Gemini...) :

```text
Je travaille sur le projet PHP situé dans ce répertoire (architecture MVC, PHP 8.3, Composer, Docker).
Le dépôt Git distant est : https://github.com/Bilal-XQ/Projet-php.git
La branche principale est "main".

Consignes strictes :
1. Assure-toi que la branche locale est à jour avec origin/main :
   git checkout main
   git pull origin main
2. Crée une nouvelle branche descriptive pour ma tâche :
   git checkout -b feature/nom-de-ma-modification
3. Applique les modifications demandées en respectant l'architecture existante (sans framework lourd, PHP 8.3, styles shadcn Zinc existants).
4. Vérifie que le code fonctionne et qu'aucune régression n'est introduite.
5. Commite les changements avec un message clair :
   git add -A
   git commit -m "Description claire de ce qui a été fait"
6. Pousse la nouvelle branche vers GitHub :
   git push -u origin feature/nom-de-ma-modification
7. Donne-moi le lien direct pour ouvrir la Pull Request sur GitHub vers la branche main de Bilal.
```

---

## 5. Comptes de test pré-remplis

Vous pouvez cliquer directement sur les boutons de remplissage rapide sur la page de connexion, ou utiliser les identifiants suivants :

| Rôle | Email | Mot de passe | Espace accessible |
| :--- | :--- | :--- | :--- |
| **Administrateur** | `admin@departement.ma` | `admin123` | Gestion filières, modules, inscriptions, audit et réclamations |
| **Enseignant** | `h.elbahi@departement.ma` | `teacher123` | Évaluation des notes, feuilles d'émargement et réponses aux réclamations |
| **Étudiant** | `b.elazzam@etudiant.ma` | `student123` | Consultation des notes, relevé PDF, assiduité et dépôt de réclamations |

---

## 6. Arrêter les services

Pour éteindre les conteneurs sans perdre les données :

```bash
docker compose down
```
