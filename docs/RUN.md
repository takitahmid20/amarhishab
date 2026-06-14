# Running AmarHishab locally

Two ways to run it. **Option A** works right now with what you already have
(standalone MySQL + PHP). **Option B** is the XAMPP setup, which also gives you
phpMyAdmin for browsing the database.

---

## Requirements

- PHP 8.x with `pdo_mysql` (check: `php -m | grep pdo_mysql`)
- MySQL 8.x running
- A browser

---

## Option A — Run now (PHP built-in server)

Fastest path. From the project root:

```bash
# 1. Import database (first time only)
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql        # optional demo data

# 2. Local config (first time only) — copy sample, then edit if needed
cp config/config.sample.php config/config.local.php

# 3. Start the server
php -S localhost:8000
```

Open **http://localhost:8000/** (landing) or
**http://localhost:8000/pages/dashboard.php**.

Stop the server with `Ctrl+C`.

> Note: `mysql` CLI path may be `/usr/local/mysql/bin/mysql` if it's not on your PATH.

---

## Option B — XAMPP (Apache + phpMyAdmin)

1. Install XAMPP, then copy/clone this project into `htdocs/`:
   - macOS: `/Applications/XAMPP/htdocs/amarhishab`
   - Windows: `C:\xampp\htdocs\amarhishab`
2. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Import the database (see "Check the database" below — use phpMyAdmin, or run
   the `mysql` commands from Option A).
4. Copy `config/config.sample.php` to `config/config.local.php`. XAMPP MySQL
   defaults are user `root`, empty password — the sample already matches.
5. Open **http://localhost/amarhishab/**.

> Port clash: XAMPP's MySQL and a standalone MySQL both use port `3306`. Run only
> one at a time, or change one of their ports.

---

## Check the database in phpMyAdmin

phpMyAdmin ships with XAMPP.

1. Start Apache + MySQL in the XAMPP Control Panel.
2. Open **http://localhost/phpmyadmin**.
3. Left sidebar → click the **`amarhishab`** database. You should see 6 tables:
   `users`, `cashbooks`, `budget_categories`, `transactions`, `borrow_lend`,
   `reminders`.
4. Click any table (e.g. `transactions`) → **Browse** tab to see rows.
5. To import fresh: top **Import** tab → choose `database/schema.sql` → **Go**,
   then repeat for `database/seed.sql`.
6. To run a query: **SQL** tab, paste, e.g.:
   ```sql
   SELECT c.name, SUM(IF(t.direction='in',  t.amount, 0)) AS cash_in,
                  SUM(IF(t.direction='out', t.amount, 0)) AS cash_out
   FROM cashbooks c JOIN transactions t ON t.cashbook_id = c.id
   GROUP BY c.id;
   ```

### No XAMPP? Check the DB from the terminal

```bash
mysql -u root amarhishab -e "SHOW TABLES;"
mysql -u root amarhishab -e "SELECT * FROM users;"
mysql -u root amarhishab -e "SELECT * FROM cashbooks;"
```

(Or use a GUI like TablePlus / DBeaver / Adminer pointed at `127.0.0.1:3306`,
user `root`, database `amarhishab`.)

---

## Demo login

After seeding:

- **Email:** `demo@amarhishab.test`
- **Password:** `password123`

*(Login is wired to the database in the auth phase.)*

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| `could not find driver` | Enable `pdo_mysql` in `php.ini`, restart server |
| `Access denied for user 'root'` | Set the right password in `config/config.local.php` |
| `Unknown database 'amarhishab'` | Run `mysql -u root < database/schema.sql` |
| Page 404 under XAMPP | Confirm the folder is `htdocs/amarhishab` and Apache is running |
| Blank page / 500 | Check the server log; verify `config/config.local.php` exists |
