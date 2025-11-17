<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Session') }}
            </h2>
            <a href="{{ route('sessions.show', $session) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Session
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Class Information -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $class->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Mentor: {{ $class->mentor->name }} | 
                            Schedule: {{ ucfirst($class->schedule_type) }} 
                            @if($class->schedule_day) on {{ ucfirst($class->schedule_day) }}s @endif
                            at {{ $class->schedule_time }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('sessions.update', [$class, $session]) }}">
                        @csrf
                        @method('PUT')

                        <!-- Session Date -->
                        <div class="mb-4">
                            <label for="session_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Session Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="session_date" id="session_date" value="{{ old('session_date', $session->session_date->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('session_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Topic -->
                        <div class="mb-4">
                            <label for="topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Topic <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="topic" id="topic" value="{{ old('topic', $session->topic) }}" required
                                   placeholder="e.g., Introduction to Discipleship, Prayer and Fasting, etc."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('topic')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Location
                            </label>
                            <input type="text" name="location" id="location" value="{{ old('location', $session->location) }}"
                                   placeholder="e.g., Main Hall, Room 101, Online"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Google Meet Link -->
                        <div class="mb-4">
                            <label for="google_meet_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Google Meet Link
                            </label>
                            <input type="url" name="google_meet_link" id="google_meet_link" value="{{ old('google_meet_link', $session->google_meet_link) }}"
                                   placeholder="https://meet.google.com/xxx-xxxx-xxx"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Optional: Add a Google Meet link for online sessions. You can share this link with enrolled members.
                            </p>
                            @error('google_meet_link')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div class="mb-4">
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Duration (minutes)
                            </label>
                            <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $session->duration_minutes) }}" 
                                   min="15" max="300" placeholder="e.g., 90"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Duration in minutes (15-300)</p>
                            @error('duration_minutes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                      placeholder="Any additional notes about this session...">{{ old('notes', $session->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('sessions.show', $session) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
