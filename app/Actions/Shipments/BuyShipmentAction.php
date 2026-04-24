<?php

namespace App\Actions\Shipments;

use App\Events\ApprovalRequested;
use App\Events\ShipmentUpdated;
use App\Jobs\CreateTrackerMirrorJob;
use App\Jobs\DownloadLabelAssetsJob;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Shipping\ApprovalRepo;
use App\Repositories\Shipping\ShipmentEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Team\TeamRepo;
use App\Services\Billing\PlanCaps;
use App\Services\Shipping\EasyPostService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BuyShipmentAction
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly ShipmentEventRepo $events,
        private readonly ApprovalRepo $approvals,
        private readonly TeamRepo $teams,
        private readonly EasyPostService $ep,
        private readonly PlanCaps $caps,
    ) {}

    /**
     * Attempt to buy a label. Returns:
     *  - ['status' => 'purchased', 'shipment' => Shipment]
     *  - ['status' => 'approval_required', 'approval' => Approval, 'shipment' => Shipment]
     *  - ['status' => 'plan_cap_exceeded', 'shipment' => Shipment, 'usage' => int, 'cap' => int]
     */
    public function execute(User $user, Shipment $shipment, string $rateId, ?int $insuranceCents = null): array
    {
        $rate = collect($shipment->rates_snapshot ?? [])
            ->firstWhere('id', $rateId);
        if (! $rate) {
            throw new RuntimeException('Selected rate not found in latest snapshot.');
        }

        $team = $this->teams->getByID($shipment->team_id)['Model'] ?? null;
        if ($team && $this->caps->isOverCap((int) $shipment->team_id, (string) $team->plan)) {
            return [
                'status' => 'plan_cap_exceeded',
                'shipment' => $shipment,
                'usage' => $this->caps->usageForTeamThisMonth((int) $shipment->team_id),
                'cap' => $this->caps->capForPlan((string) $team->plan),
                'plan' => (string) $team->plan,
            ];
        }

        $rateCents = (int) round(((float) ($rate['rate'] ?? 0)) * 100);
        $cap = $this->spendingCap($user);
        $rights = $user->rights();
        $canBuy = in_array('shipments.buy', $rights, true);

        $needsApproval = ! $canBuy || ($cap !== null && $rateCents > $cap);

        if ($needsApproval) {
            return DB::transaction(function () use ($user, $shipment, $rate, $rateCents, $cap, $canBuy) {
                $approval = $this->approvals->create([
                    'team_id' => $shipment->team_id,
                    'shipment_id' => $shipment->id,
                    'requested_by' => $user->id,
                    'rate_id' => $rate['id'] ?? null,
                    'rate_snapshot' => $rate,
                    'cost_cents' => $rateCents,
                    'reason' => $canBuy ? 'over_cap' : 'no_buy_right',
                    'status' => 'pending',
                ])['Model'];

                $this->events->record(
                    $shipment->id,
                    'approval_requested',
                    ['approval_id' => $approval->id, 'cost_cents' => $rateCents, 'cap_cents' => $cap],
                    $user->id,
                );

                $this->shipments->updateStatus($shipment, ['status' => 'pending_approval']);

                event(new ApprovalRequested($approval));

                return [
                    'status' => 'approval_required',
                    'approval' => $approval,
                    'shipment' => $shipment->fresh(),
                ];
            });
        }

        return $this->buyNow($user, $shipment, $rate, $insuranceCents);
    }

    /**
     * Buy without the cap/right pre-check (called directly by approval acceptance).
     */
    public function buyNow(User $user, Shipment $shipment, array $rate, ?int $insuranceCents = null): array
    {
        $buy = null;
        $errorMessage = null;
        if ($shipment->ep_shipment_id) {
            try {
                $buy = $this->ep->buy(
                    $shipment->ep_shipment_id,
                    (string) ($rate['id'] ?? ''),
                    $insuranceCents,
                );
            } catch (\Throwable $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $updated = $this->shipments->markPurchased($shipment->id, [
            'status' => $buy ? 'purchased' : 'rate_failed',
            'selected_rate' => $rate,
            'cost_cents' => (int) round(((float) ($rate['rate'] ?? 0)) * 100),
            'carrier' => $rate['carrier'] ?? null,
            'service' => $rate['service'] ?? null,
            'tracking_code' => $buy['tracking_code'] ?? null,
            'label_s3_key' => $buy['postage_label']['label_url'] ?? null,
            'insurance_cents' => $insuranceCents,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'messages' => $errorMessage ? ['buy_error' => $errorMessage] : null,
        ]);

        $this->events->record(
            $shipment->id,
            $buy ? 'purchased' : 'buy_failed',
            $buy ? ['tracking_code' => $buy['tracking_code'] ?? null] : ['error' => $errorMessage],
            $user->id,
        );

        if ($buy && $updated) {
            event(new ShipmentUpdated($updated));
            DownloadLabelAssetsJob::dispatch($updated->id);
            if ($updated->tracking_code && $updated->carrier) {
                CreateTrackerMirrorJob::dispatch($updated->id);
            }
        }

        return ['status' => $buy ? 'purchased' : 'failed', 'shipment' => $updated];
    }

    private function spendingCap(User $user): ?int
    {
        $teamId = $user->current_team_id;
        $pivot = $user->teams()->where('teams.id', $teamId)->first()?->pivot;
        $cap = $pivot?->spending_cap_cents;
        return $cap ? (int) $cap : null;
    }
}
