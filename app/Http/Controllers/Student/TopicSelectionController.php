<?php

namespace App\Http\Controllers\Student;

use App\Enums\TopicStatus;
use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TopicSelectionController extends Controller
{
    public function store(Request $request, Topic $topic): RedirectResponse
    {
        DB::transaction(function () use ($request, $topic) {
            $student = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $lockedTopic = Topic::whereKey($topic->id)->lockForUpdate()->firstOrFail();

            if ($lockedTopic->status !== TopicStatus::Available || $lockedTopic->student_id) {
                throw ValidationException::withMessages(['topic' => 'Ova tema više nije slobodna.']);
            }

            if ($student->selectedTopic()->exists()) {
                throw ValidationException::withMessages(['topic' => 'Već imate odabranu temu.']);
            }

            if ($lockedTopic->type->value !== $student->studentProfile->study_level->value) {
                throw ValidationException::withMessages(['topic' => 'Tema ne odgovara vašem nivou studija.']);
            }

            $lockedTopic->update([
                'student_id' => $student->id,
                'status' => TopicStatus::Reserved,
                'reserved_at' => now(),
            ]);
        }, 3);

        return redirect()->route('student.dashboard')->with('success', 'Tema je uspešno odabrana.');
    }
}
