<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Winners List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">All Winners</h3>
                        <div class="flex gap-4">
                            <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                ← Back to Dashboard
                            </a>
                            <a href="{{ route('participants.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Manage Participants
                            </a>
                        </div>
                    </div>

                    @if($winners->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winner</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Won At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($winners as $winner)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                                             style="background-color: {{ $winner->winner_color ?? '#6B7280' }}">
                                                            {{ strtoupper(substr($winner->winner_name, 0, 2)) }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $winner->winner_name }}</div>
                                                        <div class="text-sm text-gray-500">ID: {{ $winner->participant_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $winner->winner_color ?? '#6B7280' }}"></div>
                                                    <span class="text-sm text-gray-900">{{ $winner->winner_color ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $winner->won_at->format('M j, Y g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $winner->user->name ?? 'System' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form method="POST" action="{{ route('winners.destroy', $winner) }}" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this winner record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $winners->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg mb-4">No winners recorded yet.</div>
                            <p class="text-gray-400">Start spinning the wheel to record winners!</p>
                            <a href="/" class="inline-block mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Go to Wheel
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
