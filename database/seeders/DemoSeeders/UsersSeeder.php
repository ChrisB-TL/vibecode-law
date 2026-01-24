<?php

namespace Database\Seeders\DemoSeeders;

use App\Enums\TeamType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'email' => 'chris.j.bridges@gmail.com',
            'first_name' => 'Chris',
            'last_name' => 'Bridges',
            'handle' => 'chris-bridges',
            'job_title' => 'COO',
            'organisation' => 'Tacit Legal LLP',
            'bio' => fake()->paragraphs(2, true),
            'email_verified_at' => Date::now(),
            'team_type' => TeamType::CoreTeam,
            'is_admin' => true,
        ]);

        User::create([
            'email' => 'matt@lupl.com',
            'first_name' => 'Matt',
            'last_name' => 'Pollins',
            'handle' => 'matt-pollins',
            'job_title' => 'CPO',
            'organisation' => 'Lupl',
            'bio' => fake()->paragraphs(2, true),
            'email_verified_at' => Date::now(),
            'team_type' => TeamType::CoreTeam,
            'is_admin' => true,
        ]);

        User::create([
            'email' => 'alex@legaltechcollective.com',
            'first_name' => 'Alex',
            'last_name' => 'Baker',
            'handle' => 'alex-baker',
            'job_title' => 'Founder',
            'organisation' => 'Legal Tech Collective',
            'bio' => fake()->paragraphs(2, true),
            'email_verified_at' => Date::now(),
            'team_type' => TeamType::CoreTeam,
        ]);

        User::factory()->count(6)->collaborator()->create();

        User::factory()->count(50)->create();
    }
}
