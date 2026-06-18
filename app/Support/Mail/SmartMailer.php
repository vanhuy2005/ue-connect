<?php

namespace App\Support\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Smart mailer router that selects the appropriate mail transport
 * based on the recipient's email domain.
 *
 * - HCMUE domains (hcmue.edu.vn, student.hcmue.edu.vn, teacher.hcmue.edu.vn)
 *   → sent via Outlook SMTP (smtp.office365.com) to avoid Microsoft spam filters.
 *
 * - All other domains (gmail.com, outlook.com, yahoo.com, etc.)
 *   → sent via Resend (default mailer, DKIM-signed from send.ueconnect.io.vn).
 */
class SmartMailer
{
    /**
     * HCMUE institutional email domains that require Outlook SMTP delivery.
     *
     * @var array<string>
     */
    protected static array $hcmueDomains = [
        'hcmue.edu.vn',
        'student.hcmue.edu.vn',
        'teacher.hcmue.edu.vn',
    ];

    /**
     * Send a mailable to a recipient, automatically selecting the best mailer.
     *
     * When routing via Outlook SMTP, the envelope "from" is automatically
     * overridden to the OUTLOOK_SMTP_FROM_ADDRESS so Office365 doesn't reject
     * the message for sender mismatch.
     */
    public static function to(string $recipientEmail, Mailable $mailable): void
    {
        $mailer = static::resolveMailer($recipientEmail);

        // When sending via Outlook SMTP, the "from" must match the authenticated
        // SMTP username; otherwise Office365 rejects with a 554 sender mismatch.
        if ($mailer === 'outlook_smtp') {
            $outlookFrom = env('OUTLOOK_SMTP_FROM_ADDRESS', env('OUTLOOK_SMTP_USERNAME'));
            $outlookName = env('OUTLOOK_SMTP_FROM_NAME', config('mail.from.name', 'UEConnect'));

            if ($outlookFrom && method_exists($mailable, 'from')) {
                $mailable->from($outlookFrom, $outlookName);
            }
        }

        Log::info('SmartMailer: dispatching mail', [
            'to' => $recipientEmail,
            'mailer' => $mailer,
            'mailable' => class_basename($mailable),
        ]);

        Mail::mailer($mailer)->to($recipientEmail)->send($mailable);
    }

    /**
     * Determine the correct mailer for the given email address.
     */
    public static function resolveMailer(string $email): string
    {
        $normalizedEmail = Str::lower(trim($email));

        foreach (static::$hcmueDomains as $domain) {
            if (Str::endsWith($normalizedEmail, '@'.Str::lower($domain))) {
                return 'outlook_smtp';
            }
        }

        return config('mail.default', 'resend');
    }

    /**
     * Check whether an email address belongs to an HCMUE institutional domain.
     */
    public static function isHcmueDomain(string $email): bool
    {
        return static::resolveMailer($email) === 'outlook_smtp';
    }
}
