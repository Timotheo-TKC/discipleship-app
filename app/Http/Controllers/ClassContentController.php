<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassContentRequest;
use App\Models\ClassContent;
use App\Models\ClassEnrollment;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of content for a class
     */
    public function index(DiscipleshipClass $class): View
    {
        $this->authorize('manageSessions', $class); // Use manageSessions as content management permission

        $contents = $class->contents()
            ->with('creator')
            ->orderBy('week_number')
            ->orderBy('order')
            ->orderBy('content_type')
            ->get()
            ->groupBy('week_number');

        return view('classes.content.index', compact('class', 'contents'));
    }

    /**
     * Show the form for creating a new content item
     */
    public function create(DiscipleshipClass $class): View
    {
        $this->authorize('manageSessions', $class);

        $contentTypes = ClassContent::getContentTypes();
        $weeks = range(1, $class->duration_weeks);

        return view('classes.content.create', compact('class', 'contentTypes', 'weeks'));
    }

    /**
     * Store a newly created content item
     */
    public function store(ClassContentRequest $request, DiscipleshipClass $class): RedirectResponse
    {
        $this->authorize('manageSessions', $class);

        $validated = $request->validated();
        $validated['class_id'] = $class->id;
        $validated['created_by'] = auth()->id();

        // Handle attachments if provided
        if ($request->has('attachments') && is_array($request->attachments)) {
            $validated['attachments'] = array_filter($request->attachments);
        }

        ClassContent::create($validated);

        return redirect()
            ->route('classes.content.index', $class)
            ->with('success', 'Class content created successfully.');
    }

    /**
     * Display the specified content item
     */
    public function show(DiscipleshipClass $class, ClassContent $content): View
    {
        $canManage = auth()->user()->can('manageSessions', $class);
        $enrollment = $this->resolveMemberEnrollment($class);

        $canAccess = $canManage || ($enrollment && $content->is_published);

        if (! $canAccess) {
            abort(403, 'You do not have access to this content.');
        }

        $progressStates = [];
        $currentState = [
            'completed' => false,
            'locked' => false,
            'progress' => null,
        ];

        if ($enrollment) {
            $orderedContents = $class->orderedPublishedContents()->get();

            $progressStates = $enrollment->buildProgressStates($orderedContents);
            $stateForContent = $progressStates[$content->id] ?? $currentState;

            if ($stateForContent['locked'] && ! $stateForContent['completed']) {
                abort(403, 'Please complete the previous lesson before accessing this one.');
            }

            $currentState = $stateForContent;
        }

        return view('classes.content.show', [
            'class' => $class,
            'content' => $content,
            'enrollment' => $enrollment,
            'progressStates' => $progressStates,
            'currentState' => $currentState,
        ]);
    }

    /**
     * Show the form for editing the specified content item
     */
    public function edit(DiscipleshipClass $class, ClassContent $content): View
    {
        $this->authorize('manageSessions', $class);

        // Ensure content belongs to this class
        if ($content->class_id !== $class->id) {
            abort(404);
        }

        $contentTypes = ClassContent::getContentTypes();
        $weeks = range(1, $class->duration_weeks);

        return view('classes.content.edit', compact('class', 'content', 'contentTypes', 'weeks'));
    }

    /**
     * Update the specified content item
     */
    public function update(ClassContentRequest $request, DiscipleshipClass $class, ClassContent $content): RedirectResponse
    {
        $this->authorize('manageSessions', $class);

        // Ensure content belongs to this class
        if ($content->class_id !== $class->id) {
            abort(404);
        }

        $validated = $request->validated();

        // Handle attachments if provided
        if ($request->has('attachments') && is_array($request->attachments)) {
            $validated['attachments'] = array_filter($request->attachments);
        }

        $content->update($validated);

        return redirect()
            ->route('classes.content.index', $class)
            ->with('success', 'Class content updated successfully.');
    }

    /**
     * Remove the specified content item
     */
    public function destroy(DiscipleshipClass $class, ClassContent $content): RedirectResponse
    {
        $this->authorize('manageSessions', $class);

        // Ensure content belongs to this class
        if ($content->class_id !== $class->id) {
            abort(404);
        }

        $content->delete();

        return redirect()
            ->route('classes.content.index', $class)
            ->with('success', 'Class content deleted successfully.');
    }

    /**
     * Toggle publish status of content
     */
    public function togglePublish(DiscipleshipClass $class, ClassContent $content): RedirectResponse
    {
        $this->authorize('manageSessions', $class);

        // Ensure content belongs to this class
        if ($content->class_id !== $class->id) {
            abort(404);
        }

        $content->update([
            'is_published' => !$content->is_published,
        ]);

        return redirect()
            ->route('classes.content.index', $class)
            ->with('success', 'Content publish status updated successfully.');
    }

    /**
     * Update completion progress for a content item.
     */
    public function updateProgress(Request $request, DiscipleshipClass $class, ClassContent $content): RedirectResponse
    {
        $enrollment = $this->resolveMemberEnrollment($class);

        if (! $enrollment || ! $content->is_published) {
            abort(403, 'You are not authorized to update this lesson.');
        }

        $orderedContents = $class->orderedPublishedContents()->get();
        $states = $enrollment->buildProgressStates($orderedContents);
        $stateForContent = $states[$content->id] ?? [
            'completed' => false,
            'locked' => false,
        ];

        if ($stateForContent['locked'] && ! $stateForContent['completed']) {
            return redirect()
                ->route('classes.content.show', [$class, $content])
                ->with('error', 'Please complete the previous lesson before marking this one.');
        }

        $completed = $request->boolean('completed');

        $enrollment->setContentCompletion($content, $completed);

        $message = $completed
            ? 'Great job! Lesson marked as completed.'
            : 'Lesson marked as not completed.';

        return redirect()
            ->route('classes.content.show', [$class, $content])
            ->with('success', $message);
    }

    /**
     * Resolve the active enrollment for the authenticated member.
     */
    private function resolveMemberEnrollment(DiscipleshipClass $class): ?ClassEnrollment
    {
        $user = auth()->user();

        if (! $user || ! $user->isMember()) {
            return null;
        }

        $member = Member::where('user_id', $user->id)->first();

        if (! $member) {
            return null;
        }

        return $member->enrollments()
            ->where('class_id', $class->id)
            ->where('status', 'approved')
            ->first();
    }
}
