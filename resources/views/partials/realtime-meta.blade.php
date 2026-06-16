@php
    $reverbKey = config('broadcasting.connections.reverb.key');
    $reverbHost = config('broadcasting.connections.reverb.client.host') 
        ?? config('broadcasting.connections.reverb.options.host') 
        ?? '127.0.0.1';
    
    if (in_array($reverbHost, ['127.0.0.1', 'localhost', '']) || empty($reverbHost)) {
        $reverbHost = request()->getHost();
    }
    
    $reverbPort = config('broadcasting.connections.reverb.client.port') 
        ?? config('broadcasting.connections.reverb.options.port') 
        ?? (request()->isSecure() ? 443 : 8080);
        
    $reverbScheme = config('broadcasting.connections.reverb.client.scheme') 
        ?? config('broadcasting.connections.reverb.options.scheme') 
        ?? (request()->isSecure() ? 'https' : 'http');
@endphp
<meta name="reverb-app-key" content="{{ $reverbKey }}">
<meta name="reverb-host" content="{{ $reverbHost }}">
<meta name="reverb-port" content="{{ $reverbPort }}">
<meta name="reverb-scheme" content="{{ $reverbScheme }}">
