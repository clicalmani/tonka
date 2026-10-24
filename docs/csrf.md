# Protection CSRF

- [Protection CSRF](#protection-csrf)
- [Introduction](#introduction)
- [Explication de la Vulnérabilité](#explication-de-la-vulnerabilite)
- [Fonctionnement d'une Attaque CSRF](#fonctionnement-d-39-une-attaque-csrf)
- [Stratégies de Mitigation](#strategies-de-mitigation)

---

## Introduction

Le **Cross-Site Request Forgery (CSRF)** est une attaque par laquelle des commandes non autorisées sont transmises à une application web au nom d'un utilisateur en qui elle a confiance, sans que celui-ci en ait conscience. Les attaquants exploitent généralement pour cela les cookies de session que le navigateur joint automatiquement à chaque requête sortante vers le domaine concerné.

---

## Explication de la Vulnérabilité

Supposons que votre application soit un portail bancaire. Un utilisateur se connecte à son compte puis, sa session étant toujours active, ouvre un autre onglet pour consulter ses e-mails personnels. L'un de ces e-mails contient un lien malveillant.

Si l'utilisateur clique sur ce lien, le navigateur joindra automatiquement ses cookies de session — y compris ceux associés à l'application bancaire — à la requête sortante. Si la session de l'utilisateur repose sur un cookie persistant (par exemple parce qu'il a coché une option « Se souvenir de moi »), le site malveillant peut exploiter ce cookie pour détourner la session de l'utilisateur et exécuter des actions non autorisées en son nom.

---

## Fonctionnement d'une Attaque CSRF

1. **Authentification de l'utilisateur** : l'utilisateur se connecte à une application web et reçoit un jeton d'authentification (généralement un cookie de session stocké dans le navigateur).
2. **Élaboration de la requête malveillante** : un attaquant construit une requête ciblant l'application web et l'intègre dans un lien, un bouton ou un formulaire sur un site externe non fiable.
3. **Interaction de l'utilisateur** : toujours authentifié sur l'application cible, l'utilisateur visite le site de l'attaquant et déclenche sans le savoir le lien ou le formulaire piégé.
4. **Exécution** : le navigateur joint automatiquement les cookies de l'application cible à la requête. Cette dernière traite alors la requête comme si elle avait été volontairement initiée par l'utilisateur authentifié, provoquant des actions non désirées (virement de fonds, modification des informations du compte, etc.).

---

## Stratégies de Mitigation

**Tonka** génère automatiquement un jeton CSRF cryptographiquement sûr pour chaque session utilisateur active gérée par l'application. Ce jeton permet de vérifier que l'utilisateur authentifié est bien à l'origine de la requête entrante. Stocké côté serveur dans la session et renouvelé périodiquement, ce jeton ne peut être ni lu ni dérobé par une application tierce.

Pour protéger votre application, ce jeton doit être inclus dans tout formulaire HTML déclenchant une modification d'état (`POST`, `PUT`, `PATCH`, `DELETE`), afin de garantir la légitimité de la requête soumise.

Vous pouvez insérer ce jeton simplement, grâce à la directive de template `@csrf` :

```twig
<form method="POST" action="/profile">
    @csrf
    <!-- Champs du formulaire -->
</form>
```

Sous le capot, la directive Twig `@csrf` s'appuie sur le helper `csrf_token()` pour générer automatiquement un champ caché dans le formulaire :

```html
<form method="POST" action="/profile">
    <input type="hidden" name="csrf_token" value="089989098056565564644626654664646468906504498660103156" />
    <!-- Champs du formulaire -->
</form>
```

> **Pour les développeurs Laravel/Symfony :** la directive `@csrf` et le helper `csrf_token()` reprennent directement les conventions de Laravel (`@csrf` / `csrf_token()`) ; Symfony expose un mécanisme équivalent via la fonction Twig `csrf_token(id)`, à la différence près qu'elle nécessite de préciser un identifiant d'intention (`csrf_token('profile-update')`) plutôt qu'un jeton global par session.

En imposant systématiquement la vérification de ce jeton sur vos routes sensibles, votre application réduit considérablement le risque d'actions non autorisées déclenchées depuis un site tiers.

> **À distinguer de la prévention de manipulation de route :** la protection CSRF garantit que la requête émane bien de votre propre application (et non d'un site tiers exploitant la session active de l'utilisateur), tandis que le middleware [`PreventRouteTampering`](routing.md#prévention-de-la-manipulation-de-route) garantit l'intégrité de paramètres spécifiques transmis dans l'URI. Les deux mécanismes sont complémentaires et répondent à des menaces différentes : l'un protège contre une requête forgée depuis l'extérieur, l'autre contre la falsification de valeurs à l'intérieur d'une requête par ailleurs légitime.