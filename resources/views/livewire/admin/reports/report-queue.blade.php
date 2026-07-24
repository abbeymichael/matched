<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-4xl">
    <h1 class="mb-6 text-2xl font-extrabold">Report Queue</h1>

    <div class="mb-6 flex gap-2">
      @foreach(['pending' => 'Pending', 'reviewed_actioned' => 'Actioned', 'reviewed_dismissed' => 'Dismissed', 'all' => 'All'] as $value => $label)
        <button wire:click="$set('filter', '{{ $value }}')" class="rounded-full px-4 py-2 text-xs font-bold {{ $filter === $value ? 'bg-pink' : 'border border-line text-zinc-400' }}">{{ $label }}</button>
      @endforeach
    </div>

    <div class="space-y-3">
      @forelse($reports as $report)
        <a href="{{ route('admin.reports.show', $report) }}" class="glass flex items-center justify-between rounded-2xl p-4">
          <div>
            <p class="text-sm font-bold">{{ $report->reported?->profile?->display_name ?? $report->reported_id }}</p>
            <p class="text-xs text-zinc-400">Reported by {{ $report->reporter?->profile?->display_name ?? $report->reporter_id }} · {{ $report->reason->label() }}</p>
          </div>
          <span class="rounded-full px-3 py-1 text-xs font-bold {{ $report->severity->value === 'zero_tolerance' ? 'bg-rose-900/60 text-rose-300' : 'bg-panel-soft text-zinc-400' }}">
            {{ $report->severity->value === 'zero_tolerance' ? 'Zero Tolerance' : 'Standard' }}
          </span>
        </a>
      @empty
        <p class="text-sm text-zinc-500">No reports in this view.</p>
      @endforelse
    </div>

    <div class="mt-6">{{ $reports->links() }}</div>
  </div>
</div>
