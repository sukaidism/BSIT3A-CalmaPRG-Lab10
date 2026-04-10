<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

use Carbon\Carbon;
use App\Controllers\ReportController;

$appName = (string) env('APP_NAME', 'Northwind reports');

$reportController = new ReportController(db());
$redisAvailable = redis() !== null;

// Handle cache flush before benchmarking
if (isset($_GET['flush']) && $_GET['flush'] === '1' && $redisAvailable) {
    redis()->del('northwind:calendar_events');
}

// --- Benchmark: MySQL (no cache) ---
$t1 = microtime(true);
$calendarEventsMySQL = $reportController->orderCalendarEvents(false);
$mysqlTime = round((microtime(true) - $t1) * 1000, 2);

// --- Benchmark: Redis (cache) ---
if ($redisAvailable) {
    // Ensure the cache is populated before measuring the hit time
    $reportController->orderCalendarEvents(true);

    $t2 = microtime(true);
    $calendarEventsRedis = $reportController->orderCalendarEvents(true);
    $redisTime = round((microtime(true) - $t2) * 1000, 2);
} else {
    $redisTime = null;
}

// Use the cached version for rendering when available
$calendarEvents = $redisAvailable ? $calendarEventsRedis : $calendarEventsMySQL;

// Compute improvement
$improvement = ($redisTime !== null && $mysqlTime > 0)
    ? round((($mysqlTime - $redisTime) / $mysqlTime) * 100, 1)
    : null;
$speedup = ($redisTime !== null && $redisTime > 0)
    ? round($mysqlTime / $redisTime, 1)
    : null;

$totalOrders = count($calendarEvents);
$calendarDates = array_column($calendarEvents, 'start');
$latestRawDate = $calendarDates ? max($calendarDates) : null;
$earliestRawDate = $calendarDates ? min($calendarDates) : null;
$latestOrderDate = $latestRawDate
    ? (class_exists(Carbon::class) ? Carbon::parse($latestRawDate)->isoFormat('MMM D, YYYY') : (string) $latestRawDate)
    : 'No orders';
$oldestOrderDate = $earliestRawDate
    ? (class_exists(Carbon::class) ? Carbon::parse($earliestRawDate)->isoFormat('MMM D, YYYY') : (string) $earliestRawDate)
    : 'No orders';
$initialCalendarDate = $latestRawDate ?? date('Y-m-d');
$minCalendarMonth = $earliestRawDate ? substr($earliestRawDate, 0, 7) : date('Y-m');
$maxCalendarMonth = $latestRawDate ? substr($latestRawDate, 0, 7) : date('Y-m');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($appName); ?> - Calendar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
</head>
<body data-page="calendar-page">
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-dot">NW</span>
                <span><?= e($appName); ?></span>
            </div>

            <div class="menu-title">Menu</div>
            <a class="menu-item" href="index.php">
                <span class="menu-pill"></span>
                Reports
            </a>
            <a class="menu-item active" href="calendar.php">
                <span class="menu-pill"></span>
                Calendar
            </a>

        </aside>

        <main class="main">
            <section class="hero-card">
                <div class="title-block">
                    <span class="eyebrow">Order Calendar</span>
                    <h1>Display Orders in a Calendar UI</h1>
                    <p>All Northwind orders are plotted on their <strong>OrderDate</strong> with the customer name and total order value.</p>
                </div>
                <div class="hero-actions">
                    <a class="action primary" href="index.php">View Reports</a>
                    <a class="action" href="calendar.php">Refresh Calendar</a>
                </div>
            </section>

            <section class="report-card calendar-card">
                <div class="report-head">
                    <div>
                        <span class="report-kicker">FullCalendar</span>
                        <h2>Northwind Orders Calendar</h2>
                        <div class="calendar-note">Tip: use the month picker to jump straight to older Northwind order dates.</div>
                    </div>
                    <div class="report-highlight">
                        <span class="report-highlight-label">Data range</span>
                        <strong class="report-highlight-value"><?= e($oldestOrderDate); ?></strong>
                        <span class="report-highlight-amount">to <?= e($latestOrderDate); ?></span>
                        <small><?= e((string) $totalOrders); ?> total orders loaded.</small>
                    </div>
                </div>

                <div class="calendar-toolbar">
                    <label class="calendar-filter" for="calendarMonthPicker">
                        <span>Jump to month</span>
                        <input
                            type="month"
                            id="calendarMonthPicker"
                            min="<?= e($minCalendarMonth); ?>"
                            max="<?= e($maxCalendarMonth); ?>"
                            value="<?= e($maxCalendarMonth); ?>"
                        />
                    </label>
                    <div class="calendar-toolbar-actions">
                        <button class="action" id="oldestOrdersBtn" type="button">Oldest Orders</button>
                        <button class="action" id="latestOrdersBtn" type="button">Latest Orders</button>
                    </div>
                </div>

                <div class="cache-benchmark">
                    <h3 class="cache-benchmark-title">Performance Comparison: MySQL vs Redis</h3>
                    <table class="cache-table">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Load Time</th>
                                <th>Events</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="cache-row-mysql">
                                <td><strong>MySQL</strong> (direct query)</td>
                                <td><?= e((string) $mysqlTime); ?> ms</td>
                                <td><?= e((string) count($calendarEventsMySQL)); ?></td>
                                <td><span class="cache-badge badge-mysql">No Cache</span></td>
                            </tr>
                            <?php if ($redisAvailable): ?>
                            <tr class="cache-row-redis">
                                <td><strong>Redis</strong> (cache hit)</td>
                                <td><?= e((string) $redisTime); ?> ms</td>
                                <td><?= e((string) count($calendarEventsRedis)); ?></td>
                                <td><span class="cache-badge badge-redis">Cached</span></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if ($improvement !== null): ?>
                    <div class="cache-result">
                        <div class="cache-result-card">
                            <span class="cache-result-label">Speed Improvement</span>
                            <strong class="cache-result-value"><?= e((string) $improvement); ?>%</strong>
                            <span>faster with Redis</span>
                        </div>
                        <div class="cache-result-card">
                            <span class="cache-result-label">Speedup Factor</span>
                            <strong class="cache-result-value"><?= e((string) $speedup); ?>x</strong>
                            <span>compared to MySQL</span>
                        </div>
                        <div class="cache-result-card">
                            <span class="cache-result-label">Time Saved</span>
                            <strong class="cache-result-value"><?= e((string) round($mysqlTime - $redisTime, 2)); ?> ms</strong>
                            <span>per page load</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="cache-benchmark-actions">
                        <a class="action" href="?flush=1">Flush Cache &amp; Re-benchmark</a>
                    </div>
                </div>

                <div class="calendar-shell">
                    <div id="orderCalendar"></div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const calendarEvents = <?= json_encode($calendarEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const calendarRange = <?= json_encode([
            'initial' => $initialCalendarDate,
            'oldest' => $earliestRawDate,
            'latest' => $latestRawDate,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
