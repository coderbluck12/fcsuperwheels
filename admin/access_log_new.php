<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log access log page access
log_access('VIEW_ACCESS_LOG', 'access_log.php');

$message = '';

// Handle log deletion if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logs'])) {
    $days_to_keep = (int)$_POST['days_to_keep'];
    
    if ($days_to_keep > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM access_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days_to_keep]);
            $deleted_count = $stmt->rowCount();
            
            log_access('CLEAN_ACCESS_LOG', 'access_log.php', null, null, "Deleted $deleted_count old logs");
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Success! Deleted ' . $deleted_count . ' old log entries.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } catch (PDOException $e) {
            log_access('CLEAN_ACCESS_LOG_FAILED', 'access_log.php', null, null, "Failed to clean logs: " . $e->getMessage());
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// Get filter parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$user_filter = isset($_GET['user']) ? trim($_GET['user']) : '';
$action_filter = isset($_GET['action']) ? trim($_GET['action']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Get logs and stats
$logs = get_access_logs($limit, $user_filter, $action_filter, $date_from, $date_to);
$stats = get_access_log_stats();

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.log-wrapper {
    padding: 2.5rem;
    max-width: 100%;
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid #2563eb;
    flex-wrap: wrap;
    gap: 1rem;
}

.log-title h1 {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.log-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s;
}

.stat-card:hover {
    border-color: #2563eb;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-card.primary .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-card.success .stat-icon { background: #d1fae5; color: #059669; }
.stat-card.info .stat-icon { background: #cffafe; color: #0891b2; }
.stat-card.warning .stat-icon { background: #fef3c7; color: #d97706; }

.stat-label {
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    margin: 0;
}

.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 0.5rem 0 0 0;
}

.stat-description {
    font-size: 0.875rem;
    color: #9ca3af;
    margin: 0.5rem 0 0 0;
}

.filter-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.filter-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.5rem 0;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: block;
}

.form-control, .form-select {
    border: 1.5px solid #d1d5db;
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.95rem;
    width: 100%;
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1.5px solid #d1d5db;
}

.btn-warning {
    background: #d97706;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.table-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.table-header {
    padding: 1.5rem;
    background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;
}

.table-header h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

.data-table thead th {
    background: #fafbfc;
    padding: 1rem 1.25rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
}

.data-table tbody tr {
    transition: background 0.15s;
}

.data-table tbody tr:hover {
    background: #fafbfc;
}

.data-table tbody tr:not(:last-child) {
    border-bottom: 1px solid #f3f4f6;
}

.data-table tbody td {
    padding: 1.25rem;
    color: #374151;
    font-size: 0.9375rem;
}

.user-display {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #2563eb;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.user-name {
    font-weight: 600;
    color: #111827;
}

.user-handle {
    font-size: 0.8125rem;
    color: #6b7280;
}

.action-badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.action-badge.success { background: #d1fae5; color: #059669; }
.action-badge.danger { background: #fee2e2; color: #dc2626; }
.action-badge.warning { background: #fef3c7; color: #d97706; }
.action-badge.info { background: #cffafe; color: #0891b2; }
.action-badge.primary { background: #dbeafe; color: #2563eb; }
.action-badge.secondary { background: #f3f4f6; color: #6b7280; }

.timestamp-date {
    font-weight: 600;
    color: #111827;
}

.timestamp-time {
    font-size: 0.8125rem;
    color: #6b7280;
    font-family: monospace;
}

.tech-info {
    font-family: monospace;
    font-size: 0.8125rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.empty-description {
    color: #6b7280;
}

@media (max-width: 1024px) {
    .log-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .log-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .log-title h1 {
        font-size: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
    }
    
    .mobile-hide {
        display: none;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<div class="log-wrapper">
    <div class="log-header">
        <div class="log-title">
            <h1>Access Log</h1>
            <p class="log-subtitle">Monitor system activity and track user interactions</p>
        </div>
        <button type="button" class="btn btn-warning" onclick="openCleanupModal()">
            <i class="fas fa-broom"></i> Clean Logs
        </button>
    </div>

    <?php echo $message; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <p class="stat-label">Total Logs</p>
                </div>
            </div>
            <h2 class="stat-value"><?php echo number_format($stats['total_logs']); ?></h2>
            <p class="stat-description">All recorded events</p>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <p class="stat-label">Today</p>
                </div>
            </div>
            <h2 class="stat-value"><?php echo number_format($stats['today_logs']); ?></h2>
            <p class="stat-description">Last 24 hours</p>
        </div>

        <div class="stat-card info">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="stat-label">Unique Users</p>
                </div>
            </div>
            <h2 class="stat-value"><?php echo number_format($stats['unique_users']); ?></h2>
            <p class="stat-description">Active accounts</p>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <p class="stat-label">Top Action</p>
                </div>
            </div>
            <h2 class="stat-value" style="font-size: 1.25rem;">
                <?php echo strtoupper($stats['top_actions'][0]['action'] ?? 'N/A'); ?>
            </h2>
            <p class="stat-description">Most common</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <h3 class="filter-title"><i class="fas fa-filter"></i> Filter Options</h3>
        <form method="GET">
            <div class="filter-grid">
                <div>
                    <label class="form-label">User</label>
                    <input type="text" name="user" class="form-control" value="<?php echo htmlspecialchars($user_filter); ?>" placeholder="Search username">
                </div>
                <div>
                    <label class="form-label">Action</label>
                    <input type="text" name="action" class="form-control" value="<?php echo htmlspecialchars($action_filter); ?>" placeholder="e.g., LOGIN">
                </div>
                <div>
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div>
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div>
                    <label class="form-label">Limit</label>
                    <select name="limit" class="form-select">
                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 results</option>
                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 results</option>
                        <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200 results</option>
                        <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500 results</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="access_log.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Activity Log</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th class="mobile-hide">Page</th>
                        <th class="mobile-hide">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                    <h4 class="empty-title">No logs found</h4>
                                    <p class="empty-description">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="timestamp-date"><?php echo date('M j, Y', strtotime($log['created_at'])); ?></div>
                                    <div class="timestamp-time"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="user-display">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($log['firstname'] ?: $log['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="user-name">
                                                <?php echo htmlspecialchars(trim(($log['firstname'] ?? '') . ' ' . ($log['lastname'] ?? '')) ?: $log['username'] ?? 'Unknown'); ?>
                                            </div>
                                            <?php if ($log['username'] && $log['firstname']): ?>
                                                <div class="user-handle">@<?php echo htmlspecialchars($log['username']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="action-badge <?php echo getActionColor($log['action']); ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="mobile-hide"><?php echo htmlspecialchars($log['page'] ?? 'N/A'); ?></td>
                                <td class="mobile-hide">
                                    <span class="tech-info"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-broom"></i> Clean Old Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Delete logs older than</label>
                        <select name="days_to_keep" class="form-select">
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action is permanent and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_logs" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCleanupModal() {
    const modal = new bootstrap.Modal(document.getElementById('cleanupModal'));
    modal.show();
}

function getActionColor(action) {
    const colors = {
        'LOGIN': 'success',
        'LOGOUT': 'secondary',
        'VIEW': 'info',
        'ADD': 'primary',
        'EDIT': 'warning',
        'DELETE': 'danger',
        'CLEAN': 'warning'
    };
    
    for (let key in colors) {
        if (action.toUpperCase().includes(key)) {
            return colors[key];
        }
    }
    return 'secondary';
}
</script>

<?php
function getActionColor($action) {
    $colors = [
        'LOGIN' => 'success',
        'LOGOUT' => 'secondary',
        'VIEW' => 'info',
        'ADD' => 'primary',
        'EDIT' => 'warning',
        'DELETE' => 'danger',
        'CLEAN' => 'warning'
    ];
    
    foreach ($colors as $key => $color) {
        if (strpos(strtoupper($action), $key) !== false) {
            return $color;
        }
    }
    
    return 'secondary';
}

include 'inc/footer.php';
?>
