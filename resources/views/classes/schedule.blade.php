<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $class->title }} - Schedule
            </h2>
            <div class="flex space-x-2">
                @can('manageSessions', $class)
                    <a href="{{ route('classes.sessions.create', $class) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Session
                    </a>
                @endcan
                <a href="{{ route('classes.show', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Class Schedule Information -->
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Class Schedule Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule Type</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100 capitalize">{{ $class->schedule_type }}</dd>
                            </div>
                            @if($class->schedule_day)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule Day</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100 capitalize">{{ $class->schedule_day }}</dd>
                            </div>
                            @endif
                            @if($class->schedule_time)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule Time</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ date('g:i A', strtotime($class->schedule_time)) }}</dd>
                            </div>
                            @endif
                            @if($class->start_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $class->start_date->format('M d, Y') }}</dd>
                            </div>
                            @endif
                            @if($class->end_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $class->end_date->format('M d, Y') }}</dd>
                            </div>
                            @endif
                            @if($class->location)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $class->location }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Sessions List -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            All Sessions ({{ $class->sessions->count() }})
                        </h3>

                        @if($class->sessions->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Session Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Topic
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Attendance
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($class->sessions as $session)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $session->session_date->format('M d, Y') }}
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        ({{ $session->session_date->format('l') }})
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $session->topic ?? 'No topic' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $session->attendance->count() ?? 0 }} members
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($session->session_date->isPast())
                                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                            Past
                                                        </span>
                                                    @elseif($session->session_date->isToday())
                                                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                            Today
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                            Upcoming
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400">No sessions have been created for this class yet.</p>
                                @can('manageSessions', $class)
                                    <a href="{{ route('classes.sessions.create', $class) }}" class="mt-4 inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Create First Session
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
