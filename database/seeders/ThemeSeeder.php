<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'medieval',
                'label' => 'Medieval',
                'description' => 'Classic medieval fantasy setting with lush green landscapes.',
                'is_default' => true,
                'config' => [
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
                        'city' => [
                            'fill' => '#f5a623',
                            'stroke' => '#ffd700',
                        ],
                        'bases' => [
                            'resource' => '#22c55e',
                            'military' => '#ef4444',
                            'trade' => '#3b82f6',
                            'alliance' => '#a855f7',
                        ],
                    ],
                    'css' => [
                        '--theme-primary' => '#4f46e5',
                        '--theme-secondary' => '#059669',
                        '--theme-accent' => '#d97706',
                    ],
                    'battle_effect' => 'explosion',
                ],
            ],
            [
                'name' => 'desert',
                'label' => 'Desert',
                'description' => 'Arid wastelands with scorched sands and rocky outcrops.',
                'is_default' => false,
                'config' => [
                    'label' => 'Desert',
                    'description' => 'Arid wastelands with scorched sands and rocky outcrops.',
                    'colors' => [
                        'primary' => '#ea580c',
                        'secondary' => '#ca8a04',
                        'accent' => '#dc2626',
                        'terrain' => [
                            'plain' => '#d4a853',
                            'forest' => '#7c6b3e',
                            'mountain' => '#b8864e',
                            'water' => '#5ba3d9',
                            'desert' => '#f0d78a',
                        ],
                        'city' => [
                            'fill' => '#d97706',
                            'stroke' => '#f59e0b',
                        ],
                        'bases' => [
                            'resource' => '#22c55e',
                            'military' => '#ef4444',
                            'trade' => '#3b82f6',
                            'alliance' => '#a855f7',
                        ],
                    ],
                    'css' => [
                        '--theme-primary' => '#ea580c',
                        '--theme-secondary' => '#ca8a04',
                        '--theme-accent' => '#dc2626',
                    ],
                    'battle_effect' => 'sandstorm',
                ],
            ],
            [
                'name' => 'winter',
                'label' => 'Winter',
                'description' => 'Frozen tundra covered in snow and ice.',
                'is_default' => false,
                'config' => [
                    'label' => 'Winter',
                    'description' => 'Frozen tundra covered in snow and ice.',
                    'colors' => [
                        'primary' => '#7dd3fc',
                        'secondary' => '#a5f3fc',
                        'accent' => '#e0f2fe',
                        'terrain' => [
                            'plain' => '#dce8f0',
                            'forest' => '#6b8f9e',
                            'mountain' => '#b0c4d6',
                            'water' => '#6ba3d9',
                            'desert' => '#f0f4f8',
                        ],
                        'city' => [
                            'fill' => '#94a3b8',
                            'stroke' => '#cbd5e1',
                        ],
                        'bases' => [
                            'resource' => '#22c55e',
                            'military' => '#ef4444',
                            'trade' => '#3b82f6',
                            'alliance' => '#a855f7',
                        ],
                    ],
                    'css' => [
                        '--theme-primary' => '#7dd3fc',
                        '--theme-secondary' => '#a5f3fc',
                        '--theme-accent' => '#e0f2fe',
                    ],
                    'battle_effect' => 'snow',
                ],
            ],
            [
                'name' => 'neon',
                'label' => 'Neon',
                'description' => 'Cyberpunk futuristic world with vibrant neon colors.',
                'is_default' => false,
                'config' => [
                    'label' => 'Neon',
                    'description' => 'Cyberpunk futuristic world with vibrant neon colors.',
                    'colors' => [
                        'primary' => '#d946ef',
                        'secondary' => '#06b6d4',
                        'accent' => '#10b981',
                        'terrain' => [
                            'plain' => '#1a1a2e',
                            'forest' => '#16213e',
                            'mountain' => '#0f3460',
                            'water' => '#533483',
                            'desert' => '#2d1b4e',
                        ],
                        'city' => [
                            'fill' => '#06b6d4',
                            'stroke' => '#22d3ee',
                        ],
                        'bases' => [
                            'resource' => '#22c55e',
                            'military' => '#ef4444',
                            'trade' => '#3b82f6',
                            'alliance' => '#a855f7',
                        ],
                    ],
                    'css' => [
                        '--theme-primary' => '#d946ef',
                        '--theme-secondary' => '#06b6d4',
                        '--theme-accent' => '#10b981',
                    ],
                    'battle_effect' => 'cyber',
                ],
            ],
        ];

        foreach ($themes as $data) {
            Theme::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
