# NexPill Laravel platform

Laravel 13 backend/API, Sanctum mobile tokens, custom Blade/Tailwind admin and Tailwind CSS 4 marketing/legal website.

## Local setup

Requirements: PHP 8.3+, Composer 2, Node 22+, MySQL 8/PostgreSQL 16+ for production.

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` before `php artisan db:seed` to create the first administrator. Admin login is `/admin`; only active users with the `admin` role can enter.

For the mobile app, the base endpoint is `https://nexpill.kodnextech.com/api/v1`. API responses use:

```json
{"success": true, "message": "OK", "data": {}}
```

Validation/auth errors use normal HTTP codes (`401`, `403`, `404`, `409`, `422`, `429`) and a JSON `message`/`errors` payload.

## Production processes

Run these as supervised services:

```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
php artisan schedule:work
```

Or run `php artisan schedule:run` every minute from cron. Scheduled jobs materialize upcoming doses, send medication/appointment reminders, finalize missed doses and check low stock. FCM delivery uses Firebase HTTP v1 and `FIREBASE_SERVICE_ACCOUNT`.

Production deploy checklist:

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS and correct `APP_URL`/`CORS_ALLOWED_ORIGINS`.
- MySQL/PostgreSQL plus automated encrypted backups and restore drills.
- Redis for cache/queues at scale; restart workers after every deploy.
- Configure SMTP, FCM service account and object storage if prescriptions move to S3.
- Run `php artisan migrate --force`, `php artisan optimize`, `npm ci && npm run build`.
- Set retention, audit-access, incident-response and account-deletion policies.
- Replace seeded draft legal text after counsel approval.

## Key routes

- Public: `/`, `/privacy`, `/terms`, `/data-deletion`, `/support`
- Admin: `/admin`
- Health: `/api/v1/health`
- API: `/api/v1/auth/*`, `/medicines`, `/doses`, `/appointments`, `/family`, `/notifications`, `/profile`, `/support/tickets`

Use `php artisan route:list --path=api/v1` for the authoritative route list.

## Data model

UUID-based tables include users, one-time codes, device tokens, dependents, family connections, medicines, normalized schedules, dose logs, refills, side effects, appointments, notifications, support tickets/messages, plans/subscriptions, legal documents/consents and audit logs. Prescription files are private and only downloadable by their owning user.

## Tests

```bash
php artisan test
vendor/bin/pint --test
npm run build
```
