# Validation

Tonka propose un système de validation à la fois riche (chaque règle expose ses propres options, au-delà d'un simple mot-clé) et applicable à plusieurs niveaux du cycle de vie d'une requête. Cette page sert de référence complète : niveaux de validation, syntaxe des règles, catalogue des règles intégrées, et création de règles personnalisées.

- [Validation](#validation)
- [Niveaux de Validation](#niveaux-de-validation)
- [Validation des Paramètres de Route](#validation-des-parametres-de-route)
  - [Méthodes de Validation](#methodes-de-validation)
  - [Validation Personnalisée](#validation-personnalisee)
  - [Contraintes Globales](#contraintes-globales)
- [Validation des Requêtes (Form Requests)](#validation-des-requetes-form-requests-)
  - [Création d'un Form Request](#creation-d-39-un-form-request)
  - [Structure d'un Form Request](#structure-d-39-un-form-request)
  - [Validation Directe](#validation-directe)
  - [Validation avec Attributs](#validation-avec-attributs)
- [Validation des Entités](#validation-des-entites)
- [Règles Intégrées](#regles-integrees)
- [Règles Personnalisées](#regles-personnalisees)
  - [Création d'une Règle](#creation-d-39-une-regle)
  - [Enregistrement](#enregistrement)
- [Messages d'Erreur Personnalisés](#messages-d-39-erreur-personnalises)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Niveaux de Validation

Tonka propose une validation à **trois niveaux**, chacun adapté à un moment différent du traitement de la donnée :

| Niveau | Applicable à | Description |
|--------|------------|-------------|
| **Route** | Paramètres d'URL | Validation des paramètres dynamiques, avant même le dispatch vers le contrôleur |
| **Request** | Données HTTP | Validation des formulaires et des requêtes entrantes |
| **Entity** | Propriétés de modèle | Validation des attributs métier, appliquée quel que soit le point d'entrée (HTTP, console, seeder) |

> **Pour les développeurs Laravel/Symfony :** la validation de route n'a pas d'équivalent direct dans ces deux écosystèmes (où elle se limite généralement à des contraintes regex sur le segment d'URI) ; la validation de requête reprend l'esprit des Form Requests Laravel ; la validation d'entité se rapproche des contraintes `#[Assert\...]` de Symfony Validator sur une entité Doctrine.

---

## Validation des Paramètres de Route

### Méthodes de Validation

```php
Route::get('/users/:id', [UserController::class, 'show'])
    ->whereInt('id');                       // Numérique

Route::get('/posts/:slug', [PostController::class, 'show'])
    ->wherePattern('slug', '^[a-z0-9-]+$'); // Pattern regex

Route::get('/users/:id', [UserController::class, 'show'])
    ->whereModel('id', User::class);        // Existence en base

Route::get('/users/:id/posts/:post_id', [PostController::class, 'show'])
    ->whereInt(['id', 'post_id']);          // Application à plusieurs paramètres
```

### Validation Personnalisée

```php
// Validation avec la méthode where()
Route::get('/users/:id', [UserController::class, 'show'])
    ->where('id', 'required|numeric|min:1|max:1000');

// Validation combinée
Route::get('/posts/:slug', [PostController::class, 'show'])
    ->where('slug', 'required|string|pattern:[a-z0-9-]+|min:3|max:50');
```

### Contraintes Globales

```php
// app/Providers/RouteServiceProvider.php
public function register(): void
{
    Route::pattern('id', '[0-9]+');
    Route::pattern('slug', '[a-z-]+');
}
```

---

## Validation des Requêtes (Form Requests)

### Création d'un Form Request

```bash
php tonka make:request StoreUserRequest
```

### Structure d'un Form Request

```php
namespace App\Http\Requests;

use Clicalmani\Core\Http\RequestInterface;

class StoreUserRequest extends Request
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
        $this->merge([
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
            'password' => 'required|password|min:8',
            'age' => 'nullable|int|min:18',
            'role' => 'required|in|list:admin,user,moderator',
            'is_active' => 'boolean|nullable'
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'name'  => 'Le nom est obligatoire',
            'email' => 'L\'email est obligatoire'
        ];
    }
}
```

### Validation Directe

```php
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8',
            'age' => 'nullable|integer|min:18'
        ]);

        $user = User::create($validated);
        return redirect('/users');
    }
}
```

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

---

## Validation des Entités

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

    #[Validate('integer|min:18|max:120')]
    public TinyInt|int|null $age;
}
```

---

## Règles Intégrées

La syntaxe générale d'une règle est `regle:option1,option2` ou, pour une option nommée, `regle|option:valeur`. Les options se combinent en les enchaînant avec le caractère `|` : `password|min:8|common:no`.

| Règle | Description | Options |
|-------|-------------|---------|
| `string` | Chaîne de caractères | <ul><li>`length` Taille exacte de la chaîne (ex : 8)</li><li>`min` Nombre de caractères minimal (ex : 0)</li><li>`max` Nombre de caractères maximal (ex : 100)</li><li>`pattern` Expression régulière (ex : [a-zA-Z0-9])</li><li>`format` un des formats (email, url, uuid, ip, alpha, alnum, numeric, slug)</li><li>`sanitize` une des fonctions de filtrage (_trim_, _lower_, _upper_, _ucfirst_, _ucwords_, _strip_tags_, _htmlspecialchars_)</li><li>`noSpace` interdit les espaces (booléen : _yes_, _no_, _true_, _false_, _0_, _1_)</li><li>`noSpecial` interdit les caractères spéciaux (ex : @#;$..., booléen : _yes_, _no_, _true_, _false_, _0_, _1_)</li></ul> |
| `string[]` | Tableau de chaînes de caractères | Toutes les options de `string`, plus <ul><li>`minItems` Nombre minimal d'éléments requis dans le tableau</li><li>`maxItems` Nombre maximal d'éléments autorisé dans le tableau</li><li>`distinct` Supprime les doublons du tableau</li><li>`allowEmpty` Autorise les chaînes vides dans le jeu de données</li><li>`join` Concatène les éléments valides en une seule chaîne de sortie</li><li>`separator` Séparateur utilisé lors de la concaténation</li></ul> |
| `number`, `num` | Nombre | <ul><li>`min` Valeur minimale</li><li>`max` Valeur maximale</li><li>`range` Intervalle (ex : _1-6_)</li><li>`type` un des types suivants (_integer_, _float_, _positive_, _negative_, _natural_, _even_, _odd_)</li><li>`round` Nombre de chiffres après la virgule</li><li>`format` un des formats (_integer_, _float_, _string_)</li></ul> |
| `numeric` | Nombre (entier ou flottant) | <ul><li>`length` Nombre maximal de chiffres</li><li>`precision` Nombre de chiffres après la virgule</li><li>`format` un des formats (_integer_, _decimal_, _percentage_, _currency_)</li><li>`round` Arrondit à _n_ chiffres</li><li>`allowNegative` Autorise les nombres négatifs (_yes_, _no_, _true_, _false_, _0_, _1_)</li><li>`allowZero` Autorise la valeur zéro (_yes_, _no_, _true_, _false_, _0_, _1_)</li><li>`min` Valeur minimale</li><li>`max` Valeur maximale</li></ul> |
| `integer`, `int` | Entier | Toutes les options de `number`, sauf `round` et `precision` |
| `float` | Nombre à virgule flottante | Toutes les options de `number`, avec le format `float` |
| `boolean` | Booléen | <ul><li>`default` Valeur par défaut lorsque le champ est vide</li><li>`strict` N'accepte strictement qu'une valeur booléenne</li></ul> |
| `email` | Adresse e-mail valide | <ul><li>`unique` Unicité vérifiée sur le modèle (nom du modèle en minuscules, en kebab-case)</li><li>`column` Colonne à vérifier dans la table (par défaut, le nom du champ validé)</li><li>`except` Valeur à exclure de la vérification d'unicité</li><li>`exceptColumn` Colonne portant la valeur à exclure (par défaut, la clé primaire de la table)</li><li>`mx` Résolution MX (vérifie l'existence d'un enregistrement MX pour le domaine)</li><li>`dns` Résolution DNS</li><li>`block` Liste noire de domaines (ex : domaine1.com, domaine2.org, domaine2.info...)</li><li>`only` Liste blanche de domaines</li></ul> |
| `url` | URL valide | <ul><li>`schemes` ex : http,https</li><li>`allowedDomains` Liste blanche de domaines</li><li>`blockedDomains` Liste noire de domaines</li><li>`allowedPorts` Liste blanche de ports</li><li>`dns` Active la résolution DNS</li><li>`sanitize` une des valeurs _trim_, _lower_</li></ul> |
| `date`, `datetime` | Date valide | <ul><li>`format` (supporte Y, m, d, H, i, s, etc.)</li><li>`formats` Plusieurs formats acceptés</li><li>`min` Date minimale</li><li>`max` Date maximale</li><li>`timezone` Identifiant de fuseau horaire (ex : UTC, Africa/Porto-Novo...)</li><li>`relative` Date relative (_now_, _+1 day_...)</li><li>`strict` Vérification stricte du calendrier (_yes_, _no_, _true_, _false_, _0_, _1_)</li></ul> |
| `in`, `enum` | Dans une liste de valeurs | <ul><li>`list` Liste des valeurs supportées</li><li>`caseInsensitive` Ignore la casse (_yes_, _no_, _true_, _false_, _0_, _1_)</li><li>`sanitize` Filtre appliqué avant comparaison (_trim_, _lower_, _upper_)</li><li>`strict` Comparaison stricte (_yes_, _no_, _true_, _false_, _0_, _1_)</li></ul> |
| `not_in` | Absente d'une liste de valeurs | Options identiques à `in`, logique inversée |
| `password` | Mot de passe | <ul><li>`length` Taille exacte (ex : 8)</li><li>`min` Nombre minimal de caractères</li><li>`max` Nombre maximal de caractères</li><li>`uppercase` Nombre minimal de majuscules</li><li>`lowercase` Nombre minimal de minuscules</li><li>`numbers` Nombre minimal de chiffres</li><li>`symbols` Nombre minimal de caractères symboliques (ex : @, #, $...)</li><li>`strength` une des valeurs (_weak_, _medium_, _strong_, _very_strong_)</li><li>`common` Rejette les mots de passe usuels et faciles à deviner (_yes_, _no_, _true_, _false_, _0_, _1_)</li><li>`repeat` Nombre maximal de caractères identiques consécutifs (ex : 2)</li><li>`noSpace` Interdit les espaces (_yes_, _no_, _true_, _false_, _0_, _1_)</li></ul> |
| `id` | Identifiant existant en base | <ul><li>`model` Nom du modèle (ex : 'user_profile' → UserProfile)</li><li>`column` Colonne à vérifier (par défaut, la clé primaire)</li><li>`scope` Conditions supplémentaires (combinées par ET) ajoutées à la requête d'existence. Accepte un tableau colonne => valeur, ou colonne => callable évalué au moment de la validation. Exemple : `['company_id' => fn() => auth()->company_id]`</li><li>`withTrashed` Si vrai, inclut les enregistrements supprimés logiquement (soft-deleted) dans la recherche d'existence (ignoré si le modèle cible n'utilise pas le trait `SoftDelete`)</li></ul> |
| `id[]` | Tableau d'identifiants | Toutes les options de `id`, plus <ul><li>`join` Concatène les identifiants valides en une chaîne délimitée après validation</li><li>`limit` Limite le nombre maximal d'identifiants acceptés en entrée</li><li>`distinct` Supprime les identifiants en double du résultat final</li><li>`separator` Délimiteur personnalisé (la virgule par défaut)</li><li>`allowEmpty` Autorise les valeurs d'identifiant vides dans les données soumises</li></ul> |
| `file` | Fichier | <ul><li>`max` Taille maximale du fichier, convertie en octets (ex : 1024, 10k, 2M, 5G)</li><li>`min` Taille minimale du fichier, convertie en octets</li><li>`ext` Extensions de fichier autorisées (ex : png, jpg, pdf, doc...)</li><li>`mime` Types MIME autorisés</li><li>`minWidth` Largeur minimale de l'image, en pixels</li><li>`maxWidth` Largeur maximale de l'image, en pixels</li><li>`minHeight` Hauteur minimale de l'image, en pixels</li><li>`maxHeight` Hauteur maximale de l'image, en pixels</li><li>`filename` Expression régulière validant le nom du fichier (ex : [a-zA-Z0-9-_])</li><li>`allowEmpty` Autorise un champ fichier vide ou non renseigné</li></ul> |
| `image` | Image | Toutes les options de `file`, plus <ul><li>`ratio` ex : 512:512</li><li>`minRatio` Ratio minimal</li><li>`maxRatio` Ratio maximal</li></ul> |
| `regexp` | Expression régulière | <ul><li>`pattern` Motif d'expression régulière cible (sans les délimiteurs)</li><li>`flags` Indicateurs de modification du motif (ex : i, m, s, u)</li><li>`sanitize` Filtre de nettoyage appliqué avant évaluation (_trim_, _lower_, _upper_, _strip_tags_)</li><li>`capture` Capture les sous-groupes correspondants dans un tableau</li><li>`not` Inverse la logique d'évaluation — la valeur ne doit **pas** correspondre au motif (_yes_, _no_, _true_, _false_, _0_, _1_)</li></ul> |
| `json` | JSON | <ul><li>`assoc` Décodage en tableau associatif (`true`) ou en objet `stdClass` (`false`)</li><li>`depth` Profondeur maximale de récursion lors de l'analyse</li><li>`types` Validation avec typage des champs (ex : `required\|json\|types:array` → `{"users": [{"name": "John"}]}`)</li><li>`schema` Schéma de validation JSON — clés requises, types de propriétés, valeurs par défaut (ex : `required\|json\|schema:{"required":["name","email"],"properties":{"name":{"type":"string"},"email":{"type":"string"}}}` → `{"name": "John", "email": "john@example.com"}`)</li><li>`clean` Validation avec nettoyage préalable (ex : `required\|json\|clean:true`)</li><li>`maxSize` Longueur maximale de la charge utile, en nombre de caractères</li></ul> |
| `pdf` | Fichier PDF | Toutes les options de `file`, plus <ul><li>`minVersion` Version PDF minimale autorisée (ex : '1.4')</li><li>`maxVersion` Version PDF maximale autorisée (ex : '1.7')</li><li>`checkContent` Effectue des vérifications d'intégrité du contenu ; valide la structure via `pdftotext` si le binaire est disponible</li><li>`maxPages` Nombre maximal de pages autorisé. Stratégie 1 : utilitaire en ligne de commande `pdftk`. Stratégie 2 : comptage des sauts de page via `pdftotext`. Stratégie 3 : analyse directe du flux</li></ul> |

Au-delà des options propres à chaque règle, les arguments suivants s'appliquent à toute règle :

| Argument | Rôle | Exemple |
| --- | --- | --- |
| `required` | Indique que le champ est obligatoire | `required\|string\|min:8` |
| `nullable` | Indique que le champ accepte une valeur nulle | `bool\|nullable` |
| `sometimes` | N'applique la validation que si le champ est présent dans les données soumises | `bool\|sometimes` |

> Chaque règle accepte en outre une dernière option, `message`, qui permet de personnaliser le message d'erreur directement dans la signature de la règle plutôt que dans `messages()`.

---

## Règles Personnalisées

### Création d'une Règle

```php
namespace App\Http\Validators;

use Clicalmani\Validation\Rule;

class PhoneValidator extends Rule
{
    protected static string $argument = 'phone';

    public function options(): array
    {
        return [
            'country' => [
                'required' => false,
                'type' => 'string',
                'function' => function(string $country) {
                    return strtoupper($country);
                }
            ],
            'format' => [
                'required' => false,
                'type' => 'string',
                'validator' => function($value) {
                    return in_array($value, ['international', 'national']);
                }
            ]
        ];
    }

    public function validate(mixed &$value): bool
    {
        // Nettoyage
        $value = preg_replace('/[^0-9+]/', '', $value);

        // Validation
        if (strlen($value) < 8) {
            return false;
        }

        return true;
    }

    public function message(): ?string
    {
        return "Le numéro de téléphone n'est pas valide.";
    }
}
```

> **Pour les développeurs Symfony :** une règle personnalisée Tonka regroupe en une seule classe ce que Symfony Validator répartit entre deux classes distinctes (`Constraint` + `ConstraintValidator`). C'est plus proche, dans l'esprit, d'une classe implémentant `Illuminate\Contracts\Validation\Rule` côté Laravel.

### Enregistrement

```php
// app/Http/Kernel.php
class Kernel extends HttpKernel
{
    protected array $custom_rules = [
        PhoneValidator::class,
        UuidValidator::class,
    ];
}
```

---

## Messages d'Erreur Personnalisés

### Dans un Form Request

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
            'name'  => 'Le nom est obligatoire',
            'email' => 'L\'email est obligatoire'
        ];
    }
}
```

---

## Exemple Complet

### Route

```php
// routes/web.php
Route::post('/users', [UserController::class, 'store'])
    ->where('email', 'required|email');
```

### Form Request

```php
namespace App\Http\Requests;

use Clicalmani\Core\Http\RequestInterface;

class UserRequest extends Request
{
    public function authorize(): bool
    {
        return auth()->user()->can('create_user');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email),
            'name' => trim($this->name)
        ]);
    }

    public function signatures(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8|confirmed',
            'age' => 'nullable|integer|min:18',
            'role' => 'required|in:admin,user,moderator'
        ];
    }

    public function messages(): array
    {
        return [
            'name' => 'Le nom est obligatoire',
            'email' => 'L\'email est obligatoire',
            'password' => 'Le mot de passe est obligatoire',
        ];
    }
}
```

### Contrôleur

```php
namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Clicalmani\Validation\AsValidator;
use App\Models\User;

class UserController extends Controller
{
    #[AsValidator([
        'email' => 'required|email|unique:users'
    ])]
    public function store(UserRequest $request)
    {
        // La validation s'exécute automatiquement
        $validated = $request->validated();

        $user = User::create($validated);

        return redirect('/users')
            ->with('success', 'User created successfully!');
    }
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez la validation :

1. **[Sessions](sessions.md)** → Gérer les sessions
2. **[Base de Données](database.md)** → Travailler avec les modèles
3. **[Elegant ORM](orm.md)** → Approfondir l'ORM

---

**Validation comprise !** 🚀 → [Sessions](sessions.md)