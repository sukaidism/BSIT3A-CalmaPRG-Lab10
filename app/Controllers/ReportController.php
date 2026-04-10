<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Report;
use PDO;

class ReportController
{
    private Report $report;

    public function __construct(PDO $pdo)
    {
        $this->report = new Report($pdo);
    }

    public function totalRevenue(): array
    {
        $rows = $this->report->getTotalRevenue();

        $years = array_column($rows, 'Year');
        $revenues = array_map(
            fn($v) => round((float) $v, 2),
            array_column($rows, 'Total Revenue')
        );

        return [
            'title'  => 'Northwind Annual Business Performance per Year',
            'Year' => $years,
            'Revenue' => $revenues,
        ];
    }

    public function monthlyRevenue(): array
    {
        $rows = $this->report->getMonthlyRevenue();

        return [
            'title'  => 'Revenue By Month',
            'labels' => array_map(
                fn($month) => date('Y-M', strtotime($month . '-01')),
                array_column($rows, 'Month')
            ),
            'values' => array_map(fn($v) => round((float) $v, 2), array_column($rows, 'Total Revenue')),
        ];
    }

    public function revenueByProduct(): array
    {
        $rows = $this->report->getRevenueByProduct();

        return [
            'title'  => 'Revenue By Product',
            'labels' => array_column($rows, 'Product Name'),
            'values' => array_map(fn($v) => round((float) $v, 2), array_column($rows, 'Total Revenue')),
        ];
    }

    public function topCustomers(): array
    {
        $rows = $this->report->getTopCustomers(10);

        return [
            'title'  => 'Top 10 Customers by Amount Spent',
            'labels' => array_column($rows, 'Company Name'),
            'values' => array_map(fn($v) => round((float) $v, 2), array_column($rows, 'Total Spent')),
        ];
    }

    public function orderCalendarEvents(bool $useCache = true): array
    {
        $redis = function_exists('redis') ? redis() : null;
        $ttl = (int) (function_exists('env') ? env('REDIS_CACHE_TTL', 3600) : 3600);

        if ($useCache) {
            $rows = $this->report->getOrderCalendarEventsCached($redis, $ttl);
        } else {
            $rows = $this->report->getOrderCalendarEvents();
        }

        return array_map(static function (array $row): array {
            $customer = (string) ($row['customer_name'] ?? 'Unknown Customer');
            $total = round((float) ($row['total_order_value'] ?? 0), 2);

            return [
                'title' => $customer . ' • ₱' . number_format($total, 2),
                'start' => (string) ($row['order_date'] ?? ''),
                'allDay' => true,
                'extendedProps' => [
                    'orderId' => $row['order_id'] ?? null,
                    'customer' => $customer,
                    'total' => $total,
                ],
            ];
        }, $rows);
    }
}
