<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log page access
log_access('VIEW_CAR_REQUESTS', 'car_requests.php');

$message = '';
$search = '';
$status_filter = 'all';

// Handle search and filter
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? 'all';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'] ?? '';
    $new_status = $_POST['status'] ?? '';
    
    if (!empty($request_id) && !empty($new_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE car_request SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $request_id]);
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Request status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            
            log_access('UPDATE_CAR_REQUEST_STATUS', 'car_requests.php', $request_id, null, "Updated status to: $new_status");
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error updating request status: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'] ?? '';
    
    if (!empty($request_id)) {
        try {
            // Get request details for logging
            $stmt = $pdo->prepare("SELECT name, email FROM car_request WHERE id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM car_request WHERE id = ?");
            $stmt->execute([$request_id]);
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Request deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            
            log_access('DELETE_CAR_REQUEST', 'car_requests.php', $request_id, null, "Deleted request from: " . ($request['name'] ?? 'Unknown'));
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error deleting request: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// Fetch car requests
try {
    $sql = "SELECT * FROM car_request WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR others LIKE ?)";
        $search_param = '%' . $search . '%';
        $params = array_fill(0, 4, $search_param);
    }
    
    if ($status_filter !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY time_created DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>Error fetching requests: ' . $e->getMessage() . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    $requests = [];
}

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.requests-wrapper {
    padding: 2.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.requests-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.requests-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.filters-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-row {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: block;
}

.filter-input, .filter-select {
    width: 100%;
    padding: 0.75rem;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.filter-input:focus, .filter-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1.5px solid #d1d5db;
}

.btn-secondary:hover {
    background: #f9fafb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.requests-table {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table {
    margin: 0;
}

.table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    font-size: 0.95rem;
}

.table tbody tr:hover {
    background: #f9fafb;
}

.request-name {
    font-weight: 600;
    color: #111827;
}

.request-email {
    color: #2563eb;
    text-decoration: none;
}

.request-email:hover {
    text-decoration: underline;
}

.request-details {
    max-width: 300px;
    line-height: 1.5;
    color: #6b7280;
}

.request-time {
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-contacted {
    background: #dbeafe;
    color: #1e40af;
}

.status-quoted {
    background: #d1fae5;
    color: #065f46;
}

.status-closed {
    background: #f3f4f6;
    color: #374151;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .requests-wrapper {
        padding: 1.5rem;
    }
    
    .requests-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 0.75rem 0.5rem;
    }
    
    .request-details {
        max-width: 200px;
    }
    
    .actions-cell {
        flex-direction: column;
    }
}
</style>

<div class="requests-wrapper">
    <div class="requests-header">
        <h1 class="requests-title">
            <i class="fas fa-car-side"></i>
            Car Requests
        </h1>
    </div>

    <?php echo $message; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo count($requests); ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo count(array_filter($requests, fn($r) => ($r['status'] ?? 'pending') === 'pending')); ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo count(array_filter($requests, fn($r) => ($r['status'] ?? 'pending') === 'contacted')); ?></div>
            <div class="stat-label">Contacted</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo count(array_filter($requests, fn($r) => ($r['status'] ?? 'pending') === 'quoted')); ?></div>
            <div class="stat-label">Quoted</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-row">
            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input type="text" name="search" class="filter-input" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="contacted" <?php echo $status_filter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="quoted" <?php echo $status_filter === 'quoted' ? 'selected' : ''; ?>>Quoted</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <a href="car_requests.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="requests-table">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No car requests found</h3>
                <p>When customers submit car requests, they will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Request Details</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>
                                    <div class="request-name"><?php echo htmlspecialchars($request['name']); ?></div>
                                </td>
                                <td>
                                    <div>
                                        <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>" class="request-email">
                                            <?php echo htmlspecialchars($request['email']); ?>
                                        </a>
                                    </div>
                                    <div class="request-time"><?php echo htmlspecialchars($request['phone'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <div class="request-details"><?php echo htmlspecialchars($request['others'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <div class="request-time"><?php echo date('M j, Y g:i A', strtotime($request['time_created'])); ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $request['status'] ?? 'pending'; ?>">
                                        <?php echo htmlspecialchars($request['status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                                <option value="pending" <?php echo ($request['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="contacted" <?php echo ($request['status'] ?? 'pending') === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                <option value="quoted" <?php echo ($request['status'] ?? 'pending') === 'quoted' ? 'selected' : ''; ?>>Quoted</option>
                                                <option value="closed" <?php echo ($request['status'] ?? 'pending') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                                Update
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this request?');">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" name="delete_request" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
