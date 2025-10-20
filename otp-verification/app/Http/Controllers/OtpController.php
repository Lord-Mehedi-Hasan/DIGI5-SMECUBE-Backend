<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phone = $request->phone;
        $otpCode = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Delete any existing OTP for this phone
        Otp::where('phone', $phone)->delete();

        // Create new OTP
        Otp::create([
            'phone' => $phone,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send SMS via Twilio (mock for testing without real credentials)
        try {
            // For testing purposes, we'll simulate sending SMS
            // In production, uncomment the Twilio code below
            /*
            $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
            $twilio->messages->create(
                $phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your OTP code is: $otpCode. It expires in 5 minutes.",
                ]
            );
            */

            // Mock response for testing
            return response()->json(['message' => 'OTP sent successfully', 'otp' => $otpCode]); // Remove 'otp' in production
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $otp = Otp::where('phone', $request->phone)
                  ->where('otp_code', $request->otp_code)
                  ->where('expires_at', '>', now())
                  ->first();

        if (!$otp) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $otp->update(['verified' => true]);

        return response()->json(['message' => 'OTP verified successfully']);
    }
}
