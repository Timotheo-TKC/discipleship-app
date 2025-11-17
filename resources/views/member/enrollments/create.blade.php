<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Enroll in ') }}{{ $class->title }}
            </h2>
            <a href="{{ route('classes.show', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Class
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Class Information</h3>
                    <div class="space-y-2">
                        <p><strong>Title:</strong> {{ $class->title }}</p>
                        <p><strong>Description:</strong> {{ $class->description }}</p>
                        <p><strong>Mentor:</strong> {{ $class->mentor->name }}</p>
                        <p><strong>Schedule:</strong> {{ ucfirst($class->schedule_type) }} 
                            @if($class->schedule_day)
                                on {{ ucfirst($class->schedule_day) }}
                            @endif
                            @if($class->schedule_time)
                                at {{ date('g:i A', strtotime($class->schedule_time)) }}
                            @endif
                        </p>
                        <p><strong>Start Date:</strong> {{ $class->start_date->format('M d, Y') }}</p>
                        @if($class->end_date)
                            <p><strong>End Date:</strong> {{ $class->end_date->format('M d, Y') }}</p>
                        @endif
                        <p><strong>Capacity:</strong> {{ $class->getEnrollmentCount() }} / {{ $class->capacity }} enrolled</p>
                        <p><strong>Available Spots:</strong> {{ $class->getAvailableSpots() }}</p>
                    </div>
                </div>
            </div>

            @if($class->isFull())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    This class is full. Please try another class.
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Enrollment Request</h3>
                        
                        <form method="POST" action="{{ route('member.enrollments.store', $class) }}">
                            @csrf

                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Additional Notes (Optional)
                                </label>
                                <textarea 
                                    name="notes" 
                                    id="notes" 
                                    rows="4"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Any additional information you'd like to share with the mentor...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-4 mb-4">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <strong>Note:</strong> Your enrollment will be automatically approved upon submission. Make sure you have completed any previous class before enrolling.
                                </p>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('classes.show', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Enroll in Class
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
