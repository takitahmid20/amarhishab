# AmarHishab

**Smart cashbook and expense tracking for students & young adults in Bangladesh.**

---

## What is this?

AmarHishab (আমারহিসাব) is a personal finance app I built because I couldn't find anything simple enough for tracking daily expenses. You know how it goes — bus fare, chaa, rickshaw, snacks, tuition fees. Small amounts that disappear and at the end of the month you have no idea where the money went.

Most finance tools are either too complicated (looking at you, spreadsheets) or built for businesses, not for regular people trying to track their日常 spending.

So I made this.

**Live preview:** [aamar-hishab.netlify.app](https://aamar-hishab.netlify.app/)

---

## What it does

- **Multiple cashbooks** — Keep separate books for personal expenses, tuition, freelance income, whatever you need.
- **Quick expense entry** — Add an expense in a few taps. No long forms.
- **Budget tracking** — Set limits per category and see how much you've spent.
- **Borrow / Lend tracking** — Remember who owes you and what you owe others.
- **Bill reminders** — Never miss an internet bill or tuition installment again.
- **Simple reporting** — See where your money goes without digging through data.

---

## Tech stack

**Frontend:**
- Plain HTML, CSS, vanilla JavaScript
- No framework. No build tools. Just files that work in a browser.
- Lucide icons for the UI

**Backend:**
- PHP with Laravel
- REST API at `/api`
- MySQL database

---

## Project structure

```
amarhishab/
├── frontend/
│   ├── index.html              # Landing page
│   ├── pages/                  # App pages
│   │   ├── dashboard.html
│   │   ├── transactions.html
│   │   ├── cashbooks.html
│   │   ├── cashbook-details.html
│   │   ├── budget.html
│   │   ├── borrow-lend.html
│   │   ├── reminders.html
│   │   ├── reports.html
│   │   ├── settings.html
│   │   ├── login.html
│   │   ├── signup.html
│   │   ├── forgot-password.html
│   │   └── otp.html
│   ├── styles/                 # CSS files
│   ├── js/                     # JavaScript
│   │   ├── core/               # API, state, storage, formatter, validator
│   │   ├── components/         # Reusable UI components
│   │   ├── modules/            # Feature modules (auth, budget, cashbooks, etc.)
│   │   └── data/               # Mock data and seed scripts
│   ├── partials/               # Shared HTML partials (navbar, sidebar)
│   └── assets/                 # Images, icons, logos
└── backend/                    # Laravel API
```

---

## Status

This is a work in progress. The frontend is being built first as a static prototype. Some pages are fully functional (login, signup, dashboard shell) while others still have hardcoded data waiting to be wired up.

The landing page at `frontend/index.html` has the latest design work — hero section, features showcase, cashbook preview, budget section, and a dark-themed about section.

---

## Running it

For the frontend, just open any `.html` file in a browser. No server needed for the static pages.

For the backend API:
```bash
cd backend
php artisan serve
```

---

## Why the name?

"Amar Hishab" means "My Account" in Bangla. Simple, direct, tells you exactly what it does.
