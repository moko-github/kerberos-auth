# kerberos-auth

Package Laravel d'authentification SSO Kerberos via la variable serveur `REMOTE_USER`.

## Fonctionnalités

- Authentification automatique via `REMOTE_USER` (Apache/Nginx Kerberos)
- Gestion des demandes d'accès pour les comptes sans rôle
- Mode simulation pour les environnements de développement
- Composants Livewire 4 inclus (access-denied, request-access, simulate-kerberos, simulation-banner)
- Migrations, seeders et commandes artisan inclus

## Installation

### 1. Déclarer le dépôt privé dans `composer.json`

Comme ce package est hébergé sur un dépôt Git privé, ajoutez-le dans la section `repositories` de l'application consommatrice :

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

### 2. Installer le package

```bash
composer require moko-github/kerberos-auth
```

### 3. Lancer l'installateur

```bash
php artisan kerberos:install
```

Cette commande :
- Configure les middlewares dans `bootstrap/app.php`
- Met à jour le modèle `User` avec les champs et la relation Kerberos
- Ajoute les routes nécessaires dans `routes/web.php`
- Configure le scheduler dans `routes/console.php`
- Ajoute les variables d'environnement dans `.env`
- Exécute les migrations et les seeders

## Mise à jour

```bash
composer update moko-github/kerberos-auth
php artisan migrate
```

## Configuration .env

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

## Commandes artisan

```bash
php artisan kerberos:install          # Installation initiale
php artisan kerberos:purge-attempts   # Purge les tentatives anciennes
```

## Scheduler

`kerberos:purge-attempts` est automatiquement planifié à 03h00 après `kerberos:install`.

## Publication des ressources (optionnel)

```bash
# Publier la configuration
php artisan vendor:publish --tag=kerberos-config

# Publier les seeders
php artisan vendor:publish --tag=kerberos-seeders
```
