<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Class Content - {{ $class->title }}
            </h2>
            <div class="flex space-x-2">
                @can('manageSessions', $class)
                    <a href="{{ route('classes.content.create', $class) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Content
                    </a>
                @endcan
                <a href="{{ route('classes.show', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($contents->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-600 dark:text-gray-400">No content has been added to this class yet.</p>
                        @can('manageSessions', $class)
                            <a href="{{ route('classes.content.create', $class) }}" class="mt-4 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Add First Content
                            </a>
                        @endcan
                    </div>
                </div>
            @else
                <!-- Content organized by week -->
                @foreach($contents as $weekNumber => $weekContents)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Week {{ $weekNumber ?? 'General' }}
                                </h3>
                            </div>
                            
                            <div class="space-y-4">
                                @foreach($weekContents as $content)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
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
                                                </div>
                                                
                                                <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                                    <a href="{{ route('classes.content.show', [$class, $content]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                        {{ $content->title }}
                                                    </a>
                                                </h4>
                                                
                                                @if($content->content)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                                                        {{ Str::limit(strip_tags($content->content), 150) }}
                                                    </p>
                                                @endif
                                                
                                                @if($content->attachments && count($content->attachments) > 0)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ count($content->attachments) }} attachment(s)
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="flex space-x-2 ml-4">
                                                @can('manageSessions', $class)
                                                    <a href="{{ route('classes.content.edit', [$class, $content]) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                    
                                                    <form action="{{ route('classes.content.togglePublish', [$class, $content]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="{{ $content->is_published ? 'Unpublish' : 'Publish' }}">
                                                            @if($content->is_published)
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                                </svg>
                                                            @endif
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('classes.content.destroy', [$class, $content]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this content?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>

