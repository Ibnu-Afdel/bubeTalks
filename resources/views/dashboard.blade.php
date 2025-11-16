<x-layouts.app :title="__('Dashboard')">
    @php
        $user = auth()->user();
        $baseQuery = \App\Models\BubeMessage::query()->where('user_id', $user->id);
        $totalMessages = (clone $baseQuery)->count();
        $pendingMessages = (clone $baseQuery)->where('status', 'pending')->count();
        $todayCount = (clone $baseQuery)->whereDate('created_at', now()->toDateString())->count();
        $recentMessages = (clone $baseQuery)->latest()->take(4)->get();
    @endphp

    <div class="flex flex-col gap-6">
        <section class="relative overflow-hidden rounded-[32px] border border-[#ecdcc0] bg-[#fdf9f2] p-8 shadow-xl shadow-[0_25px_70px_rgba(206,174,118,0.25)]">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -right-24 top-6 size-64 rounded-full bg-[#f3d59b]/35 blur-[120px]"></div>
                <div class="absolute -left-16 -bottom-10 size-64 rounded-full bg-[#f8e9c7]/60 blur-[140px]"></div>
            </div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.4em] text-[#b96a04]">BubeTalks</p>
                    <h1 class="text-3xl font-semibold text-[#24180c]">Hey {{ $user->name }}, ready for your next spark?</h1>
                    <p class="text-sm text-[#54391b]">
                        Track the pulse of your AI conversations, jump back into new prompts, and keep an eye on the answers still brewing.
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="{{ route('ask') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#f8e6c1] via-[#f4c675] to-[#e59b39] px-5 py-2 text-sm font-semibold text-[#2b1607] shadow-lg shadow-[0_15px_30px_rgba(202,149,63,0.35)] transition hover:scale-[1.01]">
                            Ask Bube
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                        <a href="{{ route('feed') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-[#ecd6aa] bg-[#fefaf4] px-5 py-2 text-sm font-semibold text-[#72400b] shadow-sm hover:border-[#e3cfa7]">
                            Open Feed
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-[#eddcb6] bg-[#fef9f1] px-4 py-3 shadow-md">
                    <x-app-logo />
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-[#ecdcc0] bg-[#fffaf1] p-5 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]">
                <p class="text-xs uppercase tracking-[0.35em] text-[#b96a04]">Total prompts</p>
                <p class="mt-3 text-3xl font-semibold text-[#24180c]">{{ number_format($totalMessages) }}</p>
                <p class="mt-1 text-sm text-[#5f4525]">All-time conversations with Bube</p>
            </div>
            <div class="rounded-2xl border border-[#ecdcc0] bg-[#fffaf1] p-5 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]">
                <p class="text-xs uppercase tracking-[0.35em] text-[#b96a04]">Pending</p>
                <p class="mt-3 text-3xl font-semibold text-[#24180c]">{{ number_format($pendingMessages) }}</p>
                <p class="mt-1 text-sm text-[#5f4525]">Responses still being crafted</p>
            </div>
            <div class="rounded-2xl border border-[#ecdcc0] bg-[#fffaf1] p-5 shadow-lg shadow-[0_18px_40px_rgba(194,162,110,0.18)]">
                <p class="text-xs uppercase tracking-[0.35em] text-[#b96a04]">Today</p>
                <p class="mt-3 text-3xl font-semibold text-[#24180c]">{{ number_format($todayCount) }}</p>
                <p class="mt-1 text-sm text-[#5f4525]">New prompts in the last 24h</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fdf7ed] p-6 shadow-lg shadow-[0_20px_45px_rgba(204,170,115,0.22)]">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-[#24180c]">Recent questions</h2>
                        <p class="text-sm text-[#5f4525]">Your last few sparks with Bube</p>
                    </div>
                    <a href="{{ route('feed') }}" wire:navigate class="text-sm font-semibold text-[#b96a04]">View feed →</a>
                </div>

                <ul class="mt-6 space-y-4">
                    @forelse ($recentMessages as $message)
                        <li class="rounded-2xl border border-[#f1e2c8] bg-[#fdf6ec] p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm text-[#3a1f0b] leading-snug">{{ $message->question }}</p>
                                <span class="rounded-full bg-[#f6deb0] px-3 py-1 text-xs font-semibold text-[#804707] capitalize">{{ $message->status }}</span>
                            </div>
                            <p class="mt-2 text-xs text-[#7f6234]">{{ $message->created_at->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-[#e3c79d] p-6 text-center text-sm text-[#5f4525]">
                            Ask your first question to see it appear here.
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-[28px] border border-[#ecdcc0] bg-[#fdf7ed] p-6 shadow-lg shadow-[0_20px_45px_rgba(204,170,115,0.22)]">
                <h2 class="text-xl font-semibold text-[#24180c]">Quick moves</h2>
                <p class="text-sm text-[#5f4525]">Everything you need to keep the flow going.</p>

                <div class="mt-6 space-y-4">
                    <a href="{{ route('ask') }}" wire:navigate class="flex items-center justify-between rounded-2xl border border-transparent bg-gradient-to-r from-[#f8e6c0] via-[#f3c878] to-[#e29938] px-5 py-4 text-[#2b1607] shadow-lg shadow-[0_18px_32px_rgba(206,150,70,0.35)]">
                        <span>
                            <strong class="block text-base">Ask something new</strong>
                            <span class="text-sm text-[#5f4525]">Send a fresh prompt to Bube</span>
                        </span>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>

                    <a href="{{ route('feed') }}" wire:navigate class="flex items-center justify-between rounded-2xl border border-[#ead8b5] bg-[#fff9f0] px-5 py-4 text-[#2b1607] shadow-sm">
                        <span>
                            <strong class="block text-base">Replay recent answers</strong>
                            <span class="text-sm text-[#5f4525]">Listen back to your generated audio</span>
                        </span>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('settings.profile') }}" wire:navigate class="flex items-center justify-between rounded-2xl border border-[#ead8b5] bg-[#fff9f0] px-5 py-4 text-[#2b1607] shadow-sm">
                        <span>
                            <strong class="block text-base">Tune your profile</strong>
                            <span class="text-sm text-[#5f4525]">Update your name, email, or password</span>
                        </span>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m11 5 7 7-7 7m-4-14 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
