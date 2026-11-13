@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
    <x-page-header title="Audit log" description="Immutable trail of privileged actions for compliance review." />

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="card mb-6">
        <div class="card-body flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1 sm:max-w-xs">
                <label for="action" class="label">Action</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="action" type="text" name="action" value="{{ $action }}" class="input pl-9"
                           placeholder="e.g. appointment.booked">
                </div>
            </div>

            <div class="sm:w-60">
                <label for="user_id" class="label">User</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="funnel" class="h-4 w-4" />
                    </span>
                    <select id="user_id" name="user_id" class="input pl-9">
                        <option value="">All users</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-secondary">
                    <x-icon name="funnel" class="h-4 w-4" />
                    Filter
                </button>
            </div>
        </div>
    </form>

    {{-- Log table --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h3 class="section-title">Recorded events</h3>
            <span class="text-xs text-slate-500">{{ $logs->total() }} entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="table-head">Time</th>
                        <th class="table-head">User</th>
                        <th class="table-head">Action</th>
                        <th class="table-head">Record</th>
                        <th class="table-head">IP</th>
                        <th class="table-head">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="table-row">
                            <td class="table-cell whitespace-nowrap">{{ $log->created_at->format('M d, Y g:i:s A') }}</td>
                            <td class="table-cell">
                                @if ($log->user)
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$log->user->name" size="sm" />
                                        <span class="font-medium text-slate-900">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">System</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ $log->action }}</code>
                            </td>
                            <td class="table-cell whitespace-nowrap">
                                {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                            </td>
                            <td class="table-cell whitespace-nowrap text-slate-500">{{ $log->ip_address }}</td>
                            <td class="table-cell">
                                @if ($log->old_values !== null || $log->new_values !== null)
                                    <details>
                                        <summary class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-medium text-teal-600 transition hover:text-teal-700">
                                            <x-icon name="eye" class="h-4 w-4" />
                                            View changes
                                        </summary>
                                        <div class="mt-2 max-h-48 w-72 overflow-auto rounded-md border border-slate-200 bg-slate-50 p-2">
                                            @if ($log->old_values !== null)
                                                <p class="mb-1 text-xs font-semibold text-slate-500">Before</p>
                                                <pre class="whitespace-pre-wrap break-all text-xs text-slate-700">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                            @if ($log->new_values !== null)
                                                <p class="mb-1 {{ $log->old_values !== null ? 'mt-3' : '' }} text-xs font-semibold text-slate-500">After</p>
                                                <pre class="whitespace-pre-wrap break-all text-xs text-slate-700">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    —
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
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection
