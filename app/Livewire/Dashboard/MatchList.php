<?php

namespace App\Livewire\Dashboard;

use App\Models\MatchScore;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MatchList extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();
        $scores = MatchScore::query()
            ->where('viewer_id', $user->id)
            ->where('score', '>=', $user->match_threshold)
            ->where('passed_hard_filters', true)
            ->whereHas('target', function ($query) {
                $query->where('status', 'active')
                    ->where('verification_status', 'approved');
            })
            ->with(['target.profile', 'target.photos'])
            ->orderBy('score', 'desc')
            ->paginate(20);

        return view('livewire.dashboard.match-list', ['scores' => $scores]);
    }
}
