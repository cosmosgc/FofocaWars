# FofocaWars — Agent Guide

## Quick start
- **`composer test`** — runs `php artisan test` (Pest PHP)
- **`php artisan migrate --force`** — run pending migrations
- **`php artisan db:seed --class=UnitTypeSeeder --force`** — seed unit types
- **`php artisan game:seed --users=2`** — create test war + 2 users (test1@example.com is admin)
- **`php artisan game:create-war "Name" --width=100 --height=100`** — CLI war creation
- **`npm run dev`** — Vite dev server (port 3000, proxies `/api` to Laravel 8000)
- **`php artisan serve`** — Laravel on port 8000

## Tests
- Test DB: `fofocawars_test` (MySQL). Create it before running tests.
- `php artisan test --filter="WarJoinTest|ArmyTest"` — focused run
- `RefreshDatabase` applied to all Feature tests via `tests/Pest.php`
- Breeze auth tests may get CSRF 419s in test env — CSRF is bypassed via `withoutMiddleware()` in WarJoinTest/ArmyTest `beforeEach`

## Architecture
- **Game logic** lives in `app/Game/` under sub-namespaces: `Army`, `Battle`, `Economy`, `Map`
- **All API routes** (`/api/wars/{war}/...`) are defined in `routes/web.php` (not `api.php`), use session auth, NOT Sanctum tokens
- **URL subdirectory**: app runs under `/FofocaWars/public/` — always use `route()` helper, never hardcode paths or use `/api/...` in JS fetch calls
- **Scheduler** (`routes/console.php`): `game:tick-resources`, `game:tick-armies`, `game:tick-training` run every minute (requires cron)
- **Lazy completion**: Training queue and army arrivals auto-complete on API read (no cron required for basic functionality)

## Key conventions
- War lifecycle: `setup` → `running` → `ended`. Only `running` wars process ticks.
- Armies have `mission` field: `attack` (default) or `reinforce`. Reinforce armies become garrisons on arrival.
- `sendArmy()` takes 5th param `$mission`: `sendArmy($origin, $target, $units, $war, 'reinforce')`
- Resources cap at `max_*` columns. `syncCity()` catches up elapsed production on every read.
- Building cards in city view are static placeholders (hardcoded in Blade, not from DB).
- Unit types are global (seeded once), not per-war.

## Admin
- `is_admin` boolean on `users` table. Promote via tinker: `User::where('email','...')->update(['is_admin'=>true])`
- Admin panel at `/admin/wars` — manage wars, change status, edit settings

## Translations
- Default locale: `pt_BR`. Fallback: `en`.
- Translation file: `lang/pt_BR.json`. Keys are English strings, values are Portuguese.
- Views use `__('English String')` — add new keys to the JSON file.
