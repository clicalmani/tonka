# Broadcaster Client

- [Broadcaster Client](#broadcaster-client)
- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Configuration de Base](#configuration-de-base)
  - [Configuration avec Token](#configuration-avec-token)
- [Canaux Publics](#canaux-publics)
  - [Abonnement](#abonnement)
  - [Écoute d'Événements](#ecoute-d-39-evenements)
- [Canaux Privés](#canaux-prives)
  - [Abonnement](#abonnement)
  - [Écoute d'Événements](#ecoute-d-39-evenements)
- [Écoute Globale](#ecoute-globale)
- [Gestion des Tokens](#gestion-des-tokens)
  - [Token Statique](#token-statique)
  - [Token Dynamique](#token-dynamique)
- [Nettoyage](#nettoyage)
  - [Quitter un Canal](#quitter-un-canal)
  - [Fermeture Complète](#fermeture-complete)
- [Intégration React](#integration-react)
- [Intégration Vue](#integration-vue)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le **Broadcaster Client** est le package JavaScript qui permet de recevoir les événements broadcastés en temps réel via Mercure (SSE). Il s'utilise avec le package `tonka-broadcaster`.

**Fonctionnalités :**
- ✅ Canaux publics
- ✅ Canaux privés (JWT)
- ✅ Écoute d'événements
- ✅ Écoute globale (wildcard)
- ✅ Gestion des tokens
- ✅ Nettoyage automatique

---

## Installation

```bash
npm install tonka-broadcaster
```

---

## Configuration

### Configuration de Base

```typescript
// resources/js/broadcaster.ts
import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: import.meta.env.VITE_MERCURE_URL || 'http://localhost:3000/.well-known/mercure',
});

export default broadcaster;
```

### Configuration avec Token

```typescript
// resources/js/broadcaster.ts
import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: import.meta.env.VITE_MERCURE_URL || 'http://localhost:3000/.well-known/mercure',
    token: () => localStorage.getItem('tonka_jwt_token')
});

export default broadcaster;
```

---

## Canaux Publics

### Abonnement

```typescript
import broadcaster from './broadcaster';

// Abonnement à un canal public
const channel = broadcaster.channel('notifications');
```

### Écoute d'Événements

```typescript
// Écoute d'un événement spécifique
broadcaster.channel('notifications')
    .listen('NewNotification', (data) => {
        console.log('Notification reçue:', data);
        showNotification(data);
    });

// Écoute de plusieurs événements
broadcaster.channel('admin.dashboard')
    .listen('UserRegistered', (data) => {
        console.log('Nouvel utilisateur:', data);
        updateUserList(data);
    })
    .listen('OrderPlaced', (data) => {
        console.log('Nouvelle commande:', data);
        updateOrderCount(data);
    });
```

---

## Canaux Privés

### Abonnement

```typescript
import broadcaster from './broadcaster';

// Abonnement à un canal privé (préfixé par "private.")
const channel = broadcaster.private(`user.${userId}`);
```

### Écoute d'Événements

```typescript
// Canal privé utilisateur
broadcaster.private(`user.${userId}`)
    .listen('ProfileUpdated', (data) => {
        console.log('Profil mis à jour:', data);
        refreshProfile(data);
    })
    .listen('MessageReceived', (message) => {
        console.log('Nouveau message:', message);
        showMessageNotification(message);
    });

// Canal privé commande
broadcaster.private(`order.${orderId}`)
    .listen('OrderUpdated', (order) => {
        console.log(`Commande #${order.id} status: ${order.status}`);
        updateOrderStatus(order);
    })
    .listen('OrderShipped', (order) => {
        console.log(`Commande #${order.id} expédiée!`);
        showShippingNotification(order);
    });
```

---

## Écoute Globale

```typescript
// Écoute de tous les événements sur un canal
broadcaster.channel('admin.dashboard')
    .listen('*', (payload) => {
        console.log('Événement reçu:', payload);
        // Traitement générique
        handleAnyEvent(payload);
    });
```

---

## Gestion des Tokens

### Token Statique

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: 'your-jwt-token-here'
});
```

### Token Dynamique

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => localStorage.getItem('tonka_jwt_token')
});
```

### Token avec Rafraîchissement

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => {
        const token = localStorage.getItem('tonka_jwt_token');
        if (token && !isTokenExpired(token)) {
            return token;
        }
        return refreshToken();
    }
});
```

---

## Nettoyage

### Quitter un Canal

```typescript
// Quitter un canal public
broadcaster.leave('notifications');

// Quitter un canal privé
broadcaster.leave(`private.user.${userId}`);

// Quitter plusieurs canaux
broadcaster.leave('notifications');
broadcaster.leave('admin.dashboard');
broadcaster.leave(`private.user.${userId}`);
```

### Fermeture Complète

```typescript
// Récupérer le canal et le fermer
const channel = broadcaster.channel('notifications');
channel.close();

// Ou via leave
broadcaster.leave('notifications');
```

---

## Intégration React

```tsx
import React, { useEffect, useState } from 'react';
import broadcaster from '../broadcaster';

export function NotificationList({ userId }: { userId: number }) {
    const [notifications, setNotifications] = useState<string[]>([]);

    useEffect(() => {
        // Abonnement au canal privé
        const channel = broadcaster.private(`user.${userId}`);
        
        channel
            .listen('NewNotification', (data) => {
                setNotifications(prev => [data.message, ...prev]);
            })
            .listen('MessageReceived', (data) => {
                setNotifications(prev => [`📩 ${data.message}`, ...prev]);
            });

        // Nettoyage à la destruction
        return () => {
            broadcaster.leave(`private.user.${userId}`);
        };
    }, [userId]);

    return (
        <div>
            <h3>Notifications</h3>
            <ul>
                {notifications.map((msg, i) => (
                    <li key={i}>{msg}</li>
                ))}
            </ul>
        </div>
    );
}
```

---

## Intégration Vue

```vue
<template>
    <div>
        <h3>Notifications</h3>
        <ul>
            <li v-for="(msg, i) in notifications" :key="i">
                {{ msg }}
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import broadcaster from '../broadcaster';

const props = defineProps<{ userId: number }>();
const notifications = ref<string[]>([]);

let channel: any = null;

onMounted(() => {
    channel = broadcaster.private(`user.${props.userId}`);
    
    channel
        .listen('NewNotification', (data) => {
            notifications.value = [data.message, ...notifications.value];
        })
        .listen('MessageReceived', (data) => {
            notifications.value = [`📩 ${data.message}`, ...notifications.value];
        });
});

onUnmounted(() => {
    if (channel) {
        broadcaster.leave(`private.user.${props.userId}`);
    }
});
</script>
```

---

## Exemple Complet

### Backend

```php
// App\Broadcasting\UserRegistered.php
class UserRegistered implements ShouldBroadcastInterface
{
    public function broadcastOn(): array
    {
        return [
            'admin.users',
            'private.user.' . $this->userId
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.registered';
    }
}

// Dans le contrôleur
event(new UserRegistered($user->id, $user->email, $user->name));
```

### Frontend

```typescript
// resources/js/broadcaster.ts
import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: import.meta.env.VITE_MERCURE_URL || 'http://localhost:3000/.well-known/mercure',
    token: () => localStorage.getItem('tonka_jwt_token')
});

export default broadcaster;

// resources/js/app.tsx
import broadcaster from './broadcaster';

// Canal public (admins)
broadcaster.channel('admin.users')
    .listen('user.registered', (data) => {
        console.log('Nouvel utilisateur:', data);
        addUserToList(data);
    });

// Canal privé (utilisateur connecté)
broadcaster.private(`user.${userId}`)
    .listen('user.registered', (data) => {
        console.log('Bienvenue sur Tonka!');
        showWelcomeMessage(data);
    });
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez le Broadcaster Client :

1. **[Helpers](helpers.md)** → Helpers disponibles
2. **[Facades](facades.md)** → Facades principales

---

**Broadcaster Client compris !** 🚀 → [Helpers](helpers.md)