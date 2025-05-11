    <div class="max-w-xl mx-auto p-4 space-y-6">
        <h1 class="text-2xl font-bold text-center">🔮 Ask the AI Bube</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-2 rounded">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-2">
            <textarea wire:model="question" class="w-full p-2 border rounded" rows="4" placeholder="Ask your question..."></textarea>
            @error('question')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <button type="submit" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800">
                Submit to Bube
            </button>
        </form>

        <hr>

    </div>
