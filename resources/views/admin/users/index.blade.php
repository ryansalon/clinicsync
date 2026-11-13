@extends('layouts.app')

@section('title', 'User accounts')

@section('content')
    <x-page-header title="User accounts" description="Create staff and patient accounts, filter by role, and manage account status.">
        <x-slot:actions>
            <details data-popover class="card w-full shrink-0 overflow-hidden lg:w-auto">
                <summary class="btn btn-primary w-full cursor-pointer list-none lg:w-auto">
                    <x-icon name="plus" class="h-4 w-4" />
                    Add user
                </summary>
                <form method="POST" action="{{ route('admin.users.store') }}" class="card-body border-t border-slate-200 space-y-4 lg:w-[30rem]">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="create-name" class="label">Full name</label>
                            <input id="create-name" type="text" name="name" value="{{ old('name') }}"
                                   autocomplete="name"
                                   class="input @error('name') input-invalid @enderror" placeholder="Juan Dela Cruz" required>
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="create-email" class="label">Email address</label>
                            <input id="create-email" type="email" name="email" value="{{ old('email') }}"
                                   autocomplete="email"
                                   class="input @error('email') input-invalid @enderror" placeholder="name@clinic.ph" required>
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="create-phone" class="label">Phone</label>
                            <input id="create-phone" type="tel" name="phone" value="{{ old('phone') }}"
                                   inputmode="numeric" autocomplete="tel"
                                   class="input @error('phone') input-invalid @enderror" placeholder="09XXXXXXXXX">
                            @error('phone')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="create-role" class="label">Role</label>
                            <select id="create-role" name="role" class="input @error('role') input-invalid @enderror" required>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>
                                        {{ $r->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="create-password" class="label">Password</label>
                            <div class="relative">
                                <input id="create-password" type="password" name="password"
                                       class="input pr-16 @error('password') input-invalid @enderror"
                                       placeholder="••••••••" required autocomplete="new-password">
                                <x-password-toggle for="#create-password" />
                            </div>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="create-password-confirmation" class="label">Confirm password</label>
                            <div class="relative">
                                <input id="create-password-confirmation" type="password" name="password_confirmation"
                                       class="input pr-16" placeholder="••••••••" required autocomplete="new-password">
                                <x-password-toggle for="#create-password-confirmation" />
                            </div>
                        </div>

                        <div>
                            <label for="create-specialization" class="label">Specialization</label>
                            <input id="create-specialization" type="text" name="specialization"
                                   value="{{ old('specialization') }}"
                                   class="input @error('specialization') input-invalid @enderror"
                                   placeholder="e.g. Family Medicine">
                            @error('specialization')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                            <p class="helper">Required only when the role is Physician.</p>
                        </div>

                        <div>
                            <label for="create-license" class="label">License number</label>
                            <input id="create-license" type="text" name="license_number"
                                   value="{{ old('license_number') }}"
                                   class="input @error('license_number') input-invalid @enderror"
                                   placeholder="e.g. MD-000123">
                            @error('license_number')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                            <p class="helper">Required only when the role is Physician.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <x-icon name="user-plus" class="h-4 w-4" />
                        Create account
                    </button>
                </form>
            </details>
        </x-slot:actions>
    </x-page-header>

    {{-- Toolbar: search + role filter --}}
    @php
        $filterChips = [];
        if ($search) {
            $filterChips[] = [
                'label' => '“'.$search.'”',
                'url' => route('admin.users.index', array_filter(['role' => $role])),
            ];
        }
        if ($role) {
            $filterChips[] = [
                'label' => 'Role: '.\Illuminate\Support\Str::headline($role),
                'url' => route('admin.users.index', array_filter(['q' => $search])),
            ];
        }
    @endphp

    <x-filter-bar :action="route('admin.users.index')" submit-label="Search" :chips="$filterChips">
        <x-slot:fields>
            <div class="flex-1 sm:max-w-xs">
                <label for="q" class="label">Search</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" type="search" name="q" value="{{ $search }}" class="input pl-9"
                           placeholder="Name or email address">
                </div>
            </div>

            <div class="sm:w-60">
                <label for="role" class="label">Role</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="funnel" class="h-4 w-4" />
                    </span>
                    <select id="role" name="role" class="input pl-9" data-auto-submit>
                        <option value="">All roles</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->value }}" {{ $role === $r->value ? 'selected' : '' }}>
                                {{ $r->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot:fields>
    </x-filter-bar>

    {{-- Accounts container --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <x-card-title>Accounts</x-card-title>
            <span class="text-xs text-slate-500">{{ $users->total() }} total</span>
        </div>

        {{-- Mobile card view (below md) --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($users as $user)
                <article class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$user->name" size="sm" />
                            <div>
                                <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="badge badge-{{ $user->is_active ? 'enabled' : 'suspended' }}">
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-xs text-slate-500 pt-1">
                        @php($roleName = $user->getRoleNames()->first())
                        <span>{{ $roleName ? \Illuminate\Support\Str::headline($roleName) : 'No role' }} · Joined {{ $user->created_at->isoFormat('MMM D, YYYY') }}</span>

                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="q" value="{{ $search }}">
                                <input type="hidden" name="role" value="{{ $role }}">
                                <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-secondary' }} btn-sm"
                                        data-confirm="{{ $user->is_active
                                            ? 'Suspend '.$user->name.'? They will be signed out immediately.'
                                            : 'Reactivate '.$user->name.'?' }}">
                                    <x-icon name="{{ $user->is_active ? 'lock' : 'refresh' }}" class="h-4 w-4" />
                                    {{ $user->is_active ? 'Suspend' : 'Reactivate' }}
                                </button>
                            </form>
                        @else
                            <span class="text-xs font-medium text-slate-400">You</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="p-8">
                    <x-empty icon="users" title="No user accounts found"
                             description="No user accounts match the current filters." />
                </div>
            @endforelse
        </div>

        {{-- Desktop table view (md and up) --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="table-head">Name</th>
                        <th class="table-head">Role</th>
                        <th class="table-head">Status</th>
                        <th class="table-head">Created</th>
                        <th class="table-head text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$user->name" size="sm" />
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell">
                                @php($roleName = $user->getRoleNames()->first())
                                {{ $roleName ? \Illuminate\Support\Str::headline($roleName) : 'No role' }}
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $user->is_active ? 'enabled' : 'suspended' }}">
                                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>
                            <td class="table-cell whitespace-nowrap">{{ $user->created_at->isoFormat('MMM D, YYYY') }}</td>
                            <td class="table-cell text-right">
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="q" value="{{ $search }}">
                                        <input type="hidden" name="role" value="{{ $role }}">
                                        <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-secondary' }} btn-sm"
                                                data-confirm="{{ $user->is_active
                                                    ? 'Suspend '.$user->name.'? They will be signed out immediately.'
                                                    : 'Reactivate '.$user->name.'?' }}">
                                            <x-icon name="{{ $user->is_active ? 'lock' : 'refresh' }}" class="h-4 w-4" />
                                            {{ $user->is_active ? 'Suspend' : 'Reactivate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">You</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12">
                                <x-empty icon="users" title="No user accounts found"
                                         description="No user accounts match the current filters." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
