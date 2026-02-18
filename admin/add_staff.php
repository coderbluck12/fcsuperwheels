<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log page access
log_access('VIEW_ADD_STAFF', 'add_staff.php');

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    // Validate and sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
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
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
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
    
    // Check if username already exists
    if (empty($errors)) {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
            $check_stmt->execute([$username]);
            if ($check_stmt->fetch()) {
                $errors[] = 'Username already exists';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error checking username';
        }
    }
    
    // Check if email already exists (if provided)
    if (empty($errors) && !empty($email)) {
        try {
            $check_email_stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $check_email_stmt->execute([$email]);
            if ($check_email_stmt->fetch()) {
                $errors[] = 'Email already exists';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error checking email';
        }
    }
    
    // If no errors, create the staff member
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO user (username, password, firstname, lastname, email, level, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $password, $firstname, $lastname, $email, $level, $status]);
            
            log_access('ADD_STAFF', 'add_staff.php', null, null, "Added staff: $username");
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Staff member added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            
            // Clear form
            $_POST = [];
            
        } catch (PDOException $e) {
            log_access('ADD_STAFF_FAILED', 'add_staff.php', null, null, "Failed to add staff: " . $e->getMessage());
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error adding staff member: ' . $e->getMessage() . '
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

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.staff-wrapper {
    padding: 2.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.staff-header {
    text-align: center;
    margin-bottom: 2rem;
}

.staff-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
}

.staff-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
}

.form-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-select {
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.95rem;
    background-color: white;
    transition: all 0.2s;
}

.form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 1rem 0;
}

.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #2563eb;
}

.form-check-label {
    font-weight: 500;
    color: #374151;
    cursor: pointer;
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

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.help-text {
    font-size: 0.8125rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.required {
    color: #dc2626;
}

@media (max-width: 768px) {
    .staff-wrapper {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="staff-wrapper">
    <div class="staff-header">
        <h1 class="staff-title">Add Staff Member</h1>
        <p class="staff-subtitle">Create a new user account for staff members</p>
    </div>

    <?php echo $message; ?>

    <div class="form-card">
        <h2 class="form-title">
            <i class="fas fa-user-plus"></i>
            Staff Information
        </h2>
        
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           placeholder="Enter username" required>
                    <div class="help-text">Letters, numbers, and underscores only. Minimum 3 characters.</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="Enter email address">
                    <div class="help-text">Optional - for notifications and password recovery</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="firstname" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" 
                           placeholder="Enter first name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="lastname" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" 
                           placeholder="Enter last name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Enter password" required>
                    <div class="help-text">Minimum 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" 
                           placeholder="Confirm password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">User Level <span class="required">*</span></label>
                    <select name="level" class="form-select" required>
                        <option value="user" <?php echo ($_POST['level'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="manager" <?php echo ($_POST['level'] ?? '') === 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="admin" <?php echo ($_POST['level'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                    <div class="help-text">Admin has full access, Manager has limited admin access</div>
                </div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" name="status" id="status" class="form-check-input" 
                       <?php echo isset($_POST['status']) ? 'checked' : ''; ?>>
                <label for="status" class="form-check-label">
                    Active Account
                </label>
                <div class="help-text">Uncheck to create an inactive account</div>
            </div>
            
            <div class="form-actions">
                <a href="staffs.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" name="add_staff" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Add Staff Member
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
