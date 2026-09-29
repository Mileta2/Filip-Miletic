<?php

namespace App\Http\Controllers;

use App\Enums\TopicStatus;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TopicWorkflowController extends Controller
{
    public function release(Topic $topic): RedirectResponse
    {
        Gate::authorize('release', $topic);

        if ($topic->status !== TopicStatus::Reserved) {
            return back()->with('error', 'Samo zauzeta tema može biti oslobođena.');
        }

        $topic->update([
            'status' => TopicStatus::Available,
            'student_id' => null,
            'reserved_at' => null,
        ]);

        return back()->with('success', 'Tema je ponovo dostupna studentima.');
    }
}
