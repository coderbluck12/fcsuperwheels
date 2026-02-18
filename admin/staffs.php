<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log page access
log_access('VIEW_MANAGE_STAFF', 'staffs.php');

$message = '';
$errors = [];

// Handle staff deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_staff'])) {
    $staff_id = (int)$_POST['staff_id'];
    
    if ($staff_id > 0) {
        try {
            // Don't allow deletion of the current user
            if ($staff_id === $admin_id) {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>You cannot delete your own account!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                // Get staff info for logging
                $info_stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
                $info_stmt->execute([$staff_id]);
                $staff_info = $info_stmt->fetch();
                
                $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
                $stmt->execute([$staff_id]);
                
                log_access('DELETE_STAFF', 'staffs.php', null, null, "Deleted staff: " . ($staff_info['username'] ?? 'Unknown'));
                
                $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Staff member deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
        } catch (PDOException $e) {
            log_access('DELETE_STAFF_FAILED', 'staffs.php', null, null, "Failed to delete staff: " . $e->getMessage());
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error deleting staff member: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// Handle staff status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $staff_id = (int)$_POST['staff_id'];
    $new_status = (int)$_POST['new_status'];
    
    if ($staff_id > 0) {
        try {
            // Don't allow deactivating the current user
            if ($staff_id === $admin_id && $new_status === 0) {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>You cannot deactivate your own account!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                // Get staff info for logging
                $info_stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
                $info_stmt->execute([$staff_id]);
                $staff_info = $info_stmt->fetch();
                
                $stmt = $pdo->prepare("UPDATE user SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $staff_id]);
                
                $action = $new_status ? 'ACTIVATE_STAFF' : 'DEACTIVATE_STAFF';
                log_access($action, 'staffs.php', null, null, ($new_status ? 'Activated' : 'Deactivated') . " staff: " . ($staff_info['username'] ?? 'Unknown'));
                
                $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Staff member status updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
        } catch (PDOException $e) {
            log_access('TOGGLE_STATUS_FAILED', 'staffs.php', null, null, "Failed to toggle status: " . $e->getMessage());
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error updating staff status: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
}

// Handle staff edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_staff'])) {
    $staff_id = (int)$_POST['staff_id'];
    $username = trim($_POST['username'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $level = $_POST['level'] ?? 'user';
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (empty($firstname)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastname)) {
        $errors[] = 'Last name is required';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!in_array($level, ['admin', 'manager', 'user'])) {
        $errors[] = 'Invalid user level';
    }
    
    // Check if username already exists (excluding current user)
    if (empty($errors)) {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
            $check_stmt->execute([$username, $staff_id]);
            if ($check_stmt->fetch()) {
                $errors[] = 'Username already exists';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error checking username';
        }
    }
    
    // Check if email already exists (excluding current user)
    if (empty($errors) && !empty($email)) {
        try {
            $check_email_stmt = $pdo->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
            $check_email_stmt->execute([$email, $staff_id]);
            if ($check_email_stmt->fetch()) {
                $errors[] = 'Email already exists';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error checking email';
        }
    }
    
    // If no errors, update the staff member
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE user SET username = ?, firstname = ?, lastname = ?, email = ?, level = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([$username, $firstname, $lastname, $email, $level, $status, $staff_id]);
            
            log_access('EDIT_STAFF', 'staffs.php', null, null, "Updated staff: $username");
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Staff member updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            
        } catch (PDOException $e) {
            log_access('EDIT_STAFF_FAILED', 'staffs.php', null, null, "Failed to update staff: " . $e->getMessage());
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error updating staff member: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    } else {
        $error_list = '<ul class="mb-0">';
        foreach ($errors as $error) {
            $error_list .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $error_list .= '</ul>';
        
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:
            ' . $error_list . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$level_filter = isset($_GET['level']) ? $_GET['level'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$query = "SELECT * FROM user WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($level_filter)) {
    $query .= " AND level = ?";
    $params[] = $level_filter;
}

if ($status_filter !== '') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $staff_members = $stmt->fetchAll();
} catch (PDOException $e) {
    $staff_members = [];
    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>Error fetching staff members: ' . $e->getMessage() . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.staff-wrapper {
    padding: 2.5rem;
    max-width: 100%;
}

.staff-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.staff-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
}

.staff-actions {
    display: flex;
    gap: 1rem;
}

.filter-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
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
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.95rem;
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
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
    background: #059669;
    color: white;
}

.btn-warning {
    background: #d97706;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.table-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
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

.user-info {
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

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #111827;
}

.user-email {
    font-size: 0.8125rem;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-badge.active {
    background: #d1fae5;
    color: #059669;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.level-badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.level-badge.admin {
    background: #dbeafe;
    color: #2563eb;
}

.level-badge.manager {
    background: #fef3c7;
    color: #d97706;
}

.level-badge.user {
    background: #f3f4f6;
    color: #6b7280;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
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
    .staff-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .staff-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="staff-wrapper">
    <div class="staff-header">
        <h1 class="staff-title">Manage Staff</h1>
        <div class="staff-actions">
            <a href="add_staff.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i>
                Add Staff
            </a>
        </div>
    </div>

    <?php echo $message; ?>

    <!-- Filters -->
    <div class="filter-card">
        <h3 class="filter-title"><i class="fas fa-filter"></i> Filter Staff</h3>
        <form method="GET">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name, username, or email">
                </div>
                <div>
                    <label class="form-label">User Level</label>
                    <select name="level" class="form-select">
                        <option value="">All Levels</option>
                        <option value="admin" <?php echo $level_filter === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="manager" <?php echo $level_filter === 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="user" <?php echo $level_filter === 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Apply Filters
                </button>
                <a href="staffs.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Staff Table -->
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-users"></i> Staff Members</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff_members)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-users"></i></div>
                                    <h4 class="empty-title">No staff members found</h4>
                                    <p class="empty-description">Try adjusting your filters or add a new staff member</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($staff['firstname'] ?: $staff['username'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name">
                                                <?php echo htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']); ?>
                                            </div>
                                            <?php if ($staff['email']): ?>
                                                <div class="user-email"><?php echo htmlspecialchars($staff['email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($staff['username']); ?></strong>
                                    <?php if ($staff['id'] === $admin_id): ?>
                                        <div class="user-email">(You)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="level-badge <?php echo $staff['level']; ?>">
                                        <?php echo ucfirst($staff['level']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $staff['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $staff['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($staff['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-secondary" 
                                                onclick="editStaff(<?php echo $staff['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </button>
                                        
                                        <?php if ($staff['id'] !== $admin_id): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $staff['status'] ? 0 : 1; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm <?php echo $staff['status'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <i class="fas fa-<?php echo $staff['status'] ? 'ban' : 'check'; ?>"></i>
                                                    <?php echo $staff['status'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.')">
                                                <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                                <button type="submit" name="delete_staff" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editStaffForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="staff_id" id="edit_staff_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">User Level</label>
                            <select name="level" id="edit_level" class="form-select" required>
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="status" id="edit_status" class="form-check-input">
                                <label for="edit_status" class="form-check-label">Active Account</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_staff" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStaff(staffId) {
    // Fetch staff data (in a real app, you'd make an AJAX call)
    // For now, we'll use the data from the table row
    const row = document.querySelector(`tr:has(button[onclick="editStaff(${staffId})"])`);
    const cells = row.getElementsByTagName('td');
    
    // Extract data from the table
    const username = cells[1].textContent.trim().split('\n')[0];
    const name = cells[0].querySelector('.user-name').textContent.trim();
    const email = cells[0].querySelector('.user-email')?.textContent.trim() || '';
    const level = cells[2].textContent.trim().toLowerCase();
    const status = cells[3].textContent.trim() === 'Active';
    
    // Populate the modal
    document.getElementById('edit_staff_id').value = staffId;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_firstname').value = name.split(' ')[0];
    document.getElementById('edit_lastname').value = name.split(' ').slice(1).join(' ');
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_level').value = level;
    document.getElementById('edit_status').checked = status;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('editStaffModal'));
    modal.show();
}
</script>

<?php include 'inc/footer.php'; ?>
