<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-2xl">
    <a href="{{ route('dashboard') }}" class="mb-6 inline-block text-xs text-zinc-400">&larr; Back to matches</a>

    <div class="glass overflow-hidden rounded-3xl">
      <div class="grid grid-cols-2 gap-1 bg-panel-soft p-1">
        @forelse($user->photos as $photo)
          <img src="{{ $photo->url() }}" loading="lazy" class="h-48 w-full rounded-xl object-cover">
        @empty
          <div class="col-span-2 flex h-48 items-center justify-center text-zinc-500">No photos</div>
        @endforelse
      </div>

      <div class="p-6">
        <h1 class="mb-1 text-2xl font-extrabold">{{ $user->profile?->display_name }}, {{ $user->profile?->date_of_birth?->age }}</h1>
        <p class="mb-6 text-sm text-zinc-400">{{ $user->profile?->city }}</p>

        <div class="mb-6 space-y-3">
          @foreach($fields as $field)
            <div class="flex justify-between border-b border-line pb-2 text-sm">
              <span class="text-zinc-500">{{ $field->label }}</span>
              <span>{{ is_array($fieldValues[$field->key]->value ?? null) ? implode(', ', $fieldValues[$field->key]->value) : ($fieldValues[$field->key]->value ?? '—') }}</span>
            </div>
          @endforeach
        </div>

        @if($reportSubmitted)
          <div class="mb-4 rounded-xl border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">Report submitted. Our team will review it shortly.</div>
        @endif

        <div class="flex gap-3">
          @if($isMutual)
            <a href="{{ route('chat.index') }}" class="flex-1 rounded-xl bg-violet py-3 text-center text-sm font-extrabold">It's a match — Message</a>
          @elseif($hasExpressedInterest)
            <button disabled class="flex-1 rounded-xl bg-panel-soft py-3 text-sm font-bold text-zinc-400">Interest Sent ✓</button>
          @else
            <button wire:click="expressInterest" class="flex-1 rounded-xl bg-pink py-3 text-sm font-extrabold hover:bg-pink-soft">I'm Interested</button>
          @endif
          <button wire:click="openReportModal" class="rounded-xl border border-line px-4 py-3 text-sm text-zinc-400">Report</button>
        </div>
      </div>
    </div>
  </div>

  @if($showReportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6">
      <div class="glass w-full max-w-sm rounded-2xl p-6">
        <h2 class="mb-4 text-lg font-bold">Report this profile</h2>
        <select wire:model="reportReason" class="mb-3 w-full rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm">
          <option value="harassment">Harassment</option>
          <option value="threats">Threats</option>
          <option value="fake_profile">Fake profile</option>
          <option value="explicit_content">Explicit content</option>
          <option value="hate_speech">Hate speech</option>
          <option value="underage">Underage user</option>
          <option value="other">Other</option>
        </select>
        @error('reportReason')<p class="mb-3 text-xs text-rose-400">{{ $message }}</p>@enderror
        <textarea wire:model="reportDetails" placeholder="Details (optional)" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm"></textarea>
        <div class="flex gap-2">
          <button type="button" wire:click="closeReportModal" class="flex-1 rounded-xl border border-line py-2 text-sm">Cancel</button>
          <button type="button" wire:click="submitReport" class="flex-1 rounded-xl bg-rose-600 py-2 text-sm font-bold">Submit</button>
        </div>
      </div>
    </div>
  @endif
</div>
