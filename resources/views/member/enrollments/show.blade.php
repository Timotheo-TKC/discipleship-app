<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Enrollment Details') }}
            </h2>
            <a href="{{ route('member.enrollments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Enrollments
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($enrollment->isApproved())
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                    <p class="font-medium">✓ Enrollment Active</p>
                    <p class="text-sm mt-1">You can now access all class content, sessions, and materials for this class.</p>
                    <div class="mt-2">
                        <a href="{{ route('classes.show', $enrollment->class) }}" class="text-blue-800 underline font-medium">
                            Go to Class →
                        </a>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Enrollment Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Enrollment Status</h3>
                        <span class="px-3 py-1 text-sm rounded 
                            @if($enrollment->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($enrollment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($enrollment->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($enrollment->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Enrolled Date</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->enrolled_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @if($enrollment->approved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->approved_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                        @if($enrollment->approved_by && $enrollment->approver)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved By</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->approver->name }}</dd>
                            </div>
                        @endif
                    </div>

                    @if($enrollment->notes)
                        <div class="mt-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Your Notes</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                {{ $enrollment->notes }}
                            </dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Class Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Class Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Class Title</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->class->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mentor</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->class->mentor->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->class->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">
                                {{ ucfirst($enrollment->class->schedule_type) }}
                                @if($enrollment->class->schedule_day)
                                    on {{ ucfirst($enrollment->class->schedule_day) }}
                                @endif
                                @if($enrollment->class->schedule_time)
                                    at {{ date('g:i A', strtotime($enrollment->class->schedule_time)) }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->class->start_date->format('M d, Y') }}</dd>
                        </div>
                        @if($enrollment->class->end_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $enrollment->class->end_date->format('M d, Y') }}</dd>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('classes.show', $enrollment->class) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                            View Class Details →
                        </a>
                        @if($enrollment->isApproved())
                            <a href="{{ route('classes.sessions.index', $enrollment->class) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                View Class Sessions →
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if(in_array($enrollment->status, ['pending', 'approved']))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Actions</h3>
                        <form method="POST" action="{{ route('member.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Are you sure you want to cancel this enrollment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Cancel Enrollment
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
