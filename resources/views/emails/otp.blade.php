<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your OTP</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f5f6fa;">
    <table align="center" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; margin:50px auto; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 30px; text-align: center; background-color: #4f46e5; color: #ffffff; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h1 style="margin:0; font-size:24px;">Your OTP</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333333; line-height: 1.6; font-size: 16px;">
                <p>Hello,</p>
                <p>Use the OTP below to reset your password. It is valid for 10 minutes.</p>
                <p style="text-align:center; margin:30px 0; font-size: 32px; font-weight:bold; letter-spacing: 8px;">{{ $otp }}</p>
                <p>If you did not request this, please ignore this email.</p>
                <p style="margin-top:30px;">Thanks,<br><strong>{{ config('app.name') }}</strong></p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; font-size: 12px; color: #888888; background-color: #f5f6fa; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
