<?php

namespace Database\Seeders\DemoSeeders;

use App\Models\PracticeArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PracticeAreasSeeder extends Seeder
{
    public function run(): void
    {
        $practiceAreas = [
            'Commercial',
            'Real Estate',
            'Corporate',
            'Banking & Finance',
            'Family',
            'Employment',
            'Intellectual Property',
            'Litigation',
            'Tax',
            'Immigration',
            'Criminal',
            'Personal Injury',
            'Wills & Estates',
            'Environmental',
            'Construction',
        ];

        foreach ($practiceAreas as $name) {
            PracticeArea::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
