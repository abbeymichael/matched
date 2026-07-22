<?php

namespace App\Livewire\Chat;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThreadList extends Component
{
    public function render()
    {
        $matches = \App\Models\MutualMatch::query()
            ->where(function ($query) {
                $query->where('user_a_id', Auth::id())
                    ->orWhere('user_b_id', Auth::id());
            })
            ->where('is_active', true)
            ->with(['userA', 'userB', 'messages' => fn ($q) => $q->latest('sent_at')->limit(1)])
            ->orderBy('matched_at', 'desc')
            ->get();

        return view('livewire.chat.thread-list', ['matches' => $matches]);
    }
}
