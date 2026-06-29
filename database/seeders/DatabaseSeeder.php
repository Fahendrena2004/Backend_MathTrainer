<?php

namespace Database\Seeders;

use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $levels = [
            ['name' => 'CP', 'display_order' => 1, 'cycle' => 'Primaire', 'description' => 'Cours preparatoire'],
            ['name' => 'CE1', 'display_order' => 2, 'cycle' => 'Primaire', 'description' => 'Cours elementaire 1'],
            ['name' => 'CE2', 'display_order' => 3, 'cycle' => 'Primaire', 'description' => 'Cours elementaire 2'],
            ['name' => 'CM1', 'display_order' => 4, 'cycle' => 'Primaire', 'description' => 'Cours moyen 1'],
            ['name' => 'CM2', 'display_order' => 5, 'cycle' => 'Primaire', 'description' => 'Cours moyen 2'],
            ['name' => '6ème', 'display_order' => 6, 'cycle' => 'Collège', 'description' => 'Sixieme'],
            ['name' => '5ème', 'display_order' => 7, 'cycle' => 'Collège', 'description' => 'Cinquieme'],
            ['name' => '4ème', 'display_order' => 8, 'cycle' => 'Collège', 'description' => 'Quatrieme'],
            ['name' => '3ème', 'display_order' => 9, 'cycle' => 'Collège', 'description' => 'Troisieme'],
            ['name' => 'Seconde', 'display_order' => 10, 'cycle' => 'Lycée', 'description' => 'Seconde'],
            ['name' => 'Première scientifique', 'display_order' => 11, 'cycle' => 'Lycée', 'description' => 'Premiere scientifique'],
            ['name' => 'Première littéraire', 'display_order' => 12, 'cycle' => 'Lycée', 'description' => 'Premiere litteraire'],
            ['name' => 'Première OSE', 'display_order' => 13, 'cycle' => 'Lycée', 'description' => 'Premiere OSE'],
            ['name' => 'Terminale scientifique', 'display_order' => 14, 'cycle' => 'Lycée', 'description' => 'Terminale scientifique'],
            ['name' => 'Terminale littéraire', 'display_order' => 15, 'cycle' => 'Lycée', 'description' => 'Terminale litteraire'],
            ['name' => 'Terminale OSE', 'display_order' => 16, 'cycle' => 'Lycée', 'description' => 'Terminale OSE'],
            ['name' => 'Master 1', 'display_order' => 17, 'cycle' => 'Université', 'description' => 'Master 1'],
            ['name' => 'Master 2', 'display_order' => 18, 'cycle' => 'Université', 'description' => 'Master 2'],
        ];

        foreach ($levels as $level) {
            SchoolLevel::query()->updateOrCreate(['display_order' => $level['display_order']], $level);
        }

        $users = [
            ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password', 'school_level_id' => 1, 'role' => 'student'],
            ['name' => 'Admin MathTrainer', 'email' => 'admin@mathtrainer.app', 'password' => '123456', 'school_level_id' => 1, 'role' => 'admin'],
            ['name' => 'Aina Rakoto', 'email' => 'aina@example.com', 'password' => 'password', 'school_level_id' => 6, 'role' => 'student'],
            ['name' => 'Miora Rabe', 'email' => 'miora@example.com', 'password' => 'password', 'school_level_id' => 7, 'role' => 'student'],
            ['name' => 'Tahina Andry', 'email' => 'tahina@example.com', 'password' => 'password', 'school_level_id' => 8, 'role' => 'student'],
            ['name' => 'Soa Nantenaina', 'email' => 'soa@example.com', 'password' => 'password', 'school_level_id' => 9, 'role' => 'student'],
            ['name' => 'Lova Hery', 'email' => 'lova@example.com', 'password' => 'password', 'school_level_id' => 10, 'role' => 'student'],
            ['name' => 'Fetra Jean', 'email' => 'fetra@example.com', 'password' => 'password', 'school_level_id' => 11, 'role' => 'student'],
            ['name' => 'Iary Clara', 'email' => 'iary@example.com', 'password' => 'password', 'school_level_id' => 12, 'role' => 'student'],
            ['name' => 'Niry Paul', 'email' => 'niry@example.com', 'password' => 'password', 'school_level_id' => 13, 'role' => 'student'],
        ];

        foreach ($users as $user) {
            // The users list references school levels by display_order. Resolve to actual id.
            $level = SchoolLevel::query()->where('display_order', $user['school_level_id'])->first();
            $schoolLevelId = $level ? $level->id : null;

            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password_hash' => Hash::make($user['password']),
                    'school_level_id' => $schoolLevelId,
                    'role' => $user['role'],
                ]
            );
        }

        $this->call([
            MathTrainerSeeder::class,
        ]);
    }
}
