# Starter Kits

- [Starter Kits](#starter-kits)
- [Astro (React)](#astro-react-)
  - [Installation](#installation)
  - [Structure](#structure)
  - [Composants](#composants)
- [Comet (Vue)](#comet-vue-)
  - [Installation](#installation-2)
  - [Structure](#structure-1)
  - [Composants](#composants-1)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Tonka propose **deux starter kits** pour vous aider à démarrer rapidement vos projets :

| Starter Kit | Framework | Description |
|-------------|-----------|-------------|
| **Astro** | React | Starter kit moderne avec React et TypeScript |
| **Comet** | Vue | Starter kit élégant avec Vue 3 et TypeScript |

---

## Astro (React)

### Installation

```bash
composer create-project clicalmani/tonka mon-projet --prefer-dist
cd mon-projet
php tonka key:generate
npm install
```

### Structure

```
mon-projet/
├── resources/
│   └── js/
│       ├── app.tsx                 # Point d'entrée
│       ├── Pages/
│       │   ├── Home.tsx
│       │   ├── About.tsx
│       │   └── Dashboard.tsx
│       ├── Components/
│       │   ├── Layout.tsx
│       │   ├── Navbar.tsx
│       │   └── Footer.tsx
│       ├── Hooks/
│       │   ├── useAuth.ts
│       │   └── useFetch.ts
│       └── Types/
│           └── index.ts
├── vite.config.ts
├── package.json
└── tsconfig.json
```

### Composants

```tsx
// resources/js/Pages/Home.tsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Layout from '../Components/Layout';

interface Props {
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

export default function Home({ user }: Props) {
    return (
        <>
            <Head title="Accueil" />
            
            <Layout>
                <div className="hero-section">
                    <h1>Bienvenue sur Tonka Astro</h1>
                    {user ? (
                        <p>Bonjour {user.name} !</p>
                    ) : (
                        <Link href="/login">Se connecter</Link>
                    )}
                </div>
            </Layout>
        </>
    );
}
```

```tsx
// resources/js/Components/Layout.tsx
import React, { ReactNode } from 'react';
import Navbar from './Navbar';
import Footer from './Footer';

interface Props {
    children: ReactNode;
}

export default function Layout({ children }: Props) {
    return (
        <div className="app-layout">
            <Navbar />
            <main className="container mx-auto px-4 py-8">
                {children}
            </main>
            <Footer />
        </div>
    );
}
```

```tsx
// resources/js/Components/Navbar.tsx
import React from 'react';
import { Link } from '@inertiajs/react';

export default function Navbar() {
    return (
        <nav className="bg-white shadow-md">
            <div className="container mx-auto px-4 py-3 flex justify-between">
                <Link href="/" className="font-bold text-xl">
                    Tonka Astro
                </Link>
                <div className="space-x-4">
                    <Link href="/about">À propos</Link>
                    <Link href="/dashboard">Dashboard</Link>
                </div>
            </div>
        </nav>
    );
}
```

### Configuration Vite

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tonka from 'tonka-vite-plugin';

export default defineConfig({
    plugins: [
        tonka({
            input: 'resources/js/app.tsx',
            ssr: false,
            refresh: true,
        }),
        react(),
    ],
});
```

---

## Comet (Vue)

### Installation
<div id="installation-2"></div>

```bash
composer create-project clicalmani/tonka mon-projet --prefer-dist
cd mon-projet
php tonka key:generate
npm install
```

### Structure
<div id="structure-1"></div>

```
mon-projet/
├── resources/
│   └── js/
│       ├── app.ts                   # Point d'entrée
│       ├── Pages/
│       │   ├── Home.vue
│       │   ├── About.vue
│       │   └── Dashboard.vue
│       ├── Components/
│       │   ├── Layout.vue
│       │   ├── Navbar.vue
│       │   └── Footer.vue
│       ├── Composables/
│       │   ├── useAuth.ts
│       │   └── useFetch.ts
│       └── Types/
│           └── index.ts
├── vite.config.ts
├── package.json
└── tsconfig.json
```

### Composants
<div id="composants-1"></div>

```vue
<!-- resources/js/Pages/Home.vue -->
<template>
    <Head title="Accueil" />
    
    <Layout>
        <div class="hero-section">
            <h1>Bienvenue sur Tonka Comet</h1>
            <p v-if="user">Bonjour {{ user.name }} !</p>
            <Link v-else href="/login">Se connecter</Link>
        </div>
    </Layout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Layout from '../Components/Layout.vue';

defineProps<{
    user?: {
        id: number;
        name: string;
        email: string;
    };
}>();
</script>
```

```vue
<!-- resources/js/Components/Layout.vue -->
<template>
    <div class="app-layout">
        <Navbar />
        <main class="container mx-auto px-4 py-8">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<script setup lang="ts">
import Navbar from './Navbar.vue';
import Footer from './Footer.vue';
</script>
```

```vue
<!-- resources/js/Components/Navbar.vue -->
<template>
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between">
            <Link href="/" class="font-bold text-xl">
                Tonka Comet
            </Link>
            <div class="space-x-4">
                <Link href="/about">À propos</Link>
                <Link href="/dashboard">Dashboard</Link>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
```

### Configuration Vite

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tonka from 'tonka-vite-plugin';

export default defineConfig({
    plugins: [
        tonka({
            input: 'resources/js/app.ts',
            ssr: false,
            refresh: true,
        }),
        vue(),
    ],
});
```

### Point d'Entrée Vue

```typescript
// resources/js/app.ts
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'tonka-vite-plugin/helpers';

createInertiaApp({
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

---

## Comparaison

| Fonctionnalité | Astro (React) | Comet (Vue) |
|----------------|---------------|-------------|
| **Framework** | React | Vue 3 |
| **TypeScript** | ✅ | ✅ |
| **Composables/Hooks** | Hooks | Composables |
| **Syntaxe** | JSX | Template |
| **Point d'entrée** | app.tsx | app.ts |
| **Extension** | .tsx | .vue |

---

## Prochaines Étapes

Maintenant que vous connaissez les starter kits :

1. **[Spark](spark.md)** → Routage frontend
2. **[DriftQL](driftql.md)** → Requêtes frontend sécurisées
3. **[Vite](vite.md)** → Build tool

---

**Starter kits présentés !** 🚀 → [Spark](spark.md)