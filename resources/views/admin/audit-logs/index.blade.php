@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
    @php
        $filterChips = [];
        if ($action !== '') {
            $filterChips[] = [
                'label' => 'Action: '.$action,
                'url' => route('admin.audit-logs.index', array_filter(['user_id' => $userId])),
            ];
        }
        if ($userId) {
            $matchedUser = $users->firstWhere('id', (int) $userId);
            if ($matchedUser) {
                $filterChips[] = [
                    'label' => $matchedUser->name,
                    'url' => route('admin.audit-logs.index', array_filter(['action' => $action])),
                ];
            }
        }
    @endphp

    <x-page-header title="Audit log" description="Immutable trail of privileged actions for compliance review." />

    <x-filter-bar :action="route('admin.audit-logs.index')" submit-label="Filter" :chips="$filterChips">
        <x-slot:fields>
            <div class="flex-1 sm:max-w-xs">
                <label for="action" class="label">Action</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="action" type="text" name="action" value="{{ $action }}" class="input pl-9"
                           placeholder="e.g. appointment.booked">
                </div>
            </div>

            <div class="sm:w-60">
                <label for="user_id" class="label">User</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
                        <x-icon name="funnel" class="h-4 w-4" />
                    </span>
                    <select id="user_id" name="user_id" class="input pl-9" data-auto-submit>
                        <option value="">All users</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot:fields>
    </x-filter-bar>

    {{-- Log card --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h2 class="section-title">Recorded events</h2>
            <span class="text-xs text-[#465854] cs-num">{{ $logs->total() }} entries</span>
        </div>

        {{-- Mobile audit list --}}
        <div class="divide-y divide-[#DCE3E1] md:hidden">
            @forelse ($logs as $log)
                <article class="space-y-2 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <code class="rounded bg-[#EFF4F2] px-1.5 py-0.5 font-mono text-xs text-[#0F766E]">{{ $log->action }}</code>
                        <span class="cs-num font-mono text-[11px] text-[#465854]">{{ $log->created_at->format('M d, g:i A') }}</span>
                    </div>
                    <p class="text-sm font-medium text-[#14201E]">{{ $log->user?->name ?? 'System' }}</p>
                    <p class="cs-num text-xs text-[#465854]">
                        {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                        @if ($log->ip_address)
                            · {{ $log->ip_address }}
                        @endif
                    </p>
                </article>
            @empty
                <div class="p-8">
                    <x-empty icon="shield-check" title="No audit entries found"
                             description="No audit entries match the current filters." />
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <caption class="sr-only">Clinic audit log events</caption>
                <thead>
                    <tr>
                        <th scope="col" class="table-head">Time</th>
                        <th scope="col" class="table-head">User</th>
                        <th scope="col" class="table-head">Action</th>
                        <th scope="col" class="table-head">Record</th>
                        <th scope="col" class="table-head">IP</th>
                        <th scope="col" class="table-head">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="table-row">
                            <td class="table-cell cs-num whitespace-nowrap font-mono text-xs text-[#465854]">{{ $log->created_at->format('M d, Y g:i:s A') }}</td>
                            <td class="table-cell">
                                @if ($log->user)
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$log->user->name" size="sm" />
                                        <span class="font-medium text-[#14201E]">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-[#465854]">System</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <code class="rounded bg-[#EFF4F2] px-1.5 py-0.5 font-mono text-xs text-[#0F766E]">{{ $log->action }}</code>
                            </td>
                            <td class="table-cell cs-num whitespace-nowrap">
                                {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                            </td>
                            <td class="table-cell cs-num whitespace-nowrap font-mono text-xs text-[#465854]">{{ $log->ip_address }}</td>
                            <td class="table-cell">
                                @if ($log->old_values !== null || $log->new_values !== null)
                                    <details>
                                        <summary class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-medium text-[#0F766E] transition hover:underline">
                                            <x-icon name="eye" class="h-4 w-4" />
                                            View changes
                                        </summary>
                                        <div class="mt-2 max-h-48 w-72 overflow-auto rounded-md border border-[#DCE3E1] bg-[#F4F7F6] p-2">
                                            @if ($log->old_values !== null)
                                                <p class="mb-1 text-xs font-semibold text-[#465854]">Before</p>
                                                <pre class="whitespace-pre-wrap break-all font-mono text-xs text-[#14201E]">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                            @if ($log->new_values !== null)
                                                <p class="mb-1 {{ $log->old_values !== null ? 'mt-3' : '' }} text-xs font-semibold text-[#465854]">After</p>
                                                <pre class="whitespace-pre-wrap break-all font-mono text-xs text-[#14201E]">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <span class="text-[#465854]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12">
                                <x-empty icon="shield-check" title="No audit entries found"
                                         description="No audit entries match the current filters." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-[#DCE3E1] px-5 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
