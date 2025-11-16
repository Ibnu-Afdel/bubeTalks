<div class="mx-auto w-full max-w-6xl space-y-8 px-4 py-8 lg:py-12">
    <div class="rounded-[32px] border border-[#ecdcc0] bg-[#fdf7ed] p-6 shadow-lg shadow-[0_20px_45px_rgba(204,170,115,0.22)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <x-app-logo />
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-[#b96a04]">Ask Bube</p>
                    <h1 class="text-2xl font-semibold text-[#24180c]">What do you want to yap today?</h1>
                    <p class="text-sm text-[#5f4525]">Send a prompt, get a voiced response, and keep the thread flowing.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('feed') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-[#ecd6aa] bg-[#fffaf3] px-4 py-2 text-sm font-semibold text-[#72400b]">
                    Go to Feed
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fff9f0] p-6 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]">
                @if (session()->has('message'))
                    <div class="mb-4 rounded-2xl border border-[#efdbaa] bg-[#fef3d4] px-4 py-3 text-sm font-medium text-[#6b4105]">
                        {{ session('message') }}
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <label for="question" class="text-sm font-semibold text-[#3a240c]">Your prompt</label>
                        <textarea
                            id="question"
                            wire:model="question"
                            class="w-full rounded-3xl border border-[#ead7b4] bg-[#fffdf8] px-4 py-3 text-sm text-[#24180c] shadow-inner shadow-[inset_0_1px_3px_rgba(149,121,79,0.12)] focus:border-[#ebb65c] focus:outline-none focus:ring-2 focus:ring-[#ebb65c]/40"
                            rows="5"
                            placeholder="Ask Bube anything..."
                            wire:loading.attr="disabled"
                        ></textarea>
                        @error('question')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-3xl bg-gradient-to-r from-[#f8e6c0] via-[#f3c878] to-[#e29938] px-5 py-3 text-sm font-semibold text-[#2b1607] shadow-lg shadow-[0_18px_32px_rgba(206,150,70,0.35)] transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-60"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="submit">Send to Bube</span>
                        <span wire:loading wire:target="submit" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Crafting...
                        </span>
                    </button>
                </form>
            </div>
        </div>

    <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fff9f0] p-6 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]"
            x-data="{
                currentAudio: null,
                init() {
                    this.$wire.on('refresh-audio', () => {
                        this.$wire.loadLatestResponses();
                    });
                }
            }"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-[#24180c]">Latest responses</h2>
                    <p class="text-sm text-[#5f4525]">We refresh automatically when something changes.</p>
                </div>
                <a href="{{ route('feed') }}" wire:navigate class="text-sm font-semibold text-[#b96a04]">View all →</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($latestResponses as $message)
                    <div class="rounded-3xl border border-[#f1e2c8] bg-[#fdf6ec] p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-[#f6deb0] text-[#7b4608]">Q</div>
                            <div class="flex-1 text-sm text-[#3a1f0b]">{{ $message->question }}</div>
                        </div>

                        @if($message->status === 'pending' || ($message->response_text && !$message->audio_url && !$message->error_message))
                            <div class="mt-4 rounded-2xl border border-[#efdbaa] bg-[#fef3d4] px-4 py-3 text-sm text-[#6b4105]">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>
                                        @if(!$message->response_text)
                                            Generating response...
                                        @elseif(!$message->audio_url)
                                            Rendering audio...
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                        @if($message->response_text)
                            <div class="mt-4 flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-[#dff8d8] text-[#0f5c35]">A</div>
                                <audio
                                    controls
                                    class="h-12 w-full rounded-2xl border border-[#ead7b4] bg-[#fffdf8] px-2"
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
                            <div class="mt-4 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>
                                        @if(str_contains($message->error_message, 'timeout'))
                                            The response was too long to process. Try a shorter prompt.
                                        @elseif(str_contains($message->error_message, '403'))
                                            Unable to access the audio file. Please try again.
                                        @else
                                            Something went wrong while generating the response. Please try again.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="mt-3 text-xs text-[#7f6234]">{{ $message->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-[#e3c79d] px-6 py-10 text-center text-sm text-[#5f4525]">
                        <p>Nothing here yet. Ask Bube a question to see it pop into your feed.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
