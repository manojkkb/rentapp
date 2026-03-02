<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;
class OtpService
{
    const MAX_ATTEMPTS = 5;
    const EXPIRY_MINUTES = 5;

    /*
    |--------------------------------------------------------------------------
    | Send OTP
    |--------------------------------------------------------------------------
    */
    public function sendOtp(string $identifier, string $type = 'phone'): string
    {
        // Delete old active OTPs for this identifier
        OtpVerification::where('identifier', $identifier)->delete();

        // Generate OTP
        $otp = (string) random_int(100000, 999999);

        OtpVerification::create([
            'identifier'      => $identifier,
            'identifier_type' => $type,
            'otp'             => $otp,
            'expires_at'      => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        // 👉 Integrate SMS / Email here
        // SmsService::send($identifier, "Your OTP is $otp");
        if ($type === 'email') {
            // Mail::to($identifier)->send(new OtpMail($otp));
            Log::info('OTP queued for email delivery', ['identifier' => $identifier, 'otp' => $otp]);
        } else {
                $name="SMTJobs";
            // $text = "$otp is verification otp for SMT. OTPs are SECRET. DO NOT disclose it to anyone. SMT Labs Private Limited";
             $text = "$otp is verification otp for " . $name . ". OTPs are SECRET. DO NOT disclose it to anyone."; 
             SmsService::send($identifier, $text);
            Log::info('OTP queued for SMS delivery', ['identifier' => $identifier, 'otp' => $otp]);
        }

        return $otp;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */
    public function verifyOtp(string $identifier, string $inputOtp): bool
    {
        Log::info('OTP Verification Attempt', [
            'identifier' => $identifier,
            'input_otp' => $inputOtp,
            'input_otp_length' => strlen($inputOtp)
        ]);

        $record = OtpVerification::where('identifier', $identifier)
            ->latest()
            ->first();

        if (!$record) {
            Log::warning('OTP verification failed: No record found', ['identifier' => $identifier]);
            return false;
        }

        Log::info('OTP Record Found', [
            'identifier' => $identifier,
            'stored_otp' => $record->otp,
            'stored_otp_length' => strlen($record->otp),
            'expires_at' => $record->expires_at,
            'now' => now(),
            'is_expired' => $record->expires_at->isPast()
        ]);

        // Expired
        if ($record->expires_at->isPast()) {
            Log::warning('OTP verification failed: Expired', [
                'identifier' => $identifier,
                'expires_at' => $record->expires_at,
                'now' => now()
            ]);
            return false;
        }

        // Too many attempts
        if ($record->attempts >= self::MAX_ATTEMPTS) {
            Log::warning('OTP verification failed: Max attempts reached', [
                'identifier' => $identifier,
                'attempts' => $record->attempts
            ]);
            return false;
        }

        // Match OTP - Ensure both are strings for comparison
        $storedOtp = (string) $record->otp;
        $inputOtp = (string) $inputOtp;

        Log::info('OTP Comparison', [
            'stored_otp' => $storedOtp,
            'input_otp' => $inputOtp,
            'match' => $storedOtp === $inputOtp
        ]);

        if ($storedOtp === $inputOtp) {
            $record->update([
                'verified_at' => now(),
            ]);

            Log::info('OTP verification successful', ['identifier' => $identifier]);
            return true;
        }

        // Wrong attempt
        $record->increment('attempts');
        
        Log::warning('OTP verification failed: Invalid OTP', [
            'identifier' => $identifier,
            'attempts' => $record->attempts + 1
        ]);

        return false;
    }
}