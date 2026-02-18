<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log page access
log_access('VIEW_SEND_EMAIL', 'send_email.php');

$message = '';
$errors = [];

// Handle email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $recipients = $_POST['recipients'] ?? '';
    $custom_emails = trim($_POST['custom_emails'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $email_type = $_POST['email_type'] ?? 'custom';
    $custom_message = trim($_POST['custom_message'] ?? '');
    
    // Validation
    if (empty($subject)) {
        $errors[] = 'Email subject is required';
    }
    
    if ($email_type === 'custom' && empty($custom_message)) {
        $errors[] = 'Email message is required';
    }
    
    // Process recipients
    $email_list = [];
    
    if ($recipients === 'all_staff') {
        // Get all active staff members
        try {
            $stmt = $pdo->prepare("SELECT email, firstname, lastname FROM user WHERE status = 1 AND email IS NOT NULL AND email != ''");
            $stmt->execute();
            $staff_emails = $stmt->fetchAll();
            
            foreach ($staff_emails as $staff) {
                $email_list[] = [
                    'email' => $staff['email'],
                    'name' => $staff['firstname'] . ' ' . $staff['lastname']
                ];
            }
        } catch (PDOException $e) {
            $errors[] = 'Error fetching staff emails: ' . $e->getMessage();
        }
    } elseif ($recipients === 'custom' && !empty($custom_emails)) {
        // Parse custom emails
        $emails = array_map('trim', explode(',', $custom_emails));
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_list[] = [
                    'email' => $email,
                    'name' => ''
                ];
            }
        }
        
        if (empty($email_list)) {
            $errors[] = 'No valid email addresses found in custom recipients';
        }
    } else {
        $errors[] = 'Please select recipients or provide valid email addresses';
    }
    
    // Prepare email content
    $email_content = '';
    if ($email_type === 'custom') {
        $email_content = $custom_message;
    } else {
        // Predefined templates
        $templates = [
            'welcome' => [
                'subject' => 'Welcome to FC Superwheels Team',
                'message' => "Dear {name},\n\nWelcome to the FC Superwheels team! We're excited to have you on board.\n\nYour account has been created and you can now access the admin panel.\n\nIf you have any questions, please don't hesitate to reach out.\n\nBest regards,\nFC Superwheels Management"
            ],
            'maintenance' => [
                'subject' => 'System Maintenance Notice',
                'message' => "Dear {name},\n\nPlease be informed that our system will undergo scheduled maintenance.\n\nMaintenance Window: {date}\nDuration: {duration}\n\nDuring this time, the system may be temporarily unavailable. We apologize for any inconvenience.\n\nThank you for your understanding.\n\nFC Superwheels IT Team"
            ],
            'announcement' => [
                'subject' => 'Important Announcement',
                'message' => "Dear {name},\n\n{message}\n\nPlease take note of this important information.\n\nIf you have any questions, please contact the management.\n\nBest regards,\nFC Superwheels Team"
            ],
            'reminder' => [
                'subject' => 'Reminder: {reminder_title}',
                'message' => "Dear {name},\n\nThis is a reminder about: {reminder_title}\n\n{reminder_details}\n\nPlease ensure you complete this task by the deadline.\n\nThank you,\nFC Superwheels Management"
            ]
        ];
        
        if (isset($templates[$email_type])) {
            $template = $templates[$email_type];
            $email_content = $template['message'];
            if (empty($subject)) {
                $subject = $template['subject'];
            }
        }
    }
    
    // Send emails if no errors
    if (empty($errors) && !empty($email_list)) {
        $sent_count = 0;
        $failed_count = 0;
        
        foreach ($email_list as $recipient) {
            // Personalize the email
            $personalized_content = str_replace('{name}', $recipient['name'] ?: 'Team Member', $email_content);
            $personalized_content = str_replace('{date}', date('Y-m-d H:i:s'), $personalized_content);
            $personalized_content = str_replace('{duration}', '2 hours', $personalized_content);
            $personalized_content = str_replace('{reminder_title}', 'Task Reminder', $personalized_content);
            $personalized_content = str_replace('{reminder_details}', 'Please complete your pending tasks', $personalized_content);
            $personalized_content = str_replace('{message}', $custom_message ?: 'Please check the admin panel for updates.', $personalized_content);
            
            // Send email (using PHP mail function - in production, you'd use a proper email service)
            $headers = [
                'From: noreply@fcsuperwheels.com',
                'Reply-To: support@fcsuperwheels.com',
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $to = $recipient['email'];
            $subject_line = str_replace('{name}', $recipient['name'] ?: 'Team Member', $subject);
            $headers_string = implode("\r\n", $headers);
            
            if (mail($to, $subject_line, $personalized_content, $headers_string)) {
                $sent_count++;
            } else {
                $failed_count++;
            }
        }
        
        // Log the email sending activity
        log_access('SEND_EMAIL', 'send_email.php', null, null, "Sent email to $sent_count recipients, $failed_count failed");
        
        if ($sent_count > 0) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Email sent successfully to ' . $sent_count . ' recipient(s)!
                ' . ($failed_count > 0 ? ' (' . $failed_count . ' failed)' : '') . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Failed to send emails. Please check your email configuration.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        
        // Clear form
        $_POST = [];
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
        
        log_access('SEND_EMAIL_FAILED', 'send_email.php', null, null, "Failed to send email: " . implode(', ', $errors));
    }
}

$path_to_root = './';
include 'inc/header.php';
?>

<style>
.email-wrapper {
    padding: 2.5rem;
    max-width: 900px;
    margin: 0 auto;
}

.email-header {
    text-align: center;
    margin-bottom: 2rem;
}

.email-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
}

.email-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
}

.form-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
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

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: block;
}

.form-control, .form-select, .form-textarea {
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.95rem;
    transition: all 0.2s;
    width: 100%;
}

.form-control:focus, .form-select:focus, .form-textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-textarea {
    min-height: 150px;
    resize: vertical;
    font-family: inherit;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
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

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.template-preview {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
    font-family: monospace;
    font-size: 0.875rem;
    color: #475569;
    white-space: pre-wrap;
    max-height: 200px;
    overflow-y: auto;
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

.template-section {
    display: none;
}

.template-section.active {
    display: block;
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
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .email-wrapper {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="email-wrapper">
    <div class="email-header">
        <h1 class="email-title">Send Email</h1>
        <p class="email-subtitle">Send emails to staff members or custom recipients</p>
    </div>

    <?php echo $message; ?>

    <!-- Email Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="totalStaff">-</div>
            <div class="stat-label">Total Staff</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="activeStaff">-</div>
            <div class="stat-label">Active Staff</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="staffWithEmail">-</div>
            <div class="stat-label">Staff with Email</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="emailsSent">-</div>
            <div class="stat-label">Emails Sent Today</div>
        </div>
    </div>

    <div class="form-card">
        <h2 class="form-title">
            <i class="fas fa-envelope"></i>
            Compose Email
        </h2>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Recipients <span class="required">*</span></label>
                <div class="radio-group">
                    <div class="form-check">
                        <input type="radio" name="recipients" id="all_staff" value="all_staff" 
                               class="form-check-input" <?php echo ($_POST['recipients'] ?? '') === 'all_staff' ? 'checked' : ''; ?>>
                        <label for="all_staff" class="form-check-label">
                            All Active Staff Members
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="recipients" id="custom" value="custom" 
                               class="form-check-input" <?php echo ($_POST['recipients'] ?? '') === 'custom' ? 'checked' : ''; ?>>
                        <label for="custom" class="form-check-label">
                            Custom Email Addresses
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-group" id="custom_emails_group" style="display: none;">
                <label class="form-label">Email Addresses</label>
                <textarea name="custom_emails" class="form-textarea" 
                          placeholder="Enter email addresses separated by commas&#10;Example: john@example.com, jane@example.com"><?php echo htmlspecialchars($_POST['custom_emails'] ?? ''); ?></textarea>
                <div class="help-text">Enter multiple email addresses separated by commas</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Subject <span class="required">*</span></label>
                <input type="text" name="subject" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                       placeholder="Enter email subject" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Type</label>
                <select name="email_type" id="email_type" class="form-select">
                    <option value="custom" <?php echo ($_POST['email_type'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom Message</option>
                    <option value="welcome" <?php echo ($_POST['email_type'] ?? '') === 'welcome' ? 'selected' : ''; ?>>Welcome Email</option>
                    <option value="maintenance" <?php echo ($_POST['email_type'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance Notice</option>
                    <option value="announcement" <?php echo ($_POST['email_type'] ?? '') === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                    <option value="reminder" <?php echo ($_POST['email_type'] ?? '') === 'reminder' ? 'selected' : ''; ?>>Reminder</option>
                </select>
            </div>
            
            <div class="form-group template-section active" id="custom_section">
                <label class="form-label">Message <span class="required">*</span></label>
                <textarea name="custom_message" class="form-textarea" 
                          placeholder="Enter your email message here..." required><?php echo htmlspecialchars($_POST['custom_message'] ?? ''); ?></textarea>
                <div class="help-text">You can use placeholders: {name}, {date}, {time}</div>
            </div>
            
            <div class="form-group template-section" id="welcome_section">
                <label class="form-label">Welcome Message Preview</label>
                <div class="template-preview">Dear {name},

Welcome to the FC Superwheels team! We're excited to have you on board.

Your account has been created and you can now access the admin panel.

If you have any questions, please don't hesitate to reach out.

Best regards,
FC Superwheels Management</div>
            </div>
            
            <div class="form-group template-section" id="maintenance_section">
                <label class="form-label">Maintenance Notice Preview</label>
                <div class="template-preview">Dear {name},

Please be informed that our system will undergo scheduled maintenance.

Maintenance Window: {date}
Duration: 2 hours

During this time, the system may be temporarily unavailable. We apologize for any inconvenience.

Thank you for your understanding.

FC Superwheels IT Team</div>
            </div>
            
            <div class="form-group template-section" id="announcement_section">
                <label class="form-label">Announcement Preview</label>
                <div class="template-preview">Dear {name},

Please check the admin panel for updates.

Please take note of this important information.

If you have any questions, please contact the management.

Best regards,
FC Superwheels Team</div>
            </div>
            
            <div class="form-group template-section" id="reminder_section">
                <label class="form-label">Reminder Preview</label>
                <div class="template-preview">Dear {name},

This is a reminder about: Task Reminder

Please complete your pending tasks

Please ensure you complete this task by the deadline.

Thank you,
FC Superwheels Management</div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="clearForm()">
                    <i class="fas fa-times"></i>
                    Clear
                </button>
                <button type="submit" name="send_email" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle custom emails field
document.querySelectorAll('input[name="recipients"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const customGroup = document.getElementById('custom_emails_group');
        customGroup.style.display = this.value === 'custom' ? 'block' : 'none';
    });
});

// Toggle email template sections
document.getElementById('email_type').addEventListener('change', function() {
    const sections = document.querySelectorAll('.template-section');
    sections.forEach(section => section.classList.remove('active'));
    
    const selectedSection = document.getElementById(this.value + '_section');
    if (selectedSection) {
        selectedSection.classList.add('active');
    }
});

// Clear form function
function clearForm() {
    if (confirm('Are you sure you want to clear the form?')) {
        document.querySelector('form').reset();
        document.getElementById('custom_emails_group').style.display = 'none';
        document.getElementById('custom_section').classList.add('active');
    }
}

// Load statistics
async function loadStats() {
    try {
        // In a real application, you'd fetch this from an API
        // For now, we'll simulate with sample data
        document.getElementById('totalStaff').textContent = '12';
        document.getElementById('activeStaff').textContent = '10';
        document.getElementById('staffWithEmail').textContent = '8';
        document.getElementById('emailsSent').textContent = '5';
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    
    // Set initial state
    const recipientsRadio = document.querySelector('input[name="recipients"]:checked');
    if (recipientsRadio && recipientsRadio.value === 'custom') {
        document.getElementById('custom_emails_group').style.display = 'block';
    }
});
</script>

<?php include 'inc/footer.php'; ?>
