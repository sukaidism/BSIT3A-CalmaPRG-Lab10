<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Report
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 1.1 Total Revenue per year.
     */
    public function getTotalRevenue(): array
    {
        $sql = "
            SELECT
                YEAR(o.OrderDate) AS 'Year',
                SUM(od.UnitPrice * od.Quantity * (1 - od.Discount)) AS 'Total Revenue'
            FROM orders o JOIN order_details od
            ON o.OrderID = od.OrderID
            GROUP BY YEAR(o.OrderDate);
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * 2.1 Revenue by month.
     */
    public function getMonthlyRevenue(): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(o.OrderDate, '%Y-%m') AS 'Month',
                SUM(od.UnitPrice * od.Quantity * (1 - od.Discount)) AS 'Total Revenue'
            FROM orders o
            JOIN order_details od 
                ON o.OrderID = od.OrderID
            GROUP BY DATE_FORMAT(o.OrderDate, '%Y-%m')
            ORDER BY (o.OrderDate);

        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * 2.2 Revenue by product.
     */
    public function getRevenueByProduct(): array
    {
        $sql = "
            SELECT
                p.ProductName AS 'Product Name',
                SUM(od.UnitPrice * od.Quantity * (1 - od.Discount)) AS 'Total Revenue'
            FROM products p
            JOIN order_details od
                ON p.ProductID = od.ProductID
            GROUP BY p.ProductName
            ORDER BY (`Total Revenue`) DESC;
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * 3.1 Top customers by amount spent.
     */
    public function getTopCustomers(int $limit = 10): array
    {
        $sql = "
            SELECT
                c.CompanyName AS 'Company Name',
                SUM(od.UnitPrice * od.Quantity * (1 - od.Discount)) AS 'Total Spent'
            FROM customers c
            JOIN orders o
                ON c.CustomerID = o.CustomerID
            JOIN order_details od
                ON o.OrderID = od.OrderID
            GROUP BY c.CompanyName
            ORDER BY `Total Spent` DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Calendar events for all orders.
     */
    public function getOrderCalendarEvents(): array
    {
        $sql = "
            SELECT
                o.OrderID AS order_id,
                DATE(o.OrderDate) AS order_date,
                c.CompanyName AS customer_name,
                ROUND(SUM(od.UnitPrice * od.Quantity * (1 - od.Discount)), 2) AS total_order_value
            FROM orders o
            JOIN customers c
                ON o.CustomerID = c.CustomerID
            JOIN order_details od
                ON o.OrderID = od.OrderID
            WHERE o.OrderDate IS NOT NULL
            GROUP BY o.OrderID, DATE(o.OrderDate), c.CompanyName
            ORDER BY o.OrderDate
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    // Redis caching wrapper for calendar events
    public function getOrderCalendarEventsCached(?\Redis $redis, int $ttl = 3600)
    {
        $cacheKey = 'northwind:calendar_events';

        if ($redis !== null) {
            $cached = $redis->get($cacheKey);

            if ($cached !== false) {
                $decoded = json_decode($cached, true);

                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        $rows = $this->getOrderCalendarEvents();

        if ($redis !== null) {
            $redis->setex($cacheKey, $ttl, json_encode($rows, JSON_UNESCAPED_UNICODE));
        }

        return $rows;
    }
}
