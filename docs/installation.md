# Installation

- [Installation](#installation)
- [Prérequis](#prerequis)
- [Créer un Nouveau Projet](#creer-un-nouveau-projet)
- [Configuration de l'Environnement](#configuration-de-l-39-environnement)
- [Générer la Clé de l'Application](#generer-la-cle-de-l-39-application)
- [Configuration de la Base de Données](#configuration-de-la-base-de-donnees)
- [Lancer l'Application](#lancer-l-39-application)
- [Configuration Frontend (Optionnel)](#configuration-frontend-optionnel-)
- [Prochaines Étapes](#prochaines-etapes)

---

## Prérequis

Avant d'installer Tonka, assurez-vous que votre système dispose des éléments suivants :

### PHP
```
PHP 8.4 ou supérieur
Extensions PHP requises :
- PDO
- Mbstring
- XML
- JSON
- Ctype
- Tokenizer
- BCMath
- Fileinfo
- OpenSSL
- Tidy
```

### Composer
```
Composer 2.0 ou supérieur
```

### Base de Données
```
MySQL 5.7+ / MariaDB 10.2+
PostgreSQL 10+
SQLite 3.8+
```

### Node.js (Optionnel - pour le frontend)
```
Node.js 16+ 
NPM 8+ / Yarn 1.22+
```

### Serveur Web
```
Apache 2.4+ (mod_rewrite requis)
Nginx 1.18+
PHP Built-in Server (développement)
```

---

## Créer un Nouveau Projet

Pour créer un nouveau projet Tonka, utilisez la commande Composer suivante :

```bash
composer create-project clicalmani/tonka mon-projet
```

Cette commande va :
1. Télécharger la dernière version de Tonka
2. Installer toutes les dépendances PHP
3. Créer la structure de base du projet
4. Générer les fichiers de configuration par défaut

```bash
cd mon-projet
```

---

## Configuration de l'Environnement

### Créer le fichier `.env`

Copiez le fichier d'environnement exemple :

```bash
cp .env.example .env
```

### Configuration de l'Application

Ouvrez le fichier `.env` et ajustez les variables suivantes :

```env
APP_NAME="Mon Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Configuration de l'Environnement de Développement

```env
APP_ENV=local
APP_DEBUG=true
```

### Configuration de l'Environnement de Production

```env
APP_ENV=production
APP_DEBUG=false
```

---

## Générer la Clé de l'Application

La clé de l'application est utilisée pour le chiffrement des sessions, des cookies et des données. Générez-la avec la commande suivante :

```bash
php tonka key:generate
```

Cette commande va :
1. Générer une clé aléatoire de 32 caractères
2. Mettre à jour la variable `APP_KEY` dans votre fichier `.env`

**Exemple de sortie :**
```
App key set
Key: base64:abcdefghijklmnopqrstuvwxyz1234567890=
```

---

## Configuration de la Base de Données

### Configuration MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=tonka
DB_USER=root
DB_PASSWORD=your_password
DB_TABLE_PREFIX=
```

### Configuration PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=tonka
DB_USER=postgres
DB_PASSWORD=your_password
DB_TABLE_PREFIX=
```

### Configuration SQLite

```env
DB_CONNECTION=sqlite
DB_DATABASE=/chemin/vers/database.sqlite
```

### Créer la Base de Données

Pour MySQL/PostgreSQL, créez la base de données manuellement :

```sql
CREATE DATABASE tonka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Tester la Connexion

```bash
php tonka migrate:fresh
```

---

## Lancer l'Application

### Serveur de Développement (PHP Built-in)

```bash
php tonka dev
```

**Options disponibles :**

| Option | Description | Défaut |
|--------|-------------|--------|
| `--port` | Port du serveur | `8000` |
| `--host` | Hôte du serveur | `localhost` |

**Exemples :**

```bash
# Port personnalisé
php tonka dev --port=8080

# Hôte personnalisé
php tonka dev --host=0.0.0.0

# Port et hôte
php tonka dev --port=8080 --host=0.0.0.0
```

### Serveur Web (Apache)

#### Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName tonka.local
    DocumentRoot /chemin/vers/mon-projet/public
    
    <Directory /chemin/vers/mon-projet/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Fichier `.htaccess` (inclus dans le projet)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

### Serveur Web (Nginx)

```nginx
server {
    listen 80;
    server_name tonka.local;
    root /chemin/vers/mon-projet/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Accès à l'Application

Une fois le serveur démarré, ouvrez votre navigateur :

```
http://localhost:8000
```

Vous devriez voir la page d'accueil par défaut de Tonka.

---

## Configuration Frontend (Optionnel)

### Configuration de l'Environnement Frontend

```env
VITE_APP_URL=http://localhost:5173
VITE_MERCURE_URL=http://localhost:3000/.well-known/mercure
```

### Installation des Dépendances JavaScript

```bash
npm install
```

### Développement Frontend

```bash
npm run dev
```

### Build de Production

```bash
npm run build
```

---

## Installation des Dépendances JavaScript

### Dépendances Par Défaut

```bash
npm install
```

### Dépendances Recommandées

```bash
# Pour Inertia.js
npm install @inertiajs/react

# Pour le Broadcasting
npm install tonka-broadcaster

# Pour le Routing Frontend
npm install tonka-router

# Pour DriftQL
npm install driftql-react

# Pour Vite
npm install -D vite @vitejs/plugin-react
```

---

## Prochaines Étapes

Félicitations ! Tonka est maintenant installé et configuré. Voici quelques suggestions pour continuer :

1. **[Découvrir la Structure du Projet](structure.md)** → Comprendre l'organisation des fichiers
2. **[Créer votre Première Route](routing.md)** → Commencer à développer
3. **[Créer un Contrôleur](controllers.md)** → Ajouter de la logique métier
4. **[Travailler avec la Base de Données](database.md)** → Configurer les modèles

---

**Installation terminée !** 🚀 → [Structure du Projet](structure.md)