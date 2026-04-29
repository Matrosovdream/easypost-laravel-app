<?php

return [

    /*
     * Stripe price IDs keyed by plan slug. Replace placeholders with real
     * prices from your Stripe dashboard — CheckoutController rejects unknown
     * plans with a 422.
     */
    'prices' => [
        'starter' => env('BILLING_PRICE_STARTER', 'price_starter_placeholder'),
        'team' => env('BILLING_PRICE_TEAM', 'price_team_placeholder'),
        'business' => env('BILLING_PRICE_BUSINESS', 'price_business_placeholder'),
        '3pl' => env('BILLING_PRICE_3PL', 'price_3pl_placeholder'),
    ],

    /*
     * Shipments-per-month cap per plan. null = unlimited. Keep in sync with
     * App\Services\Billing\PlanCaps::CAPS.
     */
    'caps' => [
        'starter' => 100,
        'team' => 1000,
        'business' => 5000,
        '3pl' => 15000,
        'enterprise' => null,
    ],

    'success_url' => env('APP_URL', 'http://localhost:8080').'/dashboard/settings/billing?checkout=success',
    'cancel_url' => env('APP_URL', 'http://localhost:8080').'/dashboard/settings/billing?checkout=cancel',
];
