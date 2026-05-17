<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::withCount('wars')->orderBy('label')->get();
        return view('admin.themes.index', compact('themes'));
    }

    public function create()
    {
        return view('admin.themes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:themes,name|alpha_dash',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean',
            'battle_effect' => 'nullable|string|in:explosion,sandstorm,snow,cyber',
            'colors_primary' => 'required|string|size:7',
            'colors_secondary' => 'required|string|size:7',
            'colors_accent' => 'required|string|size:7',
            'colors_terrain_plain' => 'required|string|size:7',
            'colors_terrain_forest' => 'required|string|size:7',
            'colors_terrain_mountain' => 'required|string|size:7',
            'colors_terrain_water' => 'required|string|size:7',
            'colors_terrain_desert' => 'required|string|size:7',
            'colors_city_fill' => 'nullable|string|size:7',
            'colors_city_stroke' => 'nullable|string|size:7',
            'sprite_terrain_plain' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_forest' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_mountain' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_water' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_desert' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_city' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
        ]);

        $config = [
            'label' => $validated['label'],
            'description' => $validated['description'] ?? '',
            'colors' => [
                'primary' => $validated['colors_primary'],
                'secondary' => $validated['colors_secondary'],
                'accent' => $validated['colors_accent'],
                'terrain' => [
                    'plain' => $validated['colors_terrain_plain'],
                    'forest' => $validated['colors_terrain_forest'],
                    'mountain' => $validated['colors_terrain_mountain'],
                    'water' => $validated['colors_terrain_water'],
                    'desert' => $validated['colors_terrain_desert'],
                ],
                'city' => [
                    'fill' => $validated['colors_city_fill'] ?? '#f5a623',
                    'stroke' => $validated['colors_city_stroke'] ?? '#ffd700',
                ],
                'bases' => [
                    'resource' => '#22c55e',
                    'military' => '#ef4444',
                    'trade' => '#3b82f6',
                    'alliance' => '#a855f7',
                ],
            ],
            'css' => [
                '--theme-primary' => $validated['colors_primary'],
                '--theme-secondary' => $validated['colors_secondary'],
                '--theme-accent' => $validated['colors_accent'],
            ],
            'battle_effect' => $validated['battle_effect'] ?? 'explosion',
        ];

        $theme = Theme::create([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'is_default' => $request->boolean('is_default'),
            'config' => $config,
        ]);

        $this->handleSpriteUploads($request, $theme, $config);

        return redirect()->route('admin.themes.index')
            ->with('success', __('Theme created successfully.'));
    }

    public function edit(Theme $theme)
    {
        return view('admin.themes.edit', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean',
            'battle_effect' => 'nullable|string|in:explosion,sandstorm,snow,cyber',
            'colors_primary' => 'required|string|size:7',
            'colors_secondary' => 'required|string|size:7',
            'colors_accent' => 'required|string|size:7',
            'colors_terrain_plain' => 'required|string|size:7',
            'colors_terrain_forest' => 'required|string|size:7',
            'colors_terrain_mountain' => 'required|string|size:7',
            'colors_terrain_water' => 'required|string|size:7',
            'colors_terrain_desert' => 'required|string|size:7',
            'colors_city_fill' => 'nullable|string|size:7',
            'colors_city_stroke' => 'nullable|string|size:7',
            'sprite_terrain_plain' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_forest' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_mountain' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_water' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_terrain_desert' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
            'sprite_city' => 'nullable|image|mimes:png,gif,jpg,webp|max:1024',
        ]);

        $config = $theme->config;
        $config['label'] = $validated['label'];
        $config['description'] = $validated['description'] ?? '';
        $config['colors']['primary'] = $validated['colors_primary'];
        $config['colors']['secondary'] = $validated['colors_secondary'];
        $config['colors']['accent'] = $validated['colors_accent'];
        $config['colors']['terrain']['plain'] = $validated['colors_terrain_plain'];
        $config['colors']['terrain']['forest'] = $validated['colors_terrain_forest'];
        $config['colors']['terrain']['mountain'] = $validated['colors_terrain_mountain'];
        $config['colors']['terrain']['water'] = $validated['colors_terrain_water'];
        $config['colors']['terrain']['desert'] = $validated['colors_terrain_desert'];
        $config['colors']['city']['fill'] = $validated['colors_city_fill'] ?? '#f5a623';
        $config['colors']['city']['stroke'] = $validated['colors_city_stroke'] ?? '#ffd700';
        $config['css']['--theme-primary'] = $validated['colors_primary'];
        $config['css']['--theme-secondary'] = $validated['colors_secondary'];
        $config['css']['--theme-accent'] = $validated['colors_accent'];
        $config['battle_effect'] = $validated['battle_effect'] ?? 'explosion';

        $theme->update([
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'is_default' => $request->boolean('is_default'),
            'config' => $config,
        ]);

        if ($request->boolean('is_default')) {
            Theme::where('is_default', true)->where('id', '!=', $theme->id)->update(['is_default' => false]);
        }

        $this->handleSpriteUploads($request, $theme, $config);

        return redirect()->route('admin.themes.index')
            ->with('success', __('Theme updated successfully.'));
    }

    public function destroy(Theme $theme)
    {
        $dir = public_path("themes/{$theme->name}");
        if (is_dir($dir)) {
            array_map('unlink', glob("$dir/*.*"));
            rmdir($dir);
        }

        $theme->delete();

        return redirect()->route('admin.themes.index')
            ->with('success', __('Theme deleted successfully.'));
    }

    private function handleSpriteUploads(Request $request, Theme $theme, array &$config): void
    {
        $spriteFields = [
            'sprite_terrain_plain' => 'sprites/terrain/plain',
            'sprite_terrain_forest' => 'sprites/terrain/forest',
            'sprite_terrain_mountain' => 'sprites/terrain/mountain',
            'sprite_terrain_water' => 'sprites/terrain/water',
            'sprite_terrain_desert' => 'sprites/terrain/desert',
            'sprite_city' => 'sprites/city',
        ];

        $basePath = "themes/{$theme->name}";
        $publicDir = public_path($basePath);

        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        foreach ($spriteFields as $field => $configKey) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $ext = $file->getClientOriginalExtension();
                $filename = str_replace('sprites/', '', $configKey) . '.' . $ext;
                $file->move($publicDir, $filename);
                $config[$configKey] = url("{$basePath}/{$filename}");
            }
        }

        if (!empty($request->file())) {
            $theme->update(['config' => $config]);
        }
    }
}
