<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('admin.wars.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'theme' => 'required|string|in:medieval,modern,space',
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
        return view('admin.wars.edit', compact('war'));
    }

    public function update(Request $request, War $war)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'theme' => 'required|string|in:medieval,modern,space',
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
