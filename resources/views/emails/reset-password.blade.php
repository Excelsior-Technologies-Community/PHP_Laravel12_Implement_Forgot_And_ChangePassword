<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f5f6fa;">

    <!-- 
        Main container table
        Centers the email content and limits width for better readability
        Adds background color, rounded corners, and subtle shadow
    -->
    <table align="center" cellpadding="0" cellspacing="0" width="100%" 
           style="max-width:600px; margin:50px auto; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">

        <!-- Header Section -->
        <tr>
            <td style="padding: 30px; text-align: center; background-color: #4f46e5; color: #ffffff; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <!-- Title of the email -->
                <h1 style="margin:0; font-size:24px;">Reset Your Password</h1>
            </td>
        </tr>

        <!-- Body Section -->
        <tr>
            <td style="padding: 30px; color: #333333; line-height: 1.6; font-size: 16px;">
                
                <!-- Greeting -->
                <p>Hello,</p>

                <!-- Instruction -->
                <p>We received a request to reset your password. Click the button below to reset it:</p>

                <!-- Reset Password Button -->
                <p style="text-align:center; margin:30px 0;">
                    <a href="{{ $resetUrl }}" 
                       style="background-color:#4f46e5; color:#ffffff; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;">
                       Reset Password
                    </a>
                </p>

                <!-- Security Notice -->
                <p>If you did not request a password reset, you can safely ignore this email.</p>

                <!-- Closing / Signature -->
                <p style="margin-top:30px;">Thanks,<br>
                   <strong>{{ config('app.name') }}</strong>
                </p>
            </td>
        </tr>

        <!-- Footer Section -->
        <tr>
            <td style="padding: 20px; text-align: center; font-size: 12px; color: #888888; background-color: #f5f6fa; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                <!-- Footer text -->
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>

    </table>

</body>
</html>
