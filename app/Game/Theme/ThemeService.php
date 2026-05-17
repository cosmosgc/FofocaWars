<?php

namespace App\Game\Theme;

use App\Models\Theme;
use App\Models\War;

class ThemeService
{
    public function getTheme(?string $themeName = null): array
    {
        if ($themeName) {
            $theme = Theme::where('name', $themeName)->first();
        }
        if (!isset($theme)) {
            $theme = Theme::where('is_default', true)->first();
        }
        $config = $theme?->config;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        return is_array($config) ? $config : $this->defaultConfig();
    }

    public function forWar(War $war): array
    {
        return $this->getTheme($war->theme);
    }

    public function terrainColors(War $war): array
    {
        $config = $this->forWar($war);
        return $config['colors']['terrain'] ?? $this->defaultConfig()['colors']['terrain'];
    }

    public function cssVariables(War $war): string
    {
        $config = $this->forWar($war);
        $css = $config['css'] ?? [];
        $parts = [];
        foreach ($css as $key => $value) {
            $parts[] = "$key: $value;";
        }

        $terrain = $config['colors']['terrain'] ?? [];
        foreach ($terrain as $type => $color) {
            $hex = dechex($color);
            $parts[] = "--terrain-$type: #$hex;";
        }

        return implode("\n", $parts);
    }

    public function allThemes(): array
    {
        return Theme::orderBy('label')->get()->toArray();
    }

    public function legendColors(War $war): array
    {
        $terrain = $this->terrainColors($war);
        $result = [];
        foreach ($terrain as $type => $color) {
            if (is_string($color) && str_starts_with($color, '#')) {
                $result[$type] = $color;
            } else {
                $result[$type] = '#' . dechex((int) $color);
            }
        }
        return $result;
    }

    private function defaultConfig(): array
    {
        return [
            'label' => 'Medieval',
            'description' => 'Classic medieval fantasy setting with lush green landscapes.',
            'colors' => [
                'primary' => '#4f46e5',
                'secondary' => '#059669',
                'accent' => '#d97706',
                'terrain' => [
                    'plain' => '#90c96a',
                    'forest' => '#2d6a27',
                    'mountain' => '#8a7f6e',
                    'water' => '#4a90d9',
                    'desert' => '#e8d5a3',
                ],
            ],
            'css' => [
                '--theme-primary' => '#4f46e5',
                '--theme-secondary' => '#059669',
                '--theme-accent' => '#d97706',
            ],
            'battle_effect' => 'explosion',
        ];
    }
}
