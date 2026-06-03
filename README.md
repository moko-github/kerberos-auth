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

Sans option, la commande pose deux questions interactives.
Sans réponse, les valeurs par défaut (entre crochets) s'appliquent.

```
◆ Installation de l'authentification Kerberos...
  ● Installer le système de rôles ? (table roles + colonne role_id sur users) (yes/no) [yes]
  ...migrations...
  ● Exécuter les seeders Kerberos ? (yes/no) [yes]
```

La question sur les rôles est posée **avant** les migrations, ce qui garantit que la colonne `role_id` n'est ajoutée à la table `users` que si vous en avez besoin.

Cette commande effectue automatiquement :
- Ajout des middlewares dans `bootstrap/app.php`
- Ajout du champ `kerberos` (et `role_id` si rôles activés) dans `app/Models/User.php`
- Ajout des routes dans `routes/web.php`
- Configuration du scheduler dans `routes/console.php`
- Ajout des variables d'environnement dans `.env`
- Exécution des migrations et des seeders (selon les réponses)

> Toutes les étapes sont **idempotentes** : relancer `kerberos:install` ne duplique rien.
> Si une injection automatique échoue, un message `⚠` indique les lignes à ajouter manuellement.

#### Options de la commande

| Option | Effet |
|---|---|
| _(aucune)_ | Questions interactives |
| `--no-roles` | Ignore le système de rôles (migration `roles` + `role_id` + `RolesSeeder`) |
| `--no-seed` | Ignore **tous** les seeders sans poser de question (les migrations s'exécutent quand même) |

```bash
php artisan kerberos:install --no-roles   # sans système de rôles
php artisan kerberos:install --no-seed    # migrations uniquement, sans seeders
```

> Les flags ont la priorité sur les clés de config `install.run_seeders` et `install.seed_roles`.

---

## Seeders

### RolesSeeder

Crée deux rôles en base : **Admin** et **User**.

Exécuté uniquement si vous avez répondu **oui** à la question sur le système de rôles (ou si `--no-roles` n'est pas passé). À ignorer si vous utilisez un autre système de rôles (Spatie Permission, rôles personnalisés, etc.).

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

### Modèle utilisateur

Le package ne présume **pas** que votre modèle utilisateur est `App\Models\User`.
Il le résout automatiquement dans cet ordre :

1. `config('kerberos.user_model')` (override explicite)
2. `config('auth.providers.users.model')` (défaut Laravel)
3. `App\Models\User` (dernier recours)

Aucune configuration n'est nécessaire dans la majorité des cas. Pour forcer un modèle
spécifique :

```php
// config/kerberos.php
'user_model' => \App\Models\Account::class,
```

### Routes de redirection

Le package redirige vers des routes nommées de votre application. Par défaut
`dashboard` (après login réussi) et `login` (accès refusé, fin de simulation, etc.).
Si vos routes portent d'autres noms :

```php
// config/kerberos.php
'redirects' => [
    'success' => 'home',        // après authentification réussie
    'login'   => 'auth.login',  // route de connexion / fallback
],
```

ou via `.env` : `KERBEROS_SUCCESS_ROUTE` et `KERBEROS_LOGIN_ROUTE`.

### Variables d'environnement

```env
KERBEROS_ENABLED=false                    # Active l'authentification Kerberos
KERBEROS_SERVER_VAR=REMOTE_USER           # Variable serveur contenant le principal
KERBEROS_FALLBACK_AUTH=true               # true = login classique en secours ; false = Kerberos strict (403 sans ticket)
KERBEROS_SIMULATION_MODE=false            # Active le mode simulation (dév uniquement)
KERBEROS_ADMIN_ROLE=Admin                 # Nom du rôle admin (destinataires des notifications)
KERBEROS_ADMIN_EMAILS=                    # Emails admins (virgule). Si renseigné, notifie ces adresses ; sinon les users du rôle admin
KERBEROS_ADMIN_NOTIFICATION_MODE=immediate # 'immediate' ou 'disabled'
KERBEROS_AUTO_CLEANUP_DAYS=30             # Rétention des tentatives en jours
KERBEROS_ALLOWED_DOMAINS=                 # (non implémenté — réservé multi-realm)
```

> **`KERBEROS_FALLBACK_AUTH=false`** impose Kerberos : une requête sans ticket reçoit
> un `403`. Avec `true` (défaut), l'utilisateur sans ticket atteint le formulaire de
> connexion classique de votre application.
>
> **Notifications admin :** avec `KERBEROS_ADMIN_EMAILS` renseigné, les emails sont
> envoyés directement à ces adresses (mail on-demand, même sans compte User). Sinon,
> les utilisateurs portant le rôle `KERBEROS_ADMIN_ROLE` sont notifiés. Si vous utilisez
> une stratégie de rôle `relation` / `callable`, privilégiez `KERBEROS_ADMIN_EMAILS`.

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

### Layout des pages Kerberos

Les pages `/demande-acces` et `/acces-refuse` utilisent par défaut le layout minimal embarqué dans le package (`kerberos-auth::layouts.guest`) — une page blanche centrée qui ne nécessite que Tailwind CSS.

**Pour utiliser le layout de votre application :**

```php
// config/kerberos.php
'layout' => 'layouts.auth',           // layout Laravel standard
'layout' => 'components.layouts.app', // layout Livewire Volt
'layout' => 'layouts.guest',          // votre propre layout guest
```

**Pour personnaliser le layout du package :**

```bash
php artisan vendor:publish --tag=kerberos-views
# → resources/views/vendor/kerberos-auth/layouts/guest.blade.php
```

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

#### `strategy: 'relation'`

```php
'role_check' => [
    'strategy' => 'relation',
    'relation' => 'roles',
],
```

#### `strategy: 'callable'`

```php
'role_check' => [
    'strategy' => 'callable',
    'callable' => \App\Kerberos\MyAccessCheck::class,
],
```

La classe doit implémenter `MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface` :

```php
class MyAccessCheck implements UserAccessCheckInterface
{
    public function check(User $user): bool
    {
        return $user->deleted_at === null && $user->department !== 'EXTERN';
    }
}
```

### Seeders (via config)

```php
'install' => [
    'run_seeders' => false,
    'seed_roles'  => false,
],
```

---

## Composants Livewire

### `<livewire:auth.access-denied />`

Affiché quand un identifiant Kerberos est **inconnu** du système.
**Route :** `GET /acces-refuse` → `access-denied`

### `<livewire:auth.request-access />`

Formulaire pour les utilisateurs **reconnus mais sans rôle**.
**Route :** `GET /demande-acces` → `access-request.create`

### `<livewire:auth.simulate-kerberos />`

Interface de simulation réservée au développement. **Prérequis :** `KERBEROS_SIMULATION_MODE=true`.

```blade
<livewire:auth.simulate-kerberos />
```

### `<livewire:auth.simulation-banner />`

Bannière visible quand une simulation est active. **À placer dans le layout principal.**

```blade
<livewire:auth.simulation-banner />
```

---

## Personnalisation des vues

```bash
php artisan vendor:publish --tag=kerberos-views
```

Copie dans `resources/views/vendor/kerberos-auth/` :
```
├── layouts/
│   └── guest.blade.php          # layout par défaut des pages Kerberos
└── livewire/auth/
    ├── access-denied.blade.php
    ├── request-access.blade.php
    ├── simulate-kerberos.blade.php
    └── simulation-banner.blade.php
```

---

## Développement & tests

```bash
composer install
composer test        # ou : vendor/bin/pest
```

Les tests s'appuient sur Orchestra Testbench + Pest, avec une base SQLite en
mémoire et un modèle utilisateur de fixture (`tests/Fixtures/User.php`).

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

---

## Publication des ressources

```bash
php artisan vendor:publish --tag=kerberos-config   # config/kerberos.php
php artisan vendor:publish --tag=kerberos-views    # vues + layout guest
php artisan vendor:publish --tag=kerberos-seeders  # seeders
```
