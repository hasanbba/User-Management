<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexUsersRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request): View
    {
        $filters = $request->validated();
        $search = trim($filters['search'] ?? '');
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $query->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        $users = $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'filters', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $image = $data['profile_image'] ?? null;
        unset($data['profile_image']);

        $imagePath = $this->storeImage($image);

        if ($imagePath !== null) {
            $data['profile_image'] = $imagePath;
        }

        try {
            DB::transaction(fn () => User::create($data));
        } catch (Throwable $exception) {
            $this->deleteImage($imagePath);
            throw $exception;
        }

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $image = $data['profile_image'] ?? null;
        unset($data['profile_image']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $newImagePath = $this->storeImage($image);
        $oldImagePath = $user->profile_image;

        if ($newImagePath !== null) {
            $data['profile_image'] = $newImagePath;
        }

        try {
            DB::transaction(fn () => $user->update($data));
        } catch (Throwable $exception) {
            $this->deleteImage($newImagePath);
            throw $exception;
        }

        if ($newImagePath !== null && $oldImagePath !== null) {
            $this->deleteImage($oldImagePath);
        }

        return redirect()->route('users.show', $user)->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $imagePath = $user->profile_image;

        DB::transaction(fn () => $user->delete());
        $this->deleteImage($imagePath);

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }

    public function updateStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user()) && $user->isActive()) {
            return redirect()->route('users.index')->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->isActive() ? User::STATUS_INACTIVE : User::STATUS_ACTIVE,
        ]);

        $status = $user->isActive() ? 'activated' : 'deactivated';

        return redirect()->route('users.index')->with('status', "User account {$status} successfully.");
    }

    private function storeImage(?UploadedFile $image): ?string
    {
        if ($image === null) {
            return null;
        }

        $path = $image->store('profiles', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The profile image could not be stored.');
        }

        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
