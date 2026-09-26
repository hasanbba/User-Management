<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.index', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('profile_image');
        $image = $request->file('profile_image');
        $oldImage = $user->profile_image;
        $newImage = $image?->store('profiles', 'public');

        if ($image !== null && ! is_string($newImage)) {
            throw new RuntimeException('The profile image could not be stored.');
        }

        if ($newImage !== null) {
            $data['profile_image'] = $newImage;
        }

        if ($data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        try {
            DB::transaction(fn () => $user->update($data));
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('profile.index')->with('status', 'Your profile was updated successfully.');
    }

    public function editPassword(): View
    {
        return view('profile.password');
    }

    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);
        $request->session()->regenerate();

        return redirect()->route('profile.index')->with('status', 'Your password was changed successfully.');
    }
}
