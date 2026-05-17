# Theme Engine — Guide

## Overview

Themes change the visual appearance of a war: map terrain colors, city markers, base colors, battle effects, and CSS accent colors. The system is data-driven — no PHP code changes needed to add a new theme.

## Themes Table

| Column       | Type    | Description                                     |
|--------------|---------|-------------------------------------------------|
| `name`       | string  | Unique identifier (e.g. `tropical`). FK target from `wars.theme`. |
| `label`      | string  | Human-readable name shown in admin UI.          |
| `description`| text    | Brief description.                              |
| `is_default` | boolean | If `true`, used as fallback when a war's theme is not found. |
| `config`     | JSON    | Full theme configuration (see below).           |

## Config JSON Reference

```json
{
  "label": "My Theme",
  "description": "What this theme looks like.",
  "colors": {
    "primary": "#...",
    "secondary": "#...",
    "accent": "#...",
    "terrain": {
      "plain": "#...",
      "forest": "#...",
      "mountain": "#...",
      "water": "#...",
      "desert": "#..."
    },
    "city": {
      "fill": "#...",
      "stroke": "#..."
    },
    "bases": {
      "resource": "#...",
      "military": "#...",
      "trade": "#...",
      "alliance": "#..."
    }
  },
  "css": {
    "--theme-primary": "#...",
    "--theme-secondary": "#...",
    "--theme-accent": "#..."
  },
  "battle_effect": "explosion"
}
```

### Fields

| Path | Required | Used for |
|------|----------|----------|
| `label` | yes | Display name |
| `description` | no | Tooltip / info |
| `colors.primary` | yes | Reserved for UI accent |
| `colors.secondary` | yes | Reserved for UI accent |
| `colors.accent` | yes | Reserved for UI accent |
| `colors.terrain.*` | yes | 5 terrain types drawn on the PixiJS map |
| `colors.city.fill` | no | City marker fill color (default: `#f5a623`) |
| `colors.city.stroke` | no | City marker glow color (default: `#ffd700`) |
| `colors.bases.*` | no | 4 base type colors on map (default: green/red/blue/purple) |
| `css.*` | no | CSS custom properties injected into page `<style>` |
| `battle_effect` | no | Animation played on battle tiles (see below) |

### Battle Effects

| Value       | Visual                           |
|-------------|----------------------------------|
| `explosion` | Orange/yellow fire burst         |
| `sandstorm` | Sandy/brown particle swirl       |
| `snow`      | White/blue ice shards            |
| `cyber`     | Cyan/magenta neon particles      |

## How to Create a New Theme

### Option A — Artisan Tinker

```bash
php artisan tinker
```

```php
Theme::create([
    'name' => 'tropical',
    'label' => 'Tropical',
    'description' => 'Lush tropical islands with turquoise waters.',
    'is_default' => false,
    'config' => [
        'label' => 'Tropical',
        'description' => 'Lush tropical islands with turquoise waters.',
        'colors' => [
            'primary' => '#0ea5e9',
            'secondary' => '#f97316',
            'accent' => '#84cc16',
            'terrain' => [
                'plain' => '#7ec850',
                'forest' => '#1a8a3c',
                'mountain' => '#a08f7a',
                'water' => '#38bdf8',
                'desert' => '#fde68a',
            ],
            'city' => [
                'fill' => '#f59e0b',
                'stroke' => '#fbbf24',
            ],
            'bases' => [
                'resource' => '#22c55e',
                'military' => '#ef4444',
                'trade' => '#3b82f6',
                'alliance' => '#a855f7',
            ],
        ],
        'css' => [
            '--theme-primary' => '#0ea5e9',
            '--theme-secondary' => '#f97316',
            '--theme-accent' => '#84cc16',
        ],
        'battle_effect' => 'explosion',
    ],
]);
```

### Option B — Raw SQL

```sql
INSERT INTO themes (name, label, description, is_default, config, created_at, updated_at)
VALUES (
    'tropical',
    'Tropical',
    'Lush tropical islands with turquoise waters.',
    0,
    '{"label":"Tropical","description":"Lush tropical islands with turquoise waters.","colors":{"primary":"#0ea5e9","secondary":"#f97316","accent":"#84cc16","terrain":{"plain":"#7ec850","forest":"#1a8a3c","mountain":"#a08f7a","water":"#38bdf8","desert":"#fde68a"},"city":{"fill":"#f59e0b","stroke":"#fbbf24"},"bases":{"resource":"#22c55e","military":"#ef4444","trade":"#3b82f6","alliance":"#a855f7"}},"css":{"--theme-primary":"#0ea5e9","--theme-secondary":"#f97316","--theme-accent":"#84cc16"},"battle_effect":"explosion"}',
    NOW(),
    NOW()
);
```

### Option C — Seeder (for repeatable deploys)

Create or edit `database/seeders/ThemeSeeder.php`, add to the `$themes` array, then run:

```bash
php artisan db:seed --class=ThemeSeeder --force
```

## How to Assign a Theme to a War

### In Admin Panel

1. Go to `/admin/wars`
2. Edit a war
3. Select the theme from the dropdown
4. Save

### Via Tinker

```php
$war = War::find(1);
$war->update(['theme' => 'tropical']);
```

### Via Migration

```php
Schema::table('wars', function (Blueprint $table) {
    $table->string('theme', 50)->default('medieval')->change();
});
```

Then:

```bash
php artisan tinker --execute="War::where('theme', 'neon')->update(['theme' => 'tropical']);"
```

## Tips

- **Contrast**: Ensure terrain colors contrast enough to be distinguishable on the map at zoom levels. Avoid two terrains with similar luminance.
- **Water**: Make water tiles visually distinct from buildable terrain so players can see where they can't build.
- **Dark themes**: The map background is always `#111118` (near-black). Neon-style themes with dark terrain colors work well; set city colors to bright neon for visibility.
- **All terrains are required**: The `terrain` key must have all 5 types (`plain`, `forest`, `mountain`, `water`, `desert`).
- **No CSS changes needed**: Base colors default to green/red/blue/purple if not specified.
- **Re-seeding is safe**: `ThemeSeeder` uses `updateOrCreate` — it updates existing rows by `name`, never creates duplicates.

## Sprites (Map Images)

Themes support sprite images for terrain tiles and city markers. When a sprite is set for a terrain type, the PixiJS map renders the image instead of a solid color.

### Supported Sprite Keys (in config JSON)

| Config Key              | What it replaces           |
|-------------------------|----------------------------|
| `sprites/terrain/plain`  | Plain terrain color        |
| `sprites/terrain/forest` | Forest terrain color       |
| `sprites/terrain/mountain`| Mountain terrain color     |
| `sprites/terrain/water`  | Water terrain color        |
| `sprites/terrain/desert` | Desert terrain color       |
| `sprites/city`           | City marker circle         |

### How to Add Sprites

**Via Admin UI** (`/admin/themes`):
1. Create or edit a theme
2. Scroll to the Sprites section
3. Upload PNG/GIF/JPG/WebP images (max 1MB each)
4. Save — images are stored in `public/themes/{theme_name}/`

**Via Seeder** — add to the config array:

```php
'config' => [
    // ... colors, css, etc.
    'sprites/terrain/plain' => url('themes/my_theme/plain.png'),
    'sprites/terrain/forest' => url('themes/my_theme/forest.gif'),
    'sprites/city' => url('themes/my_theme/city.png'),
],
```

### Requirements

- Supported formats: `.png`, `.gif`, `.jpg`, `.webp`
- Recommended size: `32×32` pixels (tiles are rendered at `tileSize × tileSize`)
- City sprites: `24×24` pixels (centered on the tile)
- Animated GIFs work — they play on the PixiJS map
- If no sprite is provided for a terrain type, the solid color from `colors.terrain.*` is used as fallback

## Unit Images

Units can have an `image` field (stored in the `unit_types` table). This stores a URL path to the unit's sprite.

### Setting Unit Images

```php
$unitType = UnitType::where('name', 'swordsman')->first();
$unitType->update(['image' => url('themes/my_theme/units/swordsman.png')]);
```

Images are displayed in city view and army view as small thumbnails next to unit names. Supported formats: `.png`, `.gif`, `.jpg`, `.webp`.
