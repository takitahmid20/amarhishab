# AmarHishab

### 🌐 Live: http://www.amarhishab.dev.bd/

**Smart cashbook and expense tracking for students & young adults in Bangladesh.**

> Demo login — **demo@amarhishab.test** / **password123**

---

## What is this?

AmarHishab (আমারহিসাব) is a personal finance app I built because I couldn't find anything simple enough for tracking daily expenses. You know how it goes — bus fare, chaa, rickshaw, snacks, tuition fees. Small amounts that disappear and at the end of the month you have no idea where the money went.

Most finance tools are either too complicated (looking at you, spreadsheets) or built for businesses, not for regular people trying to track their daily spending.

So I made this.

---

## What it does

- **Multiple cashbooks** — Keep separate books for personal expenses, tuition, freelance income, whatever you need.
- **Quick expense entry** — Add an expense in a few taps. No long forms.
- **Budget tracking** — Set limits per category and see how much you've spent.
- **Borrow / Lend tracking** — Remember who owes you and what you owe others.
- **Bill reminders** — Never miss an internet bill or tuition installment again.
- **Simple reporting** — See where your money goes without digging through data.
- **HisabAI** — Ask questions about your own finances in plain language ("How much did I spend this month?") and get answers grounded in your data.

---

## Tech stack

Server-rendered PHP. No framework, no build step, no REST API — pages are
plain PHP that talk to MySQL directly and render HTML.

- **PHP** (vanilla, server-rendered pages with shared `include` partials)
- **MySQL** via PDO
- **HTML + CSS** with a custom design-token system
- **Vanilla JavaScript** for small interactions (modals, charts, scroll reveal)
- **Lucide** icons
- **HisabAI** powered by the **Google Gemini** API (free tier), grounded in the user's own finance data

Deployed on a DigitalOcean droplet (Apache + PHP + MySQL).

---

## Project structure

```
amarhishab/
├── index.php               # Landing page
├── pages/                  # App pages (server-rendered .php)
│   ├── dashboard.php
│   ├── transactions.php
│   ├── cashbooks.php
│   ├── cashbook-details.php
│   ├── budget.php
│   ├── borrow-lend.php
│   ├── reminders.php
│   ├── reports.php
│   ├── hisab-ai.php        # HisabAI chat
│   ├── settings.php
│   ├── login.php
│   ├── signup.php
│   ├── forgot-password.php
│   └── otp.php
├── partials/               # Shared PHP includes (navbar, sidebar)
├── config/                 # DB connection + config (config.local.php is gitignored)
├── includes/               # Helpers (escape, taka format, redirect) + session auth
├── actions/                # Form handlers (POST endpoints)
├── styles/                 # CSS (reset, variables, components, per-page)
├── js/                     # Vanilla JS (modal, charts, landing interactions)
├── data/                   # Seed / sample JSON
└── assets/                 # Images, icons, logos
```

---

## Status

Live and deployed. Full auth (signup / login / OTP password reset),
cashbooks, transactions, budgets, borrow/lend, reminders, dashboard,
reports, settings, and the HisabAI assistant — all server-rendered from
MySQL, per-user scoped and CSRF-protected.

---

## Running it

Built for **XAMPP** (Apache + MySQL).

1. Copy this folder into your XAMPP `htdocs/` (e.g. `htdocs/amarhishab`).
2. Start Apache + MySQL from the XAMPP control panel.
3. Import the schema and sample data:
   ```bash
   mysql -u root < database/schema.sql
   mysql -u root < database/seed.sql   # optional demo data
   ```
4. Copy `config/config.sample.php` to `config/config.local.php` and set your
   MySQL credentials (XAMPP default: user `root`, empty password). For HisabAI,
   add a free Google Gemini API key in the `ai` section (or `GEMINI_API_KEY` env).
5. Open `http://localhost/amarhishab/` in a browser.

Demo login (after seeding): `demo@amarhishab.test` / `password123`.

---

## Why the name?

"Amar Hishab" means "My Account" in Bangla. Simple, direct, tells you exactly what it does.

---

© 2026 AmarHishab — Taki Tahmid. All rights reserved.
