<?php

use App\Models\User;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Models\Tile;
use App\Models\UnitType;
use App\Models\Unit;
use App\Models\Army;
use App\Models\TrainingQueue;
use App\Game\Army\ArmyService;
use App\Game\Battle\BattleService;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

    UnitType::create([
        'name' => 'Spearman', 'role' => 'infantry',
        'attack' => 8, 'defense' => 5, 'speed' => 12,
        'wood_cost' => 30, 'stone_cost' => 10, 'food_cost' => 20, 'metal_cost' => 10,
        'population_cost' => 1, 'tier' => 1, 'training_time' => 1,
    ]);
    UnitType::create([
        'name' => 'Swordsman', 'role' => 'infantry',
        'attack' => 15, 'defense' => 12, 'speed' => 10,
        'wood_cost' => 50, 'stone_cost' => 20, 'food_cost' => 30, 'metal_cost' => 30,
        'population_cost' => 1, 'tier' => 2, 'training_time' => 2,
    ]);
});

test('user can train units in their city', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);
    $this->actingAs($user)->post(route('wars.join', $war));
    $war->update(['status' => 'running']);

    $city = City::where('war_id', $war->id)->first();
    $unitType = UnitType::first();

    $response = $this->actingAs($user)->postJson(route('api.wars.train', $war), [
        'city_id' => $city->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 5,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('training_queue', [
        'city_id' => $city->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 5,
    ]);

    $city->refresh();
    expect($city->wood)->toBe(350);

    $entry = TrainingQueue::where('city_id', $city->id)->first();
    $entry->update(['finishes_at' => now()->subMinute()]);

    $this->artisan('game:tick-training')->assertExitCode(0);

    $this->assertDatabaseHas('units', [
        'city_id' => $city->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 5,
    ]);

    $this->assertDatabaseMissing('training_queue', ['city_id' => $city->id]);
});

test('user can send army to another city', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);
    $this->actingAs($user)->post(route('wars.join', $war));
    $war->update(['status' => 'running']);

    $city = City::where('war_id', $war->id)->first();
    $unitType = UnitType::first();

    $this->actingAs($user)->postJson(route('api.wars.train', $war), [
        'city_id' => $city->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 8,
    ]);

    $entry = TrainingQueue::where('city_id', $city->id)->first();
    $entry->update(['finishes_at' => now()->subMinute()]);
    $this->artisan('game:tick-training');

    $target = City::create([
        'war_id' => $war->id,
        'owner_id' => $city->owner_id,
        'name' => 'Target',
        'tile_x' => $city->tile_x + 5,
        'tile_y' => $city->tile_y + 5,
        'population' => 100,
    ]);

    $response = $this->actingAs($user)->post(route('armies.send', $war), [
        'origin_city_id' => $city->id,
        'target_city_id' => $target->id,
        'units' => [$unitType->id => 4],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('armies', [
        'origin_city_id' => $city->id,
        'target_city_id' => $target->id,
        'status' => 'marching',
    ]);

    $this->assertDatabaseHas('units', [
        'city_id' => $city->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 4,
    ]);
});

test('battle resolves when army arrives', function () {
    $attackerUser = User::factory()->create();
    $defenderUser = User::factory()->create();

    $war = War::factory()->create(['status' => 'setup']);
    $this->actingAs($attackerUser)->post(route('wars.join', $war));
    $this->actingAs($defenderUser)->post(route('wars.join', $war));
    $war->update(['status' => 'running']);

    $attackerCity = City::where('war_id', $war->id)->whereHas('owner', fn($q) => $q->where('user_id', $attackerUser->id))->first();
    $defenderPlayer = WarPlayer::where('war_id', $war->id)->where('user_id', $defenderUser->id)->first();

    $defenderCity = City::create([
        'war_id' => $war->id,
        'owner_id' => $defenderPlayer->id,
        'name' => 'Defender City',
        'tile_x' => $attackerCity->tile_x + 10,
        'tile_y' => $attackerCity->tile_y + 10,
        'population' => 100,
    ]);

    $unitType = UnitType::first();

    $this->actingAs($attackerUser)->postJson(route('api.wars.train', $war), [
        'city_id' => $attackerCity->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 8,
    ]);
    TrainingQueue::where('city_id', $attackerCity->id)->update(['finishes_at' => now()->subMinute()]);
    $this->artisan('game:tick-training');

    $this->actingAs($defenderUser)->postJson(route('api.wars.train', $war), [
        'city_id' => $defenderCity->id,
        'unit_type_id' => $unitType->id,
        'quantity' => 5,
    ]);
    TrainingQueue::where('city_id', $defenderCity->id)->update(['finishes_at' => now()->subMinute()]);
    $this->artisan('game:tick-training');

    $army = app(ArmyService::class)->sendArmy(
        $attackerCity,
        $defenderCity,
        [$unitType->id => 4],
        $war
    );

    $army->update(['arrival_at' => now()->subMinute()]);

    app(ArmyService::class)->tickArrivals($war);

    $this->assertDatabaseHas('battle_reports', [
        'attacker_army_id' => $army->id,
    ]);

    $report = \App\Models\BattleReport::where('attacker_army_id', $army->id)->first();
    expect($report->winner)->toBeIn(['attacker', 'defender']);
    expect($report->details)->toHaveKeys(['attack_power', 'defense_power']);
});

test('api returns unit types', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);
    $this->actingAs($user)->post(route('wars.join', $war));
    $war->update(['status' => 'running']);

    $response = $this->actingAs($user)->get(route('api.wars.unit-types', $war));
    $response->assertStatus(200);
    $response->assertJsonCount(2);
});
