<?php

namespace App\Support\Mail;

class MailDeliveryConfiguration
{
    /**
     * Determine whether a mailer has enough production configuration to send.
     *
     * @return array{configured: bool, reason: string|null, context: array<string, mixed>}
     */
    public static function status(string $mailer): array
    {
        $mailers = config('mail.mailers', []);

        if (! array_key_exists($mailer, $mailers)) {
            return [
                'configured' => false,
                'reason' => 'mailer-not-defined',
                'context' => [
                    'mailer' => $mailer,
                ],
            ];
        }

        if (in_array($mailer, ['array', 'log'], true)) {
            return [
                'configured' => true,
                'reason' => null,
                'context' => [
                    'mailer' => $mailer,
                    'transport' => $mailers[$mailer]['transport'] ?? $mailer,
                ],
            ];
        }

        if ($mailer === 'resend') {
            return static::forRequiredValues($mailer, [
                'services.resend.key' => config('services.resend.key'),
                'mail.from.address' => config('mail.from.address'),
            ]);
        }

        if ($mailer === 'outlook_smtp') {
            return static::forRequiredValues($mailer, [
                'mail.mailers.outlook_smtp.host' => config('mail.mailers.outlook_smtp.host'),
                'mail.mailers.outlook_smtp.username' => config('mail.mailers.outlook_smtp.username'),
                'mail.mailers.outlook_smtp.password' => config('mail.mailers.outlook_smtp.password'),
                'mail.mailers.outlook_smtp.from_address' => config('mail.mailers.outlook_smtp.from_address'),
            ]);
        }

        if (($mailers[$mailer]['transport'] ?? null) === 'smtp') {
            return static::forRequiredValues($mailer, [
                "mail.mailers.{$mailer}.host" => config("mail.mailers.{$mailer}.host"),
                "mail.mailers.{$mailer}.username" => config("mail.mailers.{$mailer}.username"),
                "mail.mailers.{$mailer}.password" => config("mail.mailers.{$mailer}.password"),
                'mail.from.address' => config('mail.from.address'),
            ]);
        }

        return [
            'configured' => true,
            'reason' => null,
            'context' => [
                'mailer' => $mailer,
                'transport' => $mailers[$mailer]['transport'] ?? null,
            ],
        ];
    }

    public static function isConfigured(string $mailer): bool
    {
        return static::status($mailer)['configured'];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array{configured: bool, reason: string|null, context: array<string, mixed>}
     */
    private static function forRequiredValues(string $mailer, array $values): array
    {
        $missing = array_keys(array_filter(
            $values,
            fn (mixed $value): bool => blank($value),
        ));

        return [
            'configured' => $missing === [],
            'reason' => $missing === [] ? null : 'missing-required-mail-config',
            'context' => [
                'mailer' => $mailer,
                'missing' => $missing,
            ],
        ];
    }
}
