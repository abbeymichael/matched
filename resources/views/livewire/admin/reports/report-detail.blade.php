<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-2xl">
    <a href="{{ route('admin.reports') }}" class="mb-6 inline-block text-xs text-zinc-400">&larr; Back to queue</a>

    <div class="glass mb-6 rounded-2xl p-6">
      <h1 class="mb-1 text-xl font-extrabold">Report against {{ $report->reported?->profile?->display_name ?? $report->reported_id }}</h1>
      <p class="mb-4 text-xs text-zinc-500">Filed by {{ $report->reporter?->profile?->display_name ?? $report->reporter_id }} on {{ $report->created_at?->format('M j, Y g:ia') }}</p>
      <div class="mb-4 space-y-2 text-sm">
        <p><span class="text-zinc-500">Reason:</span> {{ $report->reason->label() }}</p>
        <p><span class="text-zinc-500">Severity:</span> {{ $report->severity->value }}</p>
        <p><span class="text-zinc-500">Status:</span> {{ $report->status->value }}</p>
        @if($report->details)
          <p><span class="text-zinc-500">Details:</span> {{ $report->details }}</p>
        @endif
      </div>

      @if($report->status->value === 'pending')
        <div class="border-t border-line pt-4">
          <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Action</label>
          <select wire:model="action" class="mb-3 w-full rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm">
            <option value="">Select an action</option>
            <option value="dismissed">Dismiss</option>
            <option value="warned">Warn</option>
            <option value="suspended">Suspend</option>
            <option value="banned">Ban</option>
          </select>
          @error('action')<p class="mb-3 text-xs text-rose-400">{{ $message }}</p>@enderror

          @if($action === 'suspended')
            <input type="number" wire:model="suspensionDays" placeholder="Suspension days (default 7)" class="mb-3 w-full rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm">
          @endif

          <textarea wire:model="adminNotes" placeholder="Admin notes" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm"></textarea>
          <button wire:click="submit" class="w-full rounded-xl bg-pink py-3 text-sm font-extrabold">Submit Decision</button>
        </div>
      @else
        <div class="border-t border-line pt-4 text-sm">
          <p><span class="text-zinc-500">Action taken:</span> {{ $report->action_taken ?? '—' }}</p>
          <p><span class="text-zinc-500">Reviewed:</span> {{ $report->reviewed_at?->format('M j, Y g:ia') }}</p>
          @if($report->admin_notes)<p><span class="text-zinc-500">Notes:</span> {{ $report->admin_notes }}</p>@endif
        </div>
      @endif
    </div>

    <h2 class="mb-3 text-sm font-bold text-zinc-400">Report history for this user</h2>
    <div class="space-y-2">
      @foreach($history as $h)
        <div class="glass rounded-xl p-3 text-xs text-zinc-400">{{ $h->reason->label() }} — {{ $h->status->value }} — {{ $h->created_at?->format('M j, Y') }}</div>
      @endforeach
    </div>
  </div>
</div>
