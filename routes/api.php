<?php

use App\Http\Controllers\Api\AccessRequests\CreateAccessRequestController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\PinLoginController;
use App\Http\Controllers\Api\Navigation\CountsController;
use App\Http\Controllers\Api\Shipments\ApprovalsController;
use App\Http\Controllers\Api\Shipments\AssignShipmentController;
use App\Http\Controllers\Api\Shipments\BuyShipmentController;
use App\Http\Controllers\Api\Shipments\CreateShipmentController;
use App\Http\Controllers\Api\Shipments\ListShipmentsController;
use App\Http\Controllers\Api\Shipments\MarkPackedController;
use App\Http\Controllers\Api\Shipments\MyQueueController;
use App\Http\Controllers\Api\Shipments\ShowShipmentController;
use App\Http\Controllers\Api\Shipments\VoidShipmentController;
use App\Http\Controllers\Api\Batches\BatchesController;
use App\Http\Controllers\Api\ScanForms\ScanFormsController;
use App\Http\Controllers\Api\Pickups\PickupsController;
use App\Http\Controllers\Api\Returns\ReturnsController;
use App\Http\Controllers\Api\Claims\ClaimsController;
use App\Http\Controllers\Api\Insurance\InsuranceController;
use App\Http\Controllers\Api\Addresses\AddressesController;
use App\Http\Controllers\Api\Trackers\TrackersController;
use App\Http\Controllers\Api\Analytics\AnalyticsController;
use App\Http\Controllers\Api\Reports\ReportsController;
use App\Http\Controllers\Api\Ops\PrintQueueController;
use App\Http\Controllers\Api\Clients\ClientsController;
use App\Http\Controllers\Api\Settings\TeamController;
use App\Http\Controllers\Api\Settings\UsersController as SettingsUsersController;
use App\Http\Controllers\Api\Settings\ManagersController as SettingsManagersController;
use App\Http\Controllers\Api\Settings\PeopleController as SettingsPeopleController;
use App\Http\Controllers\Api\Settings\AuditLogController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Billing\CheckoutController as BillingCheckoutController;
use App\Http\Controllers\Api\Billing\PlanController as BillingPlanController;
use App\Http\Controllers\Api\Billing\PortalController as BillingPortalController;
use App\Http\Controllers\Api\Demo\FeaturesVisitController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true])->name('api.health');

// Public demo endpoint — broadcasts a Reverb event so any logged-in admin
// watching the dashboard sees a real-time notification.
Route::post('/demo/features-visited', FeaturesVisitController::class)
    ->name('api.demo.features-visited');

Route::prefix('auth')->group(function () {
    Route::post('pin-login', PinLoginController::class)->name('api.auth.pin-login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class)->name('api.auth.logout');
        Route::get('me',      MeController::class)->name('api.auth.me');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('navigation/counts', CountsController::class)->name('api.navigation.counts');
    Route::post('access-requests', CreateAccessRequestController::class)->name('api.access-requests.store');

    Route::prefix('shipments')->name('api.shipments.')->group(function () {
        Route::get('/',                ListShipmentsController::class)->name('index');
        Route::post('/',               CreateShipmentController::class)->name('store');
        Route::get('my-queue',         MyQueueController::class)->name('my-queue');
        Route::get('approvals',        [ApprovalsController::class, 'index'])->name('approvals.index');
        Route::post('approvals/{id}/approve', [ApprovalsController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{id}/decline', [ApprovalsController::class, 'decline'])->name('approvals.decline');

        Route::get('{id}',             ShowShipmentController::class)->whereNumber('id')->name('show');
        Route::post('{id}/buy',        BuyShipmentController::class)->whereNumber('id')->name('buy');
        Route::post('{id}/void',       VoidShipmentController::class)->whereNumber('id')->name('void');
        Route::post('{id}/assign',     AssignShipmentController::class)->whereNumber('id')->name('assign');
        Route::post('{id}/pack',       MarkPackedController::class)->whereNumber('id')->name('pack');
    });

    Route::prefix('batches')->name('api.batches.')->group(function () {
        Route::get('/',              [BatchesController::class, 'index'])->name('index');
        Route::post('/',             [BatchesController::class, 'store'])->name('store');
        Route::get('{id}',           [BatchesController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('{id}/buy',      [BatchesController::class, 'buy'])->whereNumber('id')->name('buy');
        Route::post('{id}/labels',   [BatchesController::class, 'generateLabels'])->whereNumber('id')->name('labels');
    });

    Route::prefix('scan-forms')->name('api.scan-forms.')->group(function () {
        Route::get('/',    [ScanFormsController::class, 'index'])->name('index');
        Route::post('/',   [ScanFormsController::class, 'store'])->name('store');
        Route::get('{id}', [ScanFormsController::class, 'show'])->whereNumber('id')->name('show');
    });

    Route::prefix('pickups')->name('api.pickups.')->group(function () {
        Route::get('/',            [PickupsController::class, 'index'])->name('index');
        Route::post('/',           [PickupsController::class, 'store'])->name('store');
        Route::get('{id}',         [PickupsController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('{id}/buy',    [PickupsController::class, 'buy'])->whereNumber('id')->name('buy');
        Route::post('{id}/cancel', [PickupsController::class, 'cancel'])->whereNumber('id')->name('cancel');
    });

    Route::prefix('returns')->name('api.returns.')->group(function () {
        Route::get('/',              [ReturnsController::class, 'index'])->name('index');
        Route::post('/',             [ReturnsController::class, 'store'])->name('store');
        Route::get('{id}',           [ReturnsController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('{id}/approve',  [ReturnsController::class, 'approve'])->whereNumber('id')->name('approve');
        Route::post('{id}/decline',  [ReturnsController::class, 'decline'])->whereNumber('id')->name('decline');
    });

    Route::prefix('claims')->name('api.claims.')->group(function () {
        Route::get('/',              [ClaimsController::class, 'index'])->name('index');
        Route::post('/',             [ClaimsController::class, 'store'])->name('store');
        Route::get('{id}',           [ClaimsController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('{id}/submit',   [ClaimsController::class, 'submit'])->whereNumber('id')->name('submit');
        Route::post('{id}/approve',  [ClaimsController::class, 'approve'])->whereNumber('id')->name('approve');
        Route::post('{id}/pay',      [ClaimsController::class, 'pay'])->whereNumber('id')->name('pay');
        Route::post('{id}/close',    [ClaimsController::class, 'close'])->whereNumber('id')->name('close');
    });

    Route::prefix('insurance')->name('api.insurance.')->group(function () {
        Route::get('/',    [InsuranceController::class, 'index'])->name('index');
        Route::post('/',   [InsuranceController::class, 'store'])->name('store');
    });

    Route::prefix('addresses')->name('api.addresses.')->group(function () {
        Route::get('/',              [AddressesController::class, 'index'])->name('index');
        Route::post('/',             [AddressesController::class, 'store'])->name('store');
        Route::get('{id}',           [AddressesController::class, 'show'])->whereNumber('id')->name('show');
        Route::put('{id}',           [AddressesController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('{id}',        [AddressesController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('{id}/verify',   [AddressesController::class, 'verify'])->whereNumber('id')->name('verify');
    });

    Route::prefix('trackers')->name('api.trackers.')->group(function () {
        Route::get('/',      [TrackersController::class, 'index'])->name('index');
        Route::post('/',     [TrackersController::class, 'store'])->name('store');
        Route::get('{id}',   [TrackersController::class, 'show'])->whereNumber('id')->name('show');
        Route::delete('{id}', [TrackersController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });

    Route::prefix('analytics')->name('api.analytics.')->group(function () {
        Route::get('overview', [AnalyticsController::class, 'overview'])->name('overview');
        Route::get('carriers', [AnalyticsController::class, 'carriers'])->name('carriers');
    });

    Route::prefix('reports')->name('api.reports.')->group(function () {
        Route::get('/',  [ReportsController::class, 'index'])->name('index');
        Route::post('/', [ReportsController::class, 'store'])->name('store');
    });

    Route::prefix('ops')->name('api.ops.')->group(function () {
        Route::get('print-queue', PrintQueueController::class)->name('print-queue');
    });

    Route::prefix('clients')->name('api.clients.')->group(function () {
        Route::get('/',              [ClientsController::class, 'index'])->name('index');
        Route::post('/',             [ClientsController::class, 'store'])->name('store');
        Route::get('{id}',           [ClientsController::class, 'show'])->whereNumber('id')->name('show');
        Route::put('{id}',           [ClientsController::class, 'update'])->whereNumber('id')->name('update');
        Route::post('{id}/flex-rate',[ClientsController::class, 'flexRate'])->whereNumber('id')->name('flex-rate');
        Route::post('{id}/invoice',  [ClientsController::class, 'invoice'])->whereNumber('id')->name('invoice');
    });

    Route::prefix('settings')->name('api.settings.')->group(function () {
        Route::get('team',            [TeamController::class, 'show'])->name('team.show');
        Route::put('team',            [TeamController::class, 'update'])->name('team.update');
        Route::get('users',           [SettingsUsersController::class, 'index'])->name('users.index');
        Route::post('users/invite',   [SettingsUsersController::class, 'invite'])->name('users.invite');
        Route::post('users/{id}/role',    [SettingsUsersController::class, 'changeRole'])->whereNumber('id')->name('users.role');
        Route::post('users/{id}/disable', [SettingsUsersController::class, 'disable'])->whereNumber('id')->name('users.disable');
        Route::post('users/{id}/enable',  [SettingsUsersController::class, 'enable'])->whereNumber('id')->name('users.enable');
        Route::post('users/{id}/pin',     [SettingsUsersController::class, 'regeneratePin'])->whereNumber('id')->name('users.pin');
        Route::get('managers',        [SettingsManagersController::class, 'index'])->name('managers.index');
        Route::get('people/{role}',   [SettingsPeopleController::class, 'index'])->where('role', '[a-z_]+')->name('people.index');
        Route::get('audit-log',       AuditLogController::class)->name('audit-log');
    });

    Route::prefix('billing')->name('api.billing.')->group(function () {
        Route::get('plan',     BillingPlanController::class)->name('plan');
        Route::post('checkout', BillingCheckoutController::class)->name('checkout');
        Route::post('portal',  BillingPortalController::class)->name('portal');
    });

    Route::prefix('profile')->name('api.profile.')->group(function () {
        Route::put('/',              [ProfileController::class, 'update'])->name('update');
        Route::post('pin',           [ProfileController::class, 'changePin'])->name('pin');
        Route::get('sessions',       [ProfileController::class, 'sessions'])->name('sessions');
        Route::get('notifications',  [ProfileController::class, 'notifications'])->name('notifications.show');
        Route::put('notifications',  [ProfileController::class, 'updateNotifications'])->name('notifications.update');
    });
});
