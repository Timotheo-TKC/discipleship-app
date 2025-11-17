<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Add Content - {{ $class->title }}
            </h2>
            <a href="{{ route('classes.content.index', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('classes.content.store', $class) }}" method="POST">
                        @csrf

                        <!-- Content Type -->
                        <div class="mb-6">
                            <label for="content_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Content Type <span class="text-red-500">*</span>
                            </label>
                            <select name="content_type" id="content_type" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select content type</option>
                                @foreach($contentTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('content_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('content_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="e.g., Introduction to Salvation, Week 1 Assignment">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Week Number -->
                        <div class="mb-6">
                            <label for="week_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Week Number
                            </label>
                            <select name="week_number" id="week_number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">General (No specific week)</option>
                                @foreach($weeks as $week)
                                    <option value="{{ $week }}" {{ old('week_number') == $week ? 'selected' : '' }}>
                                        Week {{ $week }}
                                    </option>
                                @endforeach
                            </select>
                            @error('week_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order -->
                        <div class="mb-6">
                            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Order (within week)
                            </label>
                            <input type="number" name="order" id="order" value="{{ old('order', 0) }}" min="0"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first</p>
                            @error('order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Rich Content -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Content
                            </label>
                            <textarea name="content" id="content" rows="12"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter rich content here. You can use HTML formatting or plain text.">{{ old('content') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports HTML formatting. Use this for detailed lesson content, assignments, reading materials, etc.</p>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-6">
                            <label for="additional_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Notes
                            </label>
                            <textarea name="additional_notes" id="additional_notes" rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Any additional notes or instructions">{{ old('additional_notes') }}</textarea>
                            @error('additional_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Attachments -->
                        <div class="mb-6">
                            <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Attachments (URLs)
                            </label>
                            <div id="attachments-container" class="space-y-2">
                                <div class="flex space-x-2">
                                    <input type="url" name="attachments[]" placeholder="https://example.com/resource.pdf"
                                        class="flex-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <button type="button" onclick="addAttachmentField()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        +
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add URLs to documents, videos, or other resources</p>
                        </div>

                        <!-- Publish Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Publish immediately (visible to enrolled members)
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('classes.content.index', $class) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Create Content
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addAttachmentField() {
            const container = document.getElementById('attachments-container');
            const newField = document.createElement('div');
            newField.className = 'flex space-x-2';
            newField.innerHTML = `
                <input type="url" name="attachments[]" placeholder="https://example.com/resource.pdf"
                    class="flex-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <button type="button" onclick="this.parentElement.remove()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    ×
                </button>
            `;
            container.appendChild(newField);
        }
    </script>
</x-app-layout>

