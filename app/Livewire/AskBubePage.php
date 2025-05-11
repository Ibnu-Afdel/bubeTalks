<?php

namespace App\Livewire;

use App\Jobs\GenerateBubeResponseJob;
use Livewire\Component;

class AskBubePage extends Component
{
    public string $question = '';

    public function submit()
    {
        $this->validate([
            'question' => 'required|string|min:5',
        ]);

        $message = auth()->user()->bubeMessages()->create([
            'question' => $this->question,
            'status' => 'pending',
        ]);
        // dd($bubeMessage);
        GenerateBubeResponseJob::dispatch($message);

        $this->reset('question');
        session()->flash('message', 'Your question was sent to bube!');
    }
    public function render()
    {
        return view('livewire.ask-bube-page');
    }
}
