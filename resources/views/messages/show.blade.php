<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Message Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Message Header -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $message->payload['subject'] ?? 'Message Subject' }}</h3>
                            <div class="mt-2 flex space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                <span>Type: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $message->message_type ?? 'general')) }}</span></span>
                                <span>Channel: <span class="font-medium">{{ ucfirst($message->channel ?? 'email') }}</span></span>
                                <span>Status: <span class="font-medium px-2 py-1 rounded text-xs {{ $message->status === 'sent' ? 'bg-green-100 text-green-800' : ($message->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($message->status ?? 'draft') }}</span></span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('messages.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Back
                            </a>
                            <a href="{{ route('messages.edit', $message) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit
                            </a>
                            @if($message->status === 'draft')
                                <form method="POST" action="{{ route('messages.sendNow', $message) }}" class="inline" onsubmit="return confirm('Send this message to all recipients now?');">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Send Now
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('messages.destroy', $message) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="mb-6">
                        <h4 class="text-lg font-medium mb-3">Message Content</h4>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="whitespace-pre-wrap">{{ $message->template ?? 'This is the message content. It will be displayed here with proper formatting.' }}</p>
                        </div>
                    </div>

                    <!-- Message Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-lg font-medium mb-3">Message Information</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $message->created_at ?? now()->format('M d, Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled At</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $message->scheduled_at ? $message->scheduled_at->format('M d, Y H:i') : 'Immediate' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent At</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $message->sent_at ? $message->sent_at->format('M d, Y H:i') : 'Not sent' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Recipients</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ is_array($message->payload['recipients'] ?? null) ? count($message->payload['recipients']) : 0 }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-lg font-medium mb-3">Delivery Statistics</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $message->logs()->where('result', 'success')->count() }}</div>
                                    <div class="text-sm text-green-600 dark:text-green-400">Delivered</div>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900 p-3 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $message->logs()->where('result', 'failed')->count() }}</div>
                                    <div class="text-sm text-red-600 dark:text-red-400">Failed</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Logs -->
                    <div>
                        <h4 class="text-lg font-medium mb-3">Delivery Logs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Recipient
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Channel
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Sent At
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Response
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($message->logs as $log)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->recipient }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ ucfirst($log->channel) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded {{ $log->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($log->result) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                @if(is_array($log->response))
                                                    <pre class="text-xs">{{ json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                @elseif($log->response)
                                                    {{ $log->response }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No delivery logs found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

