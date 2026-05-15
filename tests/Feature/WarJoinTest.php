<?php

use App\Models\User;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Models\Tile;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
});

test('user can join a war which generates map and creates city', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);

    $this->actingAs($user)
        ->post(route('wars.join', $war));

    $this->assertDatabaseHas('war_players', [
        'war_id' => $war->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('cities', [
        'war_id' => $war->id,
    ]);

    $city = City::where('war_id', $war->id)->first();
    $this->assertNotNull($city);

    $this->assertDatabaseHas('tiles', [
        'war_id' => $war->id,
        'x' => $city->tile_x,
        'y' => $city->tile_y,
        'owner_id' => $city->owner_id,
    ]);

    $tileCount = Tile::where('war_id', $war->id)->count();
    $expectedTiles = $war->map_width * $war->map_height;
    expect($tileCount)->toBe($expectedTiles);
});

test('api returns tiles for war map', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);

    $this->actingAs($user)
        ->post(route('wars.join', $war));

    $response = $this->actingAs($user)
        ->get('/api/wars/' . $war->id . '/tiles');

    $response->assertStatus(200);
    $response->assertJsonCount($war->map_width * $war->map_height);
});

test('city show page loads for the owner', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);

    $this->actingAs($user)
        ->post(route('wars.join', $war));

    $city = City::where('war_id', $war->id)->first();

    $response = $this->actingAs($user)
        ->get(route('cities.show', [$war, $city]));

    $response->assertStatus(200);
    $response->assertSee($city->name);
});

test('api returns resources for player cities', function () {
    $user = User::factory()->create();
    $war = War::factory()->create(['status' => 'setup']);

    $this->actingAs($user)
        ->post(route('wars.join', $war));

    $response = $this->actingAs($user)
        ->get('/api/wars/' . $war->id . '/resources');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'cities' => [['id', 'name', 'wood', 'stone', 'food', 'metal', 'max_wood', 'max_stone', 'max_food', 'max_metal']],
        'rates' => ['wood', 'stone', 'food', 'metal'],
    ]);
});
