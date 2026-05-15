# Lightweight Browser Wargame Architecture

## Laravel + Blade + Tailwind + PIXI.js

### Shared Hosting Friendly Strategy Game Framework

---

# Project Vision

Create a persistent browser strategy game inspired by:

* Travian
* Clash of Clans
* Tribal Wars
* OGame

But designed with:

* Simple hosting compatibility
* Minimal infrastructure requirements
* Deterministic simulations
* No realtime websocket dependency
* No Redis dependency
* Low server cost
* Easy deployment
* Expandable architecture

The project should run on:

* Shared hosting
* VPS
* Basic Laravel hosting providers
* Cheap cloud instances

without requiring:

* Redis
* WebSockets
* Queue workers
* Kubernetes
* Dedicated realtime servers

---

# Core Design Philosophy

The engine should prioritize:

| Goal              | Description                                   |
| ----------------- | --------------------------------------------- |
| Simplicity        | Easy hosting and deployment                   |
| Determinism       | Server-authoritative predictable calculations |
| Modularity        | Systems separated cleanly                     |
| Themeability      | Swappable art/themes                          |
| Persistence       | Long-running worlds                           |
| Scalability Later | Advanced optimization optional                |
| Browser Friendly  | Mostly polling/fetch-based                    |

---

# Simplified Stack

## Backend

| Technology        | Purpose                |
| ----------------- | ---------------------- |
| Laravel           | Main backend           |
| MySQL             | Persistent storage     |
| PHP Cron          | Tick processing        |
| Laravel Scheduler | Automated updates      |
| REST APIs         | Frontend communication |

---

## Frontend

| Technology   | Purpose           |
| ------------ | ----------------- |
| Blade        | Main UI           |
| Tailwind CSS | Styling           |
| Alpine.js    | UI interactions   |
| PixiJS       | Map rendering     |
| Chart.js     | Statistics graphs |

---

# Hosting Philosophy

This architecture should work with:

| Hosting Type      | Supported      |
| ----------------- | -------------- |
| Shared Hosting    | Yes            |
| cPanel Hosting    | Yes            |
| Small VPS         | Yes            |
| Docker            | Optional       |
| Redis             | Optional later |
| WebSockets        | No             |
| Dedicated Workers | No             |

---

# High Level Architecture

```text id="lw14a1"
Browser
 ├── Blade UI
 ├── Tailwind
 ├── Alpine.js
 ├── PIXI.js
 └── Polling Requests

Laravel Backend
 ├── Controllers
 ├── Game Services
 ├── Battle Simulator
 ├── Diplomacy Systems
 ├── Messaging
 ├── Analytics
 └── Admin Panel

Database
 ├── World Data
 ├── Players
 ├── Armies
 ├── Messages
 ├── Diplomacy
 └── Statistics
```

---

# Why Avoid Realtime Infrastructure

The game is not intended for:

* Twitch gameplay
* Action combat
* Thousands of concurrent users
* Frame-perfect multiplayer

Instead:

* Movements take minutes/hours
* Battles are calculated server-side
* Notifications can refresh periodically
* Social systems are asynchronous

This makes polling perfectly acceptable.

---

# Polling Strategy

Instead of WebSockets:

```text id="92gdk1"
Every 10 seconds:
fetch('/api/notifications')
fetch('/api/army-updates')
fetch('/api/messages')
```

---

# Advantages

| Benefit                        |
| ------------------------------ |
| Easier hosting                 |
| Easier debugging               |
| Lower server cost              |
| Fewer dependencies             |
| More stable                    |
| Less infrastructure complexity |

---

# Wars (Game Servers)

A “War” acts as a server/session.

Each war is isolated.

---

# War Features

| Feature                  |
| ------------------------ |
| Unique map               |
| Unique rules             |
| Unique speed             |
| Unique theme             |
| Independent playerbase   |
| Configurable multipliers |

---

# Example War Configuration

```json id="z9p82c"
{
  "name": "Galactic War",
  "theme": "space",
  "map_width": 2000,
  "map_height": 2000,
  "resource_multiplier": 2,
  "troop_speed_multiplier": 1.5,
  "construction_speed": 1,
  "max_bases_per_player": 10
}
```

---

# Theme System

The game logic must stay generic.

Themes only swap:

* Graphics
* Sounds
* Unit names
* Building names
* UI styling

---

# Example Theme Structure

```text id="8w72lu"
/themes
    /medieval
    /modern
    /space
```

---

# Example Unit Mapping

| Generic Role | Medieval  | Modern    | Space         |
| ------------ | --------- | --------- | ------------- |
| infantry     | swordsman | rifleman  | marine        |
| siege        | catapult  | artillery | plasma cannon |
| scout        | horseman  | jeep      | drone         |

---

# Map System

---

# Tile-Based World

Each war contains a grid map.

Example:

```text id="1xy9bp"
[0,0] [1,0] [2,0]
[0,1] [1,1] [2,1]
```

---

# Tile Data

| Field         | Purpose         |
| ------------- | --------------- |
| x             | Position        |
| y             | Position        |
| terrain_type  | Terrain         |
| owner_id      | Territory owner |
| resource_type | Tile resource   |
| structure_id  | Building        |
| visibility    | Fog of war      |

---

# PIXI.js Usage

Use PixiJS for:

* World map
* Zooming
* Panning
* Army movement animation
* Territory visualization
* Battle replay visualization

---

# Important Design Choice

PIXI.js should be VISUAL ONLY.

The server remains authoritative.

This prevents cheating.

---

# Army Movement System

Army movement should be deterministic.

The server calculates:

* Distance
* Speed
* Terrain modifiers
* Arrival time

---

# Movement Formula

Example:

t = \frac{d}{s \cdot m}

Where:

| Variable | Meaning                |
| -------- | ---------------------- |
| t        | travel time            |
| d        | distance               |
| s        | troop speed            |
| m        | terrain/war multiplier |

---

# Movement Flow

```text id="hk6w2m"
Player sends army
→ Server calculates arrival timestamp
→ Database stores movement
→ Frontend polls periodically
→ Army arrives automatically
```

---

# No Continuous Simulation Required

You do NOT need:

* Realtime physics
* Per-frame updates
* Active movement processing

Instead:

```text id="u2plc0"
arrival_time = now + calculated_duration
```

Then:

```text id="p9z7qa"
if(now >= arrival_time)
resolve movement
```

Very lightweight.

---

# Deterministic Battle System

Battles must always generate the same result from the same input.

This prevents:

* Desync
* Cheating
* Complex infrastructure

---

# Battle Formula Philosophy

Server calculates using:

* Troop stats
* Terrain modifiers
* Defense bonuses
* Morale
* Technology
* Random seed (optional fixed seed)

---

# Example Battle Formula

P = A \cdot T \cdot M

Where:

| Variable | Meaning               |
| -------- | --------------------- |
| P        | final power           |
| A        | attack/defense value  |
| T        | technology multiplier |
| M        | terrain modifier      |

---

# Battle Flow

```text id="pb0rzm"
Battle starts
→ Server gathers units
→ Server calculates outcome
→ Battle report stored
→ Frontend displays replay
```

---

# Visual Battle Replays

Optional:

Use PixiJS to replay results visually.

The replay is cosmetic only.

The battle already happened server-side.

---

# Players & Bases

Players can own:

* Multiple cities
* Multiple outposts
* Multiple territories

---

# City Systems

Each city contains:

| Feature             |
| ------------------- |
| Resource production |
| Construction queue  |
| Defense             |
| Army station        |
| Population          |
| Technology          |

---

# Resource System

Resources should update periodically.

Example:

```text id="1s2pml"
wood
stone
food
metal
energy
```

---

# Simple Tick System

Instead of realtime workers:

Use Laravel Scheduler + Cron.

Example:

```text id="3ukynf"
* * * * * php artisan schedule:run
```

---

# Scheduler Tasks

| Task                 | Frequency        |
| -------------------- | ---------------- |
| Resource generation  | Every minute     |
| Construction updates | Every minute     |
| Army arrival checks  | Every minute     |
| Diplomacy expiration | Every 5 minutes  |
| Statistics snapshots | Every 10 minutes |

---

# Why This Works

These games are naturally slow-paced.

A 1-minute resolution is usually acceptable.

Especially for:

* Travel
* Economy
* Construction

---

# Diplomacy System

---

# Alliances / Clans / Factions

Players can create organizations.

---

# Features

| Feature               |
| --------------------- |
| Shared chat           |
| Shared diplomacy      |
| Alliance rankings     |
| Officer permissions   |
| Shared territory      |
| Alliance descriptions |

---

# Diplomacy Relations

| Type                |
| ------------------- |
| Allied              |
| Neutral             |
| Ceasefire           |
| Trade Pact          |
| War                 |
| Non-aggression Pact |

---

# War Declaration System

Should include:

* Cooldowns
* Timers
* Notifications
* Public history

---

# Messaging System

---

# Social Features

| Feature              |
| -------------------- |
| Private messages     |
| Alliance chat        |
| Notifications        |
| Battle reports       |
| Global announcements |

---

# Polling-Based Notifications

Example:

```javascript id="p8twg5"
setInterval(async () => {
    const response = await fetch('/api/notifications');
    const data = await response.json();
}, 10000);
```

---

# Database Structure

---

# Core Tables

## Wars

```text id="h4s5pw"
wars
war_settings
```

---

## Players

```text id="zjlwm4"
users
war_players
```

---

## World

```text id="2dz8v6"
tiles
territories
cities
```

---

## Armies

```text id="s4c90q"
units
armies
army_movements
battle_reports
```

---

## Diplomacy

```text id="br1gcl"
alliances
alliance_members
diplomacy_relations
```

---

## Communication

```text id="v2l5wb"
conversations
messages
notifications
```

---

## Statistics

```text id="f84m0r"
resource_history
army_history
territory_history
```

---

# Statistics & Analytics

Track:

* Territory growth
* Army count
* Resource production
* Wealth
* Battle wins/losses

Use Chart.js for:

* Line graphs
* Progress charts
* Rankings

---

# Admin Panel

Admins should manage:

| Feature           |
| ----------------- |
| Create wars       |
| Edit war settings |
| Manage maps       |
| Moderate chat     |
| Ban players       |
| Spawn resources   |
| Force diplomacy   |

---

# Suggested Folder Structure

```text id="g1kq1z"
app/
    Game/
        Army/
        Battle/
        Diplomacy/
        Economy/
        Map/
        Theme/
        Analytics/
```

---

# Recommended Development Order

---

# Phase 1 — Foundation

* Authentication
* War system
* Tile map
* PIXI renderer
* Cities
* Resources

---

# Phase 2 — Armies

* Unit movement
* Army calculations
* Battle resolution
* Battle reports

---

# Phase 3 — Social Systems

* Alliances
* Messaging
* Diplomacy
* Notifications

---

# Phase 4 — Analytics

* Rankings
* Graphs
* History tracking

---

# Phase 5 — Theme Engine

* Theme packs
* Asset replacement
* UI skins
* Battle effects

---

# Long-Term Optional Improvements

Later, IF necessary:

| Feature        | Priority |
| -------------- | -------- |
| Redis cache    | Optional |
| Queues         | Optional |
| WebSockets     | Optional |
| Chunked maps   | Optional |
| Battle workers | Optional |

The architecture should function completely without them initially.

---

# Recommended Philosophy

Build the game like classic browser strategy games:

* Slow paced
* Server authoritative
* Polling-based
* Deterministic
* Lightweight
* Persistent

This dramatically reduces complexity while still supporting:

* Diplomacy
* Territory warfare
* Large maps
* Persistent worlds
* Social gameplay
* Multi-theme support
