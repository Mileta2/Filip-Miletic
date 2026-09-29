<?php

namespace App\Http\Controllers;

use App\Enums\CommitteeRole;
use App\Enums\TopicStatus;
use App\Enums\UserRole;
use App\Http\Requests\DefendTopicRequest;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DefenseController extends Controller
{
    public function edit(Topic $topic): View
    {
        Gate::authorize('defend', $topic);
        abort_unless($topic->status === TopicStatus::Reserved, 404);

        return view('topics.defense', [
            'topic' => $topic->load('student', 'mentor'),
            'professors' => User::where('role', UserRole::Professor)
                ->where('is_active', true)
                ->whereKeyNot($topic->mentor_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(DefendTopicRequest $request, Topic $topic): RedirectResponse
    {
        $data = $request->validated();
        $memberIds = array_map('intval', $data['member_ids']);
        $presidentId = (int) $data['president_id'];

        if (in_array($presidentId, $memberIds, true)) {
            throw ValidationException::withMessages([
                'member_ids' => 'Predsednik komisije ne može istovremeno biti član.',
            ]);
        }

        if (in_array($topic->mentor_id, [...$memberIds, $presidentId], true)) {
            throw ValidationException::withMessages([
                'president_id' => 'Mentor se prikazuje posebno i ne može biti član komisije.',
            ]);
        }

        DB::transaction(function () use ($topic, $data, $memberIds, $presidentId) {
            $lockedTopic = Topic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            if ($lockedTopic->status !== TopicStatus::Reserved || ! $lockedTopic->student_id) {
                throw ValidationException::withMessages([
                    'defended_at' => 'Samo zauzeta tema može biti označena kao odbranjena.',
                ]);
            }

            $lockedTopic->committeeMembers()->delete();
            $lockedTopic->committeeMembers()->create([
                'professor_id' => $presidentId,
                'role' => CommitteeRole::President,
            ]);
            foreach ($memberIds as $memberId) {
                $lockedTopic->committeeMembers()->create([
                    'professor_id' => $memberId,
                    'role' => CommitteeRole::Member,
                ]);
            }
            $lockedTopic->update([
                'status' => TopicStatus::Defended,
                'defended_at' => $data['defended_at'],
            ]);
        });

        return redirect()->route('topics.show', $topic)->with('success', 'Rad je označen kao odbranjen.');
    }
}
