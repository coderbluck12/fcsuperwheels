<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');
include_once('inc/pagination.php');

// Log reports page access
log_access('VIEW_REPORTS', 'reports.php');

$start_date  = isset($_GET['start_date'])  ? $_GET['start_date']  : date('Y-m-01');
$end_date    = isset($_GET['end_date'])    ? $_GET['end_date']    : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'general';

// Allowed types guard
if (!in_array($report_type, ['general','sales','expenses','inventory'])) {
    $report_type = 'general';
}

// --- Pagination for Sales (used in general + sales tabs) ---
try {
    $count_sql  = "SELECT COUNT(*) FROM vehicles WHERE status = 'Sold'";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute();
    $total_sales_count = $count_stmt->fetchColumn();

    $items_per_page = 10;
    $current_page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pagination     = new Pagination($total_sales_count, $items_per_page, $current_page);
    $offset         = $pagination->getOffset();

    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE status = 'Sold' ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit',  (int)$items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset,         PDO::PARAM_INT);
    $stmt->execute();
    $recent_sales = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_sales      = [];
    $total_sales_count = 0;
    error_log("Reports Error: " . $e->getMessage());
}

// --- Expenses data (used in expenses tab) ---
try {
    $stmt_exp_tab = $pdo->prepare("
        SELECT v.make, v.model, v.vin, e.description, e.amount, e.expense_date, e.id
        FROM expenses e
        LEFT JOIN vehicles v ON e.vehicle_id = v.id
        WHERE e.expense_date BETWEEN ? AND ?
        ORDER BY e.expense_date DESC
    ");
    $stmt_exp_tab->execute([$start_date, $end_date]);
    $expenses_tab = $stmt_exp_tab->fetchAll();
    $expenses_tab_total = array_sum(array_column($expenses_tab, 'amount'));
} catch (PDOException $e) {
    $expenses_tab       = [];
    $expenses_tab_total = 0;
}

// --- Inventory data (used in inventory tab) ---
try {
    $stmt_inv = $pdo->query("SELECT make, model, year, vin, status, listing_price FROM vehicles ORDER BY id DESC");
    $inventory_tab = $stmt_inv->fetchAll();
} catch (PDOException $e) {
    $inventory_tab = [];
}

// Handle CSV Export
if (isset($_GET['export_csv'])) {
    log_access('EXPORT_CSV_REPORTS', 'reports.php', null, null, "Exported $report_type report from $start_date to $end_date");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . $start_date . '_to_' . $end_date . '.csv"');
    $output = fopen('php://output', 'w');

    if ($report_type === 'general') {
        // Summary + sales + expenses
        $stmt_s = $pdo->prepare("SELECT make, model, year, vin, purchase_price, sale_price, sale_date, updated_at FROM vehicles WHERE status = 'Sold' AND (sale_date BETWEEN ? AND ? OR (sale_date IS NULL AND updated_at BETWEEN ? AND ?)) ORDER BY COALESCE(sale_date, updated_at) ASC");
        $stmt_s->execute([$start_date, $end_date, $start_date, $end_date]);
        $csv_sales = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

        $stmt_e = $pdo->prepare("SELECT v.make, v.model, v.vin, e.description, e.amount, e.expense_date FROM expenses e LEFT JOIN vehicles v ON e.vehicle_id = v.id WHERE e.expense_date BETWEEN ? AND ? ORDER BY e.expense_date ASC");
        $stmt_e->execute([$start_date, $end_date]);
        $csv_exp = $stmt_e->fetchAll(PDO::FETCH_ASSOC);

        $total_s  = $pdo->prepare("SELECT SUM(sale_price) FROM vehicles WHERE status = 'Sold'"); $total_s->execute(); $ts = $total_s->fetchColumn() ?: 0;
        $total_ex = $pdo->prepare("SELECT SUM(amount) FROM expenses WHERE expense_date BETWEEN ? AND ?"); $total_ex->execute([$start_date, $end_date]); $te = $total_ex->fetchColumn() ?: 0;
        $gross_p  = $pdo->prepare("SELECT SUM(sale_price - purchase_price) FROM vehicles WHERE status = 'Sold'"); $gross_p->execute(); $gp = $gross_p->fetchColumn() ?: 0;

        fputcsv($output, ['GENERAL FINANCIAL REPORT']);
        fputcsv($output, ['Period:', "$start_date to $end_date"]);
        fputcsv($output, []);
        fputcsv($output, ['Metric','Amount']);
        fputcsv($output, ['Total Sales',    number_format($ts, 2, '.', '')]);
        fputcsv($output, ['Total Expenses', number_format($te, 2, '.', '')]);
        fputcsv($output, ['Gross Profit',   number_format($gp, 2, '.', '')]);
        fputcsv($output, ['Net Profit',     number_format($gp - $te, 2, '.', '')]);
        fputcsv($output, []);
        fputcsv($output, ['SALES DETAILS']);
        fputcsv($output, ['Sale Date','Vehicle','VIN','Purchase Price','Sale Price','Gross Profit']);
        foreach ($csv_sales as $sale) {
            $g = $sale['sale_price'] - $sale['purchase_price'];
            fputcsv($output, [$sale['sale_date'] ?: $sale['updated_at'], $sale['year'].' '.$sale['make'].' '.$sale['model'], $sale['vin'], $sale['purchase_price'], $sale['sale_price'], $g]);
        }
        fputcsv($output, []);
        fputcsv($output, ['EXPENSES DETAILS']);
        fputcsv($output, ['Expense Date','Vehicle','VIN','Description','Amount']);
        foreach ($csv_exp as $exp) {
            $vn = ($exp['make'] && $exp['model']) ? ($exp['make'].' '.$exp['model']) : 'General/Unknown';
            fputcsv($output, [$exp['expense_date'], $vn, $exp['vin'] ?? 'N/A', $exp['description'], $exp['amount']]);
        }

    } elseif ($report_type === 'sales') {
        $stmt_s = $pdo->prepare("SELECT make, model, year, vin, purchase_price, sale_price, sale_date, updated_at FROM vehicles WHERE status = 'Sold' AND (sale_date BETWEEN ? AND ? OR (sale_date IS NULL AND updated_at BETWEEN ? AND ?)) ORDER BY COALESCE(sale_date, updated_at) ASC");
        $stmt_s->execute([$start_date, $end_date, $start_date, $end_date]);
        $csv_sales = $stmt_s->fetchAll(PDO::FETCH_ASSOC);
        fputcsv($output, ['SALES REPORT – ' . $start_date . ' to ' . $end_date]);
        fputcsv($output, []);
        fputcsv($output, ['Sale Date','Vehicle','VIN','Purchase Price','Sale Price','Gross Profit']);
        foreach ($csv_sales as $sale) {
            $g = $sale['sale_price'] - $sale['purchase_price'];
            fputcsv($output, [$sale['sale_date'] ?: $sale['updated_at'], $sale['year'].' '.$sale['make'].' '.$sale['model'], $sale['vin'], $sale['purchase_price'], $sale['sale_price'], $g]);
        }

    } elseif ($report_type === 'expenses') {
        $stmt_e = $pdo->prepare("SELECT v.make, v.model, v.vin, e.description, e.amount, e.expense_date FROM expenses e LEFT JOIN vehicles v ON e.vehicle_id = v.id WHERE e.expense_date BETWEEN ? AND ? ORDER BY e.expense_date ASC");
        $stmt_e->execute([$start_date, $end_date]);
        $csv_exp = $stmt_e->fetchAll(PDO::FETCH_ASSOC);
        fputcsv($output, ['EXPENSES REPORT – ' . $start_date . ' to ' . $end_date]);
        fputcsv($output, []);
        fputcsv($output, ['Expense Date','Vehicle','VIN','Description','Amount']);
        foreach ($csv_exp as $exp) {
            $vn = ($exp['make'] && $exp['model']) ? ($exp['make'].' '.$exp['model']) : 'General/Unknown';
            fputcsv($output, [$exp['expense_date'], $vn, $exp['vin'] ?? 'N/A', $exp['description'], $exp['amount']]);
        }

    } elseif ($report_type === 'inventory') {
        $stmt_i = $pdo->query("SELECT make, model, year, vin, status, listing_price FROM vehicles ORDER BY id DESC");
        $csv_inv = $stmt_i->fetchAll(PDO::FETCH_ASSOC);
        fputcsv($output, ['INVENTORY REPORT']);
        fputcsv($output, []);
        fputcsv($output, ['Make','Model','Year','VIN','Status','Listing Price']);
        foreach ($csv_inv as $v) {
            fputcsv($output, [$v['make'], $v['model'], $v['year'], $v['vin'], $v['status'], $v['listing_price']]);
        }
    }

    fclose($output);
    exit;
}

// Summary metrics (general tab)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'Available'");
$available_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(sale_price) as total_sales FROM vehicles WHERE status = 'Sold'");
$stmt->execute();
$total_sales = $stmt->fetch()['total_sales'] ?? 0;

$stmt = $pdo->prepare("SELECT SUM(amount) as total_expenses FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$total_expenses = $stmt->fetch()['total_expenses'] ?? 0;

$stmt = $pdo->prepare("SELECT SUM(sale_price - purchase_price) as gross_profit FROM vehicles WHERE status = 'Sold'");
$stmt->execute();
$gross_profit = $stmt->fetch()['gross_profit'] ?? 0;
$net_profit   = $gross_profit - $total_expenses;

$profit_margin = $total_sales > 0 ? ($net_profit / $total_sales) * 100 : 0;

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.reports-wrapper {
    padding: 2.5rem;
    max-width: 100%;
}

.reports-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid #2563eb;
}

.reports-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.reports-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

.date-filter-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.date-filter-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.date-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: block;
}

.date-input {
    border: 1.5px solid #d1d5db;
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.95rem;
    width: 100%;
    transition: all 0.2s;
}

.date-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.filter-btn-group {
    display: flex;
    gap: 0.5rem;
}

.filter-btn {
    background: #2563eb;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
}

.export-btn {
    background: #10b981;
}

.export-btn:hover {
    background: #059669;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.75rem;
    position: relative;
    overflow: hidden;
    transition: all 0.2s;
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--accent-color);
}

.metric-card:hover {
    border-color: var(--accent-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.metric-card.primary { --accent-color: #2563eb; }
.metric-card.success { --accent-color: #059669; }
.metric-card.danger { --accent-color: #dc2626; }
.metric-card.info { --accent-color: #0891b2; }

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}

.metric-icon {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.metric-card.primary .metric-icon {
    background: #dbeafe;
    color: #2563eb;
}

.metric-card.success .metric-icon {
    background: #d1fae5;
    color: #059669;
}

.metric-card.danger .metric-icon {
    background: #fee2e2;
    color: #dc2626;
}

.metric-card.info .metric-icon {
    background: #cffafe;
    color: #0891b2;
}

.metric-trend {
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 0.25rem 0.625rem;
    border-radius: 4px;
}

.metric-trend.positive {
    background: #d1fae5;
    color: #059669;
}

.metric-trend.negative {
    background: #fee2e2;
    color: #dc2626;
}

.metric-label {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
}

.metric-value {
    font-size: 2.25rem;
    font-weight: 800;
    color: #111827;
    line-height: 1;
    margin: 0 0 0.5rem 0;
}

.metric-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.period-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.period-indicator strong {
    color: #111827;
}

.sales-table-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.sales-table-header {
    padding: 1.5rem;
    background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;
}

.sales-table-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sales-table {
    width: 100%;
    border-collapse: collapse;
}

.sales-table thead th {
    background: #fafbfc;
    padding: 1rem 1.5rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
}

.sales-table tbody tr {
    transition: background 0.15s;
}

.sales-table tbody tr:hover {
    background: #fafbfc;
}

.sales-table tbody tr:not(:last-child) {
    border-bottom: 1px solid #f3f4f6;
}

.sales-table tbody td {
    padding: 1.25rem 1.5rem;
    color: #374151;
    font-size: 0.9375rem;
}

.vehicle-name {
    font-weight: 600;
    color: #111827;
}

.sale-amount {
    font-weight: 700;
    color: #059669;
}

.profit-amount {
    font-weight: 700;
    white-space: nowrap;
}

.profit-positive {
    color: #059669;
}

.profit-negative {
    color: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

@media (max-width: 1024px) {
    .reports-wrapper {
        padding: 1.5rem;
    }
    
    .date-filter-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .date-filter-grid > div:last-child {
        grid-column: 1 / -1;
    }
    
    .filter-btn-group {
        flex-direction: column;
    }
    
    .filter-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .reports-title {
        font-size: 1.5rem;
    }
    
    .date-filter-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .metric-value {
        font-size: 1.75rem;
    }
    
    .mobile-hide {
        display: none;
    }
}

@media (max-width: 576px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="reports-wrapper">
    <div class="reports-header">
        <h1 class="reports-title">Financial Reports</h1>
        <p class="reports-subtitle">Track sales, expenses, and profitability metrics</p>
    </div>

    <div class="date-filter-card">
        <h3 class="date-filter-title">
            <i class="bi bi-calendar-range"></i>
            Report Period
        </h3>
        <form method="GET">
            <div class="date-filter-grid">
                <div>
                    <label class="date-label">Start Date</label>
                    <input type="date" name="start_date" class="date-input" value="<?php echo $start_date; ?>" required>
                </div>
                <div>
                    <label class="date-label">End Date</label>
                    <input type="date" name="end_date" class="date-input" value="<?php echo $end_date; ?>" required>
                </div>
                <div class="filter-btn-group">
                    <button type="submit" class="filter-btn">
                        <i class="bi bi-funnel"></i>
                        Apply Filter
                    </button>
                    <button type="submit" name="export_csv" value="1" class="filter-btn export-btn">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        Export CSV
                    </button>
                </div>
            </div>
        </form>
    </div><!-- end date-filter-card -->

    <!-- Report Type Tabs -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <?php
        $tabs = ['general' => 'General', 'sales' => 'Sales', 'expenses' => 'Expenses', 'inventory' => 'Inventory'];
        $base_tab_url = 'reports.php?start_date=' . urlencode($start_date) . '&end_date=' . urlencode($end_date);
        foreach ($tabs as $key => $label):
            $is_active = ($report_type === $key);
        ?>
        <a href="<?php echo $base_tab_url . '&report_type=' . $key; ?>"
           style="padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 700; font-size: 0.9rem; text-decoration: none; border: 2px solid #2563eb;
                  background: <?php echo $is_active ? '#2563eb' : 'white'; ?>; color: <?php echo $is_active ? 'white' : '#2563eb'; ?>; transition: all 0.2s;">
            <?php echo $label; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="period-indicator">
        <i class="bi bi-info-circle"></i>
        Showing data from <strong><?php echo date('M j, Y', strtotime($start_date)); ?></strong>
        to <strong><?php echo date('M j, Y', strtotime($end_date)); ?></strong>
    </div>

    <?php if ($report_type === 'general'): ?>
    <div class="metrics-grid">
        <div class="metric-card primary">
            <div class="metric-header"><div class="metric-icon"><i class="bi bi-car-front"></i></div></div>
            <p class="metric-label">Available Inventory</p>
            <h2 class="metric-value"><?php echo number_format($available_count); ?></h2>
            <p class="metric-description">Cars ready for sale</p>
        </div>
        <div class="metric-card success">
            <div class="metric-header"><div class="metric-icon"><i class="bi bi-currency-dollar"></i></div></div>
            <p class="metric-label">Total Sales</p>
            <h2 class="metric-value">₦<?php echo number_format($total_sales, 0); ?></h2>
            <p class="metric-description">Revenue in this period</p>
        </div>
        <div class="metric-card danger">
            <div class="metric-header"><div class="metric-icon"><i class="bi bi-receipt"></i></div></div>
            <p class="metric-label">Total Expenses</p>
            <h2 class="metric-value">₦<?php echo number_format($total_expenses, 0); ?></h2>
            <p class="metric-description">Costs incurred</p>
        </div>
        <div class="metric-card info">
            <div class="metric-header">
                <div class="metric-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <?php if ($profit_margin > 0): ?>
                    <span class="metric-trend positive"><i class="bi bi-arrow-up"></i> <?php echo number_format($profit_margin, 1); ?>%</span>
                <?php else: ?>
                    <span class="metric-trend negative"><i class="bi bi-arrow-down"></i> <?php echo number_format(abs($profit_margin), 1); ?>%</span>
                <?php endif; ?>
            </div>
            <p class="metric-label">Net Profit</p>
            <h2 class="metric-value" style="color: <?php echo $net_profit >= 0 ? '#059669' : '#dc2626'; ?>">
                ₦<?php echo number_format($net_profit, 0); ?>
            </h2>
            <p class="metric-description">After expenses</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- General / Sales table -->
    <?php if (in_array($report_type, ['general','sales'])): ?>
    <div class="sales-table-card">
        <div class="sales-table-header">
            <h3 class="sales-table-title"><i class="bi bi-list-check"></i> Sales Records</h3>
        </div>
        <?php if (count($recent_sales) > 0): ?>
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="sales-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Sale Date</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sales as $sale): ?>
                        <?php
                        $actual_sale_price = !empty($sale['sale_price']) ? $sale['sale_price'] : ($sale['listing_price'] ?? 0);
                        $profit = $actual_sale_price - $sale['purchase_price'];
                        ?>
                        <tr>
                            <td><div class="vehicle-name"><?php echo htmlspecialchars($sale['make'] . ' ' . $sale['model'] . ' (' . $sale['year'] . ')'); ?></div></td>
                            <td><?php echo date('M j, Y', strtotime($sale['sale_date'] ?: $sale['updated_at'])); ?></td>
                            <td>₦<?php echo number_format($sale['purchase_price'], 2); ?></td>
                            <td class="sale-amount">₦<?php echo number_format($actual_sale_price, 2); ?></td>
                            <td>
                                <span class="profit-amount <?php echo $profit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                    <?php echo $profit >= 0 ? '+' : '-'; ?>₦<?php echo number_format(abs($profit), 2); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if (isset($pagination) && $pagination->getTotalPages() > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted" style="font-size: 0.875rem;">
                        <?php echo $pagination->getPaginationInfo(); ?>
                    </div>
                    <div>
                        <?php
                        $base_url = "reports.php?start_date=" . urlencode($start_date) . "&end_date=" . urlencode($end_date) . "&report_type=" . urlencode($report_type);
                        echo $pagination->generatePaginationLinks($base_url);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h4 style="color: #111827; font-weight: 600; margin-bottom: 0.5rem;">No sales in this period</h4>
                <p>Try selecting a different date range to see results.</p>
            </div>
        <?php endif; ?>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; text-align: right;">
            <a href="reports.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&report_type=<?php echo urlencode($report_type); ?>&export_csv=1"
               class="filter-btn export-btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 700; text-decoration: none; background: #10b981; color: white;">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Expenses table -->
    <?php if ($report_type === 'expenses'): ?>
    <div class="sales-table-card">
        <div class="sales-table-header">
            <h3 class="sales-table-title"><i class="bi bi-receipt"></i> Expense Records</h3>
        </div>
        <?php if (count($expenses_tab) > 0): ?>
            <div style="overflow-x: auto;">
            <table class="sales-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>VIN</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses_tab as $exp): ?>
                        <?php $vn = ($exp['make'] && $exp['model']) ? htmlspecialchars($exp['make'].' '.$exp['model']) : 'General / Unknown'; ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($exp['expense_date'])); ?></td>
                            <td class="vehicle-name"><?php echo $vn; ?></td>
                            <td><?php echo htmlspecialchars($exp['vin'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($exp['description']); ?></td>
                            <td class="sale-amount">₦<?php echo number_format($exp['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="font-weight: 800; padding: 1rem 1.5rem; background: #f9fafb; border-top: 2px solid #e5e7eb;">Total</td>
                        <td style="font-weight: 800; padding: 1rem 1.5rem; background: #f9fafb; border-top: 2px solid #e5e7eb;">₦<?php echo number_format($expenses_tab_total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-receipt"></i></div>
                <h4 style="color: #111827; font-weight: 600; margin-bottom: 0.5rem;">No expenses in this period</h4>
                <p>Try selecting a different date range.</p>
            </div>
        <?php endif; ?>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; text-align: right;">
            <a href="reports.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&report_type=expenses&export_csv=1"
               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 700; text-decoration: none; background: #10b981; color: white;">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Expenses CSV
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Inventory table -->
    <?php if ($report_type === 'inventory'): ?>
    <div class="sales-table-card">
        <div class="sales-table-header">
            <h3 class="sales-table-title"><i class="bi bi-car-front"></i> Full Inventory</h3>
        </div>
        <?php if (count($inventory_tab) > 0): ?>
            <div style="overflow-x: auto;">
            <table class="sales-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>VIN / Chassis</th>
                        <th>Status</th>
                        <th>Listing Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory_tab as $v): ?>
                    <tr>
                        <td class="vehicle-name"><?php echo htmlspecialchars($v['make']); ?></td>
                        <td><?php echo htmlspecialchars($v['model']); ?></td>
                        <td><?php echo htmlspecialchars($v['year']); ?></td>
                        <td><?php echo htmlspecialchars($v['vin'] ?? 'N/A'); ?></td>
                        <td><span class="<?php echo $v['status']==='Available' ? 'profit-positive' : 'profit-negative'; ?>" style="font-weight:700;"><?php echo htmlspecialchars($v['status']); ?></span></td>
                        <td class="sale-amount">₦<?php echo number_format($v['listing_price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-car-front"></i></div>
                <h4 style="color: #111827; font-weight: 600; margin-bottom: 0.5rem;">No vehicles in inventory</h4>
            </div>
        <?php endif; ?>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; text-align: right;">
            <a href="reports.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&report_type=inventory&export_csv=1"
               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 700; text-decoration: none; background: #10b981; color: white;">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Inventory CSV
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'inc/footer.php'; ?>