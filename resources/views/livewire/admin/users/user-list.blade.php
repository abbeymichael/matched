<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-5xl">
    <h1 class="mb-6 text-2xl font-extrabold">Users</h1>

    <input wire:model.live.debounce.400ms="search" placeholder="Search by phone or name..." class="mb-6 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm">

    <div class="glass overflow-hidden rounded-2xl">
      <table class="w-full text-left text-sm">
        <thead class="bg-panel-soft text-xs uppercase tracking-wider text-zinc-500">
          <tr>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Phone</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Verification</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $u)
            <tr class="border-t border-line" wire:key="user-{{ $u->id }}">
              <td class="px-4 py-3">{{ $u->profile?->display_name ?? '—' }}</td>
              <td class="px-4 py-3 font-mono text-xs">{{ $u->phone }}</td>
              <td class="px-4 py-3">{{ $u->status }}</td>
              @if($editing === $u->id)
                <td class="px-4 py-3">
                  <select wire:model="verificationStatus" class="rounded-lg border border-line bg-panel-soft px-2 py-1 text-xs">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <button wire:click="saveVerification('{{ $u->id }}')" class="mr-1 rounded-lg bg-pink px-2 py-1 text-xs font-bold">Save</button>
                  <input type="number" wire:model="suspensionDays" placeholder="days" class="mr-1 w-14 rounded-lg border border-line bg-panel-soft px-1 py-1 text-xs">
                  <button wire:click="suspend('{{ $u->id }}')" class="mr-1 rounded-lg border border-amber-700 px-2 py-1 text-xs text-amber-400">Suspend</button>
                  <button wire:click="ban('{{ $u->id }}')" class="mr-1 rounded-lg border border-rose-700 px-2 py-1 text-xs text-rose-400">Ban</button>
                  <button wire:click="restore('{{ $u->id }}')" class="mr-1 rounded-lg border border-emerald-700 px-2 py-1 text-xs text-emerald-400">Restore</button>
                  <button wire:click="cancel" class="rounded-lg border border-line px-2 py-1 text-xs">Cancel</button>
                </td>
              @else
                <td class="px-4 py-3">{{ $u->verification_status }}</td>
                <td class="px-4 py-3"><button wire:click="edit('{{ $u->id }}')" class="rounded-lg border border-line px-3 py-1 text-xs">Manage</button></td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
  </div>
</div>
