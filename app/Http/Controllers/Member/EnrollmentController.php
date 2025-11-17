<?php

namespace App\Http\Controllers\Member;

use App\Events\ClassEnrollmentCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\EnrollmentRequest;
use App\Models\ClassEnrollment;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isMember()) {
                abort(403, 'Only members can enroll in classes.');
            }
            return $next($request);
        });
    }

    /**
     * Display enrollments for the logged-in member
     */
    public function index(): View
    {
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        
        // Auto-create member profile if it doesn't exist
        if (!$member) {
            $member = Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'date_of_conversion' => now()->toDateString(),
                'preferred_contact' => 'email',
            ]);
        }

        $enrollments = $member->enrollments()
            ->with(['class.mentor'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('member.enrollments.index', compact('enrollments', 'member'));
    }

    /**
     * Show form to enroll in a class
     */
    public function create(DiscipleshipClass $class): View|RedirectResponse
    {
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        
        // Auto-create member profile if it doesn't exist
        if (!$member) {
            $member = Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'date_of_conversion' => now()->toDateString(),
                'preferred_contact' => 'email',
            ]);
        }

        // Check if member has any previous enrollment that is not completed or cancelled
        $activeEnrollment = $member->enrollments()
            ->whereIn('status', ['pending', 'approved'])
            ->with('class')
            ->first();
            
        if ($activeEnrollment) {
            return redirect()->route('classes.show', $class)
                ->with('error', "You already have an active enrollment in '{$activeEnrollment->class->title}'. Please complete or cancel it before enrolling in another class.");
        }

        // Check if already enrolled in this specific class (any status except cancelled/rejected allows re-enrollment check)
        $existingEnrollment = $member->enrollments()
            ->where('class_id', $class->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('member.enrollments.show', $existingEnrollment)
                ->with('info', 'You already have an active enrollment for this class.');
        }
        
        // Check if member has previously enrolled in this class (even if completed/cancelled)
        // Member can only enroll once per class - cannot re-enroll
        $previousEnrollment = $member->enrollments()
            ->where('class_id', $class->id)
            ->whereIn('status', ['completed', 'cancelled', 'rejected'])
            ->first();
            
        if ($previousEnrollment) {
            return redirect()->route('classes.show', $class)
                ->with('error', 'You have already enrolled in this class before. You can only enroll once per class.');
        }

        // Check if class is full
        if ($class->isFull()) {
            return redirect()->route('classes.show', $class)
                ->with('error', 'This class is full. Please try another class.');
        }

        // Check if class is active
        if (!$class->is_active) {
            return redirect()->route('classes.show', $class)
                ->with('error', 'This class is not currently active. Please choose another class.');
        }

        return view('member.enrollments.create', compact('class', 'member'));
    }

    /**
     * Store enrollment request
     */
    public function store(EnrollmentRequest $request, DiscipleshipClass $class)
    {
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        
        // Auto-create member profile if it doesn't exist
        if (!$member) {
            $member = Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'date_of_conversion' => now()->toDateString(),
                'preferred_contact' => 'email',
            ]);
        }

        // Check if member has any previous enrollment that is not completed
        // Member can enroll if they have no active enrollment OR if their previous enrollment is completed
        $activeEnrollment = $member->enrollments()
            ->whereIn('status', ['pending', 'approved'])
            ->with('class')
            ->first();
            
        if ($activeEnrollment) {
            return redirect()->route('classes.show', $class)
                ->with('error', "You already have an active enrollment in '{$activeEnrollment->class->title}'. Please complete or cancel it before enrolling in another class.");
        }
        
        // Member with completed enrollment can enroll in next class without admin verification
        // This is automatically handled as there's no active enrollment blocking them

        // Check if already enrolled in this specific class (any status - member can only enroll once per class)
        $existingEnrollment = $member->enrollments()
            ->where('class_id', $class->id)
            ->first();

        if ($existingEnrollment) {
            // If enrollment exists with active status, redirect to it
            if (in_array($existingEnrollment->status, ['pending', 'approved'])) {
                return redirect()->route('member.enrollments.show', $existingEnrollment)
                    ->with('info', 'You already have an enrollment for this class.');
            }
            // If enrollment exists with any other status, cannot re-enroll
            return redirect()->route('classes.show', $class)
                ->with('error', 'You have already enrolled in this class before. You can only enroll once per class.');
        }

        // Check if class is full
        if ($class->isFull()) {
            return redirect()->route('classes.show', $class)
                ->with('error', 'This class is full. Please try another class.');
        }

        // Check if class is active
        if (!$class->is_active) {
            return redirect()->route('classes.show', $class)
                ->with('error', 'This class is not currently active. Please choose another class.');
        }

        // Auto-approve enrollment - no admin verification needed
        $enrollment = ClassEnrollment::create([
            'class_id' => $class->id,
            'member_id' => $member->id,
            'status' => 'approved', // Auto-approve instead of pending
            'notes' => $request->notes,
            'enrolled_at' => now(),
            'approved_at' => now(),
            'approved_by' => $class->mentor_id, // Set mentor as approver for record keeping
        ]);

        // Fire event to send welcome message
        event(new ClassEnrollmentCreated($enrollment));

        // Redirect to class page so member can immediately access class content
        return redirect()->route('classes.show', $class)
            ->with('success', 'You have been successfully enrolled in the class! You can now access all class content and sessions.');
    }

    /**
     * Display enrollment details
     */
    public function show(ClassEnrollment $enrollment): View
    {
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        
        // Auto-create member profile if it doesn't exist
        if (!$member) {
            $member = Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'date_of_conversion' => now()->toDateString(),
                'preferred_contact' => 'email',
            ]);
        }

        // Ensure member owns this enrollment
        if ($enrollment->member_id !== $member->id) {
            abort(403, 'You can only view your own enrollments.');
        }

        $enrollment->load(['class.mentor']);

        return view('member.enrollments.show', compact('enrollment', 'member'));
    }

    /**
     * Cancel enrollment
     */
    public function destroy(ClassEnrollment $enrollment)
    {
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        
        // Auto-create member profile if it doesn't exist
        if (!$member) {
            $member = Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'date_of_conversion' => now()->toDateString(),
                'preferred_contact' => 'email',
            ]);
        }

        // Ensure member owns this enrollment
        if ($enrollment->member_id !== $member->id) {
            abort(403, 'You can only cancel your own enrollments.');
        }

        if ($enrollment->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot cancel a completed enrollment.');
        }

        $enrollment->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('member.enrollments.index')
            ->with('success', 'Enrollment cancelled successfully.');
    }
}