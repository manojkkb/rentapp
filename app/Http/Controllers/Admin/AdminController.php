<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function profile()
    {
        $admin = auth()->guard('admin')->user();

        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        /** @var Admin $admin */
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->ignore($admin->id),
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'current_password' => 'nullable|required_with:password|current_password:admin',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];
        $admin->phone = $validated['phone'] ?? null;

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($admin->avatar);
            $admin->avatar = $request->file('avatar')->store("admins/{$admin->id}", 'public');
        }

        if (! empty($validated['password'])) {
            $admin->password = $validated['password'];
        }

        $admin->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function settings()
    {
        PlatformSettings::seedDefaults();

        return view('admin.settings', [
            'settings' => PlatformSettings::valuesForForm(),
            'definitions' => PlatformSettings::definitions(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_display_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'support_phone' => 'nullable|string|max:20',
            'currency_code' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'maintenance_mode' => 'sometimes|boolean',
            'trial_days' => 'required|integer|min:1|max:365',
            'platform_commission_percent' => 'required|numeric|min:0|max:100',
            'vendor_kyc_required' => 'sometimes|boolean',
            'min_booking_hours' => 'required|integer|min:0|max:720',
            'cancellation_hours_before' => 'required|integer|min:0|max:720',
            'default_security_deposit_percent' => 'required|numeric|min:0|max:100',
            'gst_rate_percent' => 'required|numeric|min:0|max:100',
            'gst_enabled' => 'sometimes|boolean',
        ]);

        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        $validated['vendor_kyc_required'] = $request->boolean('vendor_kyc_required');
        $validated['gst_enabled'] = $request->boolean('gst_enabled');

        PlatformSettings::updateMany($validated);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Platform settings saved.');
    }

    private function deleteAvatar(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
