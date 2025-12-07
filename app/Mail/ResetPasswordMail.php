<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * ResetPasswordMail
 *
 * This Mailable class is responsible for:
 * ✅ Sending reset password email to customer
 * ✅ Passing reset URL to email view
 *
 * Used in PasswordController
 */
class ResetPasswordMail extends Mailable
{
    /**
     * Allows the email to be queued (optional)
     * Improves performance for large applications
     */
    use Queueable;

    /**
     * Converts models into arrays when queueing emails
     */
    use SerializesModels;

    /**
     * Public variable accessible inside Blade email view
     *
     * Example usage in blade:
     * {{ $resetUrl }}
     */
    public $resetUrl;

    /**
     * Constructor
     *
     * Receives reset password URL from controller
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the email
     *
     * - Sets email subject
     * - Defines which blade file is used for email content
     */
    public function build()
    {
        return $this->subject('Reset Password Notification')
                    ->view('emails.reset-password');
    }
}
