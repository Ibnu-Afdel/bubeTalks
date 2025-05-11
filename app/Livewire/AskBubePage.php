<?php

namespace App\Livewire;

use Livewire\Component;

class AskBubePage extends Component
{
    public string $question = '';

    public function submit()
    {
        $this->validate([
            'question' => 'required|string|min:5',
        ]);

        auth()->user()->bubeMessages()->create([
            'question' => $this->question,
            'status' => 'pending',
        ]);

        $this->reset('question');
        session()->flash('message', 'Your question was sent to the Oracle!');
    }
    public function render()
    {
        return view('livewire.ask-bube-page');
    }
}
