<?php

use App\Actions\Mentor\AcceptMentorRequestAction;
use App\Enums\MentorRequestStatus;
use App\Enums\MentorUrgency;
use App\Models\MentorRequest;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    $mentor = User::findOrFail(34);
    $student = User::findOrFail(1);

    // Create a temporary submitted request
    $request = MentorRequest::create([
        'student_id' => $student->id,
        'mentor_id' => $mentor->id,
        'mentor_profile_id' => 10,
        'topic' => 'Test Temporary Request',
        'goal' => 'Learn Laravel',
        'question' => 'How to do X?',
        'urgency' => MentorUrgency::Normal,
        'status' => MentorRequestStatus::Submitted,
    ]);

    echo 'Created Temp Request ID: '.$request->id."\n";
    echo 'Request Status: '.var_export($request->status, true)."\n";
    echo 'User Active: '.var_export($mentor->isActive(), true)."\n";
    echo 'Mentor ID comparison: '.var_export($request->mentor_id === $mentor->id, true)."\n";

    // Authenticate as mentor
    auth()->login($mentor);

    $action = app(AcceptMentorRequestAction::class);
    $res = $action->execute($mentor, $request, [
        'mentor_response' => 'Accepting your request!',
    ]);
    echo 'SUCCESS: Convo ID: '.$res->conversation_id."\n";

    // Clean up
    $request->delete();
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
    if (isset($request)) {
        $request->delete();
    }
}
