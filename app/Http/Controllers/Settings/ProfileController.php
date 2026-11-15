<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    /**
     * Display the account settings page.
     */
    public function edit(Request $request): View
    {
        return view('settings.profile', [
            'user' => $request->user()->load(['patient', 'physician']),
        ]);
    }

    /**
     * Update the signed-in user's name, phone number and profile photo.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.image' => 'The photo must be a JPG, PNG or WebP image.',
            'photo.mimes' => 'The photo must be a JPG, PNG or WebP image.',
            'photo.max' => 'The photo may not be larger than 2 MB.',
        ]);

        $before = $user->only(['name', 'phone']);

        $user->name = $data['name'];
        $user->phone = $data['phone'];

        if ($request->hasFile('photo')) {
            $this->storePhoto($request, $user);
        }

        $user->save();

        AuditLogger::log($user, 'user.profile_updated', $user, $before, $user->fresh()->only(['name', 'phone']));

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your profile has been updated.');
    }

    /**
     * Stream the signed-in user's own profile photo from private storage.
     *
     * Photos are medical-adjacent identity data, so they live on the private
     * disk and are only ever served to the account that owns them.
     */
    public function photo(Request $request): StreamedResponse
    {
        $user = $request->user()->load(['patient', 'physician']);

        $path = $user->patient?->photo_path ?? $user->physician?->photo_path;

        $disk = Storage::disk();

        abort_unless($path !== null && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Change the signed-in user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        $user->update(['password' => $request->string('password')]);

        // Invalidate "remember me" tokens on every other device so a stolen
        // session cannot survive a password change.
        Auth::logoutOtherDevices((string) $request->input('password'));

        AuditLogger::log($user, 'user.password_changed', $user);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your password has been changed.');
    }

    /**
     * Persist the uploaded photo on the linked patient/physician profile.
     *
     * @param  array<string, mixed>  $data
     */
    private function storePhoto(Request $request, $user): void
    {
        $profile = $user->patient ?? $user->physician;

        if ($profile === null) {
            return;
        }

        $disk = Storage::disk();

        if ($profile->photo_path !== null) {
            $disk->delete($profile->photo_path);
        }

        $file = $request->file('photo');
        $raw = @file_get_contents($file->getRealPath());

        // Re-encode raster images through GD when available to strip EXIF metadata.
        if ($raw !== false && function_exists('imagecreatefromstring')) {
            $image = @imagecreatefromstring($raw);

            if ($image !== false) {
                ob_start();
                imagejpeg($image, null, 88);
                $encoded = ob_get_clean();
                imagedestroy($image);

                if (is_string($encoded) && $encoded !== '') {
                    $path = 'photos/'.$file->hashName();
                    $path = preg_replace('/\.[^.]+$/', '.jpg', $path) ?? $path.'.jpg';
                    $disk->put($path, $encoded);
                    $profile->photo_path = $path;
                    $profile->save();

                    return;
                }
            }
        }

        $profile->photo_path = $file->store('photos');
        $profile->save();
    }
}
