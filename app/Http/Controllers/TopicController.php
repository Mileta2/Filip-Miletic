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
        $studentTopicType = $request->user()?->hasRole(UserRole::Student)
            ? TopicType::from($request->user()->studentProfile->study_level->value)
            : null;
        $type = $studentTopicType?->value ?: ($forcedType?->value ?: $request->string('type')->toString());
        $status = $request->string('status')->toString();
        $mentorId = $request->integer('mentor_id') ?: null;
        $course = trim($request->string('course')->toString());
        $search = trim($request->string('search')->toString());
        $mine = $request->boolean('mine') && $request->user()?->hasRole(UserRole::Professor);

        $baseTopics = Topic::query()
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->when($mine, fn (Builder $query) => $query->where('mentor_id', $request->user()->id));

        $courses = (clone $baseTopics)
            ->whereNotNull('course')
            ->where('course', '!=', '')
            ->distinct()
            ->orderBy('course')
            ->pluck('course');

        $professors = User::query()
            ->where('role', UserRole::Professor)
            ->whereHas('mentoredTopics', function (Builder $query) use ($type, $mine, $request) {
                $query->when($type, fn (Builder $query) => $query->where('type', $type))
                    ->when($mine, fn (Builder $query) => $query->where('mentor_id', $request->user()->id));
            })
            ->orderBy('name')
            ->get();

        $topics = $baseTopics
            ->with(['mentor.professorProfile', 'student.studentProfile'])
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($mentorId, fn (Builder $query) => $query->where('mentor_id', $mentorId))
            ->when($course, fn (Builder $query) => $query->where('course', $course))
            ->when($search, function (Builder $query) use ($search, $request) {
                $query->where(function (Builder $query) use ($search, $request) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('course', 'like', "%{$search}%")
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

        return view('topics.index', compact(
            'topics',
            'type',
            'status',
            'mentorId',
            'course',
            'search',
            'mine',
            'professors',
            'courses',
        ));
    }

    public function undergraduate(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectStudentToOwnCatalog($request, TopicType::Undergraduate)) {
            return $redirect;
        }

        return $this->index($request, TopicType::Undergraduate);
    }

    public function master(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectStudentToOwnCatalog($request, TopicType::Master)) {
            return $redirect;
        }

        return $this->index($request, TopicType::Master);
    }

    public function show(Request $request, Topic $topic): View|RedirectResponse
    {
        if ($redirect = $this->redirectStudentToOwnCatalog($request, $topic->type)) {
            return $redirect;
        }

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
            unset($data['course'], $data['type'], $data['mentor_id']);
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

    private function redirectStudentToOwnCatalog(Request $request, TopicType $requestedType): ?RedirectResponse
    {
        if (! $request->user()?->hasRole(UserRole::Student)) {
            return null;
        }

        $studentType = TopicType::from($request->user()->studentProfile->study_level->value);

        if ($studentType === $requestedType) {
            return null;
        }

        return redirect()->route(
            $studentType === TopicType::Undergraduate ? 'topics.undergraduate' : 'topics.master'
        );
    }
}
