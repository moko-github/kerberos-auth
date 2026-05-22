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

**Environnement de production (dépôt VCS privé) :**

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

**Environnement de développement (chemin local) :**

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

Cette commande effectue automatiquement :
- Ajout des middlewares dans `bootstrap/app.php`
- Ajout des champs `kerberos` et `role_id` dans `app/Models/User.php`
- Ajout des routes dans `routes/web.php`
- Configuration du scheduler dans `routes/console.php`
- Ajout des variables d'environnement dans `.env`
- Exécution des migrations et des seeders

---

## Composants Livewire

Les quatre composants sont enregistrés automatiquement par le `KerberosServiceProvider`. Aucune déclaration manuelle n'est nécessaire.

### `<livewire:auth.access-denied />`

Affiché quand un identifiant Kerberos est inconnu du système. Notifie l'utilisateur et informe qu'une alerte a été envoyée aux administrateurs.

**Route enregistrée automatiquement par `kerberos:install` :**
```
GET /acces-refuse  →  name: access-denied
```

Le composant utilise le layout `layouts.auth`. Il est redirigé automatiquement par le middleware `KerberosAuthentication` en cas d'identifiant inconnu.

---

### `<livewire:auth.request-access />`

Formulaire permettant à un utilisateur reconnu mais sans rôle de soumettre une justification d'accès. Les administrateurs reçoivent une notification.

**Route enregistrée automatiquement par `kerberos:install` :**
```
GET /demande-acces  →  name: access-request.create
```

Le composant utilise le layout `layouts.auth`. Il est redirigé automatiquement par le middleware en cas de compte sans rôle.

---

### `<livewire:auth.simulate-kerberos />`

Interface de simulation Kerberos réservée aux environnements de développement et pré-production. Permet de simuler une connexion avec n'importe quel identifiant Kerberos sans KDC réel.

**Activer la simulation dans `.env` :**
```env
KERBEROS_SIMULATION_MODE=true
```

**Placer le composant sur la page de connexion** (ex. `resources/views/livewire/auth/login.blade.php`) :
```blade
<livewire:auth.simulate-kerberos />
```

> Le composant se masque automatiquement si `KERBEROS_SIMULATION_MODE=false` ou si `APP_ENV=production`.

---

### `<livewire:auth.simulation-banner />`

Bannière affichée en haut de l'application quand une simulation Kerberos est active. Indique l'identifiant simulé et propose un bouton pour quitter la simulation.

**Placer le composant dans le layout principal** (ex. `resources/views/components/layouts/app.blade.php`) :
```blade
<livewire:auth.simulation-banner />
```

> La bannière est invisible si aucune simulation n'est active.

---

## Mise à jour

```bash
composer update moko-github/kerberos-auth
php artisan migrate
```

---

## Configuration `.env`

```env
KERBEROS_ENABLED=false
KERBEROS_SERVER_VAR=REMOTE_USER
KERBEROS_FALLBACK_AUTH=true
KERBEROS_SIMULATION_MODE=false
KERBEROS_ADMIN_EMAILS=
KERBEROS_ADMIN_NOTIFICATION_MODE=immediate
KERBEROS_AUTO_CLEANUP_DAYS=30
KERBEROS_ALLOWED_DOMAINS=
```

---

## Commandes artisan

```bash
php artisan kerberos:install          # Installation initiale
php artisan kerberos:purge-attempts   # Purge les tentatives anciennes
```

`kerberos:purge-attempts` est automatiquement planifié à 03h00 après `kerberos:install`.

---

## Publication des ressources (optionnel)

```bash
# Publier la configuration
php artisan vendor:publish --tag=kerberos-config

# Publier les seeders
php artisan vendor:publish --tag=kerberos-seeders
```
