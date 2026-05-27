# kerberos-auth

Package Laravel d'authentification SSO Kerberos via la variable serveur `REMOTE_USER`.

## Fonctionnalités

- Authentification automatique via `REMOTE_USER` (Apache/Nginx Kerberos)
- Gestion des demandes d'accès pour les comptes sans rôle
- Mode simulation pour les environnements de développement
- Composants Livewire inclus (access-denied, request-access, simulate-kerberos, simulation-banner)
- Migrations, seeders et commandes artisan inclus

---

## Installation

### 1. Déclarer le dépôt dans `composer.json`

**Production (dépôt VCS privé) :**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/moko-github/kerberos-auth"
        }
    ]
}
```

**Développement (chemin local) :**

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/chemin/vers/kerberos-auth",
            "options": { "symlink": true }
        }
    ]
}
```

### 2. Installer le package

```bash
# Production
composer require moko-github/kerberos-auth

# Développement (path local, pas encore de tag)
composer require moko-github/kerberos-auth:@dev
```

### 3. Lancer l'installateur

```bash
php artisan kerberos:install
```

Sans option, la commande pose des questions interactives pour les seeders.
Sans réponse, les valeurs par défaut (entre crochets) s'appliquent.

```
◆ Installation de l'authentification Kerberos...
  ● Exécuter les seeders Kerberos ? (yes/no) [yes]
  ● Inclure le RolesSeeder (crée les rôles Admin et User) ? (yes/no) [yes]
```

Cette commande effectue automatiquement :
- Ajout des middlewares dans `bootstrap/app.php`
- Ajout des champs `kerberos` et `role_id` dans `app/Models/User.php`
- Ajout des routes dans `routes/web.php`
- Configuration du scheduler dans `routes/console.php`
- Ajout des variables d'environnement dans `.env`
- Exécution des migrations et des seeders (selon les réponses)

> Toutes les étapes sont **idempotentes** : relancer `kerberos:install` ne duplique rien.
> Si une injection automatique échoue, un message `⚠` indique les lignes à ajouter manuellement.

#### Options de la commande

| Option | Effet |
|---|---|
| _(aucune)_ | Questions interactives pour les seeders |
| `--no-seed` | Ignore **tous** les seeders sans poser de question |
| `--no-roles` | Ignore uniquement le `RolesSeeder` sans poser de question |

```bash
php artisan kerberos:install --no-seed    # migrations uniquement
php artisan kerberos:install --no-roles   # migrations + KerberosSetupSeeder uniquement
```

> Les flags ont la priorité sur les clés de config `install.run_seeders` et `install.seed_roles`.

---

## Seeders

### RolesSeeder

Crée deux rôles en base : **Admin** et **User**.

À utiliser si votre application s'appuie sur le système de rôles fourni par ce package (`MokoGithub\KerberosAuth\Models\Role`). À **ignorer** (`--no-roles`) si vous utilisez un autre système de rôles (Spatie Permission, rôles personnalisés, etc.).

### KerberosSetupSeeder

Crée un compte administrateur de test (`admin@example.com` / `password`) avec l'identifiant Kerberos `admin@krb.example.com`, et assigne le rôle **User** aux utilisateurs existants sans rôle.

À utiliser pour initialiser la base lors d'une première installation. À **ignorer** (`--no-seed`) si vous gérez vos propres données initiales.

> **Note :** si `App\Enums\UserStatus` existe dans l'application, le compte admin est créé avec le statut `ACTIVE`.

---

## Configuration

Publiez le fichier de configuration pour le personnaliser :

```bash
php artisan vendor:publish --tag=kerberos-config
```

Cela crée `config/kerberos.php` dans votre application.

### Variables d'environnement

```env
KERBEROS_ENABLED=false                    # Active l'authentification Kerberos
KERBEROS_SERVER_VAR=REMOTE_USER           # Variable serveur contenant le principal
KERBEROS_FALLBACK_AUTH=true               # Autorise la connexion classique en fallback
KERBEROS_SIMULATION_MODE=false            # Active le mode simulation (dév uniquement)
KERBEROS_ADMIN_EMAILS=                    # Emails admins (séparés par virgule)
KERBEROS_ADMIN_NOTIFICATION_MODE=immediate # 'immediate' ou 'disabled'
KERBEROS_AUTO_CLEANUP_DAYS=30             # Rétention des tentatives en jours
KERBEROS_ALLOWED_DOMAINS=                 # Domaines autorisés (vide = tous)
```

### Routes exclues

Par défaut, le middleware Kerberos exclut automatiquement ces routes :
`access-denied`, `access-request.create`, `access-request.store`, `logout`, `livewire.*`

Pour ajouter vos propres exclusions :

```php
// config/kerberos.php
'excluded_routes' => [
    'admin.*',      // toutes les routes d'admin
    'api.*',        // toutes les routes API
    'webhook.pay',  // une route spécifique
],
```

> Supports les wildcards (`*`) dans les noms de route.

### Stratégie de vérification des rôles

Définit comment le package détermine qu'un utilisateur est autorisé à se connecter.
Un utilisateur qui échoue ce contrôle reçoit le statut `NO_ROLE` et est redirigé vers le formulaire de demande d'accès.

#### `strategy: 'column'` _(défaut)_

Vérifie une colonne du modèle User avec un opérateur.

```php
'role_check' => [
    'strategy' => 'column',
    'column'   => 'role_id',       // colonne à tester
    'operator' => 'is_not_null',   // 'is_not_null' (défaut) | 'is_null'
],
```

| `operator` | Condition d'accès | Cas d'usage typique |
|---|---|---|
| `is_not_null` | `$user->role_id !== null` | Système mono-rôle (FK) |
| `is_null` | `$user->deleted_at === null` | Soft-delete comme garde d'accès |

Exemples :

```php
// Accès si l'utilisateur a un rôle assigné (comportement par défaut)
'role_check' => ['strategy' => 'column', 'column' => 'role_id', 'operator' => 'is_not_null'],

// Accès si l'utilisateur n'est pas supprimé (deleted_at IS NULL)
'role_check' => ['strategy' => 'column', 'column' => 'deleted_at', 'operator' => 'is_null'],
```

#### `strategy: 'relation'`

Convient aux applications avec des **rôles multiples** (table pivot, Spatie Permission, etc.).

```php
'role_check' => [
    'strategy' => 'relation',
    'relation' => 'roles',  // nom de la relation sur le modèle User
],
```

L'utilisateur est considéré **sans rôle** si `$user->roles()->exists()` retourne `false`.

Exemple avec Spatie Permission :
```php
'role_check' => ['strategy' => 'relation', 'relation' => 'roles'],
```

#### `strategy: 'callable'`

Pour toute logique métier arbitraire ou composite, déléguez le contrôle à une classe dédiée.

```php
'role_check' => [
    'strategy' => 'callable',
    'callable' => \App\Kerberos\MyAccessCheck::class,
],
```

La classe doit implémenter `MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface` :

```php
<?php

namespace App\Kerberos;

use App\Models\User;
use MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface;

class MyAccessCheck implements UserAccessCheckInterface
{
    public function check(User $user): bool
    {
        // true  → accès autorisé
        // false → redirigé vers le formulaire de demande d'accès
        return $user->deleted_at === null
            && $user->department !== 'EXTERN';
    }
}
```

> La classe est résolue via le conteneur Laravel : l'injection de dépendances fonctionne normalement.

### Seeders (via config)

Alternative aux flags artisan pour désactiver les seeders de façon permanente :

```php
'install' => [
    'run_seeders' => false,  // équivalent à --no-seed à chaque installation
    'seed_roles'  => false,  // équivalent à --no-roles à chaque installation
],
```

> Les flags `--no-seed` et `--no-roles` ont toujours la priorité sur ces clés.

---

## Composants Livewire

Les quatre composants sont enregistrés automatiquement. Aucune déclaration manuelle n'est nécessaire.

### `<livewire:auth.access-denied />`

Affiché quand un identifiant Kerberos est **inconnu** du système. Notifie l'utilisateur et informe que les administrateurs ont été alertés.

**Route auto-enregistrée :** `GET /acces-refuse` → `access-denied`

---

### `<livewire:auth.request-access />`

Formulaire pour les utilisateurs **reconnus mais sans rôle** (ou dont le contrôle d'accès échoue). Permet de soumettre une justification. Les administrateurs reçoivent une notification.

**Route auto-enregistrée :** `GET /demande-acces` → `access-request.create`

---

### `<livewire:auth.simulate-kerberos />`

Interface de simulation réservée aux environnements de développement.

**Prérequis :** `KERBEROS_SIMULATION_MODE=true` dans `.env`.

**À placer sur la page de connexion** :

```blade
<livewire:auth.simulate-kerberos />
```

Le composant se masque automatiquement si `KERBEROS_SIMULATION_MODE=false` ou `APP_ENV=production`.

---

### `<livewire:auth.simulation-banner />`

Bannière visible quand une simulation est active.

**À placer dans le layout principal** :

```blade
<livewire:auth.simulation-banner />
```

---

## Personnalisation des vues

```bash
php artisan vendor:publish --tag=kerberos-views
```

Copie les vues dans `resources/views/vendor/kerberos-auth/livewire/auth/`. Livewire cherche automatiquement dans ce dossier avant les vues du package.

---

## Mise à jour

```bash
composer update moko-github/kerberos-auth
php artisan migrate
```

---

## Commandes artisan

```bash
php artisan kerberos:install            # Installation initiale (interactif)
php artisan kerberos:install --no-seed  # Installation sans seeders
php artisan kerberos:install --no-roles # Installation sans RolesSeeder
php artisan kerberos:purge-attempts     # Purge les tentatives anciennes
```

`kerberos:purge-attempts` est automatiquement planifié à 03h00 après `kerberos:install`.

---

## Publication des ressources

```bash
php artisan vendor:publish --tag=kerberos-config   # config/kerberos.php
php artisan vendor:publish --tag=kerberos-views    # vues Blade personnalisables
php artisan vendor:publish --tag=kerberos-seeders  # seeders dans database/seeders/
```
