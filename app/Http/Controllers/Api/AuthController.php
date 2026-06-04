<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OtpService;
use App\Services\VendorRoleProvisioner;
use App\Support\ApiUserPresenter;
use App\Support\VendorAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function __construct(private readonly OtpService $otpService) {}

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10'],
        ]);

        $otp = $this->otpService->sendOtp($validated['mobile'], 'phone');

        return $this->otpSentResponse($validated['mobile'], $otp, 'OTP sent successfully.');
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10'],
        ]);

        $mobile = $validated['mobile'];

        if (! $this->otpService->hasPendingOtp($mobile)) {
            return $this->fail('No active OTP found. Request a new OTP first.', 400);
        }

        $retryAfter = $this->otpService->secondsUntilResend($mobile);
        if ($retryAfter > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another OTP.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $otp = $this->otpService->resendOtp($mobile, 'phone');

        return $this->otpSentResponse($mobile, $otp, 'OTP resent successfully.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        if (! $this->otpService->verifyOtp($validated['mobile'], $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 400);
        }

        $user = User::query()->firstOrCreate(
            ['mobile' => $validated['mobile']],
            [
                'name' => 'Vendor User',
                'email' => $validated['mobile'].'@rentkia.temp',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        $ownedVendors = Vendor::query()->where('user_id', $user->id)->get();
        $provisioner = app(VendorRoleProvisioner::class);

        foreach ($ownedVendors as $vendor) {
            if (! $user->vendors()->where('vendors.id', $vendor->id)->exists()) {
                $user->vendors()->attach($vendor->id, [
                    'is_owner' => true,
                    'role' => 'owner',
                    'is_active' => true,
                    'last_login_at' => now(),
                ]);
            }
            $provisioner->ensureDefaultRoles($vendor, $user->id);
        }

        $this->autoSelectVendor($user);

        return $this->authResponse($user, $validated['device_name'] ?? 'mobile-app');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $memberships = ApiUserPresenter::memberships($user);

        return $this->ok([
            'user' => ApiUserPresenter::user($user),
            'memberships' => $memberships,
            'context' => ApiUserPresenter::vendorContext($user),
            'requires_vendor_selection' => ! $user->vendor_id && count($memberships) > 1,
            'requires_vendor_creation' => $memberships === [],
        ]);
    }

    public function vendors(Request $request): JsonResponse
    {
        return $this->ok(ApiUserPresenter::memberships($request->user()));
    }

    public function selectVendor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
        ]);

        $user = $request->user();
        $vendor = Vendor::query()->findOrFail($validated['vendor_id']);

        if (! $user->vendors()->where('vendors.id', $vendor->id)->exists()) {
            return $this->fail('You do not have access to this vendor.', 403);
        }

        if (! $vendor->is_verified) {
            return $this->fail('This vendor account is pending verification.', 403);
        }

        $user->vendors()->updateExistingPivot($vendor->id, ['last_login_at' => now()]);
        $user->setCurrentVendorId($vendor->id);
        VendorAccess::flush();
        app(VendorRoleProvisioner::class)->ensureDefaultRoles($vendor, $user->id);

        return $this->ok([
            'user' => ApiUserPresenter::user($user->fresh()),
            'context' => ApiUserPresenter::vendorContext($user->fresh()),
        ], 'Vendor selected.');
    }

    public function createVendor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_category_id' => ['required', 'integer', 'exists:business_categories,id'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'language' => ['required', 'string', 'in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa'],
        ]);

        $user = $request->user();
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;

        while (Vendor::query()->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $vendor = Vendor::query()->create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'business_category_id' => $validated['business_category_id'],
            'owner_name' => $validated['owner_name'],
            'slug' => $slug,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'language' => $validated['language'],
            'is_verified' => false,
            'is_active' => true,
            'rating' => 0,
            'total_reviews' => 0,
        ]);

        $user->update([
            'name' => $validated['owner_name'] ?: $validated['name'],
            'language' => $validated['language'],
        ]);

        $user->vendors()->attach($vendor->id, [
            'is_owner' => true,
            'role' => 'owner',
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        app(VendorRoleProvisioner::class)->ensureDefaultRoles($vendor, $user->id);
        $user->setCurrentVendorId($vendor->id);
        VendorAccess::flush();

        return $this->ok([
            'vendor' => ApiUserPresenter::vendor($vendor),
            'context' => ApiUserPresenter::vendorContext($user->fresh()),
        ], 'Vendor created successfully.', 201);
    }

    public function businessCategories(): JsonResponse
    {
        $categories = BusinessCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return $this->ok($categories->values()->all());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->ok(null, 'Logged out.');
    }

    private function authResponse(User $user, string $deviceName): JsonResponse
    {
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        $memberships = ApiUserPresenter::memberships($user);

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => ApiUserPresenter::user($user),
            'memberships' => $memberships,
            'requires_vendor_selection' => ! $user->vendor_id && count($memberships) > 1,
            'requires_vendor_creation' => $memberships === [],
            'context' => ApiUserPresenter::vendorContext($user),
        ], 'Authenticated.');
    }

    private function otpSentResponse(string $mobile, string $otp, string $message): JsonResponse
    {
        $data = [
            'mobile' => $mobile,
            'retry_after' => OtpService::RESEND_COOLDOWN_SECONDS,
            'expires_in' => OtpService::EXPIRY_MINUTES * 60,
        ];

        if (config('app.debug')) {
            $data['otp'] = $otp;
        }

        return $this->ok($data, $message);
    }

    private function autoSelectVendor(User $user): void
    {
        $vendors = $user->vendors()->wherePivot('is_active', true)->get();

        if ($vendors->count() === 1) {
            $vendor = $vendors->first();
            if ($vendor->is_verified) {
                $user->setCurrentVendorId($vendor->id);
                $user->vendors()->updateExistingPivot($vendor->id, ['last_login_at' => now()]);
            }

            return;
        }

        if ($user->vendor_id) {
            $defaultVendor = $vendors->firstWhere('id', $user->vendor_id);
            if ($defaultVendor?->is_verified) {
                $user->vendors()->updateExistingPivot($defaultVendor->id, ['last_login_at' => now()]);
            }
        }
    }
}
