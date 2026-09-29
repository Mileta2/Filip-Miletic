<?php

namespace App\Http\Controllers;

use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TopicController extends Controller
{
    public function index(Request $request, ?TopicType $forcedType = null): View
    {
        $type = $forcedType?->value ?: $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $mine = $request->boolean('mine') && $request->user()?->hasRole(UserRole::Professor);

        $topics = Topic::query()
            ->with(['mentor.professorProfile', 'student.studentProfile'])
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($mine, fn (Builder $query) => $query->where('mentor_id', $request->user()->id))
            ->when($search, function (Builder $query) use ($search, $request) {
                $query->where(function (Builder $query) use ($search, $request) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('mentor', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));

                    if ($request->user()?->hasRole(UserRole::SuperAdmin) || $request->user()?->hasRole(UserRole::Professor)) {
                        $query->orWhereHas('student', fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
                            ->orWhereHas('studentProfile', fn (Builder $query) => $query->where('index_number', 'like', "%{$search}%")));
                    }
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('topics.index', compact('topics', 'type', 'status', 'search', 'mine'));
    }

    public function undergraduate(Request $request): View
    {
        return $this->index($request, TopicType::Undergraduate);
    }

    public function master(Request $request): View
    {
        return $this->index($request, TopicType::Master);
    }

    public function show(Topic $topic): View
    {
        return view('topics.show', [
            'topic' => $topic->load(['mentor.professorProfile', 'student.studentProfile', 'committeeMembers.professor.professorProfile']),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Topic::class);

        return view('topics.create', [
            'professors' => $this->professors(),
            'selectedType' => $request->enum('type', TopicType::class),
        ]);
    }

    public function store(StoreTopicRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['mentor_id'] = $request->user()->hasRole(UserRole::Professor)
            ? $request->user()->id
            : $data['mentor_id'];
        $data['status'] = TopicStatus::Available;

        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $request->file('pdf')->store('topics');
        }

        $topic = Topic::create($data);

        return redirect()->route('topics.show', $topic)->with('success', 'Tema je uspešno kreirana.');
    }

    public function edit(Topic $topic): View
    {
        Gate::authorize('update', $topic);

        return view('topics.edit', [
            'topic' => $topic,
            'professors' => $this->professors(),
        ]);
    }

    public function update(UpdateTopicRequest $request, Topic $topic): RedirectResponse
    {
        $data = $request->validated();
        $data['mentor_id'] = $request->user()->hasRole(UserRole::Professor)
            ? $request->user()->id
            : $data['mentor_id'];

        if ($topic->status !== TopicStatus::Available) {
            unset($data['type'], $data['mentor_id']);
        }

        if ($request->hasFile('pdf')) {
            $newPath = $request->file('pdf')->store('topics');
            if ($topic->pdf_path) {
                Storage::delete($topic->pdf_path);
            }
            $data['pdf_path'] = $newPath;
        }

        $topic->update($data);

        return redirect()->route('topics.show', $topic)->with('success', 'Tema je uspešno izmenjena.');
    }

    public function destroy(Topic $topic): RedirectResponse
    {
        Gate::authorize('delete', $topic);

        if ($topic->status !== TopicStatus::Available) {
            return back()->with('error', 'Zauzeta ili odbranjena tema ne može biti obrisana.');
        }

        if ($topic->pdf_path) {
            Storage::delete($topic->pdf_path);
        }
        $topic->delete();

        return redirect()->route('topics.index')->with('success', 'Tema je obrisana.');
    }

    public function download(Topic $topic): StreamedResponse
    {
        Gate::authorize('view', $topic);
        abort_unless($topic->pdf_path && Storage::exists($topic->pdf_path), 404);

        return Storage::download($topic->pdf_path, 'tema-'.$topic->id.'.pdf');
    }

    public function deletePdf(Topic $topic): RedirectResponse
    {
        Gate::authorize('update', $topic);
        if ($topic->pdf_path) {
            Storage::delete($topic->pdf_path);
            $topic->update(['pdf_path' => null]);
        }

        return back()->with('success', 'PDF dokument je obrisan.');
    }

    private function professors()
    {
        return User::where('role', UserRole::Professor)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
