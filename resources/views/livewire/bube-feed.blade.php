<div class="max-w-2xl mx-auto p-4 space-y-6" x-data="{ currentAudio: null }">
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('ask') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Ask
        </a>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">🔮 Bube's Responses</h1>
        <div class="w-24"></div> <!-- Spacer for alignment -->
    </div>

    <div class="space-y-4">
        @forelse($messages as $message)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-all hover:shadow-md">
                <div class="p-4 space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                            <span class="text-purple-600 dark:text-purple-300">Q</span>
                        </div>
                        <div class="flex-1 text-gray-700 dark:text-gray-200">
                            {{ $message->question }}
                        </div>
                    </div>
                    
                    @if($message->audio_url)
                        <div class="flex items-center space-x-2 mt-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <span class="text-green-600 dark:text-green-300">A</span>
                            </div>
                            <audio 
                                controls 
                                class="w-full h-10 rounded-lg"
                                x-on:play="
                                    if (currentAudio && currentAudio !== $el) {
                                        currentAudio.pause();
                                    }
                                    currentAudio = $el;
                                "
                            >
                                <source src="{{ $message->audio_url }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    @endif

                    @if($message->error_message)
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                            <div class="flex items-center space-x-2 text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>
                                    @if(str_contains($message->error_message, 'timeout'))
                                        The response was too long to process. Please try asking a shorter question.
                                    @elseif(str_contains($message->error_message, '403'))
                                        Unable to access the audio file. Please try again.
                                    @else
                                        Something went wrong while generating the response. Please try again.
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="text-xs text-gray-400 mt-2">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No conversations yet</h3>
                <p class="mt-2 text-gray-500">Start by asking Bube a question!</p>
                <div class="mt-6">
                    <a href="{{ route('ask') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Ask a Question
                    </a>
                </div>
            </div>
        @endforelse

        @if($messages->hasPages())
            <div class="mt-6">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
