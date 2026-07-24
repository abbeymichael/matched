<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-5xl">
    <div class="mb-8 flex items-center justify-between">
      <h1 class="text-2xl font-extrabold">Your matches</h1>
      <a href="{{ route('settings') }}" class="pill rounded-xl px-4 py-2 text-xs text-zinc-300">Settings</a>
    </div>

    @if($scores->isEmpty())
      <div class="glass rounded-3xl p-10 text-center text-zinc-400">
        No matches above your threshold yet. Check back soon, or lower your threshold in Settings.
      </div>
    @else
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($scores as $score)
          <a href="{{ route('matches.show', $score->target_id) }}" class="glass block overflow-hidden rounded-2xl transition hover:-translate-y-1">
            <div class="relative h-56 bg-panel-soft">
              @if($photo = $score->target->photos->firstWhere('is_primary', true))
                <img src="{{ $photo->url() }}" loading="lazy" class="h-full w-full object-cover">
              @endif
              <span class="absolute right-3 top-3 rounded-full bg-pink px-3 py-1 text-xs font-extrabold">{{ $score->score }}%</span>
            </div>
            <div class="p-4">
              <p class="font-bold">{{ $score->target->profile?->display_name }}, {{ $score->target->profile?->date_of_birth?->age }}</p>
              <p class="text-xs text-zinc-400">{{ $score->target->profile?->city }}</p>
            </div>
          </a>
        @endforeach
      </div>
      <div class="mt-8">{{ $scores->links() }}</div>
    @endif
  </div>
</div>
