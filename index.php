<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

use Carbon\Carbon;
use App\Controllers\ReportController;

$appName = (string) env('APP_NAME', 'JobList Admin Dashboard');
$adminName = (string) env('ADMIN_NAME', 'Admin');
$reportController = new ReportController(db());
$totalRevenue = $reportController->totalRevenue();
$monthlyRevenue = $reportController->monthlyRevenue();
$revenueByProduct = $reportController->revenueByProduct();
$topCustomers = $reportController->topCustomers();
$topCustomerName = $topCustomers['labels'][0] ?? 'No data';
$topCustomerSpend = isset($topCustomers['values'][0]) ? (float) $topCustomers['values'][0] : 0.0;
$topCustomerCount = count($topCustomers['labels'] ?? []);

$activities = [
    'New job post for Backend Developer created.',
    '12 applicants moved to Interview stage.',
    'Monthly hiring report exported by Admin.',
    'Company profile details were updated.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body data-page="admin-dashboard">
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-dot">NW</span>
                <span><?= e($appName); ?></span>
            </div>

            <div class="menu-title">Menu</div>
            <a class="menu-item active" href="index.php">
                <span class="menu-pill"></span>
                Reports
            </a>
            <a class="menu-item" href="calendar.php">
                <span class="menu-pill"></span>
                Calendar
            </a>
        </aside>

        <main class="main">
            <section class="hero-card">
                <div class="title-block">
                    <span class="eyebrow">Northwind Reports</span>
                    <h1>Business performance overview</h1>
                    <p>Review yearly revenue, monthly sales trends, top products, and customer spending in one dashboard.</p>
                </div>
                <div class="hero-actions">
                    <a class="action primary" href="calendar.php">Open Calendar</a>
                    <button class="action" type="button">Export Report</button>
                </div>
            </section>

            <section class="report-card">
                <h2><?= e($totalRevenue['title']); ?></h2>
                <p>Sum of all sales per year</p>
                <div class="chart-wrap">
                    <canvas id="revenueChart"></canvas>
                </div>
            </section>

            <section class="report-card">
                <h2><?= e($monthlyRevenue['title']); ?></h2>
                <p>Demand distribution (YYYY-MM)</p>
                <div class="chart-wrap chart-wide">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </section>

            <section class="report-card">
                <h2><?= e($revenueByProduct['title']); ?></h2>
                <p>Demand distribution &mdash; revenue per product</p>
                <div class="chart-wrap chart-tall">
                    <canvas id="productRevenueChart"></canvas>
                </div>
            </section>

            <section class="report-card top-customers-card">
                <div class="report-head">
                    <div>
                        <span class="report-kicker">Customer insights</span>
                        <h2><?= e($topCustomers['title']); ?></h2>
                        <p>Ranked view of the highest-spending customers in Northwind.</p>
                    </div>
                    <div class="report-highlight">
                        <span class="report-highlight-label">Highest spender</span>
                        <strong class="report-highlight-value"><?= e($topCustomerName); ?></strong>
                        <span class="report-highlight-amount">₱<?= number_format($topCustomerSpend, 2); ?></span>
                        <small><?= e((string) $topCustomerCount); ?> customers shown</small>
                    </div>
                </div>
                <div class="chart-wrap chart-top-customers">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <script>
        const revenueData = <?= json_encode($totalRevenue, JSON_UNESCAPED_UNICODE); ?>;
        const monthlyRevenueData = <?= json_encode($monthlyRevenue, JSON_UNESCAPED_UNICODE); ?>;
        const productRevenueData = <?= json_encode($revenueByProduct, JSON_UNESCAPED_UNICODE); ?>;
        const topCustomersData = <?= json_encode($topCustomers, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>