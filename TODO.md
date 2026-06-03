# TODO — Chantiers d'amélioration du package

Issu de l'audit du 2026-06-03. Classé par criticité.

---

## 🔴 Critique — bloque la réutilisabilité

### [1] Découplage du modèle User ✅ FAIT (2026-06-03)
**Fichiers impactés :** `src/Services/KerberosAuthService.php`, `src/Services/AccessRequestService.php`,
`src/DTOs/AuthResult.php`, `src/Contracts/UserAccessCheckInterface.php`,
`src/Models/AccessRequest.php`, `src/Models/Role.php`,
`src/Livewire/Auth/SimulateKerberos.php`, `src/Livewire/Auth/RequestAccess.php`,
`database/seeders/KerberosSetupSeeder.php`

- Remplacer tous les `use App\Models\User` par une résolution dynamique via
  `config('auth.providers.users.model')` + clé override `kerberos.user_model`
- `UserAccessCheckInterface::check()` et `AuthResult::$user` → typer sur
  `Illuminate\Contracts\Auth\Authenticatable` au lieu de `App\Models\User`

### [2] Routes `dashboard` et `login` codées en dur ✅ FAIT (2026-06-03)
**Fichiers impactés :** `src/Http/Middleware/KerberosAuthentication.php` (ligne 57),
`src/Livewire/Auth/SimulateKerberos.php` (ligne 58),
`src/Livewire/Auth/AccessDenied.php` (lignes 16, 24),
`src/Livewire/Auth/RequestAccess.php` (ligne 25),
`src/Notifications/AccessRequestAcceptedNotification.php` (ligne 36),
`src/Livewire/Auth/SimulationBanner.php` (ligne 34)

- Ajouter dans `config/kerberos.php` :
  ```php
  'redirects' => ['success' => 'dashboard', 'login' => 'login'],
  ```
- Remplacer tous les `route('dashboard')` / `route('login')` codés en dur

### [3] Harnais de tests (zéro test exécutable en l'état) ✅ FAIT (2026-06-03)
> Bonus : correction des `down()` des migrations roles/kerberos (drop index/FK
> avant la colonne) — révélé par les tests, bug réel sur SQLite.
**Fichiers impactés :** `composer.json`, `tests/Feature/KerberosSetupSeederTest.php`

- Ajouter `orchestra/testbench` + `pestphp/pest` en `require-dev`
- Créer `phpunit.xml` (ou `pest.config.php`) + `TestCase` de base étendant Testbench
- Corriger `KerberosSetupSeederTest` (mauvais namespaces : `App\Models\Role` → `MokoGithub\…\Role`,
  `Database\Seeders\KerberosSetupSeeder` → `MokoGithub\…\Database\Seeders\KerberosSetupSeeder`)
- Écrire les tests manquants : `KerberosAuthService::authenticate()` (5 cas),
  middleware `KerberosAuthentication` (success / no_role / unknown / disabled),
  stratégies `role_check` (column / relation / callable),
  `AccessRequestService::approve()` + `reject()`

---

## 🟠 Important — config morte & incohérences

### [4] Clés de config documentées mais jamais utilisées
- **`fallback_auth`** ✅ FAIT (2026-06-03) : consommée dans le middleware
  (`handleNoKerberos`) — `false` = Kerberos strict (403), `true` = login de secours.
- **`admin_notification_emails`** ✅ FAIT (2026-06-03) : consommée via `notifyAdmins()`
  — si remplie, notification on-demand par mail ; sinon, users du rôle admin.
- **`allowed_domains`** ⏸️ EN ATTENTE : environnement mono-domaine Kerberos chez le
  client (filtré en amont par le serveur web). À implémenter **si** passage en
  multi-realm un jour. Sinon, candidate à suppression.
  → validation dans `authenticate()` : extraire le domaine du principal
  (`user@DOMAIN`) et vérifier contre la whitelist.

### [5] `getAdminUsers()` : couplage au rôle 'Admin' ✅ FAIT (2026-06-03)
**Fichier :** `src/Services/KerberosAuthService.php`

- Nom du rôle admin désormais configurable via `kerberos.admin_role` (défaut 'Admin')
- Pour les stratégies `relation` / `callable` (Spatie, custom) qui n'ont pas la
  relation `role`, renseigner `admin_notification_emails` (notification on-demand)
  — documenté dans la config.

### [6] `logAttempt($user)` ignore son paramètre `$user`
**Fichier :** `src/Services/KerberosAuthService.php` ligne 127,
`database/migrations/2025_11_18_100002_create_kerberos_attempts_table.php`

- Ajouter colonne `user_id` nullable avec FK sur `users` dans la migration
- Stocker `$user?->id` dans `KerberosAttempt::create()`

---

## 🟡 Améliorable — qualité & sécurité

### [7] Seeder `KerberosSetupSeeder` — compte admin/password en prod
**Fichier :** `database/seeders/KerberosSetupSeeder.php`

- Ajouter gate `if (app()->environment('production')) { return; }` ou warning explicite
- Documenter clairement que ce seeder est réservé au développement/staging

### [8] `$_SERVER` direct au lieu de `request()->server()`
**Fichier :** `src/Services/KerberosAuthService.php` ligne 23

- Remplacer `$_SERVER[$serverVar] ?? null` par `request()->server($serverVar)`

### [9] Internationalisation incohérente (FR/EN mélangés, chaînes en dur)
- Créer `resources/lang/fr/kerberos.php` et `resources/lang/en/kerberos.php`
- Publier via `--tag=kerberos-lang`
- Passer tous les messages user-facing par `__('kerberos-auth::kerberos.xxx')`

### [10] `declare(strict_types=1)` partiel (Notifications uniquement)
- Ajouter `declare(strict_types=1)` dans tous les fichiers PHP du package

### [11] Prérequis non documentés dans le README
- Canal `database` des notifications → exige migration `notifications` + trait `Notifiable`
- `remember: true` forcé au login → rendre configurable via `kerberos.remember_me`
- Documenter ces prérequis dans le README (section "Prérequis")

---

## 🔵 Outillage / CI

### [12] CI GitHub Actions
- Créer `.github/workflows/tests.yml` : PHP 8.2/8.3 × Laravel 12
- Lancer `pest` + `phpstan` sur chaque PR

### [13] `composer.json` incomplet
- Ajouter `require-dev` : `orchestra/testbench`, `pestphp/pest`, `phpstan/phpstan`,
  `laravel/pint`
- Ajouter `scripts` : `test`, `analyse`, `format`
- Ajouter `authors`, `keywords`, `support`

### [14] Versioning & CHANGELOG
- Créer `CHANGELOG.md`
- Mettre en place une stratégie de tags/releases (SemVer)
