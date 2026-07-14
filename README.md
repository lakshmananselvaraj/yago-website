# Vipasa Yoga — Booking Platform

A yoga appointment booking SaaS: public landing page, client booking flow
(browse → choose instructor → pick a slot → pay via Razorpay or Stripe test
mode), a client dashboard (bookings, payment history, invoices), and a full
admin panel (dashboard, bookings, calendar, packages, instructors, customers,
payments, reports with CSV/PDF/Excel export, settings, notifications, activity
log). Plain PHP 8 MVC (no framework) + MySQL/MariaDB + HTML5/CSS3/vanilla ES6
JS, with two Composer libraries (Dompdf, PhpSpreadsheet) vendored in for
report export — see `docs/architecture.md` for the full design.

## Is it running right now?

If you're picking this up in the same session it was built in, it's likely
already live at **http://localhost:8000** — check before starting a second
copy. Otherwise, follow Setup below.

## Setup (first time only)

1. **Configure environment**
   ```
   copy .env.example .env
   ```
   The defaults target the portable PHP/MariaDB described below
   (`127.0.0.1:3307`, user `root`, no password). Change `DB_*` if you're
   pointing at a different MySQL instance.

2. **Create the database**
   ```
   D:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -P 3307 -u root < database/schema.sql
   D:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -P 3307 -u root < database/seed.sql
   ```
   (`schema.sql` and `seed.sql` are already fully up to date — you don't need
   the files under `database/migrations/`, those were one-time updates applied
   to this machine's dev database as the schema evolved.)

3. **Install PHP dependencies** — `vendor/` is committed to this repo (see
   "Composer dependencies" below), so this step is normally a no-op. Only run
   it if `vendor/` is missing (e.g. you cloned without it):
   ```
   php composer.phar install
   ```
   (or plain `composer install` if you have Composer on your `PATH`.)

## Running the app

Two things need to be running: MariaDB, then the PHP server. Open two
terminals (or run the MariaDB one in the background).

**1. Start MariaDB** (skip if `mysqld.exe` is already running — check with
`tasklist /FI "IMAGENAME eq mysqld.exe"`):
```
D:\xampp\mysql\bin\mysqld.exe --defaults-file=D:\xampp\mysql\data\my.ini
```

**2. Start the PHP dev server**, from the project root (`D:\YOGA`):
```
D:\xampp\php\php.exe -S localhost:8000 -t public
```

**3. Visit** http://localhost:8000

To stop: close both terminal windows, or find and kill the processes
(`taskkill /F /PID <pid>` using the PIDs from `tasklist`).

## This machine's environment (context for why it's not a standard XAMPP setup)

This machine had no PHP/MySQL installed, C: had 0 bytes free, and the official
XAMPP installer requires a UAC prompt that couldn't be approved
non-interactively. A portable (zip, no-installer) PHP and MariaDB were set up
directly on the D: drive instead:

- PHP 8.3.32 (NTS) at `D:\xampp\php\php.exe`
- MariaDB 11.4.5 at `D:\xampp\mysql\` (data dir: `D:\xampp\mysql\data`)
- MariaDB runs on **port 3307**, not the default 3306 — a pre-existing MySQL
  8.0 Windows service on this machine already owns 3306 and was left untouched
- `php.ini` has `pdo_mysql`, `mysqli`, `mbstring`, `openssl`, `curl`,
  `fileinfo`, `zip`, and `gd` enabled (`zip`/`gd` were added for the PDF/Excel
  report export — see below)

If you deploy this elsewhere with a normal XAMPP/WAMP/Laragon/hosting install,
none of the above matters — just point `.env` at whatever MySQL instance you
have (default port 3306 is fine), make sure the `zip` PHP extension is enabled
(required by the Excel export), and serve `public/` through Apache or PHP's
built-in server as usual.

## Composer dependencies

Two libraries are used, both purely for the admin Reports export (PDF via
Dompdf, `.xlsx` via PhpSpreadsheet) — everything else in the app is still
hand-rolled PHP with zero dependencies. `composer.json`, `composer.lock`, and
the `vendor/` directory are all committed to this repo, so **no `composer
install` is needed to deploy** — upload/copy the project as-is (including
`vendor/`) to any PHP host (Hostinger, cPanel, VPS) and it works immediately,
even on hosts without shell/SSH access to run Composer. Only re-run
`composer install` yourself if you add/change a dependency.

## Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@vipasa.demo` | `Admin@12345` |
| Instructor | `ananya.iyer@vipasa.demo` | `Instructor@123` |
| Instructor | `rahul.menon@vipasa.demo` | `Instructor@123` |
| Instructor | `sarah.thomas@vipasa.demo` | `Instructor@123` |

There's no seeded client account — sign up as a new client through `/signup`
to walk the full booking flow. New instructors added via `/admin/instructors`
don't get a password set by the admin — they're emailed a "set your password"
link (the same mechanism as `/forgot-password`) and their account has no
usable password until they click it.

`APP_DEBUG=true` (the `.env.example` default) logs outbound "emails" (signup
verification link, welcome email, password reset, instructor invite, booking
confirmation/receipt/invoice/cancellation/reminder, contact form notices) to
`storage/logs/mail.log` instead of actually sending them, since no real SMTP
provider is configured — open that file to grab a link while testing locally.

## What's wired up vs. what needs real credentials

Everything below is fully built and tested, but some integrations are stubbed
against placeholder `.env` values until you supply real credentials:

- **Payments (Razorpay or Stripe, test or live)** — booking creation,
  checkout, signature/status verification, and post-payment emails all work
  against whichever gateway `PAYMENT_GATEWAY` selects (`razorpay` by default,
  or `stripe`). Both are blank in `.env.example`, so completing a payment
  against the *real* gateway isn't possible until you add real (sandbox or
  live) keys — `RAZORPAY_KEY_ID`/`RAZORPAY_KEY_SECRET` or
  `STRIPE_PUBLIC_KEY`/`STRIPE_SECRET_KEY`. **Switching gateways, or switching
  test keys for live keys, is purely an `.env` change — no code changes are
  needed** (see `app/Core/Payments/` for the gateway abstraction). Stripe uses
  a hosted Stripe Checkout page (redirect-based), so no Stripe.js UI work is
  needed either. In the meantime, `app/Core/Payments/MockGateway.php`
  automatically stands in whenever `PAYMENT_MODE=test` and no real gateway
  keys are set, so "Proceed to Payment" completes the full booking flow
  end-to-end (no external calls) instead of being disabled — useful for
  demoing/testing before real credentials exist. It's never used once real
  keys are present, regardless of `PAYMENT_MODE`.
- **Google Sign-In** — the OAuth flow (redirect, callback, account
  linking/creation) is fully implemented; `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET`
  are blank, so the button currently shows "Google sign-in isn't configured
  yet" rather than erroring. To activate: create a Google Cloud OAuth Client
  ID (Web application), add `GOOGLE_REDIRECT_URI` as an authorized redirect
  URI there, and either add the credentials to `.env` or paste them into
  `/admin/settings` → Google Login (settings there take priority over
  `.env`, no redeploy needed). Note for the client: a freshly created OAuth
  consent screen starts in "Testing" mode and only allows pre-approved test
  emails to sign in — it needs to be submitted for verification/publishing in
  Google Cloud Console before real customers can use it.
- **Branding logo** — `public/assets/img/brand/logo-dark.png` (the glowing
  white-on-dark lockup) is wired into the dashboard sidebar and shown only in
  dark theme (`app/Views/partials/dashboard-sidebar.php` +
  `dash-sidebar__brand-logo--dark` in `dashboard-shell.css`). Light theme still
  falls back to the plain "V" letter mark + wordmark — a light-surface logo
  file hasn't been supplied yet; drop one at
  `public/assets/img/brand/logo-light.png` and add a matching `<img
  class="dash-sidebar__brand-logo--light">` + CSS rule to complete it. The
  landing page, login/signup pages, and emails still use the letter mark only.

## Upcoming-session email reminders (scheduled task)

`bin/send-reminders.php` is a standalone CLI script — it emails a reminder for
every confirmed booking starting in the next 24 hours that hasn't already had
one sent (`bookings.reminder_sent_at` tracks this, so it's always safe to run
more often than needed; nothing is ever double-sent). It isn't wired up to run
automatically — schedule it yourself:

- **Windows (this dev machine)**, e.g. every 15 minutes:
  ```
  schtasks /create /tn "VipasaReminders" /tr "D:\xampp\php\php.exe D:\YOGA\bin\send-reminders.php" /sc minute /mo 15
  ```
- **Linux hosting (cPanel "Cron Jobs" or a crontab)**, e.g. every 15 minutes:
  ```
  */15 * * * * /usr/bin/php /home/youruser/vipasa/bin/send-reminders.php
  ```

Run it manually any time with `D:\xampp\php\php.exe bin\send-reminders.php` —
it prints a one-line summary (`Reminders sent: N (of M due)`).

## Site map

- `/` — public landing page (guests) / splash → dashboard redirect (logged in)
- `/signup`, `/login`, `/forgot-password`, `/reset-password` — auth (email
  verification and password reset are both real emailed links, not OTP codes;
  no phone-based login)
- `/onboarding/profile` — profile setup / edit (also the permanent "edit
  profile" page)
- `/services` → `/instructors` → `/instructors/{id}` → `/booking/schedule` →
  `/booking/confirm/{ref}` → Razorpay checkout modal or Stripe Checkout
  redirect (whichever `PAYMENT_GATEWAY` selects) → `/booking/{ref}/success` or
  `/booking/{ref}/failed` — the booking + payment flow
- `/booking/{ref}/invoice` — printable invoice
- `/dashboard/bookings` — client's own bookings, split into Upcoming and
  Completed & Past, with a Cancel action on any still-future booking
- `/dashboard/payments` — client's own payment history across all bookings
- `/dashboard/invoices` — client's own list of invoices (confirmed/completed
  bookings only), linking to the printable invoice page
- `/admin` — analytics dashboard (revenue/bookings/user-growth charts,
  today's schedule, upcoming/cancelled sessions)
- `/admin/bookings` — manage bookings, attach Google Meet/Zoom links
- `/admin/calendar` — cross-instructor month view of all bookings
- `/admin/packages` — create/edit/activate packages (only 3 — Single Session,
  Weekly, Monthly — are `is_featured` and show on the public Services page by
  default; add more here any time)
- `/admin/instructors` — create/edit/deactivate instructors (creates the
  paired user account and emails a password-set invite link)
- `/admin/customers` — browse clients, drill into a customer's full booking +
  payment history
- `/admin/payments` — all payment transactions, filterable by status/gateway
- `/admin/settings` — edit site name, tax percent, default currency/timezone
- `/admin/notifications` — send an in-app notification to any user; view sent
- `/admin/video-sessions` — placeholder for the inbuilt video classroom (a
  separate future build phase — sessions currently use the meeting-link field
  on `/admin/bookings`)
- `/admin/reports` — revenue/bookings/payments/instructor-performance reports,
  downloadable as CSV, real PDF, or real Excel (`.xlsx`), or viewed as a
  printable HTML page
- `/admin/activity` — platform activity timeline

Every `/admin/*` page shares one admin sub-navigation bar
(`app/Views/partials/admin-nav.php`) linking all of the above.

## Security

Bcrypt password hashing, 100% PDO prepared statements, CSRF tokens on every
state-changing request, output escaping via `View::e()`, role-based
middleware, secure/httponly/samesite session cookies with regeneration on
login, and a DB-backed rate limiter on login/signup/password-reset/contact
endpoints.

## Before going live with real customers

This is a fully working, tested build — but not production-ready as-is:

1. Add real Razorpay or Stripe credentials and Google OAuth credentials (see
   above) — going from sandbox/test to live payments is purely swapping the
   `.env` key values (and optionally `PAYMENT_GATEWAY`), no code changes.
2. Deploy to real hosting with HTTPS (PHP's built-in dev server used here is
   explicitly not for production) and point a real SMTP provider at
   `MAIL_DRIVER`/`MAIL_HOST`/etc. instead of the local `log` driver.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, a real random `APP_KEY`,
   `SESSION_SECURE_COOKIE=true`, and `PAYMENT_MODE=production`.
4. Schedule `bin/send-reminders.php` on the host (cPanel Cron Jobs or
   crontab — see above); nothing sends reminder emails until this is set up.
5. Replace demo/seed accounts and the placeholder landing-page testimonials
   with real content; add your Terms of Service / Privacy Policy / Refund
   Policy pages.
6. Finish the visual pass — several pages/flows (profile, booking confirm,
   payment success) have been checked in an actual browser and had UI bugs
   fixed along the way, but most of the app has only been tested via HTTP
   requests (status codes, absence of PHP errors, correct data), not visually
   inspected. Also finish the light-theme logo (see "Branding logo" above).
