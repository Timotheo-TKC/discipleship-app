<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Members') }}
            </h2>
            <div class="flex space-x-2">
                @can('create', App\Models\Member::class)
                    <a href="{{ route('members.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Member
                    </a>
                @endcan
                @can('import', App\Models\Member::class)
                    <a href="{{ route('members.import') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Import CSV
                    </a>
                @endcan
                @can('viewAny', App\Models\Member::class)
                <a href="{{ route('members.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Export CSV
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('members.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Name, phone, or email..." 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="conversion_date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Conversion Date From</label>
                            <input type="date" name="conversion_date_from" id="conversion_date_from" value="{{ request('conversion_date_from') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="conversion_date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Conversion Date To</label>
                            <input type="date" name="conversion_date_to" id="conversion_date_to" value="{{ request('conversion_date_to') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="preferred_contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Contact</label>
                            <select name="preferred_contact" id="preferred_contact" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All</option>
                                <option value="email" {{ request('preferred_contact') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="call" {{ request('preferred_contact') === 'call' ? 'selected' : '' }}>Call</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-4 flex justify-end space-x-2">
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('members.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Members Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($members->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Conversion Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($members as $member)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $member->full_name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $member->phone }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $member->email ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $member->date_of_conversion->format('M d, Y') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($member->preferred_contact === 'email') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($member->preferred_contact === 'call') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                    @endif">
                                                    {{ ucfirst($member->preferred_contact) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('members.show', $member) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        View
                                                    </a>
                                                    @can('update', $member)
                                                        <a href="{{ route('members.edit', $member) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                            Edit
                                                        </a>
                                                    @endcan
                                                    @can('delete', $member)
                                                        <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $members->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No members found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a new member.</p>
                            @can('create', App\Models\Member::class)
                                <div class="mt-6">
                                    <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Add Member
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
