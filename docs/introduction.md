# Introduction

- [Introduction](#introduction)
- [Qu'est-ce que Tonka ?](#qu-39-est-ce-que-tonka-)
- [Philosophie](#philosophie)
  - [Simplicité](#1-strong-simplicite-strong-)
  - [Performance](#2-strong-performance-strong-)
  - [Flexibilité](#3-strong-flexibilite-strong-)
- [Pourquoi choisir Tonka ?](#pourquoi-choisir-tonka-)
  - [Pour les Développeurs Backend](#-strong-pour-les-developpeurs-backend-strong-)
  - [Pour les Développeurs Frontend](#-strong-pour-les-developpeurs-frontend-strong-)
  - [Pour les Équipes](#-strong-pour-les-equipes-strong-)
- [Fonctionnalités Clés](#fonctionnalites-cles)
- [Comparaison avec d'autres Frameworks](#comparaison-avec-d-39-autres-frameworks)
- [Versions et Dépendances](#versions-et-dependances)
  - [Prérequis Techniques](#prerequis-techniques)
  - [Dépendances Clés](#dependances-cles)
- [Prochaines Étapes](#prochaines-etapes)
- [Licence](#licence)

---

## Qu'est-ce que Tonka ?

**Tonka** est un framework PHP flexible et performant, conçu pour simplifier le processus de développement grâce à une architecture élégante et des standards modernes. Inspiré des meilleures pratiques de l'écosystème PHP, **Tonka** offre une suite complète d'outils qui permettent aux développeurs de concevoir des applications web robustes et scalables, tout en mettant l'accent sur un code propre et testable.

---

## Philosophie

Tonka repose sur trois piliers fondamentaux :

### 1. **Simplicité**
- Syntaxe intuitive et expressive
- Pas de configuration complexe
- Courbe d'apprentissage douce

### 2. **Performance**
- Optimisé pour les applications modernes
- Eager loading intelligent
- Système de cache intégré

### 3. **Flexibilité**
- Adaptable à vos besoins
- Extensible via Service Providers
- Compatible avec les outils existants

---

## Pourquoi choisir Tonka ?

### ✅ **Pour les Développeurs Backend**
- **ORM Elegant** : Un Eloquent-like avec auto-casting et auto-formatting
- **Système de validation multiniveau** : Route, Request, Entity
- **Routing avancé** : Builders personnalisables, middlewares, groupes
- **Scheduler intégré** : Tâches planifiées sans cron manuel
- **Messenger** : Système de queues robuste

### ✅ **Pour les Développeurs Frontend**
- **Inertia.js** : Pas d'API REST à maintenir
- **Spark** : Routes PHP disponibles côté JavaScript
- **DriftQL** : Requêtes sécurisées depuis le frontend
- **Broadcasting** : Temps réel avec Mercure (SSE)

### ✅ **Pour les Équipes**
- **Code structuré** : Architecture claire et modulaire
- **Tests intégrés** : Système de test simple et efficace
- **Documentation complète** : En français
- **Communauté active** : Support et évolutions

---

## Fonctionnalités Clés

| Fonctionnalité | Description |
|----------------|-------------|
| **Routing** | Routes flexibles avec middlewares et groupes |
| **Elegant ORM** | Modèles Eloquent-like avec auto-casting |
| **DriftQL** | Requêtes frontend sécurisées |
| **Broadcasting** | Temps réel avec Mercure |
| **Messenger** | Système de queues asynchrones |
| **Scheduler** | Tâches planifiées intégrées |
| **Validation** | Multiniveau (Route, Request, Entity) |
| **Console** | CLI complet avec commandes custom |
| **Tests** | Système de test intégré |

---

## Comparaison avec d'autres Frameworks

| Fonctionnalité | Tonka | Laravel | Symfony |
|----------------|-------|---------|---------|
| **ORM** | Elegant (auto-casting) | Eloquent | Doctrine |
| **Real-time** | Mercure (package) | Pusher (package) | Mercure (package) |
| **Routing** | Builders extensibles | Standard | Standard |
| **Validation** | 3 niveaux | 2 niveaux | 1 niveau |

---

## Versions et Dépendances

### Prérequis Techniques
```
PHP 8.4+
Composer 2.0+
MySQL 5.7+ / PostgreSQL 10+
```

### Dépendances Clés
```json
{
    "php": "^8.4",
    "symfony/console": "^6.0",
    "symfony/dependency-injection": "^7.3",
    "twig/twig": "^3.0"
}
```

---

## Prochaines Étapes

Maintenant que vous avez une vue d'ensemble de Tonka, vous pouvez :

1. **[Installer Tonka](installation.md)** → Démarrer votre premier projet
2. **[Comprendre la Structure](structure.md)** → Explorer l'architecture
3. **[Créer votre première route](routing.md)** → Commencer à coder
4. **[Découvrir Elegant](orm.md)** → Travailler avec la base de données

---

## Licence

Tonka est un framework open-source sous licence **MIT**.

```
MIT License

Copyright (c) 2025 Clicalmani

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
...
```

---

**Prêt à commencer ?** → [Installation](installation.md) 🚀