@extends('layouts.app')

@section('title', 'User accounts')

@section('content')
    <x-page-header title="User accounts" description="Create staff and patient accounts, filter by role, and manage access.">
        <x-slot:actions>
            <button type="button" class="btn btn-primary w-full lg:w-auto" data-dialog-open="create-user-dialog">
                <x-icon name="plus" class="h-4 w-4" />
                Add user
            </button>
        </x-slot:actions>
    </x-page-header>

    <div id="create-user-dialog" data-dialog role="dialog" aria-modal="true" aria-labelledby="create-user-title"
         class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-bright/45 backdrop-blur-xs" data-dialog-close></div>
        <div class="relative max-h-[90dvh] w-full max-w-xl overflow-y-auto rounded-lg border border-hairline bg-surface shadow-[0_16px_40px_-10px_rgba(24,32,29,0.22)]">
            <div class="flex items-center justify-between border-b border-hairline bg-surface-subtle/80 px-5 py-4">
                <h2 id="create-user-title" class="text-base font-semibold text-bright">Create account</h2>
                <button type="button" data-dialog-close aria-label="Close"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-meta transition hover:bg-rail-hover hover:text-bright md:min-h-9 md:min-w-9">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="card-body space-y-4">
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
                               class="input cs-num @error('phone') input-invalid @enderror" placeholder="09XXXXXXXXX">
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="create-role" class="label">Role</label>
                        <select id="create-role" name="role" data-role-picker class="input @error('role') input-invalid @enderror" required>
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

                    <div data-role-only="physician">
                        <label for="create-specialization" class="label">Specialization</label>
                        <input id="create-specialization" type="text" name="specialization"
                               value="{{ old('specialization') }}"
                               class="input @error('specialization') input-invalid @enderror"
                               placeholder="e.g. Family Medicine">
                        @error('specialization')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div data-role-only="physician">
                        <label for="create-license" class="label">License number</label>
                        <input id="create-license" type="text" name="license_number"
                               value="{{ old('license_number') }}"
                               class="input cs-num @error('license_number') input-invalid @enderror"
                               placeholder="e.g. MD-000123">
                        @error('license_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-hairline pt-4">
                    <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="user-plus" class="h-4 w-4" />
                        Create account
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-meta">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" type="search" name="q" value="{{ $search }}" class="input pl-9"
                           placeholder="Name or email address">
                </div>
            </div>

            <div class="sm:w-60">
                <label for="role" class="label">Role</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-meta">
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
    <section class="card mt-5">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <x-card-title>Accounts</x-card-title>
            <span class="cs-num font-mono text-xs text-meta">{{ $users->total() }} total</span>
        </div>

        {{-- Mobile card view --}}
        <div class="divide-y divide-hairline md:hidden">
            @forelse ($users as $user)
                @php
                    $roleName = $user->getRoleNames()->first();
                @endphp
                <article class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$user->name" size="sm" />
                            <div>
                                <p class="font-semibold text-bright">{{ $user->name }}</p>
                                <p class="text-xs text-meta">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="badge badge-{{ $user->is_active ? 'enabled' : 'suspended' }}">
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1 text-xs text-meta">
                        <span class="cs-num">{{ $roleName ? \Illuminate\Support\Str::headline($roleName) : 'No role' }} · Joined {{ $user->created_at->isoFormat('MMM D, YYYY') }}</span>

                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="q" value="{{ $search }}">
                                <input type="hidden" name="role" value="{{ $role }}">
                                <button type="submit" class="btn {{ $user->is_active ? 'btn-outline-danger' : 'btn-secondary' }} btn-sm"
                                        data-confirm="{{ $user->is_active
                                            ? 'Suspend '.$user->name.'? They will be signed out immediately.'
                                            : 'Reactivate '.$user->name.'?' }}">
                                    <x-icon name="{{ $user->is_active ? 'lock' : 'refresh' }}" class="h-4 w-4" />
                                    {{ $user->is_active ? 'Suspend' : 'Reactivate' }}
                                </button>
                            </form>
                        @else
                            <span class="text-xs font-medium text-meta">You</span>
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

        {{-- Desktop table view --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <caption class="sr-only">Clinic user accounts and access status</caption>
                <thead>
                    <tr>
                        <th scope="col" class="table-head">Name</th>
                        <th scope="col" class="table-head">Role</th>
                        <th scope="col" class="table-head">Status</th>
                        <th scope="col" class="table-head">Created</th>
                        <th scope="col" class="table-head text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse ($users as $user)
                        @php
                            $roleName = $user->getRoleNames()->first();
                        @endphp
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$user->name" size="sm" />
                                    <div>
                                        <p class="font-semibold text-bright">{{ $user->name }}</p>
                                        <p class="text-xs text-meta">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell text-bright">
                                {{ $roleName ? \Illuminate\Support\Str::headline($roleName) : 'No role' }}
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $user->is_active ? 'enabled' : 'suspended' }}">
                                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>
                            <td class="table-cell cs-num font-mono text-xs whitespace-nowrap text-meta">{{ $user->created_at->isoFormat('MMM D, YYYY') }}</td>
                            <td class="table-cell text-right">
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="q" value="{{ $search }}">
                                        <input type="hidden" name="role" value="{{ $role }}">
                                        <button type="submit" class="btn {{ $user->is_active ? 'btn-outline-danger' : 'btn-secondary' }} btn-sm"
                                                data-confirm="{{ $user->is_active
                                                    ? 'Suspend '.$user->name.'? They will be signed out immediately.'
                                                    : 'Reactivate '.$user->name.'?' }}">
                                            <x-icon name="{{ $user->is_active ? 'lock' : 'refresh' }}" class="h-4 w-4" />
                                            {{ $user->is_active ? 'Suspend' : 'Reactivate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-medium text-meta">You</span>
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
    </section>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
