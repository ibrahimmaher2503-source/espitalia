<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyDashboardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'today_orders' => $this->resource['today_orders'] ?? 0,
            'today_revenue' => $this->resource['today_revenue'] ?? 0,
            'pending_orders' => $this->resource['pending_orders'] ?? 0,
            'low_stock_count' => $this->resource['low_stock_count'] ?? 0,
            'expiring_soon_count' => $this->resource['expiring_soon_count'] ?? 0,
            'pending_prescriptions' => $this->resource['pending_prescriptions'] ?? 0,
            'monthly_revenue' => $this->resource['monthly_revenue'] ?? 0,
            'top_products' => $this->resource['top_products'] ?? [],
            'order_status_breakdown' => $this->resource['order_status_breakdown'] ?? [],
        ];
    }
}
