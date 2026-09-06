# Login Suite

A PHP authentication starter kit. Drop it into a project that needs real
accounts without pulling in a framework.

Sessions live in the database, so they can be listed, revoked and expired.
Tokens are single use. Passwords, secrets and session tokens are hashed or
encrypted at rest. There is a user area and an admin panel with separate
logins.

Requires **PHP 8.0+** (developed and tested on 8.2) and **MySQL / MariaDB**.
One dependency: PHPMailer.

---

## Setup

**1.** Create `includes/inc/env.php`:

```php
<?php
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_NAME", "your_database");
define("DB_PASSWORD", "");
define("ENV", "local");                      // local | dev | prod
define("SITE_URL", "http://localhost/your-project");

// Encrypts settings stored in the database. Keep it, and keep it out of git.
define("APP_KEY", "");                       // 64 random hex characters
```

Generate the key:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

> **Losing `APP_KEY` makes every encrypted setting unreadable.** There is no
> recovery. Each environment needs its own.

**2.** Install and create the tables:

```bash
composer install
cd includes && php sql.php
```

`sql.php` is safe to re-run. It creates what is missing and leaves the rest
alone.

**3.** Set your SMTP details in the admin panel under **Settings**, or in
`includes/inc/globals.php` if you would rather keep them in code.

---

## What you get

**Sessions** — a row per signed-in device, not a PHP session. Two independent
deadlines: idle (2h) and absolute (7d), whichever comes first. The cookie holds
a 64-char random token; only its SHA-256 reaches the database.

**Remember me** — a second, longer-lived token that revives an expired session.
Single use: redeeming it issues a fresh pair, so a stolen cookie works at most
once.

**Devices** — users see where they are signed in and can sign out one device or
all the others.

**Login protection** — 5 attempts per email and 20 per IP over 15 minutes. A
correct password is refused while blocked, so the limit cannot be used to
confirm a guess.

**Tokens** — email verification and password reset are separate typed rows with
their own expiry, single use, hashed. A password reset signs out every device.

**CSRF** — a token on every state-changing request, from the session row once
signed in and a readable cookie before that.

**Settings** — key/value config in the database, editable from the admin panel.
Anything listed in `SETTINGS_SECRETS` is encrypted with AES-256-GCM.

**Cron** — a self-contained component for scheduled work. See below.

**Two panels** — a user area for profile, password and devices; an admin panel
for users, settings and cron, each with its own login.

---

## Structure

```
includes/
  Classes/         Auth, Session, Token, Guard, Settings, Emails
  inc/
    env.php        secrets, gitignored
    config.php     site name, auth tuning
    globals.php    paths, SMTP, which settings are secret
  templates/       email bodies, one file each
  sql.php          the installer

controllers/       public post handlers
views/             public pages
user/              signed-in area
admin/             admin panel
cron/              scheduled tasks
```

The classes are the whole auth system:

| Class | Does |
|---|---|
| `Session` | creates, resolves and revokes sessions; remember-me; device list |
| `Auth` | verifies credentials, exposes the current user, roles |
| `Token` | one-time secrets for verification and password reset |
| `Guard` | throttling, CSRF, route guards |
| `Settings` | database config, encrypted where flagged |
| `Emails` | templated mail through PHPMailer |

Each creates a global at the bottom of its file: `$_auth`, `$_session`,
`$_token`, `$_guard`, `$_settings`, `$_email`.

---

## Cron

```bash
php /path/to/project/cron/index.php
```

Run it every few minutes from Task Scheduler or crontab. It works from any
working directory. Tasks carry their own interval and only run when due, so it
does not matter what minute it fires.

Add a task with a file in `cron/includes/tasks/` and a line in
`cron/includes/tasks.php`:

```php
$_cron->task('auth-cleanup', 'Delete expired sessions, tokens and attempts', 3600);
```

The task returns a string, which becomes its status message in the admin panel.
Each runs isolated, so one failure does not stop the rest.

`cron/.htaccess` denies web access. The folder is reachable by CLI only.

**To remove cron entirely:** delete `cron/`, `admin/cron.php` and
`admin/controllers/cron.php`, drop the `cron_jobs` table, and remove its entry
from the admin sidebar and `sql.php`.

---

## Not included

No OAuth or social login, no two-factor, no multi-tenancy, and no way for a user
to change their email address. Sessions are cookie-based; `Session` reads an
`Authorization: Bearer` header but nothing issues API tokens yet.
