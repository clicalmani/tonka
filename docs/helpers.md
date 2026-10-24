# Helpers

- [Helpers](#helpers)
- [Introduction](#introduction)
- [Application Helpers](#application-helpers)
- [Database Helpers](#database-helpers)
- [HTTP Helpers](#http-helpers)
- [String Helpers](#string-helpers)
- [Collection Helpers](#collection-helpers)
- [Authentication Helpers](#authentication-helpers)
- [File Helpers](#file-helpers)
- [Security Helpers](#security-helpers)
- [Miscellaneous Helpers](#miscellaneous-helpers)
- [Prochaines Étapes](#prochaines-étapes)

---

## Introduction

Tonka fournit de nombreux helpers pour simplifier votre code. Ces fonctions globales sont disponibles partout dans votre application et vous permettent d'écrire un code plus concis et expressif.

---

## Application Helpers

### `app()`

Retourne l'instance de l'application.

```php
$app = app();
$rootPath = app()->rootPath();
$config = app()->config('app.name');
```

### `config()`

Récupère une valeur de configuration.

```php
// Récupération simple
$name = config('app.name');
$debug = config('app.debug', false);

// Récupération imbriquée (dot notation)
$mailDriver = config('database.connections.mysql.charset', 'utf8mb4');
```

### `env()`

Récupère une variable d'environnement.

```php
$env = env('APP_ENV', 'local');
$debug = env('APP_DEBUG', false);
$key = env('APP_KEY');
```

### `root_path()`

Retourne le chemin racine du projet.

```php
$root = root_path();
$path = root_path('storage/logs/app.log');
```

### `app_path()`

Retourne le chemin du dossier `app`.

```php
$path = app_path();
$controller = app_path('Http/Controllers/UserController.php');
```

### `public_path()`

Retourne le chemin du dossier `public`.

```php
$path = public_path();
$asset = public_path('build/manifest.json');
```

### `storage_path()`

Retourne le chemin du dossier `storage`.

```php
$path = storage_path();
$log = storage_path('logs/app.log');
```

### `config_path()`

Retourne le chemin du dossier `config`.

```php
$path = config_path();
$config = config_path('app.php');
```

### `database_path()`

Retourne le chemin du dossier `database`.

```php
$path = database_path();
$migration = database_path('manifests/manifest.xml');
```

---

## Database Helpers

### `DB`

Facade pour le Query Builder (voir [Query Builder](query-builder.md)).

```php
$users = DB::table('users')->get();
$user = DB::table('users')->where('id = ?', [1])->first();
```

### `db()`

Équivalent de la façade `DB`, sous forme de helper.

```php
$users = db()->table('users')->get();
```

---

## HTTP Helpers

### `request()`

Récupère la requête courante.

```php
$request = request();
$name = request()->input('name');
$all = request()->all();
```

### `response()`

Retourne une instance de réponse.

```php
// Réponse JSON
return response()->json(['data' => $users]);

// Réponse de succès
return response()->success(['user' => $user]);

// Réponse d'erreur
return response()->error('User not found');

// Réponse avec statut
return response()->status(404)->json(['error' => 'Not found']);
```

### `redirect()`

Effectue une redirection.

```php
return redirect('/users');
return redirect('/users')->with('success', 'User created!');
return redirect()->route('users.show', ['id' => 123]);
```

### `back()`

Retourne à la page précédente.

```php
return back();
return back()->withInput();
```

### `view()`

Rend une vue Twig.

```php
return view('users.index', ['users' => $users]);
```

### `inertia()`

Rend une vue Inertia.

```php
return inertia('User/Index', ['users' => $users]);
```

---

## String Helpers

### `slugify()`

Slugifie une chaîne.

```php
$slug = slugify('Hello World!'); // hello-world
$slug = slugify('Mon Article'); // mon-article
```

### `sanitize_attribute()`

Nettoie un nom d'attribut.

```php
$attr = sanitize_attribute('user_id'); // user_id
```

### `class_basename()`

Retourne le nom de la classe sans namespace.

```php
$name = class_basename(User::class); // User
$name = class_basename($user); // User
```

---

## Collection Helpers

### `collection()`

Crée une collection.

```php
$coll = collection([1, 2, 3]);
$filtered = $coll->filter(fn($item) => $item > 1);
$mapped = $coll->map(fn($item) => $item * 2);
```

### `collect()`

Alias de `collection()`.

```php
$coll = collect([1, 2, 3]);
```

---

## Authentication Helpers

### `auth()`

Récupère l'utilisateur authentifié (voir [Authentification](authentication.md#auth)).

```php
$user = auth();
$id = auth()->id();
$name = auth()->name;
```

### `token()`

Génère un token JWT.

```php
$token = token($user->id, 3600);
```

### `verify_token()`

Vérifie un token JWT.

```php
$payload = verify_token($token);
$userId = $payload->jti;
```

### `bearerToken()`

Récupère le token du header `Authorization`.

```php
$token = bearerToken();
```

---

## File Helpers

### `storage()`

Le helper `storage()` donne accès à l'abstraction du système de fichiers de Tonka. Il  vous permettre d'interagir de manière identique avec des fichiers locaux, des buckets AWS S3, Google Cloud Storage ou un serveur SFTP.

```php
// Accès au disque par défaut (configuré dans config/filesystems.php)
storage()->put('file.txt', 'Contenu');

// Ciblage explicite d'un disque ('public', 's3', 'local', etc.)
storage('s3')->put('uploads/avatar.png', $binaryData);

```

#### `get()`

Récupère l'intégralité du contenu d'un fichier sous forme de chaîne de caractères (`string`).

Contrairement à `file_get_contents()`, cette méthode recherche le fichier dans le disque ciblé, gère les identifiants d'accès réseau (S3/Cloud) et lève une exception `UnableToReadFile` si le fichier n'existe pas ou ne peut pas être lu.

```php
// Lecture simple
$content = storage()->get('logs/app.log');

// Sur un disque spécifique
$privateDocument = storage('private')->get('contracts/2026-001.pdf');

```

> **Note :** À réserver aux fichiers de taille petite à moyenne (fichiers texte, JSON, configurations). Pour les gros fichiers, privilégiez [`readStream()`](#readstream).

#### `put()`

Crée un nouveau fichier ou **écrase entièrement** un fichier existant à l'emplacement indiqué. Il crée automatiquement les sous-dossiers manquants dans le chemin.

Le paramètre `$content` accepte une chaîne (`string`) ou un flux PHP (`resource`). Le tableau d'options permet de spécifier des métadonnées (comme la visibilité).

```php
// 1. Écriture à partir d'une chaîne
storage()->put('settings/theme.json', json_encode(['theme' => 'dark']));

// 2. Écriture à partir d'un flux (Upload de fichier volumineux sans surcharger la RAM)
$stream = fopen($_FILES['avatar']['tmp_name'], 'rb');
storage('s3')->put('avatars/user_12.jpg', $stream, ['visibility' => 'public']);

if (is_resource($stream)) {
    fclose($stream);
}

```

#### `readStream()`

Ouvre un fichier et retourne un flux de lecture PHP (`resource stream`).

C'est la méthode recommandée pour lire ou transférer des **fichiers volumineux** (vidéos, archives ZIP, gros PDF, sauvegardes), car elle consomme une quantité minime de mémoire RAM en ne liant que de petits tampons (*chunks*) de données à la fois.

```php
// Exemple : Copier un fichier vidéo du stockage vers le disque local sans saturer la mémoire
$sourceStream = storage('s3')->readStream('videos/tutoriel_hd.mp4');
$destinationStream = fopen(storage_path('exports/video.mp4'), 'wb');

// Transfert efficace de flux à flux
stream_copy_to_stream($sourceStream, $destinationStream);

// Toujours fermer les flux après utilisation
fclose($sourceStream);
fclose($destinationStream);

```

#### `json()`

Lit un fichier `.json` et le décode directement en tableau associatif ou en objet PHP.

```php
// Signature : json(string $filename, bool $assoc = false, int $depth = 512, int $flag = 0)

// Décodage en tableau associatif ($assoc = true)
$config = storage()->json('config/settings.json', true);
echo $config['app_name'];

// Décodage en objet (par défaut)
$user = storage()->json('data/user.json');
echo $user->name;

```

#### `prepend()`

Ajoute du contenu **au début** d'un fichier. Si le fichier n'existe pas encore, il est créé automatiquement.

```php
// Utile pour ajouter un en-tête CSV ou un log prioritaire
storage()->prepend('export/users.csv', "ID,Nom,Email\n");

```

#### `append()`

Ajoute du contenu **à la fin** d'un fichier (concaténation). Si le fichier n'existe pas encore, il est créé automatiquement.

```php
// Utile pour écrire dans des fichiers journaux (logs)
$logLine = "[" . date('Y-m-d H:i:s') . "] Nouvel utilisateur inscrit.\n";
storage()->append('logs/activity.log', $logLine);

```

### `copy()`

Duplique un fichier vers un nouveau chemin **sur le même disque**.

```php
// Duplique une image modèle vers un nouveau dossier
storage('public')->copy('templates/default_avatar.png', 'avatars/user_99.png');

```

#### `move()`

Déplace ou renomme un fichier vers une nouvelle destination **sur le même disque**.

```php
// Renomme ou déplace un fichier temporaire vers son emplacement définitif
storage()->move('tmp/upload_123.tmp', 'documents/facture_2026.pdf');

```

#### `store()`

Alias de la méthode `move()`. Conçu pour enregistrer un fichier temporaire traité vers sa destination finale d'archivage.

```php
storage()->store('/tmp/processed_video.mp4', 'videos/final_movie.mp4');

```

#### `delete()`

Supprime un ou plusieurs fichiers du disque.

```php
// Suppression d'un seul fichier
storage()->delete('cache/home.html');

// Suppression de plusieurs fichiers à la fois via un tableau
storage()->delete([
    'cache/home.html',
    'cache/about.html',
    'temp/old_report.pdf'
]);

```

#### `deleteDirectory()`

Supprime un répertoire complet ainsi que **tout son contenu** (fichiers et sous-dossiers) de manière récursive.

```php
// Supprime le dossier 'temp/exports' et tout ce qu'il contient
storage()->deleteDirectory('temp/exports');

```

#### `cleanDirectory()`

Supprime tous les fichiers et sous-dossiers contenus dans un répertoire, **sans supprimer le répertoire parent lui-même**.

```php
// Vide le dossier de cache mais conserve le dossier 'cache/'
storage()->cleanDirectory('storage/cache');

```

#### `makeDirectory()`

Crée un nouveau répertoire. Si des sous-dossiers parents n'existent pas, ils sont créés automatiquement.

```php
storage('public')->makeDirectory('uploads/photos/2026/10');

```

#### `files()`

Retourne un tableau contenant les chemins relatifs de **tous les fichiers** situés à la racine d'un répertoire donné. Par défaut, n'explore pas les sous-dossiers.

```php
// Récupère uniquement les fichiers à la racine du dossier 'documents'
$files = storage()->files('documents');
// Résultat : ['documents/doc1.pdf', 'documents/notes.txt']

// Passez le second paramètre à true pour une recherche récursive
$allFiles = storage()->files('documents', true);

```

#### `allFiles()`

Raccourci de `files()`. Récupère la liste de **tous les fichiers** présents dans un répertoire et dans tous ses sous-dossiers (exploration récursive).

```php
$allPhotos = storage('public')->allFiles('gallery');
// Résultat : ['gallery/2025/pic1.jpg', 'gallery/2026/vacances/pic2.jpg', ...]

```

#### `directories()`

Retourne un tableau des chemins relatifs de tous les **sous-répertoires** situés directement dans un dossier donné.

```php
$subFolders = storage()->directories('uploads');
// Résultat : ['uploads/avatars', 'uploads/documents']

```

#### `allDirectories()`

Récupère la liste de **tous les sous-répertoires** de manière récursive.

```php
$allFolders = storage()->allDirectories('uploads');
// Résultat : ['uploads/avatars', 'uploads/avatars/thumbs', 'uploads/documents']

```

#### `fileExists()`

Vérifie l'existence d'un fichier ou d'un répertoire sur le disque ciblé. Renvoie `true` si l'élément existe, `false` sinon.

```php
if (storage('s3')->fileExists('backups/db_latest.sql')) {
    // Le fichier existe sur S3
}

```

#### `missing()`

Méthode inverse de `fileExists()`. Vérifie si un fichier ou un dossier **n'existe pas**.

```php
if (storage()->missing('avatars/user_1.png')) {
    // Utiliser l'image par défaut si l'avatar est absent
}

```

#### `size()`

Retourne la taille d'un fichier exprimée en **octets** (*bytes*).

```php
$bytes = storage('s3')->size('videos/tuto.mp4');
$megaBytes = round($bytes / 1024 / 1024, 2);

echo "Taille du fichier : {$megaBytes} Mo";

```

#### `mimeType()`

Détecte et retourne le type MIME officiel d'un fichier (ex: `image/jpeg`, `application/pdf`).

```php
$mime = storage()->mimeType('uploads/document.pdf'); 
// Retourne : "application/pdf"

```

#### `lastModified()`

Retourne la date de dernière modification d'un fichier sous la forme d'un **Timestamp UNIX**.

```php
$timestamp = storage()->lastModified('reports/annual.pdf');

// Formatage de la date avec la fonction date()
$formattedDate = date('d/m/Y H:i:s', $timestamp);

```

#### `visibility()`

Récupère le niveau de visibilité actuel d'un fichier ou dossier. Retourne `'public'` ou `'private'`.

```php
$visibility = storage()->visibility('documents/contrat.pdf'); 
// Retourne : "private" ou "public"

```

#### `setVisibility()`

Modifie le niveau de visibilité d'un fichier ou d'un répertoire.

* **Sur un stockage Local** : Traduit la visibilité en permissions système (ex: `0644` pour public, `0600` pour privé).
* **Sur AWS S3 / Cloud Storage** : Modifie les règles d'accès ACL (ex: `public-read` ou `private`).

```php
// Rendre un fichier privé accessible publiquement
storage()->setVisibility('uploads/avatar.png', 'public');

// Passer un fichier en privé (via chaîne brute)
storage('s3')->setVisibility('exports/data.csv', 'private');

```

#### `symlink()`

Crée un lien symbolique (*symlink*) sur le système de fichiers local.

Cette méthode est très utile pour lier le répertoire de stockage privé (ex: `storage/app/public`) à un dossier accessible publiquement par le serveur web (ex: `public/storage`).

```php
// Création d'un lien symbolique
$target = storage_path('app/public');
$link   = public_path('storage');

storage()->symlink($target, $link);

```

#### `download()`

Génère une réponse HTTP de téléchargement forcé (`Content-Disposition: attachment`). Le fichier est transmis directement au navigateur.

```php
// Téléchargement simple
return storage()->download('factures/2026-001.pdf');

// Téléchargement avec un nom de fichier personnalisé pour l'utilisateur
return storage()->download('factures/2026-001.pdf', 'Votre_Facture_Tonka.pdf');

```

#### `url()`

Génère l'URL publique d'un fichier à partir de la propriété `url` configurée pour le disque dans `config/filesystems.php`.

```php
// Si la config du disque 'public' définit 'url' => 'https://cdn.example.com/storage'
$url = storage('public')->url('avatars/user.jpg');

// Résultat : "https://cdn.example.com/storage/avatars/user.jpg"
```

---

## Security Helpers

### `enc()`

Fournit une instance du service de chiffrement de Tonka (`EncryptionManager`) pour sécuriser les données sensibles au repos. Utilise un algorithme AES (ex: AES-256-CBC ou AES-256-GCM) combiné à une clé secrète configurée via `APP_KEY`.

```php
// Chiffrement d'une chaîne ou d'une structure de données
$encrypted = enc()->encrypt('Donnée bancaire sensible');

// Déchiffrement
$decrypted = enc()->decrypt($encrypted);

```

### `password()`

Génère un hachage sécurisé d'un mot de passe à l'aide de l'algorithme recommandé par la configuration de sécurité `config/hashing.php` (ex: **BCrypt** ou **Argon2id**).

```php
// Hachage d'un mot de passe brut avant sauvegarde en BDD
$hashedPassword = password('MonMotDePasseSécurisé123!');

// Vérification de la correspondance (méthode de vérification interne)
if (password_verify('MonMotDePasseSécurisé123!', $hashedPassword)) {
    // Mot de passe valide
}

```

### `csrf_token()`

Récupère ou génère le jeton CSRF (*Cross-Site Request Forgery*) associé à la session active de l'utilisateur.

```php
// Exemple dans une vue HTML / Twig brute
echo '<input type="hidden" name="_token" value="' . csrf_token() . '">';

// Utilisation pour un appel AJAX via Fetch/Axios
$headers = [
    'X-CSRF-TOKEN' => csrf_token()
];

```

### `create_parameters_hash()`

Génère une signature cryptographique (HMAC) basée sur un tableau de paramètres de route. Utilisée par le middleware `PreventRouteTampering` pour empêcher la modification non autorisée de paramètres dans une URL signée.

```php
$params = ['user_id' => 42, 'action' => 'unsubscribe'];

// Génère le hash unique lié à la clé d'application
$hash = create_parameters_hash($params);

// L'URL peut être construite avec la signature
$signedUrl = "/user/unsubscribe?user_id=42&hash={$hash}";

```

---

## Miscellaneous Helpers

### `now()`

Retourne une instance orientée objet de la date et heure actuelles (basée sur `DateTimeImmutable` ou une extension type `Carbon`).

```php
// Date courante
$currentDate = now();

// Formatage rapide
echo now()->format('Y-m-d H:i:s'); // "2026-10-06 13:00:00"

// Opérations de dates (si supporté par la classe interne)
$nextWeek = now()->addDays(7);

```

### `logger()`

Accède au service de journalisation (PSR-3 / Monolog) pour enregistrer des événements, avertissements ou erreurs système.

```php
// Écriture directe via méthodes de niveau
logger()->info("L'utilisateur ID #12 a changé son adresse email.");

// Capture d'erreurs avec contexte
try {
    // traitement...
} catch (\Throwable $e) {
    logger()->error("Échec du paiement", [
        'user_id' => 12,
        'message' => $e->getMessage(),
        'trace'   => $e->getTraceAsString()
    ]);
}

```

### `event()`

Déclenche un événement système et exécute tous les écouteurs (*listeners*) enregistrés dans l'application.

```php
// Instanciation et déclenchement
$event = new \App\Events\UserRegistered($user);

event($event);

```

### `mailer()`

Retourne une instance du service d'envoi d'emails (`MailerManager`). Permet de cibler un transport spécifique (SMTP, Sendgrid, Mailgun) ou d'utiliser le transport par défaut.

```php
// Utilisation du transport par défaut
mailer()->to('client@example.com')->send(new \App\Mails\WelcomeMail($user));

// Ciblage explicite d'un transport configuré dans config/mail.php
mailer()
    ->to('admin@example.com')
    ->subject('Alerte Sécurité')
    ->send(new \App\Mails\SecurityAlert(), 'smtp');

```

> Suppose que le transport `smtp` est bien configuré dans `config/mail.php` (voir [Configuration](mail-notifications.md#fichier-de-configuration)).

### `with()`

Passe la valeur fournie au premier argument dans un callback anonyme et retourne le résultat de ce dernier. Utile pour éviter les variables intermédiaires lors des transformations d'objets.

```php
// Sans with()
$user = User::find(1);
$displayName = $user ? strtoupper($user->name) : 'INVITÉ';

// Avec with()
$displayName = with(User::find(1), function ($user) {
    return $user ? strtoupper($user->name) : 'INVITÉ';
});

```

### `tap()`

Exécute un callback en lui passant la valeur fournie, puis retourne **la valeur elle-même** (et non le résultat du callback). Très utile pour appliquer un effet de bord sur un objet sans interrompre la chaîne d'appels.

```php
// Exécute la méthode ->save() tout en retournant l'instance de $user
$user = tap(new User(['name' => 'Alice']), function ($u) {
    $u->role = 'admin';
    $u->save();
});

// $user contient toujours l'instance de la classe User

```er = tap(new User(), fn($u) => $u->save());
```

### `call()`

Exécute une fonction ou une méthode callable en lui injectant dynamiquement des arguments.

```php
$callable = [new \App\Services\PaymentService(), 'process'];

$result = call($callable, ['amount' => 100, 'currency' => 'EUR']);

```

### `abort()`

Interrompt immédiatement l'exécution de la requête HTTP en lançant une exception de statut HTTP spécifique. Le framework intercepte cette exception pour rendre la vue d'erreur correspondante (ex: `errors/404.twig`).

```php
// Interruption basique avec un statut 404
if (!$article) {
    abort(404);
}

// Interruption 403 avec un message personnalisé
if (!$user->isAdmin()) {
    abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette ressource.");
}

```

### `asset()`

Génère une URL absolue ou relative correcte pour accéder aux ressources publiques du projet (fichiers CSS, JS, images).

```php
// Génère : "/css/app.css" ou "https://cdn.example.com/css/app.css" (selon la config)
$cssUrl = asset('css/app.css');

// Dans un template HTML
echo '<script src="' . asset('js/bundle.js') . '"></script>';

```

### `client_url()`

Retourne l'URL complète de la requête HTTP entrante effectuée par le client (incluant le protocole, le domaine et les paramètres de requête).

```php
// Si la requête courante est sur : https://example.com/products?page=2
$currentUrl = client_url(); 
// Retourne : "https://example.com/products?page=2"

```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les helpers :

1. **[Facades](facades.md)** → Facades principales
2. **[Console](console.md)** → Toutes les commandes

---

**Helpers compris !** 🚀 → [Facades](facades.md)