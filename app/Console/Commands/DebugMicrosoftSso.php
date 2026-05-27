<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DebugMicrosoftSso extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ueconnect:debug-microsoft-sso';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safe local debugging of Microsoft SSO configurations and environment state.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('UEConnect Microsoft SSO Configuration Debugger');
        $this->info('==================================================');

        $enabled = config('services.microsoft.enabled') ? 'true' : 'false';
        $clientId = config('services.microsoft.client_id');
        $clientSecret = config('services.microsoft.client_secret');
        $tenantId = config('services.microsoft.tenant');
        $redirect = config('services.microsoft.redirect');
        $allowedDomains = config('services.microsoft.allowed_domains');

        $this->line('MICROSOFT_LOGIN_ENABLED: '.$enabled);
        $this->line('MICROSOFT_CLIENT_ID present: '.(filled($clientId) ? 'yes' : 'no'));
        $this->line('MICROSOFT_CLIENT_SECRET present: '.(filled($clientSecret) ? 'yes' : 'no'));
        $this->line('MICROSOFT_TENANT_ID value: '.($tenantId ?: '(not set)'));
        $this->line('MICROSOFT_REDIRECT_URI value: '.($redirect ?: '(not set)'));
        $this->line('MICROSOFT_ALLOWED_DOMAINS parsed list: '.(is_array($allowedDomains) ? implode(', ', $allowedDomains) : '(not set)'));
        $this->line('APP_URL: '.config('app.url'));

        // Check routes
        $hasRedirectRoute = Route::has('auth.microsoft.redirect') ? 'yes' : 'no';
        $hasCallbackRoute = Route::has('auth.microsoft.callback') ? 'yes' : 'no';

        $this->line('login redirect route exists: '.$hasRedirectRoute);
        $this->line('callback route exists: '.$hasCallbackRoute);
        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
