<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Topic;
use App\Models\Exercise;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\Badge;
use App\Models\SchoolLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$counts = [
    'topics' => Topic::count(),
    'exercises' => Exercise::count(),
    'courses' => Course::count(),
    'course_chapters' => CourseChapter::count(),
    'badges' => Badge::count(),
    'school_levels' => SchoolLevel::count(),
    'users' => User::count(),
    'learning_sessions' => DB::table('learning_sessions')->count(),
    'attempts' => DB::table('attempts')->count(),
    'user_badges' => DB::table('user_badges')->count(),
];

echo json_encode($counts, JSON_PRETTY_PRINT) . PHP_EOL;
