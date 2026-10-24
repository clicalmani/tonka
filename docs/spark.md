# Spark (Routing Frontend)

- [Spark](#spark)
- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Backend](#backend)
  - [Frontend](#frontend)
- [Routes Frontend](#routes-frontend)
  - [Exposition des Routes](#exposition-des-routes)
  - [Groupes de Routes](#groupes-de-routes)
  - [Politiques d'Autorisation](#politiques-d-39-autorisation)
- [Génération d'URL](#generation-d-39-url)
  - [Paramètres Requis](#parametres-requis)
  - [Paramètres Optionnels](#parametres-optionnels)
  - [Query Strings](#query-strings)
- [Route Actuelle](#route-actuelle)
  - [Vérification de la Route](#verification-de-la-route)
  - [URL Actuelle](#url-actuelle)
- [Intégration React](#integration-react)
- [Intégration Vue](#integration-vue)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

**Spark** est le système de routage frontend de Tonka. Il expose vos routes PHP au frontend JavaScript, permettant une génération d'URL sécurisée et typée directement depuis le client.

**Fonctionnalités :**
- ✅ Routes PHP disponibles côté JavaScript
- ✅ Génération d'URL avec paramètres
- ✅ Groupes de routes
- ✅ Politiques d'autorisation
- ✅ TypeScript support
- ✅ Intégration React / Vue

---

## Installation

### Backend

```bash
composer require clicalmani/spark
```

### Frontend

```bash
npm install tonka-router
```

---

## Configuration

### Backend

```php
// config/spark.php
return [
    // Groupes de routes
    'groups' => [
        'web' => [
            'home', 'about', 'contact',
            'dashboard', 'profile.*', 'settings.*'
        ],
        'admin' => [
            'admin.*', 'users.*', 'posts.*'
        ]
    ],
    
    // Routes autorisées (mode 'only')
    'only' => [
        ['name' => 'home', 'policy' => null],
        ['name' => 'about', 'policy' => null],
        ['name' => 'dashboard', 'policy' => null],
    ],
    
    // Routes exclues (mode 'except')
    'except' => [
        ['name' => 'admin.*', 'policy' => null],
    ],
    
    // Politiques d'autorisation
    'only' => [
        [
            'name' => 'user.*',
            'policy' => \App\Contracts\Spark\UserPolicy::class
        ],
        [
            'name' => 'post.*',
            'policy' => \App\Contracts\Spark\PostPolicy::class
        ]
    ]
];
```

### Frontend

```typescript
// resources/js/bootstrap/spark.ts
import { route, current, currentUrl, useRoute } from 'tonka-router';

export { route, current, currentUrl, useRoute };
```

---

## Routes Frontend

### Exposition des Routes

```twig
{# resources/views/app.twig #}
<!DOCTYPE html>
<html>
<head>
    @inertiaHead
</head>
<body>
    {# Définition du groupe de routes #}
    @routes('web')
    
    {# Injection des routes dans le DOM #}
    <div id="app" 
         data-routes="@SparkRoutes" 
         data-currentroute="@currentRoute">
    </div>
    
    @vite('resources/js/app.tsx')
</body>
</html>
```

### Groupes de Routes

```twig
{# Groupe web (routes publiques) #}
@routes('web')

{# Groupe admin (routes protégées) #}
@routes('admin')

{# Groupe API (routes API) #}
@routes('api')
```

### Politiques d'Autorisation

```php
// app/Contracts/Spark/UserPolicy.php
class UserPolicy extends Contract
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->role === 'admin' || $user->id === (int) request()->input('id');
    }
}
```

---

## Génération d'URL

### Paramètres Requis

```javascript
import { route } from 'tonka-router';

// Route: /users/:id
route('users.show', { id: 123 }); // /users/123
route('users.show', [123]); // /users/123

// Route: /posts/:slug
route('posts.show', { slug: 'hello-world' }); // /posts/hello-world
```

### Paramètres Optionnels

```javascript
// Route: /users/?:id
route('users.index', { id: 123 }); // /users/123
route('users.index'); // /users

// Route: /posts/:category/?:slug
route('posts.index', { category: 'tech', slug: 'hello' }); // /posts/tech/hello
route('posts.index', { category: 'tech' }); // /posts/tech
```

### Query Strings

```javascript
// Paramètres supplémentaires → query string
route('search.results', {
    q: 'tonka framework',
    page: 2,
    sort: 'relevance'
});
// /search?q=tonka%20framework&page=2&sort=relevance

// Paramètres multiples
route('products.index', {
    category: 'electronics',
    brand: 'sony',
    price_min: 100,
    price_max: 500
});
// /products?category=electronics&brand=sony&price_min=100&price_max=500
```

---

## Route Actuelle

### Vérification de la Route

```javascript
import { current } from 'tonka-router';

// Vérifier si on est sur une route spécifique
if (current('dashboard')) {
    console.log('Dashboard actif');
}

// Récupérer la route actuelle
const currentRoute = current();
console.log(currentRoute.name); // 'users.show'
console.log(currentRoute.parameters); // { id: 123 }
```

### URL Actuelle

```javascript
import { currentUrl } from 'tonka-router';

// Récupérer l'URL actuelle
const url = currentUrl(); // /users/123
```

---

## Intégration React

```tsx
import { route, current, useRoute } from 'tonka-router';
import { Link } from '@inertiajs/react';

export default function Navigation() {
    return (
        <nav>
            <Link href={route('home')}>Accueil</Link>
            <Link href={route('users.show', { id: 123 })}>Utilisateur</Link>
            
            {current('dashboard') && (
                <div className="active">Dashboard actif</div>
            )}
        </nav>
    );
}
```

### Hook useRoute

```tsx
import { useRoute } from 'tonka-router';

export default function UserProfile({ userId }: { userId: number }) {
    const route = useRoute();
    
    return (
        <div>
            <Link href={route('users.show', { id: userId })}>
                Voir le profil
            </Link>
        </div>
    );
}
```

---

## Intégration Vue

```vue
<template>
    <nav>
        <Link :href="route('home')">Accueil</Link>
        <Link :href="route('users.show', { id: 123 })">Utilisateur</Link>
        
        <div v-if="current('dashboard')" class="active">
            Dashboard actif
        </div>
    </nav>
</template>

<script setup lang="ts">
import { route, current } from 'tonka-router';
import { Link } from '@inertiajs/vue3';
</script>
```

---

## Exemple Complet

### Backend

```php
// routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/:id', [UserController::class, 'show'])->name('users.show');
Route::get('/posts/:slug', [PostController::class, 'show'])->name('posts.show');
Route::get('/search', [SearchController::class, 'index'])->name('search.results');

// config/spark.php
return [
    'only' => [
        ['name' => 'home', 'policy' => null],
        ['name' => 'users.*', 'policy' => null],
        ['name' => 'posts.*', 'policy' => \App\Contracts\Spark\PostPolicy::class],
        ['name' => 'search.results', 'policy' => null],
    ]
];
```

### Frontend

```tsx
import { route, current } from 'tonka-router';

export default function App() {
    return (
        <div>
            <nav>
                <Link href={route('home')}>Accueil</Link>
                <Link href={route('users.index')}>Utilisateurs</Link>
                <Link href={route('search.results', { q: 'tonka' })}>Recherche</Link>
            </nav>
            
            {current('home') && <HomePage />}
            {current('users.show') && <UserProfile userId={current().parameters.id} />}
            {current('posts.show') && <PostDetail slug={current().parameters.slug} />}
        </div>
    );
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez Spark :

1. **[DriftQL](driftql.md)** → Requêtes frontend sécurisées
2. **[Vite](vite.md)** → Build tool
3. **[Broadcaster Client](broadcaster-client.md)** → Événements en temps réel

---

**Spark compris !** 🚀 → [DriftQL](driftql.md)