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

Définit comment le package détermine qu'un utilisateur a un rôle. Un utilisateur sans rôle est redirigé vers le formulaire de demande d'accès (`NO_ROLE`).

#### `strategy: 'column'` _(défaut)_

Convient aux applications avec un **rôle unique** par utilisateur (colonne FK sur la table users).

```php
'role_check' => [
    'strategy' => 'column',
    'column'   => 'role_id',  // null = pas de rôle
],
```

L'utilisateur est considéré **sans rôle** si `$user->role_id === null`.

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
// config/kerberos.php
'role_check' => [
    'strategy' => 'relation',
    'relation' => 'roles',  // relation native de Spatie
],
```

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

Le middleware redirige automatiquement vers cette route. Aucune insertion manuelle requise.

---

### `<livewire:auth.request-access />`

Formulaire pour les utilisateurs **reconnus mais sans rôle**. Permet de soumettre une justification. Les administrateurs reçoivent une notification.

**Route auto-enregistrée :** `GET /demande-acces` → `access-request.create`

Le middleware redirige automatiquement vers cette route. Aucune insertion manuelle requise.

---

### `<livewire:auth.simulate-kerberos />`

Interface de simulation réservée aux environnements de développement. Permet de tester tous les flux Kerberos sans KDC réel.

**Prérequis :** `KERBEROS_SIMULATION_MODE=true` dans `.env`.

**À placer sur la page de connexion** (ex. `resources/views/livewire/auth/login.blade.php`) :

```blade
<livewire:auth.simulate-kerberos />
```

Le composant se masque automatiquement si `KERBEROS_SIMULATION_MODE=false` ou `APP_ENV=production`.

---

### `<livewire:auth.simulation-banner />`

Bannière visible en haut de page quand une simulation est active. Affiche l'identifiant simulé et propose un bouton pour quitter.

**À placer dans le layout principal** (ex. `resources/views/components/layouts/app.blade.php`) :

```blade
<livewire:auth.simulation-banner />
```

La bannière est invisible si aucune simulation n'est active.

---

## Personnalisation des vues

Pour adapter les composants à votre stack CSS (Bootstrap, Bulma, etc.) :

```bash
php artisan vendor:publish --tag=kerberos-views
```

Cela copie les vues dans `resources/views/vendor/kerberos-auth/livewire/auth/` :

```
resources/views/vendor/kerberos-auth/
└── livewire/
    └── auth/
        ├── access-denied.blade.php
        ├── request-access.blade.php
        ├── simulate-kerberos.blade.php
        └── simulation-banner.blade.php
```

Livewire cherche automatiquement dans ce dossier avant d'utiliser les vues du package. Les modifications sont immédiatement prises en compte.

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
