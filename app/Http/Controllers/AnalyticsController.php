<?php

namespace App\Http\Controllers;

use App\Game\Analytics\AnalyticsService;
use App\Models\War;
use App\Models\WarPlayer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(War $war, AnalyticsService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $ranking = $service->ranking($war);

        $charts = [];

        $history = DB::table('resource_history')
            ->where('war_id', $war->id)
            ->where('war_player_id', $player->id)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'total', 'wood', 'stone', 'food', 'metal']);

        $charts['resources'] = [
            'labels' => $history->pluck('recorded_at')->map(fn($d) => $d->format('d/m H:i'))->toArray(),
            'datasets' => [
                ['label' => __('Total'), 'data' => $history->pluck('total')->toArray(), 'borderColor' => '#6366f1', 'fill' => false],
                ['label' => __('Wood'), 'data' => $history->pluck('wood')->toArray(), 'borderColor' => '#22c55e', 'fill' => false],
                ['label' => __('Stone'), 'data' => $history->pluck('stone')->toArray(), 'borderColor' => '#a855f7', 'fill' => false],
                ['label' => __('Food'), 'data' => $history->pluck('food')->toArray(), 'borderColor' => '#f59e0b', 'fill' => false],
                ['label' => __('Metal'), 'data' => $history->pluck('metal')->toArray(), 'borderColor' => '#6b7280', 'fill' => false],
            ],
        ];

        $armyHistory = DB::table('army_history')
            ->where('war_id', $war->id)
            ->where('war_player_id', $player->id)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'total_units', 'attack_power', 'defense_power']);

        $charts['army'] = [
            'labels' => $armyHistory->pluck('recorded_at')->map(fn($d) => $d->format('d/m H:i'))->toArray(),
            'datasets' => [
                ['label' => __('Units'), 'data' => $armyHistory->pluck('total_units')->toArray(), 'borderColor' => '#ef4444', 'fill' => false],
                ['label' => __('Attack Power'), 'data' => $armyHistory->pluck('attack_power')->map(fn($v) => round($v, 1))->toArray(), 'borderColor' => '#f97316', 'fill' => false],
                ['label' => __('Defense Power'), 'data' => $armyHistory->pluck('defense_power')->map(fn($v) => round($v, 1))->toArray(), 'borderColor' => '#3b82f6', 'fill' => false],
            ],
        ];

        $territoryHistory = DB::table('territory_history')
            ->where('war_id', $war->id)
            ->where('war_player_id', $player->id)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'city_count', 'base_count', 'tile_count']);

        $charts['territory'] = [
            'labels' => $territoryHistory->pluck('recorded_at')->map(fn($d) => $d->format('d/m H:i'))->toArray(),
            'datasets' => [
                ['label' => __('Cities'), 'data' => $territoryHistory->pluck('city_count')->toArray(), 'borderColor' => '#f5a623', 'fill' => false],
                ['label' => __('Bases'), 'data' => $territoryHistory->pluck('base_count')->toArray(), 'borderColor' => '#22c55e', 'fill' => false],
                ['label' => __('Tiles'), 'data' => $territoryHistory->pluck('tile_count')->toArray(), 'borderColor' => '#94a3b8', 'fill' => false],
            ],
        ];

        return view('analytics.index', compact('war', 'player', 'ranking', 'charts'));
    }
}
