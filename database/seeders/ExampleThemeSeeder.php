<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ExampleThemeSeeder extends Seeder
{
    public function run(): void
    {
        Theme::updateOrCreate(['name' => 'tropical'], [
            'name' => 'tropical',
            'label' => 'Tropical',
            'description' => 'Lush tropical islands with turquoise waters and sandy beaches.',
            'is_default' => false,
            'config' => [
                'label' => 'Tropical',
                'description' => 'Lush tropical islands with turquoise waters and sandy beaches.',
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

        $this->info('Tropical theme created! Assign it to a war via admin panel or:');
        $this->info('  php artisan tinker --execute="War::find(1)->update([\'theme\' => \'tropical\']);"');
    }
}
