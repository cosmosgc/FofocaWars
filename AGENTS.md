# FofocaWars — Agent Guide

## Commands
- **`composer test`** — runs `config:clear` then `php artisan test` (Pest). Fast filter: `php artisan test --filter="WarJoinTest|ArmyTest"` (8 tests, 43 assertions)
- **`php artisan migrate --force`** — apply pending migrations
- **`php artisan db:seed --class=UnitTypeSeeder --force`** — seed 6 global unit types
- **`php artisan game:seed --users=2`** — creates war + 2 users (test1@example.com is admin)
- **`php artisan game:create-war "Name" --width=100 --height=100`** — CLI war creation
- **`npm run dev`** — Vite on port 3000, proxies `/api` to Laravel 8000
- **`php artisan serve`** — Laravel on port 8000
- **`composer dev`** — runs `serve` + `queue:listen` + `pail` + `vite` concurrently

## Tests
- Test DB: `fofocawars_test` (MySQL). Create before running. Config in `phpunit.xml`.
- `RefreshDatabase` applied to all Feature tests via `tests/Pest.php`.
- CSRF bypassed via `withoutMiddleware()` in `WarJoinTest` and `ArmyTest` `beforeEach`.
- Unit types must be seeded in `ArmyTest` `beforeEach` (no factory — inserted manually).

## Architecture
- **API routes** live in `routes/web.php` (NOT `api.php`), use session auth, NOT Sanctum.
- **URL subdirectory**: app under `/FofocaWars/public/` — always use `route()` in Blade, never hardcode `/api/...` in JS.
- **Game logic**: `app/Game/{Army,Battle,Diplomacy,Economy,Map,Analytics,Theme}/`.
- **All controllers**: `app/Http/Controllers/`, API controllers under `Api/`, admin under `Admin/`.
- **PixiJS v7.3.2**: loaded from CDN (`pixi.min.js`), all map rendering is inline JS in `resources/views/wars/map.blade.php`.
- **Alpine.js**: used for resource panel polling (`x-data="resources()"`) and profile avatar preview.

## Key conventions
- War lifecycle: `setup` → `running` → `ended`. Players can join even while `running`.
- Resources per-city, capped at `max_*`. `syncCity()` catches up elapsed production on every read (lazy, no cron needed for basic operation).
- Training and army arrivals auto-complete on API read (checks `finishes_at <= now()` / `arrival_at <= now()`).
- `sendArmy($origin, $target, $units, $war, $mission)` — 5th param is `'attack'` (default) or `'reinforce'`.
- Reinforce armies become separate `Army` records (`status=stationed`, `mission=reinforce`), not merged into city units.
- Unit types are global (seeded once in `UnitTypeSeeder`), not per-war.
- Building cards in city view are hardcoded Blade placeholders (no DB model).
- Battle loot: attacker takes half of defender's city resources (capped by winner's origin city max caps).
- Founding cities: free if player has 0 cities; otherwise costs resource proportional to `construction_speed`. Can found on any unowned non-water tile via click on PixiJS map (`POST /api/wars/{war}/tiles/found`).

## Admin
- `is_admin` on `users` table. Promote: `User::where('email','...')->update(['is_admin'=>true])`
- Admin panel at `/admin/wars` — CRUD wars, change status, edit settings.

## Translations
- Default: `pt_BR`. Fallback: `en`.
- All keys in `lang/pt_BR.json` (English keys, Portuguese values). No PHP lang files — JSON only.
- Views use `__('English String')`; JS uses `__i18n()` (identity — new keys need Blade-rendered strings).
- CRITICAL: `__('Messages')` matched `messages.php` filename on Windows case-insensitively and returned the whole array. Solution: delete all `lang/*.php` files, only use JSON.

## Gotchas
- Avatar files stored directly in `public/avatars/` (not Storage disk). Accessor: `url('avatars/' . $filename)`.
- No queue workers in dev (sync driver). In prod, `queue:listen` handles deferred ticks.
- Scheduler (`routes/console.php`): `game:tick-*` every minute. Requires cron on shared hosting.
- Map tile coordinates in API responses use `x`, `y` columns on `tiles` table, not lat/lng.
