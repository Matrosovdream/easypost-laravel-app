<?php

namespace App\Providers;

use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Services\Shipping\EasyPostService;
use Illuminate\Support\ServiceProvider;

/**
 * Binds application services and integration clients.
 *
 * - Integrations (app/Mixins/Integrations/{Vendor}/) get their Guzzle-based
 *   clients wired as singletons with typed config objects here.
 * - Application services (app/Services/{Name}/) that orchestrate integrations
 *   + repos + domain logic are bound here too.
 */
final class ServicesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EasyPostClient::class, function ($app): EasyPostClient {
            $useTest = $app->environment(['local', 'testing']);
            $key = $useTest
                ? (string) config('services.easypost.test_api_key', '')
                : (string) config('services.easypost.api_key', '');
            return new EasyPostClient(
                apiKey: $key ?: 'EZTK_TEST',
                baseUrl: (string) config('services.easypost.base_url', 'https://api.easypost.com/v2'),
            );
        });

        $this->app->singleton(EasyPostService::class);
    }
}
