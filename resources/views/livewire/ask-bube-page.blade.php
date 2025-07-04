    <div class="max-w-xl mx-auto p-4 space-y-6">
        <h1 class="text-2xl font-bold text-center">Ask the AI Bube</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-2 rounded">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-2">
            <textarea 
                wire:model="question" 
                class="w-full p-2 border rounded" 
                rows="4" 
                placeholder="Ask your question..."
                wire:loading.attr="disabled"
            ></textarea>
            @error('question')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <button 
                type="submit" 
                class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[120px]"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="submit">
                    Submit to Bube
                </span>
                <span wire:loading wire:target="submit" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </form>

        <hr class="my-8">

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Latest Responses</h2>
                <a href="{{ route('feed') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                    View all responses →
                </a>
            </div>

            <div class="space-y-4" 
                x-data="{ 
                    currentAudio: null,
                    init() {
                        this.$wire.on('refresh-audio', () => {
                            this.$wire.loadLatestResponses();
                        });
                    }
                }"
            >
                @forelse($latestResponses as $message)
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
                            
                            @if($message->status === 'pending' || ($message->response_text && !$message->audio_url && !$message->error_message))
                                <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                                    <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>
                                            @if(!$message->response_text)
                                                Generating response...
                                            @elseif(!$message->audio_url)
                                                Generating audio...
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if($message->response_text)
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
                                        wire:key="audio-{{ $message->id }}-{{ $message->audio_url }}"
                                    >
                                        @if($message->audio_url)
                                            <source src="{{ $message->audio_url }}" type="audio/mpeg">
                                        @endif
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
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-500 mb-4">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500">No responses yet. Be the first to ask Bube a question!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
