# Déploiement

Cette section décrit les prérequis, les bonnes pratiques et les étapes nécessaires pour déployer une application Tonka en environnement de production, ainsi que des exemples de configuration pour les serveurs web Apache et Nginx.

- [Déploiement](#deploiement)
- [Introduction](#introduction)
- [Prérequis Serveur](#prerequis-serveur)
  - [Version PHP](#version-php)
  - [Extensions PHP](#extensions-php)
- [Permissions des Dossiers](#permissions-des-dossiers)
- [Optimisation](#optimisation)
  - [Autoloader Composer](#autoloader-composer)
  - [Session](#session)
- [Vérification Finale](#verification-finale)
- [Serveur Web (Apache)](#serveur-web-apache)
- [Serveur Web (Nginx)](#serveur-web-nginx)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le déploiement d'une application **Tonka** est délibérément simple. Contrairement à de nombreux frameworks PHP, Tonka ne repose pas sur un système de cache de configuration ou de routes persisté sur disque : les données mises en cache sont gérées en mémoire et purgées automatiquement par le garbage collector. En pratique, cela supprime toute étape de compilation ou d'invalidation manuelle du cache lors d'une mise en production, et réduit d'autant les risques d'erreurs liées à un cache obsolète.

---

## Prérequis Serveur

Tonka impose des exigences minimales, volontairement restreintes afin de simplifier son adoption. Assurez-vous que l'environnement cible satisfait les critères suivants avant tout déploiement.

### Version PHP

Le serveur doit exécuter **PHP 8.4 ou une version supérieure**.

```bash
php -v
# PHP 8.4+ ou supérieur
```

### Extensions PHP

Les extensions suivantes doivent être activées :

| Extension | Description |
|-----------|-------------|
| `pdo` | Interface d'accès à la base de données |
| `mbstring` | Gestion des chaînes de caractères multi-octets |
| `json` | Encodage et décodage JSON |
| `openssl` | Chiffrement et fonctions de sécurité |
| `curl` | Émission de requêtes HTTP sortantes |
| `xml` | Traitement de documents XML |
| `tidy` | Nettoyage et normalisation de HTML/XML |

**Vérification des extensions installées :**

```bash
php -m | grep -E 'pdo|mbstring|json|openssl|curl|xml|tidy'
```

**Installation des extensions (Ubuntu/Debian) :**

```bash
sudo apt-get update
sudo apt-get install php8.4-cli php8.4-common \
    php8.4-pdo php8.4-mysql php8.4-pgsql \
    php8.4-mbstring php8.4-curl php8.4-xml \
    php8.4-json php8.4-openssl php8.4-tidy
```

!> **Note :** adaptez le numéro de version PHP des paquets (`php8.4-*`) à la version réellement installée sur le serveur cible.

---

## Permissions des Dossiers

Tonka doit disposer de droits d'écriture sur le dossier `storage`, utilisé pour la persistance des fichiers utilisateurs, des sessions, des vues compilées et des journaux applicatifs. Assurez-vous que le processus du serveur web (Apache, Nginx/PHP-FPM) dispose des permissions adéquates sur ce répertoire.

### Permissions recommandées

```bash
# Définir le propriétaire
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/framework

# Accorder les droits d'écriture
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/framework
```

### Vérification des permissions

```bash
# Vérifier les permissions appliquées
ls -la storage/
# drwxrwxr-x 5 www-data www-data 4096 ...
```

> **Bonne pratique :** évitez d'accorder des permissions `777` en production ; privilégiez l'attribution explicite de la propriété au groupe du processus serveur (`775`), comme illustré ci-dessus.

---

## Optimisation

L'absence de cache de configuration ou de routes chez Tonka simplifie considérablement la phase d'optimisation en production : il n'y a, par construction, aucun fichier de cache à générer ni à vider. La seule optimisation pertinente concerne l'autoloader de Composer.

### Autoloader Composer

Optimisez l'autoloader de Composer avant toute mise en production :

```bash
composer install --no-dev -o
```

**Options utilisées :**

| Option | Description |
|--------|-------------|
| `--no-dev` | Exclut les dépendances de développement de l'installation |
| `-o` | Génère un autoloader optimisé (classmap), réduisant le temps de résolution des classes |

### Session

En cas de besoin, le dossier de stockage des sessions peut être purgé manuellement :

```bash
php tonka session:clear
```

---

## Vérification Finale

Avant de considérer le déploiement comme terminé, effectuez les vérifications suivantes.

### 1. Exécuter les migrations

```bash
php tonka migrate:fresh --manifest=manifest
```

!> **Attention :** la commande `migrate:fresh` réinitialise le schéma de la base de données. Réservez son usage à un environnement contrôlé et assurez-vous de disposer d'une sauvegarde avant toute exécution en production.

### 2. Démarrer l'application

```bash
# Avec le serveur PHP intégré (développement uniquement)
php tonka dev

# En production, configurer un virtual host pointant vers le dossier public/
```

---

## Serveur Web (Apache)

### Virtual Host

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

### `.htaccess`

Le fichier `.htaccess` situé dans `public/` réécrit l'ensemble des requêtes entrantes vers `index.php`, point d'entrée unique de l'application :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

---

## Serveur Web (Nginx)

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
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

!> **Note :** adaptez le chemin du socket PHP-FPM (`php8.4-fpm.sock`) à la version de PHP effectivement installée sur le serveur.

---

## Prochaines Étapes

Votre application Tonka est désormais déployée. Pour aller plus loin :

1. **[Introduction](introduction.md)** → Retour à la page d'accueil de la documentation
2. **[Architecture](architecture.md)** → Comprendre le fonctionnement interne du framework
3. **[Routing](routing.md)** → Définir et organiser vos routes

---

**Déploiement terminé !** 🚀 → [Introduction](introduction.md)