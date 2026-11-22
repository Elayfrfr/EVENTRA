# Eventra – Full Step-by-Step Guide
(Laragon → Git Bash → GitHub → InfinityFree)

Demo password for ALL seeded accounts: `Eventra@Demo2026`
- Faculty: `zemdumabog43@uc.edu.ph`
- Students: `joievelasco69@uc.edu.ph`, `zwahvelastico@uc.edu.ph`, `felmssenings@uc.edu.ph`, `simonaphilipia@uc.edu.ph`, `keithyenjambers@uc.edu.ph`

## PART 1 – Run it locally (Laragon + VS Code)
1. Install Laragon Full from laragon.org (default options). Open it and click **Start All** (Apache + MySQL turn green).
2. Unzip the project so the folder is `C:\laragon\www\eventra` (index.php must sit directly inside it; see the folder map in README.md).
3. In Laragon: right-click → **Quick app**/or just restart; Auto Virtual Hosts gives you `http://eventra.test`. (Fallback: `http://localhost/eventra`; then set `BASE_URL` to `/eventra` in config.local.php.)
4. Create the database: Laragon → **Database** button (HeidiSQL) → connect (user `root`, empty password) → right-click → Create new → Database → name `eventra`, collation `utf8mb4_unicode_ci`.
5. Import: select `eventra` → File → Run SQL file → `database/schema.sql`, then `database/seed.sql`.
   (Or phpMyAdmin at http://localhost/phpmyadmin → eventra → Import.)
6. Config: copy `includes/config.sample.php` to `includes/config.local.php` (already included in the zip with Laragon defaults).
7. Open `http://eventra.test` and log in with a demo account.
8. Open the folder in VS Code: `code C:\laragon\www\eventra`.

## PART 2 – Git Bash and GitHub
1. Install Git for Windows (git-scm.com). Open **Git Bash**.
2. One-time identity:
   ```
   git config --global user.name "Your Name"
   git config --global user.email "you@example.com"
   ```
3. Go to the project (or make the folder if starting fresh):
   ```
   cd /c/laragon/www
   mkdir -p eventra && cd eventra
   ```
4. Initialise and commit:
   ```
   git init
   git add .
   git status        # config.local.php must NOT be listed
   git commit -m "Initial commit: Eventra"
   ```
5. On github.com click **New repository** → name `eventra` → leave it empty (no README) → Create.
6. Connect and push:
   ```
   git branch -M main
   git remote add origin https://github.com/YOUR-USERNAME/eventra.git
   git push -u origin main
   ```
   When asked to sign in, use the browser prompt or a Personal Access Token as the password.
7. Later changes: `git add .` → `git commit -m "message"` → `git push`.

## PART 3 – Deploy to InfinityFree
1. Sign up at infinityfree.com → **Create Account** → pick a subdomain (e.g. `eventra.infinityfreeapp.com`).
2. Open the **Client Area → your account → Control Panel (VistaPanel)**.
3. **MySQL Databases** → create a database named `eventra`. Note the full name (`if0_XXXXXXXX_eventra`), username (`if0_XXXXXXXX`), password (your vPanel/account password) and host (`sqlXXX.infinityfree.com`).
4. Click **phpMyAdmin** next to the database → select it → **Import** → upload `database/schema.sql`, then `database/seed.sql`.
5. Upload files with **FileZilla** (FTP details are on the account page: host, username, password, port 21) or the Online File Manager. Everything goes INSIDE `htdocs`:
   - `htdocs/index.php`, `htdocs/includes/`, `htdocs/assets/`, `htdocs/database/`, `htdocs/uploads/`, `.htaccess` etc.
   - Delete InfinityFree's default `index2.html`/placeholder if present.
   - Do not upload `.git` or `testenv`.
6. Create `htdocs/includes/config.local.php` (copy of the sample) with:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');
   define('DB_NAME', 'if0_XXXXXXXX_eventra');
   define('DB_USER', 'if0_XXXXXXXX');
   define('DB_PASS', 'your-password');
   define('BASE_URL', '');
   define('APP_DEBUG', false);
   define('SHOW_RESET_LINK', true);
   define('MAIL_FROM', 'no-reply@yoursite.infinityfreeapp.com');
   ```
   (Since this file is gitignored, upload it by FTP separately.)
7. Make `htdocs/uploads/avatars` writable (FileZilla → right-click → File permissions → 755, or 775 if uploads fail).
8. Visit your site. First visits may show InfinityFree's "browser check" page for a few seconds; that is normal.
9. Updating later: push to GitHub, then re-upload changed files by FTP (InfinityFree has no shell, cron, or git).

## Things to know on free hosting
- **Email**: InfinityFree blocks PHP `mail()`. That is why `SHOW_RESET_LINK` shows the reset link on screen. For real emails you would add PHPMailer with an SMTP provider (e.g. Gmail app password) – optional.
- **Payment** is simulated (demo). A real gateway (e.g. PayMongo) would be needed for real money.
- **QR codes** use the external api.qrserver.com image service.
- **Reminders** are generated when pages load (no cron on free hosting).
- **Terms page** keeps the prototype's brand text "Bokla"; change `$TERMS_BRAND` in `includes/terms_content.php` to "Eventra".
- File limits: PHP/JS files must stay under 1 MB each (all ours are far smaller).

## Troubleshooting
- Blank page / DB error → check `config.local.php` values; set `APP_DEBUG` true temporarily.
- Links 404 on Laragon → use `eventra.test`, or set `BASE_URL` for `/eventra`.
- Login fails for seeded users → make sure `seed.sql` imported after `schema.sql`; password is `Eventra@Demo2026`.
- Photo upload fails → uploads/avatars permissions.
