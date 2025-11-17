<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $content->title }}
            </h2>
            <div class="flex space-x-2">
                @can('manageSessions', $class)
                    <a href="{{ route('classes.content.edit', [$class, $content]) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('classes.content.index', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Content
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Content Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($content->content_type === 'outline') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @elseif($content->content_type === 'lesson') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($content->content_type === 'assignment') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($content->content_type === 'homework') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($content->content_type === 'resource') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ \App\Models\ClassContent::getContentTypes()[$content->content_type] ?? ucfirst($content->content_type) }}
                        </span>
                        @if($content->is_published)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Published
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                Draft
                            </span>
                        @endif
                        @if($content->week_number)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                Week {{ $content->week_number }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        {{ $content->title }}
                    </h1>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <p>Created by {{ $content->creator->name ?? 'Unknown' }} on {{ $content->created_at->format('F d, Y') }}</p>
                        @if($content->updated_at != $content->created_at)
                            <p>Last updated on {{ $content->updated_at->format('F d, Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($content->content)
                        <div class="prose dark:prose-invert max-w-none">
                            {!! nl2br(e($content->content)) !!}
                        </div>
                    @else
                        <p class="text-gray-600 dark:text-gray-400 italic">No content provided.</p>
                    @endif
                </div>
            </div>

            @if(isset($enrollment))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lesson Progress</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Mark this lesson as complete once you have finished it. Your attendance/progress rate updates automatically.
                            </p>
                            @php
                                $completedTimestamp = isset($currentState['progress']) ? optional($currentState['progress'])->completed_at : null;
                            @endphp
                            @if(($currentState['completed'] ?? false) && $completedTimestamp)
                                <p class="text-xs text-green-600 dark:text-green-300 mt-2">
                                    Completed on {{ $completedTimestamp->format('F d, Y g:i A') }}
                                </p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('classes.content.progress', [$class, $content]) }}">
                            @csrf
                            <input type="hidden" name="completed" value="{{ $currentState['completed'] ? 0 : 1 }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {{ $currentState['completed'] ? 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2">
                                @if($currentState['completed'])
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 9.75l7.5 7.5 7.5-7.5" />
                                    </svg>
                                    Mark as not done
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as done
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Additional Notes -->
            @if($content->additional_notes)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-2">Additional Notes</h3>
                    <p class="text-blue-800 dark:text-blue-200 whitespace-pre-wrap">{{ $content->additional_notes }}</p>
                </div>
            @endif

            <!-- Attachments -->
            @if($content->attachments && count($content->attachments) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Attachments</h3>
                        <div class="space-y-2">
                            @foreach($content->attachments as $attachment)
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    <a href="{{ $attachment }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        {{ $attachment }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Class Link -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <a href="{{ route('classes.show', $class) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    ← Back to {{ $class->title }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

