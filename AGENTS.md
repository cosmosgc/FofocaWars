# FofocaWars — Agent Guide

## Commands
- **`composer test`** — runs `config:clear` then `php artisan test` (Pest). Fast filter: `php artisan test --filter="WarJoinTest|ArmyTest"` (8 tests, 43 assertions)
- **`php artisan migrate --force`** — apply pending migrations
- **`php artisan db:seed --class=UnitTypeSeeder --force`** — seed 6 global unit types
- **`php artisan game:seed --users=2`** — creates war + 2 users (test1@example.com is admin)
- **`php artisan game:create-war "Name" --width=100 --height=100`** — CLI war creation
- **`php artisan db:seed --class=ThemeSeeder --force`** — seed 4 themes (medieval/default, desert, winter, neon)
- **`php artisan game:tick-analytics`** — manual analytics snapshot
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

## Theme Engine
- **`themes` table**: `name` (string, FK target from `wars.theme`), `label`, `description`, `is_default`, `config` (JSON).
- **`ThemeService`** (`app/Game/Theme/ThemeService.php`): resolves per-war theme, exposes `terrainColors()`, `legendColors()`, `cssVariables()`.
- **Config keys**: `colors.terrain.{plain,forest,mountain,water,desert}`, `colors.city.{fill,stroke}`, `colors.bases.{resource,military,trade,alliance}`, `css.*`, `battle_effect`.
- **Battle effects** (`explosion`/`sandstorm`/`snow`/`cyber`): PixiJS particle animation at battle tile location, driven by `themeConfig.battle_effect`.
- **Admin**: theme dropdown in war create/edit uses `Theme::pluck('name')` for validation.
- **Sprite crop (mini-spritesheets)**: Each of 6 sprite types (5 terrain + city) has its own file + Crop W/H inputs. If image is larger than crop, it becomes mini-spritesheet — PixiJS slices into tiles, picks randomly per coordinate (`seededRandom(x*1000+y)`). Config stored as `{url, tile_w, tile_h, img_w, img_h}` (object) or plain URL string (backward compat). Map handles both via `getMiniTextures()`.
- **Full reference**: see `THEMES.md` for creating custom themes, sprites, and unit images.

## Key conventions
- War lifecycle: `setup` → `running` → `ended`. Players can join even while `running`.
- Resources per-city, capped at `max_*`. `syncCity()` catches up elapsed production on every read (lazy, no cron needed).
- Training and army arrivals auto-complete on API read (checks `finishes_at <= now()` / `arrival_at <= now()`).
- Armies: `sendArmy($origin, $target, $units, $war, $mission)` — 5th param `'attack'` (default) or `'reinforce'`. Reinforce armies are separate `Army` records (`status=stationed`, `mission=reinforce`).
- Unit types are global (seeded once in `UnitTypeSeeder`), not per-war.
- Building cards in city view are hardcoded Blade placeholders (no DB model).
- Battle loot: attacker takes half of defender's city resources (capped by winner's origin city max caps).
- Founding cities: free if player has 0 cities; resource cost proportional to `construction_speed` for subsequent.
- Admin: `is_admin` on `users` table. Panel at `/admin/wars` (CRUD wars, change status, edit settings) and `/admin/themes` (full theme CRUD with color pickers, battle effect dropdown, sprite uploads).

## Translations
- Default: `pt_BR`. Fallback: `en`.
- All keys in `lang/pt_BR.json` (English keys, Portuguese values). No PHP lang files — JSON only.
- Views use `__('English String')`; JS uses `__i18n()` (identity — new keys need Blade-rendered strings).
- CRITICAL: Never create `lang/*.php` files — `__('Messages')` matched `messages.php` on Windows case-insensitively and returned the whole array.

## Gotchas
- Avatar files stored in `public/avatars/` (not Storage disk). Accessor: `url('avatars/' . $filename)`.
- No queue workers in dev (sync driver). Scheduler in `routes/console.php`: resources/armies/training every minute, analytics every 10 min.
- Map tile coordinates use `x`, `y` columns (not lat/lng).
- **miniSpriteCache TDZ**: `const miniSpriteCache = {}` must be declared before the first `redrawTiles()` call at initial render — not after `function getMiniTextures` — or `ReferenceError` occurs.
- **Crop-only update path resolution**: In `ThemeController::handleSpriteUploads()`, when only crop dimensions change (no new file), construct local path as `$publicDir . '/' . basename(parse_url($url, PHP_URL_PATH))`. Do NOT use `public_path(ltrim(...))` — the URL has a subdirectory prefix and path doubles.
- **getMiniTextures fallback**: Falls back to full uncropped image if `img_w`/`img_h` are missing or grid math produces 0 tiles/rows — prevents invisible tiles but shows wrong image.
- **Old spritesheet config**: `spritesheet_*` config keys are auto-cleaned on theme save. If theme data seems stale, re-save via admin UI.
- Chart.js v4.4.7 loaded from CDN in analytics view.
- Unit images: `unit_types.image` column, displayed as 16×16 thumbnails in garrison/training views.
- Base rename: `POST /wars/{war}/bases/{base}/rename` form on bases show page.
