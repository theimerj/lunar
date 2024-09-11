<?php

namespace Lunar\Base;

use Lunar\Facades\DB;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Order;

class OrderReferenceGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(OrderContract $order): string
    {
        /** @var Order $order */
        $year = $order->created_at->year;

        $month = $order->created_at->format('m');

        $latest = Order::modelClass()::select(
            DB::RAW('MAX(reference) as reference')
        )->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month)
            ->where('id', '!=', $order->id)
            ->first();

        if (! $latest || ! $latest->reference) {
            $increment = 1;
        } else {
            $segments = explode('-', $latest->reference);

            if (count($segments) == 1) {
                $increment = 1;
            } else {
                $increment = end($segments) + 1;
            }
        }

        return $year.'-'.$month.'-'.str_pad($increment, 4, 0, STR_PAD_LEFT);
    }
}
