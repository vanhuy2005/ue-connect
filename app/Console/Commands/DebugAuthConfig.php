<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DebugAuthConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ueconnect:debug-auth-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safe local debugging of authentication configurations, domain policies, and Microsoft SSO configurations.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('UEConnect Auth & Identity Configuration Debugger');
        $this->info('==================================================');

        // 1. General Config
        $this->line('APP_URL: '.config('app.url', 'not set'));
        try {
            $dbName = DB::connection()->getDatabaseName();
            $this->line('Active Database: '.$dbName);
        } catch (\Exception $e) {
            $this->error('Database connection failed: '.$e->getMessage());
        }

        $this->line('--------------------------------------------------');

        // 2. UEConnect Domain Settings
        $this->info('UEConnect Identity Suffix Policies:');
        $studentDomains = config('ueconnect.identity.student_email_domains', []);
        $staffDomains = config('ueconnect.identity.staff_email_domains', []);
        $alumniAllowed = config('ueconnect.identity.alumni_personal_email_allowed') ? 'yes' : 'no';
        $mentorAllowed = config('ueconnect.identity.external_mentor_personal_email_allowed') ? 'yes' : 'no';

        $this->line('Student Allowed Domains: '.implode(', ', $studentDomains));
        $this->line('Staff/Advisor Allowed Domains: '.implode(', ', $staffDomains));
        $this->line('Alumni Personal Email Allowed: '.$alumniAllowed);
        $this->line('External Mentor Personal Email Allowed: '.$mentorAllowed);

        $this->line('--------------------------------------------------');

        // 3. Microsoft SSO Configuration (Secrets redacted)
        $this->info('Microsoft SSO Settings:');
        $ssoEnabled = config('services.microsoft.enabled') ? 'yes' : 'no';
        $clientId = config('services.microsoft.client_id') ? 'present (redacted)' : 'missing';
        $clientSecret = config('services.microsoft.client_secret') ? 'present (redacted)' : 'missing';
        $tenantId = config('services.microsoft.tenant', 'missing');
        $allowedSsoDomains = config('services.microsoft.allowed_domains', []);

        $this->line('Microsoft SSO Enabled: '.$ssoEnabled);
        $this->line('Client ID: '.$clientId);
        $this->line('Client Secret: '.$clientSecret);
        $this->line('Tenant ID: '.$tenantId);
        $this->line('SSO Allowed Domains: '.implode(', ', $allowedSsoDomains));

        $this->line('--------------------------------------------------');

        // 4. Verification Route Verification
        $this->info('Auth & Verification Route Registrations:');
        $routesToCheck = [
            'login',
            'register',
            'dashboard',
            'verification.start',
            'verification.status',
            'auth.microsoft.redirect',
            'auth.microsoft.callback',
        ];

        foreach ($routesToCheck as $routeName) {
            $hasRoute = Route::has($routeName) ? 'registered' : 'NOT registered';
            $this->line("Route '{$routeName}': {$hasRoute}");
        }

        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
