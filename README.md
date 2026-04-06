# TP4 — Doctrine CRUD (Symfony 7)

Application Symfony 7 avec **CRUD complet** sur l’entité `Article` (Doctrine ORM), **Bootstrap 5** et **formulaires Symfony**.

Structure du projet (dossier racine équivalent à `TP4_Doctrine_CRUD_symfony_7/`) :

```
TP4_Doctrine_CRUD_symfony_7/
├── config/packages/twig.yaml
├── src/Controller/IndexController.php
├── src/Entity/Article.php
├── src/Repository/ArticleRepository.php
├── src/Form/ArticleType.php
├── templates/base.html.twig
├── templates/inc/navbar.html.twig
└── templates/articles/
    ├── index.html.twig
    ├── show.html.twig
    ├── new.html.twig
    └── edit.html.twig
└── README.md
```

## Prérequis

- PHP **8.2+**, [Composer](https://getcomposer.org/)
- MySQL/MariaDB (ou SQLite pour un test rapide via `.env`)

## Installation des packages

```bash
composer require doctrine/orm doctrine/doctrine-bundle doctrine/doctrine-migrations-bundle symfony/form symfony/validator symfony/maker-bundle
```

## Configuration `DATABASE_URL`

Définissez `DATABASE_URL` dans **`.env.local`** (recommandé) ou `.env`.

**Exemple Docker (MariaDB, service `symfony-mysql` sur le réseau Docker) :**

```env
DATABASE_URL="mysql://root:secret@symfony-mysql:3306/symfony_db?serverVersion=mariadb-10.4.11&charset=utf8mb4"
```

**Exemple Docker — PHP sur la machine hôte, MySQL exposé sur le port 3306 :**

```env
DATABASE_URL="mysql://root:secret@127.0.0.1:3306/symfony_db?serverVersion=mariadb-10.4.11&charset=utf8mb4"
```

**Exemple MySQL local :**

```env
DATABASE_URL="mysql://root:password@127.0.0.1:3306/symfony_db?serverVersion=8.0&charset=utf8mb4"
```

Le dépôt peut utiliser **SQLite** par défaut dans `.env` pour démarrer sans serveur SQL (voir fichier `.env`).

## Base de données et migrations

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

> Avec **SQLite**, `doctrine:database:create` peut être inadapté ; dans ce cas, après configuration de l’URL, exécutez `make:migration` puis `doctrine:migrations:migrate`, ou en développement : `php bin/console doctrine:schema:update --force`.

## Routes disponibles

| Méthode | URL | Description |
|--------|-----|-------------|
| `GET` | `/` | Liste des articles |
| `GET` / `POST` | `/article/new` | Création |
| `GET` | `/article/{id}` | Affichage |
| `GET` / `POST` | `/article/edit/{id}` | Modification |
| `DELETE` | `/article/delete/{id}` | Suppression (formulaire POST + `_method=DELETE` + jeton CSRF) |

Lister les routes en console :

```bash
php bin/console debug:router
```

## Démarrer le serveur

```bash
symfony server:start
```

Sans la CLI Symfony :

```bash
php -S 127.0.0.1:8000 -t public
```

Puis ouvrir `http://127.0.0.1:8000/`.

## Remarques techniques

- Le contrôleur principal est **`IndexController`** : injection de **`EntityManagerInterface`** dans le constructeur (pas d’appel à `getDoctrine()`).
- Les formulaires utilisent le thème global **`bootstrap_5_layout.html.twig`** (voir `config/packages/twig.yaml`).
- La suppression utilise la méthode **HTTP DELETE** avec `http_method_override: true` et un champ `_method` dans le formulaire POST.
