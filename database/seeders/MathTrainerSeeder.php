<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MathTrainerSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'Arithmetique', 'description' => 'Calculs de base, nombres entiers et decimaux', 'display_order' => 1],
            ['name' => 'Algebre', 'description' => 'Equations, fonctions et expressions litterales', 'display_order' => 2],
            ['name' => 'Geometrie', 'description' => 'Formes, aires, volumes et theoremes', 'display_order' => 3],
            ['name' => 'Analyse', 'description' => 'Limites, derivees et integrales', 'display_order' => 4],
            ['name' => 'Probabilites', 'description' => 'Hasard, denombrement et statistiques', 'display_order' => 5],
            ['name' => 'Logique', 'description' => 'Raisonnement, implication et demonstration', 'display_order' => 6],
            ['name' => 'Statistiques', 'description' => 'Moyenne, mediane, dispersion et graphiques', 'display_order' => 7],
            ['name' => 'Trigonometrie', 'description' => 'Angles, sinus, cosinus et tangente', 'display_order' => 8],
            ['name' => 'Nombres complexes', 'description' => 'Forme algebrique, module et argument', 'display_order' => 9],
            ['name' => 'Matrices', 'description' => 'Tableaux, operations et transformations lineaires', 'display_order' => 10],
        ];

        $topicNames = array_column($topics, 'name');
        foreach ($topics as $topic) {
            DB::table('topics')->updateOrInsert(
                ['name' => $topic['name']],
                array_merge($topic, ['is_active' => true])
            );
        }

        DB::table('topics')->whereNotIn('name', $topicNames)->update(['is_active' => false]);

        $topicIds = DB::table('topics')->whereIn('name', $topicNames)->pluck('id', 'name');
        DB::table('exercises')->whereNotIn('topic_id', $topicIds->values()->all())->update(['is_active' => false]);
        DB::table('courses')->whereNotIn('topic_id', $topicIds->values()->all())->update(['is_active' => false]);

        $levels = DB::table('school_levels')->orderBy('display_order')->get();
        $exerciseIdsByLevel = [];

        foreach ($levels as $level) {
            foreach ($topics as $topic) {
                for ($variant = 1; $variant <= 3; $variant++) {
                    $exercise = $this->exerciseFor($topic['name'], $level, $variant);

                    DB::table('exercises')->updateOrInsert(
                        ['title' => $exercise['title'], 'school_level_id' => $level->id],
                        [
                            'topic_id' => $topicIds[$topic['name']],
                            'school_level_id' => $level->id,
                            'title' => $exercise['title'],
                            'statement' => $exercise['statement'],
                            'exercise_type' => $exercise['exercise_type'],
                            'options' => $exercise['options'],
                            'expected_answer' => $exercise['answer'],
                            'correction' => $exercise['correction'],
                            'points_max' => 10 * $exercise['difficulty'],
                            'difficulty' => $exercise['difficulty'],
                            'chapter' => $topic['name'].' - '.$level->name,
                            'is_active' => true,
                            'is_new' => true,
                        ]
                    );

                    $exerciseId = DB::table('exercises')
                        ->where('title', $exercise['title'])
                        ->where('school_level_id', $level->id)
                        ->value('id');

                    $exerciseIdsByLevel[$level->id][] = $exerciseId;
                }

                $courseTitle = $topic['name'].' - '.$level->name.' - Cours complet';
                DB::table('courses')->updateOrInsert(
                    ['title' => $courseTitle, 'school_level_id' => $level->id],
                    [
                        'topic_id' => $topicIds[$topic['name']],
                        'school_level_id' => $level->id,
                        'title' => $courseTitle,
                        'description' => 'Cours de '.$topic['name'].' adapte au niveau '.$level->name.'.',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $courseId = DB::table('courses')
                    ->where('title', $courseTitle)
                    ->where('school_level_id', $level->id)
                    ->value('id');

                foreach ($this->chaptersFor($topic['name'], $level->name) as $order => $chapter) {
                    DB::table('course_chapters')->updateOrInsert(
                        ['course_id' => $courseId, 'display_order' => $order + 1],
                        [
                            'title' => $chapter['title'],
                            'content' => $chapter['content'],
                            'video_url' => null,
                            'display_order' => $order + 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        $badges = [
            ['name' => 'Premier succes', 'description' => 'Reussir un premier exercice', 'required_success_count' => 1],
            ['name' => 'Depart solide', 'description' => 'Atteindre 3 exercices reussis', 'required_success_count' => 3],
            ['name' => 'En rythme', 'description' => 'Atteindre 5 exercices reussis', 'required_success_count' => 5],
            ['name' => 'Dix sur la table', 'description' => 'Atteindre 10 exercices reussis', 'required_success_count' => 10],
            ['name' => 'Confiance', 'description' => 'Atteindre 15 exercices reussis', 'required_success_count' => 15],
            ['name' => 'Regulier', 'description' => 'Atteindre 20 exercices reussis', 'required_success_count' => 20],
            ['name' => 'Champion de calcul', 'description' => 'Atteindre 30 exercices reussis', 'required_success_count' => 30],
            ['name' => 'Expert', 'description' => 'Atteindre 40 exercices reussis', 'required_success_count' => 40],
            ['name' => 'Maitre du chapitre', 'description' => 'Atteindre 50 exercices reussis', 'required_success_count' => 50],
            ['name' => 'Excellence', 'description' => 'Atteindre 75 exercices reussis', 'required_success_count' => 75],
        ];

        foreach ($badges as $badge) {
            DB::table('badges')->updateOrInsert(
                ['name' => $badge['name']],
                array_merge($badge, [
                    'image_url' => null,
                    'unlock_condition' => 'success_count >= '.$badge['required_success_count'],
                    'is_active' => true,
                ])
            );
        }

        $this->seedStudentProgress($exerciseIdsByLevel);
    }

    private function seedStudentProgress(array $exerciseIdsByLevel): void
    {
        $students = DB::table('users')->where('role', 'student')->get();
        $badgeRules = DB::table('badges')->pluck('id', 'required_success_count')->all();

        foreach ($students as $student) {
            $availableExerciseIds = $exerciseIdsByLevel[$student->school_level_id] ?? [];
            if (empty($availableExerciseIds)) {
                $availableExerciseIds = DB::table('exercises')->pluck('id')->all();
            }

            $totalScore = 0;
            $totalSuccesses = 0;

            for ($sessionIndex = 1; $sessionIndex <= 2; $sessionIndex++) {
                $startedAt = now()->subDays(rand(1, 14))->subMinutes(rand(0, 60));
                $endedAt = (clone $startedAt)->addMinutes(10 + rand(5, 25));

                $sessionId = DB::table('learning_sessions')->insertGetId([
                    'user_id' => $student->id,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'score_total' => 0,
                ]);

                $sessionScore = 0;
                $questionIds = array_slice($availableExerciseIds, 0, 5);
                if (count($questionIds) < 5) {
                    $questionIds = array_merge($questionIds, array_slice(DB::table('exercises')->pluck('id')->all(), 0, 5 - count($questionIds)));
                }

                foreach ($questionIds as $exerciseId) {
                    $exercise = DB::table('exercises')->where('id', $exerciseId)->first();
                    if (!$exercise) {
                        continue;
                    }

                    $success = rand(0, 100) < 75;
                    $score = $success ? $exercise->points_max : max(1, intval($exercise->points_max * rand(30, 70) / 100));
                    $totalScore += $score;
                    $sessionScore += $score;
                    $totalSuccesses += $success ? 1 : 0;

                    DB::table('attempts')->insert([
                        'user_id' => $student->id,
                        'exercise_id' => $exerciseId,
                        'learning_session_id' => $sessionId,
                        'answer' => $success ? $exercise->expected_answer : 'Tentative incorrecte',
                        'score' => $score,
                        'success' => $success,
                        'time_spent' => rand(20, 120),
                        'created_at' => now(),
                    ]);
                }

                DB::table('learning_sessions')->where('id', $sessionId)->update(['score_total' => $sessionScore]);
            }

            DB::table('users')->where('id', $student->id)->update(['points_total' => $totalScore]);

            foreach ($badgeRules as $required => $badgeId) {
                if ($totalSuccesses >= $required) {
                    DB::table('user_badges')->updateOrInsert(
                        ['user_id' => $student->id, 'badge_id' => $badgeId],
                        ['earned_at' => now()->subDays(rand(0, 14))]
                    );
                }
            }
        }
    }

    private function exerciseFor(string $topic, object $level, int $variant): array
    {
        $levelOrder = (int) $level->display_order;
        $difficulty = min(3, max(1, intdiv($levelOrder - 1, 6) + 1));
        $base = $levelOrder + 4;
        $title = sprintf('%s - %s - Exercice %d', $topic, $level->name, $variant);
        $exerciseType = match ($variant) {
            1 => 'free_text',
            2 => 'multiple_choice',
            3 => 'true_false',
        };

        $data = match ($topic) {
            'Arithmetique' => $this->arithmetiqueQuestion($base, $variant),
            'Algebre' => $this->algebreQuestion($base, $variant),
            'Geometrie' => $this->geometrieQuestion($base, $variant),
            'Analyse' => $this->analyseQuestion($base, $variant),
            'Probabilites' => $this->probabilitesQuestion($base, $variant),
            'Logique' => $this->logiqueQuestion($base, $variant),
            'Statistiques' => $this->statistiquesQuestion($base, $variant),
            'Trigonometrie' => $this->trigonometrieQuestion($base, $variant),
            'Nombres complexes' => $this->complexesQuestion($base, $variant),
            'Matrices' => $this->matricesQuestion($base, $variant),
            default => ['statement' => 'Repondez a la question du theme '.$topic.'.', 'answer' => '1', 'options' => null],
        };

        return [
            'title' => $title,
            'statement' => $data['statement'],
            'answer' => $data['answer'],
            'difficulty' => $difficulty,
            'exercise_type' => $exerciseType,
            'options' => $data['options'] ?? null,
            'is_new' => true,
            'correction' => 'Correction adaptee au niveau '.$level->name.' pour le theme '.$topic.'.',
        ];
    }

    private function arithmetiqueQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "Calculez {$base} + " . ($base + 3) . '.', 'answer' => (string) ($base + $base + 3), 'options' => null];
        }

        if ($variant === 2) {
            $options = [($base + $base + 3), ($base + $base + 4), ($base + $base + 5), ($base + $base + 6)];
            shuffle($options);
            return ['statement' => "Quel est le resultat de {$base} + " . ($base + 3) . ' ?', 'answer' => (string) ($base + $base + 3), 'options' => implode('|', $options)];
        }

        return ['statement' => "Est-ce que {$base} + " . ($base + 1) . ' = ' . ($base + $base + 1) . ' ?', 'answer' => 'oui', 'options' => null];
    }

    private function algebreQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "{$variant}x = " . ($variant * $base) . '. Trouvez x.', 'answer' => (string) $base, 'options' => null];
        }

        if ($variant === 2) {
            $options = [$base, $base + 1, $base + 2, $base + 3];
            shuffle($options);
            return ['statement' => "Quel est x si " . ($variant + 2) . "x = " . (($variant + 2) * $base) . ' ?', 'answer' => (string) $base, 'options' => implode('|', $options)];
        }

        return ['statement' => "L'equation x + {$base} = " . ($base + 2) . '. Est-ce que x = 2 ?', 'answer' => 'oui', 'options' => null];
    }

    private function geometrieQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "Quel est le perimetre d'un carre de cote {$base} ?", 'answer' => (string) (4 * $base), 'options' => null];
        }

        if ($variant === 2) {
            $options = [4 * $base, 4 * ($base + 1), 4 * ($base - 1), 2 * $base];
            shuffle($options);
            return ['statement' => "Perimetre d'un carre de cote {$base} :", 'answer' => (string) (4 * $base), 'options' => implode('|', $options)];
        }

        return ['statement' => "Un carre de cote {$base} a-t-il une aire egale a " . ($base * $base) . ' ?', 'answer' => 'oui', 'options' => null];
    }

    private function analyseQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "Quelle est la derivee de f(x) = {$base}x + 2 ?", 'answer' => (string) $base, 'options' => null];
        }

        if ($variant === 2) {
            $options = [$base, $base + 1, $base - 1, 2 * $base];
            shuffle($options);
            return ['statement' => "Derivee de f(x) = {$base}x + 2 ?", 'answer' => (string) $base, 'options' => implode('|', $options)];
        }

        return ['statement' => "La derivee de {$base}x + 0 est-elle {$base} ?", 'answer' => 'oui', 'options' => null];
    }

    private function probabilitesQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "Dans une urne avec {$base} boules rouges et {$base} boules bleues, quelle est la probabilite de tirer une boule rouge ?", 'answer' => '1/2', 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'Parmi ' . (2 * $base) . ' boules, '.$base.' sont rouges et '.$base.' bleues. Probabilite :', 'answer' => '1/2', 'options' => '1/2|1/3|2/3|1/4'];
        }

        return ['statement' => 'La probabilite de tirer une boule rouge est-elle 1/2 ?', 'answer' => 'oui', 'options' => null];
    }

    private function logiqueQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => 'Si A implique B et A est vrai, B est-il vrai ?', 'answer' => 'oui', 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'Si A est faux et B est vrai, A implique B est-il vrai ?', 'answer' => 'oui', 'options' => 'oui|non'];
        }

        return ['statement' => 'Si A implique B et B est faux, A est-il forcement faux ?', 'answer' => 'non', 'options' => null];
    }

    private function statistiquesQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => "Calculez la moyenne de {$base}, " . ($base + 2) . ' et ' . ($base + 4) . '.', 'answer' => (string) ($base + 2), 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'La moyenne de ' . implode(', ', [$base, $base + 1, $base + 2]) . ' est-elle ' . ($base + 1) . ' ?', 'answer' => 'oui', 'options' => null];
        }

        return ['statement' => 'La mediane de ' . implode(', ', [$base, $base + 2, $base + 4]) . ' est-elle ' . ($base + 2) . ' ?', 'answer' => 'oui', 'options' => 'oui|non'];
    }

    private function trigonometrieQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => 'Quelle est la valeur de cos(0) ?', 'answer' => '1', 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'sin(0) vaut-elle 0 ?', 'answer' => 'oui', 'options' => 'oui|non'];
        }

        return ['statement' => 'Est-ce que cos(90 degres) est egal a 0 ?', 'answer' => 'oui', 'options' => null];
    }

    private function complexesQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => 'Quel est le module du nombre complexe i ?', 'answer' => '1', 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'Le module de 1 + i est-il sqrt(2) ?', 'answer' => 'oui', 'options' => 'oui|non'];
        }

        return ['statement' => 'Le nombre i eleve au carre est-il -1 ?', 'answer' => 'oui', 'options' => null];
    }

    private function matricesQuestion(int $base, int $variant): array
    {
        if ($variant === 1) {
            return ['statement' => 'Quelle est la trace de la matrice nulle 2x2 ?', 'answer' => '0', 'options' => null];
        }

        if ($variant === 2) {
            return ['statement' => 'La trace de la matrice identite 2x2 est-elle 2 ?', 'answer' => 'oui', 'options' => 'oui|non'];
        }

        return ['statement' => 'La trace d une matrice diagonale 2x2 avec elements 3 et 4 est-elle 7 ?', 'answer' => 'oui', 'options' => null];
    }

    private function chaptersFor(string $topic, string $levelName): array
    {
        return [
            [
                'title' => 'Notions essentielles',
                'content' => 'Presentation des notions de '.$topic.' pour le niveau '.$levelName.'.',
            ],
            [
                'title' => 'Methode',
                'content' => 'Methode de resolution pas a pas avec exemples pour '.$topic.'.',
            ],
            [
                'title' => 'Application',
                'content' => 'Exercices guides et conseils pour appliquer '.$topic.' en '.$levelName.'.',
            ],
        ];
    }
}
