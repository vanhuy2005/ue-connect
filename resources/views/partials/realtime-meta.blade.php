@php
    $reverbKey = config('broadcasting.connections.reverb.key');
    $isProduction = app()->environment('production');
    
    // 1. Host resolution
    // Read client host first.
    $reverbHost = config('broadcasting.connections.reverb.client.host');
    
    $localHosts = ['127.0.0.1', 'localhost', '0.0.0.0'];
    
    // If client host is empty, 127.0.0.1, localhost, or 0.0.0.0:
    if (empty($reverbHost) || in_array(strtolower($reverbHost), $localHosts)) {
        if ($isProduction) {
            // If app()->environment('production'), use request()->getHost()
            $reqHost = request()->getHost();
            if (empty($reqHost) || in_array(strtolower($reqHost), ['127.0.0.1', 'localhost', '0.0.0.0', '::1'])) {
                $reverbHost = 'ueconnect.io.vn';
            } else {
                $reverbHost = $reqHost;
            }
        } else {
            // Otherwise use localhost/local value.
            $reverbHost = config('broadcasting.connections.reverb.options.host') ?? '127.0.0.1';
        }
    }
    
    // 2. Port resolution
    $reverbPort = config('broadcasting.connections.reverb.client.port');
    // If request is secure and production, default port should be 443.
    if ($isProduction && request()->isSecure()) {
        $reverbPort = 443;
    } elseif (empty($reverbPort)) {
        if ($isProduction) {
            $reverbPort = 443;
        } else {
            $reverbPort = config('broadcasting.connections.reverb.options.port') 
                ?? (request()->isSecure() ? 443 : 8080);
        }
    }
    
    // 3. Scheme resolution
    // If production, scheme should be https.
    if ($isProduction) {
        $reverbScheme = 'https';
    } else {
        $reverbScheme = config('broadcasting.connections.reverb.client.scheme') 
            ?? config('broadcasting.connections.reverb.options.scheme') 
            ?? (request()->isSecure() ? 'https' : 'http');
    }
@endphp
<meta name="reverb-app-key" content="{{ $reverbKey }}">
<meta name="reverb-host" content="{{ $reverbHost }}">
<meta name="reverb-port" content="{{ $reverbPort }}">
<meta name="reverb-scheme" content="{{ $reverbScheme }}">
