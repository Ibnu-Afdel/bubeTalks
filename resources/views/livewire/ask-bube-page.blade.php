<div class="mx-auto w-full max-w-6xl space-y-8 px-4 py-8 lg:py-12">
    <div class="rounded-[32px] border border-[#ecdcc0] bg-[#fdf7ed] p-6 shadow-lg shadow-[0_20px_45px_rgba(204,170,115,0.22)]">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <x-app-logo />
                <div class="text-center sm:text-left">
                    <p class="text-xs uppercase tracking-[0.4em] text-[#b96a04]">Ask Bube</p>
                    <h1 class="text-2xl font-semibold text-[#24180c]">What do you want to yap today?</h1>
                    <p class="text-sm text-[#5f4525]">Send a prompt, get a voiced response, and keep the thread flowing.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3 sm:justify-end">
                <a href="{{ route('feed') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-[#ecd6aa] bg-[#fffaf3] px-4 py-2 text-sm font-semibold text-[#72400b]">
                    Go to Feed
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
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
            <div
                class="rounded-[28px] border border-[#ecdcc0] bg-[#fff9f0] p-6 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]"
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
                                <div class="flex-1 text-[0.97rem] text-[#3a1f0b] sm:text-base">{{ $message->question }}</div>
                                <button
                                    type="button"
                                    class="rounded-full border border-[#e1c89b] bg-white/80 p-2 text-[#7b4608] shadow-sm transition hover:bg-white"
                                    wire:click="toggleBookmark({{ $message->id }})"
                                    wire:loading.attr="disabled"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21 12 17 5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" fill="{{ $message->is_bookmarked ? '#f4b24c' : 'none' }}" />
                                        <path d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-4-7 4z" />
                                    </svg>
                                </button>
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

        <div class="space-y-4">
            <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fffdf7] p-6 shadow-lg shadow-[0_15px_30px_rgba(194,162,110,0.15)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-[#b96a04]">Saved</p>
                        <h2 class="mt-2 text-xl font-semibold text-[#24180c]">Bookmarked yaps</h2>
                    </div>
                    <a href="{{ route('feed') }}" wire:navigate class="text-sm font-semibold text-[#b96a04]">View all →</a>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($savedMessages as $message)
                        <div class="rounded-2xl border border-[#f1e2c8] bg-[#fff9f0] p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex size-8 items-center justify-center rounded-full bg-[#f6deb0] text-[#7b4608]">Q</div>
                                <div class="flex-1 text-[0.97rem] text-[#3a1f0b] sm:text-base">{{ $message->question }}</div>
                                <button
                                    type="button"
                                    class="rounded-full border border-[#e1c89b] bg-white/80 p-2 text-[#7b4608] shadow-sm transition hover:bg-white"
                                    wire:click="toggleBookmark({{ $message->id }})"
                                    wire:loading.attr="disabled"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21 12 17 5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" fill="#f4b24c" />
                                        <path d="M5 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-7-4-7 4z" />
                                    </svg>
                                </button>
                            </div>

                            @if($message->audio_url)
                                <audio controls class="mt-3 w-full rounded-2xl border border-[#ead7b4] bg-[#fffdf8] px-2">
                                    <source src="{{ $message->audio_url }}" type="audio/mpeg">
                                </audio>
                            @endif

                            <div class="mt-2 flex items-center justify-between text-xs text-[#7f6234]">
                                <span>{{ $message->created_at->diffForHumans() }}</span>
                                @if($message->response_text)
                                    <button type="button" class="text-[#b96a04]" wire:click="toggleBookmark({{ $message->id }})">Remove</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#e3c79d] px-6 py-10 text-center text-sm text-[#5f4525]">
                            <p class="text-base font-semibold text-[#72400b]">Nothing saved yet</p>
                            <p class="mt-2">Use the bookmark icon to keep the gems you love.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
