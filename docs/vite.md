# Vite

- [Vite](#vite)
- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Fichier de Configuration](#fichier-de-configuration)
  - [Plugin Tonka](#plugin-tonka)
- [Points d'Entrée](#points-d-39-entree)
- [HMR (Hot Module Replacement)](#hmr-hot-module-replacement-)
- [Build Production](#build-production)
  - [Commande de Build](#commande-de-build)
  - [Structure du Build](#structure-du-build)
- [Intégration avec les Tags Twig](#integration-avec-les-tags-twig)
  - [@vite](#-vite)
  - [@reactRefresh](#-reactrefresh)
- [Optimisation](#optimisation)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

**Vite** est l'outil de build de Tonka pour le frontend. Il offre une expérience de développement rapide avec HMR (Hot Module Replacement) et une optimisation de production efficace.

**Fonctionnalités :**
- ✅ Build rapide (esbuild)
- ✅ HMR (Hot Module Replacement)
- ✅ Optimisation de production
- ✅ Support TypeScript
- ✅ Plugin dédié pour Tonka

---

## Installation

### Dépendances NPM

```bash
npm install -D vite tonka-vite-plugin
```

### Dépendances Recommandées

```bash
# React
npm install -D @vitejs/plugin-react

# Vue
npm install -D @vitejs/plugin-vue

# TypeScript
npm install -D typescript @types/react @types/react-dom
```

---

## Configuration

### Fichier de Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tonka from 'tonka-vite-plugin';

export default defineConfig({
    plugins: [
        tonka({
            // Point d'entrée principal
            input: 'resources/js/app.tsx',
            
            // SSR (Server-Side Rendering)
            ssr: false,
            
            // HMR: Hot Module Replacement
            refresh: true,
        }),
        react(),
    ],
    define: {
        'process.env': {
            NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development'),
        },
    }
});
```

### Plugin Tonka

```typescript
// Options du plugin Tonka
tonka({
    // Points d'entrée
    input: {
        'resources/js/app.tsx': undefined,
        'resources/css/app.css': undefined,
    },
    
    // Dossier de sortie
    outDir: 'public/build',
    
    // Dossier des assets
    assetsDir: 'assets',
    
    // Génération du manifest
    manifest: 'manifest.json',
    
    // SSR
    ssr: 'resources/js/ssr.tsx',
    
    // HMR
    refresh: true,
    
    // Aliases
    alias: {
        '@': '/resources',
        '@components': '/resources/js/Components',
        '@pages': '/resources/js/Pages',
    },
})
```

---

## Points d'Entrée

```typescript
// resources/js/app.tsx
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'tonka-vite-plugin/helpers';

createInertiaApp({
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.tsx`,
        import.meta.glob('./Pages/**/*.tsx', { eager: true })
    ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
```

---

## HMR (Hot Module Replacement)

### Configuration

```typescript
// vite.config.ts
export default defineConfig({
    plugins: [
        tonka({
            refresh: true, // Active HMR
        }),
        react(),
    ],
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
});
```

### Développement

```bash
npm run dev
```

### Tags Twig pour HMR

```twig
{# resources/views/app.twig #}
<!DOCTYPE html>
<html>
<head>
    @reactRefresh
    @vite('resources/css/app.css', 'resources/js/app.tsx')
    @inertiaHead
</head>
<body>
    @routes
    @inertia
</body>
</html>
```

---

## Build Production

### Commande de Build

```bash
# Build standard
npm run build

# Build avec optimisation
npm run build -- --mode production
```

### Structure du Build

```
public/build/
├── manifest.json          # Manifest des assets
├── assets/
│   ├── app-8f7a9b3c.js    # JavaScript compilé
│   ├── app-4d5e6f7g.css   # CSS compilé
│   └── vendor-123abc.js   # Vendor chunks
└── [autres fichiers]
```

---

## Intégration avec les Tags Twig

### @vite

```twig
{# Chargement du point d'entrée principal #}
@vite('resources/js/app.tsx')

{# Chargement d'un fichier CSS #}
@vite('resources/css/app.css')
```

### @reactRefresh

```twig
{# Activation du HMR pour React #}
@reactRefresh
```

---

## Optimisation

### Optimisation des Dépendances

```typescript
optimizeDeps: {
    include: [
        'react',
        'react-dom',
        '@inertiajs/react',
        'axios',
        'react-rnd',
    ],
    exclude: [],
}
```

### Aliases

```typescript
resolve: {
    alias: {
        '@': path.resolve(process.cwd(), 'resources/js'),
        '@components': path.resolve(process.cwd(), 'resources/js/Components'),
        '@pages': path.resolve(process.cwd(), 'resources/js/Pages'),
        '@css': path.resolve(process.cwd(), 'resources/css'),
    },
}
```

### Minification

```typescript
build: {
    minify: 'esbuild',
    target: 'es2015',
    sourcemap: process.env.NODE_ENV === 'development',
}
```

---

## Exemple Complet

### Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tonka from 'tonka-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        tonka({
            input: 'resources/js/app.tsx',
            ssr: false,
            refresh: true,
            alias: {
                '@': path.resolve(process.cwd(), 'resources/js'),
                '@components': path.resolve(process.cwd(), 'resources/js/Components'),
                '@pages': path.resolve(process.cwd(), 'resources/js/Pages'),
            },
        }),
        react(),
    ],
    define: {
        'process.env': {
            NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development'),
        },
    },
    optimizeDeps: {
        include: ['react', 'react-dom', '@inertiajs/react', 'axios'],
    },
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
    build: {
        minify: 'esbuild',
        target: 'es2015',
        sourcemap: false,
    },
});
```

### Package.json

```json
{
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview"
    },
    "devDependencies": {
        "vite": "^4.0.0",
        "tonka-vite-plugin": "^1.0.0",
        "@vitejs/plugin-react": "^4.0.0",
        "@types/react": "^18.0.0",
        "@types/react-dom": "^18.0.0"
    }
}
```

### Template

```twig
{# resources/views/app.twig #}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @inertiaHead
    @vite('resources/js/app.tsx')
    @reactRefresh
</head>
<body>
    @routes
    @inertia
</body>
</html>
```

### Développement

```bash
# Démarrer le serveur de développement
npm run dev

# Démarrer l'application Tonka
php tonka dev
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez Vite :

1. **[Broadcaster Client](broadcaster-client.md)** → Événements en temps réel
2. **[Helpers](helpers.md)** → Helpers disponibles
3. **[Facades](facades.md)** → Facades principales

---

**Vite compris !** 🚀 → [Broadcaster Client](broadcaster-client.md)