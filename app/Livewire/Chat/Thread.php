<?php

namespace App\Livewire\Chat;

use App\Actions\Social\SendMessage;
use App\Models\MutualMatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class Thread extends Component
{
    public MutualMatch $match;
    public string $body = '';

    public function mount(string $match): void
    {
        $this->match = MutualMatch::findOrFail($match);

        if (! $this->match->includesUser(Auth::id())) {
            abort(403);
        }
    }

    public function send(SendMessage $action): void
    {
        $this->validate(['body' => 'required|string|max:2000']);

        try {
            $action->handle($this->match, Auth::user(), $this->body);
        } catch (ValidationException $e) {
            $this->addError('body', $e->getMessage());
            return;
        }

        $this->body = '';
    }

    #[On('echo-private:matches.{match.id},MessageSent')]
    public function refreshMessages(): void
    {
        // Handled by wire:poll for MVP, this is a hook for Reverb later.
    }

    public function render()
    {
        return view('livewire.chat.thread', [
            'messages' => $this->match->messages()->with('sender')->orderBy('sent_at')->get(),
            'otherUser' => $this->match->otherUser(Auth::id()),
        ]);
    }
}
