@extends('layouts.app', ['section' => 'Users'])

@section('content')
    <section class="surface-card users-card">
        <div class="card-heading users-card-heading">
            <div><h3>User directory</h3><p>Manage accounts, roles, and account status.</p></div>
            <a class="btn btn-primary btn-sm" href="{{ route('users.create') }}"><i class="bi bi-person-plus me-1"></i> Add user</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="status">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 mb-0" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form class="user-filters" method="GET" action="{{ route('users.index') }}">
            <div class="user-search-field">
                <label class="visually-hidden" for="user-search">Search users</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input class="form-control" id="user-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, or phone">
            </div>
            <div class="user-filter-field">
                <label class="visually-hidden" for="user-role">Filter by role</label>
                <select class="form-select" id="user-role" name="role">
                    <option value="">All roles</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                    <option value="user" @selected(($filters['role'] ?? '') === 'user')>User</option>
                </select>
            </div>
            <div class="user-filter-field">
                <label class="visually-hidden" for="user-status">Filter by status</label>
                <select class="form-select" id="user-status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i> Apply</button>
            @if (count(array_filter($filters, fn ($value) => filled($value))) > 0)
                <a class="btn btn-light btn-sm user-filter-reset" href="{{ route('users.index') }}">Reset</a>
            @endif
        </form>

        @if ($users->isEmpty())
            <div class="empty-state users-empty"><span class="empty-icon"><i class="bi bi-people"></i></span><strong>No users found.</strong><p>Try changing your search or filter criteria.</p><a class="btn btn-light btn-sm mt-3" href="{{ route('users.index') }}">Clear filters</a></div>
        @else
            @php
                $sortUrl = fn (string $column) => route('users.index', array_merge(request()->except('page'), [
                    'sort' => $column,
                    'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
                ]));
                $sortIcon = fn (string $column) => $sort !== $column ? 'bi-arrow-down-up' : ($direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down');
            @endphp
            <div class="table-responsive users-table-wrap">
                <table class="table align-middle users-table">
                    <thead><tr><th><a class="user-sort-link" href="{{ $sortUrl('name') }}">Person <i class="bi {{ $sortIcon('name') }}"></i></a></th><th>Phone</th><th>Role</th><th>Status</th><th><a class="user-sort-link" href="{{ $sortUrl('last_login_at') }}">Last login <i class="bi {{ $sortIcon('last_login_at') }}"></i></a></th><th><a class="user-sort-link" href="{{ $sortUrl('created_at') }}">Created <i class="bi {{ $sortIcon('created_at') }}"></i></a></th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    @if ($user->profile_image)
                                        <img class="user-avatar" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_image) }}" alt="">
                                    @else
                                        <span class="user-avatar user-avatar-fallback">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                                    @endif
                                    <span class="user-cell-copy"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
                                </div>
                            </td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td><span @class(['badge role-badge', 'text-bg-primary' => $user->isAdmin(), 'text-bg-light text-dark' => $user->isUser()])>{{ ucfirst($user->role) }}</span></td>
                            <td><span @class(['badge', 'text-bg-success' => $user->isActive(), 'text-bg-secondary' => $user->isInactive()])>{{ ucfirst($user->status) }}</span></td>
                            <td>{{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}</td>
                            <td>{{ $user->created_at?->format('M j, Y') }}</td>
                            <td>
                                <div class="table-actions justify-content-end">
                                    <a class="table-action" href="{{ route('users.show', $user) }}" aria-label="View {{ $user->name }}" title="View"><i class="bi bi-eye"></i></a>
                                    <a class="table-action" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->name }}" title="Edit"><i class="bi bi-pencil"></i></a>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('users.status', $user) }}" onsubmit="return confirm('Are you sure you want to {{ $user->isActive() ? 'deactivate' : 'activate' }} this account?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="table-action {{ $user->isActive() ? 'table-action-danger' : '' }}" type="submit" aria-label="{{ $user->isActive() ? 'Deactivate' : 'Activate' }} {{ $user->name }}" title="{{ $user->isActive() ? 'Deactivate' : 'Activate' }}"><i class="bi {{ $user->isActive() ? 'bi-person-dash' : 'bi-person-check' }}"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="table-action table-action-danger" type="submit" aria-label="Delete {{ $user->name }}" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-row"><span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>{{ $users->links() }}</div>
        @endif
    </section>
@endsection
