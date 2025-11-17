<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $session->topic }}
            </h2>
            <div class="flex space-x-2">
                @can('manageSessions', $session->class)
                    @if($session->google_meet_link)
                        <form method="POST" action="{{ route('sessions.sendGoogleMeetLink', $session) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    onclick="return confirm('Send Google Meet link to all enrolled members?')">
                                📧 Send Link to Members
                            </button>
                        </form>
                    @endif
                @endcan
                @can('viewAttendance', $session->class)
                    <a href="{{ route('sessions.attendance', $session) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Mark Attendance
                    </a>
                @endcan
                @can('manageSessions', $session->class)
                    <a href="{{ route('sessions.edit', $session) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('classes.show', $session->class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Session Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Session Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Class</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->class->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Topic</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->topic }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->session_date->format('l, F d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mentor</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->class->mentor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->location ?? 'Not specified' }}</dd>
                                </div>
                                @if($session->google_meet_link)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Google Meet</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                                            <a href="{{ $session->google_meet_link }}" target="_blank" rel="noopener noreferrer"
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                                Join Meeting
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                                @if($session->duration_minutes)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $session->duration_minutes }} minutes</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @if($session->notes)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $session->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Attendance Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $attendanceStats['total_members'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Members</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $attendanceStats['present_count'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Present</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $attendanceStats['absent_count'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Absent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $attendanceStats['attendance_rate'] }}%</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Attendance Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Records -->
            @if($session->attendance->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Attendance Records</h3>
                            <a href="{{ route('attendance.exportSession', $session) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Export CSV
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marked By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marked At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($session->attendance as $attendance)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $attendance->member->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $attendance->member->phone }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($attendance->status === 'present') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($attendance->status === 'absent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @endif">
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $attendance->markedBy->name ?? 'System' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $attendance->marked_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $attendance->notes ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No attendance recorded</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start marking attendance for this session.</p>
                        @can('viewAttendance', $session->class)
                            <div class="mt-6">
                                <a href="{{ route('sessions.attendance', $session) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Mark Attendance
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
