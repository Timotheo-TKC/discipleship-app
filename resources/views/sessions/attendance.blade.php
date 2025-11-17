<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mark Attendance') }}
            </h2>
            <a href="{{ route('sessions.show', $session) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Session
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Session Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $session->topic }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $class->title }} | {{ $session->session_date->format('l, F d, Y') }} | 
                        Mentor: {{ $class->mentor->name }}
                    </p>
                </div>
            </div>

            <!-- Attendance Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="attendanceForm" method="POST" action="{{ route('attendance.storeBulk') }}">
                        @csrf
                        <input type="hidden" name="class_session_id" value="{{ $session->id }}">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Member Attendance</h3>
                            <div class="flex space-x-2">
                                <button type="button" id="markAllPresent" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Mark All Present
                                </button>
                                <button type="button" id="markAllAbsent" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Mark All Absent
                                </button>
                            </div>
                        </div>

                        @if($members->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Member</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($members as $member)
                                            @php
                                                $existingAttendance = $existingAttendance->get($member->id);
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $member->full_name }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $member->phone }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <select name="attendance[{{ $loop->index }}][status]" 
                                                            class="attendance-status rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                        <option value="present" {{ $existingAttendance && $existingAttendance->status === 'present' ? 'selected' : '' }}>Present</option>
                                                        <option value="absent" {{ $existingAttendance && $existingAttendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                        <option value="excused" {{ $existingAttendance && $existingAttendance->status === 'excused' ? 'selected' : '' }}>Excused</option>
                                                    </select>
                                                    <input type="hidden" name="attendance[{{ $loop->index }}][member_id]" value="{{ $member->id }}">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="text" name="attendance[{{ $loop->index }}][notes]" 
                                                           value="{{ $existingAttendance ? $existingAttendance->notes : '' }}"
                                                           placeholder="Optional notes..."
                                                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 flex justify-end space-x-3">
                                <a href="{{ route('sessions.show', $session) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Save Attendance
                                </button>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No members assigned</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This class doesn't have any members assigned yet.</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mark all present
            document.getElementById('markAllPresent').addEventListener('click', function() {
                const statusSelects = document.querySelectorAll('.attendance-status');
                statusSelects.forEach(select => {
                    select.value = 'present';
                });
            });

            // Mark all absent
            document.getElementById('markAllAbsent').addEventListener('click', function() {
                const statusSelects = document.querySelectorAll('.attendance-status');
                statusSelects.forEach(select => {
                    select.value = 'absent';
                });
            });

            // Form submission with loading state
            document.getElementById('attendanceForm').addEventListener('submit', function() {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            });
        });
    </script>
</x-app-layout>
