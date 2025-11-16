<div class="mx-auto w-full max-w-5xl space-y-8 px-4 py-8 lg:py-12" x-data="{ currentAudio: null }">
    <div class="rounded-[32px] border border-[#ecdcc0] bg-[#fdf7ed] p-6 shadow-lg shadow-[0_20px_45px_rgba(204,170,115,0.22)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <a href="{{ route('ask') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-[#72400b]">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 19 7 12l7-7"/></svg>
                Back to Ask Bube
            </a>
            <div class="flex items-center gap-3">
                <x-app-logo />
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-[#b96a04]">Bube Feed</p>
                    <h1 class="text-2xl font-semibold text-[#24180c]">Every answer, ready to replay</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($messages as $message)
            <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fff9f0] p-5 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 items-center justify-center rounded-full bg-[#f6deb0] text-[#7b4608]">Q</div>
                    <div class="flex-1 text-sm text-[#3a1f0b]">{{ $message->question }}</div>
                    <div class="flex items-center gap-2">
                        @if($message->is_bookmarked)
                            <span class="rounded-full bg-[#fdebd0] px-3 py-1 text-xs font-semibold text-[#7b4608]">Saved</span>
                        @endif
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
                </div>

                @if($message->audio_url)
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
                        >
                            <source src="{{ $message->audio_url }}" type="audio/mpeg">
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
            <div class="rounded-[32px] border border-dashed border-[#e3c79d] px-6 py-10 text-center text-sm text-[#5f4525]">
                <p class="text-base font-semibold text-[#72400b]">No conversations yet</p>
                <p class="mt-2">Start by asking Bube a question!</p>
                <div class="mt-6">
                    <a href="{{ route('ask') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#f8e6c0] via-[#f3c878] to-[#e29938] px-5 py-2 text-sm font-semibold text-[#2b1607] shadow-md">
                        Ask a Question
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
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
