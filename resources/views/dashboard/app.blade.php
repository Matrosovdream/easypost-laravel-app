@php
    // Browser-facing Reverb config. Falls back to the server-side values when
    // REVERB_PUBLIC_* aren't set (e.g. local non-Docker dev).
    $reverbConfig = [
        'REVERB_APP_KEY' => config('broadcasting.connections.reverb.key'),
        'REVERB_HOST'    => env('REVERB_PUBLIC_HOST',   config('broadcasting.connections.reverb.options.host')),
        'REVERB_PORT'    => env('REVERB_PUBLIC_PORT',   config('broadcasting.connections.reverb.options.port')),
        'REVERB_SCHEME'  => env('REVERB_PUBLIC_SCHEME', config('broadcasting.connections.reverb.options.scheme')),
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="reverb" content='{{ json_encode($reverbConfig) }}' />
    <title>ShipDesk — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/dashboard/main.ts'])
</head>
<body class="h-full">
    <div id="dashboard-app"></div>
</body>
</html>
