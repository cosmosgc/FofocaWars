<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\War;
use Illuminate\Http\Request;

class WarController extends Controller
{
    public function index()
    {
        $wars = War::withCount('warPlayers')->latest()->get();
        return view('admin.wars.index', compact('wars'));
    }

    public function create()
    {
        $themes = Theme::orderBy('label')->get();
        return view('admin.wars.create', compact('themes'));
    }

    public function store(Request $request)
    {
        $themeNames = Theme::pluck('name')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'theme' => 'required|string|in:' . implode(',', $themeNames),
            'map_width' => 'required|integer|min:10|max:500',
            'map_height' => 'required|integer|min:10|max:500',
            'resource_multiplier' => 'required|numeric|min:0.1|max:10',
            'troop_speed_multiplier' => 'required|numeric|min:0.1|max:10',
            'construction_speed' => 'required|numeric|min:0.1|max:10',
            'max_bases_per_player' => 'required|integer|min:1|max:50',
        ]);

        War::create(array_merge($validated, ['status' => 'setup']));

        return redirect()->route('admin.wars.index')
            ->with('success', __('War created successfully.'));
    }

    public function edit(War $war)
    {
        $themes = Theme::orderBy('label')->get();
        return view('admin.wars.edit', compact('war', 'themes'));
    }

    public function update(Request $request, War $war)
    {
        $themeNames = Theme::pluck('name')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'theme' => 'required|string|in:' . implode(',', $themeNames),
            'map_width' => 'required|integer|min:10|max:500',
            'map_height' => 'required|integer|min:10|max:500',
            'resource_multiplier' => 'required|numeric|min:0.1|max:10',
            'troop_speed_multiplier' => 'required|numeric|min:0.1|max:10',
            'construction_speed' => 'required|numeric|min:0.1|max:10',
            'max_bases_per_player' => 'required|integer|min:1|max:50',
            'status' => 'required|string|in:setup,running,ended',
        ]);

        $war->update($validated);

        return redirect()->route('admin.wars.index')
            ->with('success', __('War updated successfully.'));
    }

    public function destroy(War $war)
    {
        $war->delete();
        return redirect()->route('admin.wars.index')
            ->with('success', __('War deleted successfully.'));
    }
}
