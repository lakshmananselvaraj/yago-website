# Vipasa Yoga — Architecture

Living document. Phase 1 covers workflow steps 1–8 (Splash → Auth → Verification →
Profile Setup → Browse Services → Choose Instructor → Select Date/Time → Booking
Confirmation). Later phases extend this document rather than replacing it.

## Sitemap (Phase 1)

```
/                           Splash (animated intro, auto-redirects)
/login                      Login (email/phone + password, remember me)
/signup                     Signup
/forgot-password            Request password reset OTP
/reset-password             Set new password (after OTP verified)
/verify-account             OTP entry (shared by signup verification + password reset)
/onboarding/profile         Multi-step profile setup wizard
/services                   Browse service types → packages
/instructors                Instructor list (filterable by service/package)
/instructors/{id}           Instructor profile, reviews, availability
/booking/schedule           Date + time slot picker (?instructor_id=&package_id=)
/booking/confirm/{ref}      Booking summary (price/tax/discount/total)
```

## User flow (Phase 1)

Splash → (session check) → Login/Signup → OTP verify (+ set password if new) →
Profile setup wizard → Browse services → pick a package → Choose instructor →
Select date/time → Booking confirmation (status `pending_payment`) → **[Phase 2:
Payment]**.

## Database schema

Full DDL lives in `database/schema.sql`. Phase 1 actively uses:

`users`, `otp_codes`, `password_resets`, `rate_limits`, `client_profiles`,
`instructors`, `instructor_reviews`, `instructor_availability`,
`instructor_blocked_dates`, `service_types`, `packages`, `instructor_services`,
`coupons`, `booking_slots`, `bookings`.

Created now but populated starting later phases: `payments`, `video_sessions`,
`session_feedback`, `notifications`, `receipts`, `activity_logs`, `favorites`,
`wallets`, `wallet_transactions`, `calendar_syncs`, `settings` (settings IS used
in Phase 1 for `tax_percent`/`default_currency`/`default_timezone`).

Key relationships: `users 1—1 client_profiles`; `users 1—1 instructors` (where
`role='instructor'`); `instructors 1—N instructor_availability /
instructor_blocked_dates / instructor_reviews`; `service_types 1—N packages`;
`instructors N—N packages` via `instructor_services` (with an optional
per-instructor `price_override`); `bookings` references `client` (a user),
`instructor`, `package`, `coupon` (nullable), and a locked `booking_slots` row.

**Booking slot generation is lazy**: `booking_slots` rows are NOT pre-seeded for
every future date. `BookingSlot::ensureGeneratedForDate()` materializes rows for
a given instructor+date on first request, from that instructor's
`instructor_availability` windows (recurring by weekday, or one-off by specific
date), split into chunks sized by the package's `duration_minutes`, skipping
platform-wide or instructor-specific `instructor_blocked_dates` and any time
already in the past. A unique key on `(instructor_id, slot_date, start_time)`
makes concurrent generation attempts collide harmlessly.

**Booking creation is race-safe**: `BookingSlot::lockSlot()` runs a conditional
`UPDATE ... WHERE id = :id AND status = 'available'`; if another request wins
the race, `rowCount()` is 0, the booking row is rolled back, and the client gets
a 409 to pick another slot.

`bookings.status` starts at `pending_payment` when created (end of Phase 1).
Phase 2 moves it to `confirmed`/`failed` based on real payment gateway results.

## API (Phase 1)

All JSON endpoints are same-origin under `/api/*`, authenticated via PHP session
cookie (not JWT — this is a server-rendered app progressively enhanced with
`fetch`, not an SPA). Every non-GET request must carry the CSRF token from the
`<meta name="csrf-token">` tag as an `X-CSRF-Token` header (handled centrally by
`public/assets/js/modules/api.js`).

```
POST /api/auth/signup            {name, email?, phone?, password, password_confirmation}
POST /api/auth/login             {identifier, password, remember?}
POST /api/auth/logout
POST /api/auth/forgot-password   {email}
POST /api/auth/reset-password    {password, password_confirmation}
POST /api/auth/verify-otp        {purpose: verify_account|reset_password, code}
POST /api/auth/resend-otp        {purpose}
GET  /api/profile
PUT  /api/profile                {date_of_birth, gender, yoga_experience, fitness_goals[], health_conditions, preferences[], emergency_contact_name, emergency_contact_phone, timezone, complete?}
GET  /api/services
GET  /api/services/{id}/packages
GET  /api/instructors            ?package=
GET  /api/instructors/{id}       ?package=
GET  /api/instructors/{id}/availability   ?month=&year=  OR  ?date=&package_id=
POST /api/coupons/validate       {code, subtotal}
POST /api/bookings               {instructor_id, package_id, slot_date, start_time, coupon_code?, notes?, timezone}
GET  /api/bookings/{ref}
```

## Folder structure

```
YOGA/
  public/                 # web root (point Apache/PHP dev server here)
    index.php             # front controller
    assets/{css,js,img,svg,fonts}
  app/
    bootstrap.php          # autoload, env, session, error handling
    Config/{app,database,routes}.php
    Core/                  # Router, Controller, Model, Database, Auth, Session,
                            # Csrf, Validator, Request, Response, RateLimiter,
                            # Mailer, View, Env, Middleware
    Controllers/            # web page controllers
    Controllers/Api/        # JSON API controllers
    Models/                 # one class per table, extends Core\Model
    Middleware/             # Auth, Guest, Csrf, RateLimit, Role
    Views/{layouts,auth,onboarding,services,instructors,booking,partials,errors}
  database/{schema.sql, seed.sql}
  storage/{logs, cache}
  docs/architecture.md      # this file
```

Plain PHP MVC, no framework. PDO with prepared statements everywhere (never
string-interpolated SQL). Session-based auth with `session_regenerate_id()` on
login, secure/httponly/samesite cookies, CSRF tokens on every state-changing
request, bcrypt password hashing, and a DB-backed sliding-window rate limiter
on login/signup/password-reset endpoints.

## What's deferred to later phases

Payment (Razorpay/Stripe/PayPal — integration layer stubbed against `.env`
config, real checkout wired in Phase 2), Payment Processing/Status, Booking
Confirmed notifications (email/SMS/in-app), Calendar Sync (Google/Outlook/
Apple), Join Session (Jitsi Meet embed), Session Completed (rating/feedback/
notes/attendance), Receipt/Invoice PDF, the full Client Dashboard, and the full
Admin Dashboard (11 modules: bookings, calendar, customers, payments,
instructors, video sessions, reports, notifications, settings, activity logs).

---

## Update: Auth & Foundation phase (post-Phase-2)

**Everything in this section supersedes the corresponding claims above.** The
sections above describe the original Phase 1 plan; auth in particular shipped
differently (link-based, not OTP) and a large "foundation phase" has since been
built on top of Phase 2. This addendum is intentionally a summary, not a
rewrite of the whole document — see `README.md`'s Site Map for the full list
of routes, and read the referenced source files directly for exact behavior.

- **Auth is email+password + Google Sign-In only — no OTP, no phone login.**
  Email verification and password reset are both real emailed links (token
  hashed in `email_verifications`/`password_resets`, single-use, time-limited),
  not OTP codes — `app/Models/EmailVerification.php`, `app/Models/PasswordReset.php`.
- **Payments are gateway-agnostic.** `app/Core/Payments/PaymentGateway` is an
  interface with `RazorpayGateway` and `StripeGateway` implementations,
  resolved by `PaymentGatewayFactory` off the `PAYMENT_GATEWAY` env var (not
  hardcoded). Routes are gateway-neutral (`/api/payments/create-order`,
  `/api/payments/verify`) — the frontend (`app/Views/booking/confirm.php`)
  branches on a `provider` field in the response rather than knowing the
  gateway upfront. Stripe uses a hosted Checkout Session (redirect), Razorpay
  uses its checkout.js modal. `payments.gateway_order_id`/`gateway_txn_id`
  columns are generic enough to hold either gateway's identifiers.
- **Composer is now used**, but only for two libraries (`dompdf/dompdf`,
  `phpoffice/phpspreadsheet`) needed for real PDF/Excel report export —
  everything else is still hand-rolled. `vendor/` is committed so no
  `composer install` is needed to deploy.
- **The admin panel now has all 12 modules** the original plan deferred:
  Dashboard, Bookings, Calendar, Packages, Instructors, Customers, Payments,
  Reports (CSV/PDF/Excel), Settings, Notifications, Video Sessions (a
  placeholder — the inbuilt video classroom itself is a separate future
  build), and Activity — sharing one nav partial (`app/Views/partials/admin-nav.php`).
- **7 email templates now exist**, all via the same `Mailer::send()` used in
  Phase 1: Welcome, Email Verification, Password Reset, Booking Confirmation,
  Payment Receipt, Invoice, Booking Cancellation, and Upcoming Session
  Reminder (the last one via a standalone CLI script, `bin/send-reminders.php`,
  since there's no cron/queue infrastructure in the app itself — see README.md
  for how to schedule it).
- **Bookings can now be cancelled** by their owning client
  (`POST /api/bookings/{ref}/cancel`) while still in the future and not
  already cancelled/completed — releases the held `booking_slots` row and
  emails the cancellation notice.
- **The client dashboard is split** into `/dashboard/bookings` (Upcoming vs.
  Completed & Past), `/dashboard/payments` (payment history), and
  `/dashboard/invoices` (list of invoices) rather than one flat list.
