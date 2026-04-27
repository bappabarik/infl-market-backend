<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Settings\SettingsInterface;

class PricingService
{
    private float $platformCommissionPercent;

    public function __construct(SettingsInterface $settings)
    {
        $this->platformCommissionPercent = (float)($settings->get('platform')['commission_percent'] ?? 15.00);
    }

    /**
     * Calculate frozen pricing for a deal.
     *
     * @param float $baseCost             Influencer's cost per post/video
     * @param float $affiliatePercent     Affiliate's commission %
     * @return array{
     *   influencer_base_cost: float,
     *   affiliate_commission_percent: float,
     *   platform_commission_percent: float,
     *   affiliate_commission_amount: float,
     *   platform_commission_amount: float,
     *   total_amount: float
     * }
     */
    public function calculate(float $baseCost, float $affiliatePercent, int $quantity = 1): array
    {
        $unitAffiliate = round($baseCost * $affiliatePercent / 100, 2);
        $unitPlatform  = round($baseCost * $this->platformCommissionPercent / 100, 2);
        $unitTotal     = $baseCost + $unitAffiliate + $unitPlatform;

        return [
            'influencer_base_cost'         => $baseCost,
            'affiliate_commission_percent' => $affiliatePercent,
            'platform_commission_percent'  => $this->platformCommissionPercent,
            'affiliate_commission_amount'  => round($unitAffiliate * $quantity, 2),
            'platform_commission_amount'   => round($unitPlatform * $quantity, 2),
            'total_amount'                 => round($unitTotal * $quantity, 2),
        ];
    }
}