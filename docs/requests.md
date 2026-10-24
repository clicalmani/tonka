# Requêtes

L'objet `Request` centralise l'accès à tout ce que le client a transmis : données de formulaire, fichiers, session, cookies. Cette page détaille son API, ainsi que les différentes stratégies de validation proposées par Tonka.

- [Requêtes](#requetes)
- [Accès aux Données](#acces-aux-donnees)
  - [Récupérer une Entrée](#recuperer-une-entree)
  - [Récupérer Toutes les Entrées](#recuperer-toutes-les-entrees)
  - [Récupérer des Entrées Spécifiques](#recuperer-des-entrees-specifiques)
  - [Vérifier l'Existence](#verifier-l-39-existence)
  - [Accès aux Données JSON](#acces-aux-donnees-json)
- [Validation des Requêtes](#validation-des-requetes)
  - [Validation avec Form Request](#validation-avec-form-request)
  - [Validation Directe](#validation-directe)
  - [Validation avec Attributs](#validation-avec-attributs)
  - [Validation dans les Entités](#validation-dans-les-entites)
  - [Règles de Validation Disponibles](#regles-de-validation-disponibles)
  - [Messages d'Erreur Personnalisés](#messages-d-39-erreur-personnalises)
- [Upload de Fichiers](#upload-de-fichiers)
  - [Récupérer un Fichier](#recuperer-un-fichier)
  - [Valider un Fichier](#valider-un-fichier)
  - [Déplacer un Fichier](#deplacer-un-fichier)
  - [Fichiers Multiples](#fichiers-multiples)
- [Sessions](#sessions)
  - [Lire une Session](#lire-une-session)
  - [Écrire une Session](#ecrire-une-session)
  - [Messages Flash](#messages-flash)
- [Cookies](#cookies)
  - [Lire un Cookie](#lire-un-cookie)
  - [Écrire un Cookie](#ecrire-un-cookie)
  - [Supprimer un Cookie](#supprimer-un-cookie)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Accès aux Données

### Récupérer une Entrée

```php
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Récupération simple
        $name = $request->input('name');

        // Avec valeur par défaut
        $role = $request->input('role', 'user');

        // Récupération via méthode magique
        $email = $request->email;

        // Récupération via helper
        $name = request()->input('name');

        // Récupération avec valeur par défaut via helper
        $role = request()->input('role', 'user');
    }
}
```

### Récupérer Toutes les Entrées

```php
// Toutes les données
$all = $request->all();

// Toutes les données, via le helper
$all = request()->all();
```

### Récupérer des Entrées Spécifiques

```php
// Uniquement certaines clés
$data = $request->only(['name', 'email', 'role']);

// Toutes sauf certaines clés
$data = $request->except(['password', 'password_confirmation']);
```

`except()` est particulièrement utile pour réinjecter les données d'un formulaire en cas d'échec de validation, sans jamais renvoyer les champs sensibles (mot de passe, jeton) au client.

### Vérifier l'Existence

```php
// Une seule clé
if ($request->has('name')) {
    // ...
}

// Plusieurs clés (toutes présentes)
if ($request->has(['name', 'email'])) {
    // ...
}

// Au moins une clé présente
if ($request->hasAny(['name', 'email'])) {
    // ...
}

// Champ rempli (non vide)
if ($request->filled('name')) {
    // ...
}

// Au moins un champ rempli
if ($request->anyFilled(['name', 'email'])) {
    // ...
}
```

`has()` vérifie uniquement la **présence** de la clé (une chaîne vide `''` est donc considérée comme présente), tandis que `filled()` exige en plus que la valeur ne soit ni vide ni `null` — une distinction à connaître avant de valider manuellement un champ optionnel.

### Accès aux Données JSON

```php
// Récupération en tant que collection
$data = $request->collect('users');

// Récupération en tant qu'objet JSON brut
$json = $request->json();
```

---

## Validation des Requêtes

Tonka propose plusieurs stratégies de validation, à choisir selon le niveau de réutilisation et de complexité souhaité : Form Request pour une validation réutilisable et testable isolément, validation directe pour les cas simples, validation par attribut pour une déclaration au plus près de l'action, et validation au niveau de l'entité pour une contrainte appliquée systématiquement, indépendamment du point d'entrée HTTP.

### Validation avec Form Request

#### Création du Form Request

```bash
php tonka make:request UserRequest
```

#### Définition du Form Request

```php
namespace App\Http\Requests;

use Clicalmani\Core\Http\RequestInterface;

class UserRequest extends Request
{
    /**
     * Autorisation
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Préparation des données avant validation
     */
    public function prepareForValidation(): void
    {
        $this->make([
            'email' => strtolower($this->email),
            'name' => trim($this->name)
        ]);
    }

    /**
     * Règles de validation
     */
    public function signatures(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8|uppercase:1|number:1|symbols:1|strength:medium|common:no|noSpace:yes',
            'age' => 'nullable|int|min:18',
            'role' => 'required|in:admin,user,moderator',
            'is_active' => 'boolean|nullable'
        ];
    }
}
```

#### Utilisation dans le Contrôleur

```php
class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        // La validation s'exécute automatiquement à la résolution
        // du type-hint UserRequest — les données sont déjà validées
        // au moment où le corps de la méthode s'exécute.

        // Récupération des données validées
        $validated = $request->validated();

        $user = User::create($validated);
        return redirect('/users');
    }
}
```

### Validation Directe

```php
use Clicalmani\Validation\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8',
            'age' => 'nullable|integer|min:18'
        ], $request->all());

        if ($validator->hasErrors()) {
            // ...
        }

        $validated = $validator->validated();

        $user = User::create($validated);
        return redirect('/users');
    }
}
```

> **Pour les développeurs Laravel :** `Validator::make()` reprend l'esprit de la façade `Validator` de Laravel — utile pour valider un jeu de données en dehors du cycle habituel requête/Form Request (import CSV, appel de service interne, etc.).

### Validation avec Attributs

```php
use Clicalmani\Validation\AsValidator;

class UserController extends Controller
{
    #[AsValidator([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|password|min:8'
    ])]
    public function store(Request $request)
    {
        $validated = $request->validated();
        $user = User::create($validated);
        return response()->json($user);
    }
}
```

### Validation dans les Entités

```php
// database/entities/UserEntity.php
use Clicalmani\Database\Factory\Validate;

class UserEntity extends Entity
{
    #[Validate('required|email|max:255')]
    public VarChar $email;

    #[Validate('required|password|hash|min:8')]
    public VarChar $password;

    #[Validate('required|in:M,F,O')]
    public Enum|string|null $gender = 'M';
}
```

> **Pour les développeurs Symfony :** cette approche rappelle les contraintes `#[Assert\...]` posées directement sur les propriétés d'une entité Doctrine. La différence de fond est le périmètre : ici, la contrainte est rattachée à l'entité de persistance elle-même, ce qui garantit son application quel que soit le point d'entrée (contrôleur HTTP, commande console, seeder) — contrairement à une règle définie uniquement dans un Form Request, qui ne s'applique qu'au contexte HTTP dans lequel elle est invoquée.

### Règles de Validation Disponibles

| Règle | Description | Exemple |
|-------|-------------|---------|
| `required` | Champ obligatoire | `required` |
| `nullable` | Peut être `null` | `nullable` |
| `string` | Chaîne de caractères | `string` |
| `integer` | Nombre entier | `integer` |
| `numeric` | Nombre (entier ou flottant) | `numeric` |
| `boolean` | Booléen | `boolean` |
| `email` | Adresse e-mail valide | `email` |
| `url` | URL valide | `url` |
| `uuid` | UUID valide | `uuid` |
| `date` | Date valide | `date` |
| `datetime` | Date et heure valides | `datetime` |
| `min:18` | Valeur minimale | `min:18` |
| `max:100` | Valeur maximale | `max:100` |
| `between:1,100` | Comprise entre deux valeurs | `between:1,100` |
| `in:1,2,3` | Dans une liste de valeurs | `in:1,2,3` |
| `not_in:4,5,6` | Absente d'une liste de valeurs | `not_in:4,5,6` |
| `password` | Règles de robustesse d'un mot de passe | `password` |
| `confirmed` | Doit avoir un champ `_confirmation` correspondant | `confirmed` |
| `hash` | Hache la valeur avant stockage | `hash` |
| `id` | Identifiant existant en base | `id\|model:user` |
| `file` | Fichier | `file\|max:2048` |
| `image` | Image | `image\|ext:jpg,png` |
| `regexp` | Expression régulière | `regexp\|pattern:/^[a-z]+$/` |

### Messages d'Erreur Personnalisés

```php
class UserRequest extends Request
{
    public function signatures(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }

    public function messages(): array
    {
        return [
            'name' => 'Le nom est obligatoire',
            'email' => 'L\'email est obligatoire',
            'email' => 'L\'email n\'est pas valide',
            'email' => 'Cet email est déjà utilisé',
        ];
    }
}
```

---

## Upload de Fichiers

### Récupérer un Fichier

```php
class UserController extends Controller
{
    public function upload(Request $request)
    {
        // Vérifier qu'un fichier a bien été transmis
        if (!$request->hasFile('avatar')) {
            return response()->error('No file uploaded');
        }

        // Récupérer le fichier
        $file = $request->file('avatar');

        // Récupérer ses métadonnées
        $name = $file->getClientFilename();
        $extension = $file->getClientExtension();
        $size = $file->getSize();
        $mimeType = $file->getClientMediaType();
    }
}
```

### Valider un Fichier

```php
class UserRequest extends Request
{
    public function signatures(): array
    {
        return [
            'avatar' => 'required|image|max:2048|ext:jpg,png,gif',
            'document' => 'required|file|max:10240|ext:pdf,doc,docx',
        ];
    }
}
```

### Déplacer un Fichier

```php
class UserController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('avatar');

        // Déplacement bas niveau (API PSR-7 UploadedFileInterface)
        $path = storage_path('avatars/' . uniqid() . '.' . $file->getClientExtension());
        $file->moveTo($path, 'public'); // Public disk
    }
}
```

> **Pour les développeurs Laravel :** `moveTo()` correspond à l'API bas niveau standard PSR-7 (`UploadedFileInterface`).

### Fichiers Multiples

```php
class UserController extends Controller
{
    public function uploadMultiple(Request $request)
    {
        $files = $request->file('documents');

        if ($files instanceof CollectionInterface) {
            for ($i = 0; $i < $files->count(); $i++) {
                $file = $filesget($i);
                if ($file->isValid()) {
                    $file->moveTo(storage_path('documents/' . uniqid()));
                }
            }
        }
    }
}
```

Vérifier `isValid()` sur chaque fichier avant de le déplacer permet d'écarter individuellement un fichier corrompu ou incomplet au sein d'un lot, sans faire échouer l'ensemble de l'upload.

---

## Sessions

### Lire une Session

```php
class UserController extends Controller
{
    public function profile(Request $request)
    {
        // Via la requête
        $userId = $request->session()->get('user_id');
        $name = $request->session()->get('name', 'Guest');

        // Via le helper
        $userId = session()->get('user_id');
        $name = session()->get('name', 'Guest');

        // Via l'instance de session
        $session = $request->session();
        $userId = $session->user_id;
    }
}
```

### Écrire une Session

```php
class UserController extends Controller
{
    public function login(Request $request)
    {
        // Via la requête
        $request->session()->set('user_id', auth()->id());
        $request->session()->set('user_name', auth()->name());

        // Via le helper
        session()->set('user_id', auth()->id());

        // Via l'instance de session
        $session = $request->session();
        $session->user_id = auth()->id();
    }
}
```

### Messages

```php
// Définir un message lors d'une redirection
return redirect('/users')->with('success', 'Utilisateur créé avec succès !');

// Définir un message de succes lors d'une redirection
return redirect('/users')->withSuccess('Utilisateur créé avec succès !');

// Définir un message d'erreur lors d'une redirection
return redirect('/users')->withError('Impossible de créer l\'utilisateur !');
```

```php
// Le lire au chargement de la page suivante
class UserController extends Controller
{
    public function index(Request $request)
    {
        $successMessage = session()->get('success');

        return Inertia::render('User/Index', [
            'flash' => ['success' => $successMessage]
        ]);
    }
}
```

> **Point d'attention :** contrairement à une donnée de session classique, un message flash est automatiquement purgé après avoir été lu une première fois. Évitez de vous y fier pour une information devant persister au-delà de la requête qui suit immédiatement la redirection.

---

## Cookies

### Lire un Cookie

```php
class UserController extends Controller
{
    public function index(Request $request)
    {
        // Via la requête
        $theme = $request->cookie()->get('theme', 'light');

        // Via le helper
        $theme = cookie()->get('theme', 'light');

        // Vérifier l'existence
        if ($request->hasCookie('theme')) {
            // ...
        }
    }
}
```

### Écrire un Cookie

```php
class UserController extends Controller
{
    public function settings(Request $request)
    {
        // Via la requête (durée exprimée en minutes)
        $request->cookie('theme', 'dark', 60 * 24 * 30)->set();

        // Via le helper
        cookie('theme', 'dark', 60 * 24 * 30)->set();

        // Via la réponse (attaché directement à la redirection)
        return response()
            ->cookie('theme', 'dark', 60 * 24 * 30)
            ->redirect('/settings');
    }
}
```

### Supprimer un Cookie

```php
class UserController extends Controller
{
    public function logout(Request $request)
    {
        // Via la requête
        $request->cookie('remember_token')->delete();

        // Via la réponse
        return response()
            ->deleteCookie('remember_token')
            ->redirect('/login');
    }
}
```

---

## Exemple Complet

```php
namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $users = User::where('active = ?', [1]);

        if ($search) {
            $users->where('name LIKE ? OR email LIKE ?', [
                "%$search%",
                "%$search%"
            ]);
        }

        return Inertia::render('User/Index', [
            'users' => $users->paginate($page, $perPage),
            'filters' => compact('search', 'perPage')
        ]);
    }

    /**
     * Création d'un utilisateur
     */
    public function store(UserRequest $request)
    {
        $user = DB::transaction(function() use ($request) {
            $user = User::create($request->validated());

            if ($user) {
                Profile::create([
                    'bio' => $request->input('bio'),
                    'avatar' => $request->file('avatar')
                ]);
                return $user;
            }
            return false;
        });

        if ($user) return redirect('/users')->withSuccess('User created successfully!');

        return redirect('/users')->withError('Failed to create user');
    }

    /**
     * Upload d'avatar
     */
    public function uploadAvatar(Request $request)
    {
        if (!$request->hasFile('avatar')) {
            return response()->error('No file uploaded');
        }

        $file = $request->file('avatar');

        if (!$file->isValid()) {
            return response()->error('Invalid file');
        }

        $path = storage('public')->move(
            $file->getName(),
            'user_' . auth()->id() . '.png'
        );

        auth()->user()->update(['avatar' => $path]);

        return response()->success([
            'path' => $path,
            'url' => storage('public')->url($path)
        ]);
    }

    /**
     * Mise à jour de l'utilisateur
     */
    public function update(UserRequest $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->notFound();
        }

        $user->update($request->validated());

        return redirect('/users/' . $id)
            ->with('success', 'User updated successfully!');
    }
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les requêtes :

1. **[Réponses](responses.md)** → Retourner différentes réponses
2. **[Validation](validation.md)** → Approfondir la validation
3. **[Base de Données](database.md)** → Travailler avec les modèles
4. **[Vues](views.md)** → Créer l'interface utilisateur

---

**Requêtes comprises !** 🚀 → [Réponses](responses.md)