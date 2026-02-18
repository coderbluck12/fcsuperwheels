<?php
require_once 'inc/session_manager.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: inventory.php');
    exit;
}

$message = '';

// Add Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $description = validate_input($_POST['description']);
    $amount = (float)$_POST['amount'];
    $expense_date = $_POST['expense_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (vehicle_id, description, amount, expense_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $description, $amount, $expense_date]);
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Expense added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Fetch Vehicle
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: inventory.php');
    exit;
}

// Fetch Expenses
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE vehicle_id = ? ORDER BY expense_date DESC");
$stmt->execute([$id]);
$expenses = $stmt->fetchAll();

$total_expenses = array_sum(array_column($expenses, 'amount'));
$total_cost = $vehicle['purchase_price'] + $total_expenses;

$path_to_root = './';
include 'inc/header.php';
?>

<style>
    .details-wrapper {
        padding: 2.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid #2563eb;
    }

    .vehicle-title-section h1 {
        font-size: 2.25rem;
        font-weight: 800;
        color: #111827;
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.025em;
    }

    .vehicle-meta {
        display: flex;
        gap: 2rem;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .vehicle-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .back-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border: 2px solid #2563eb;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .back-link:hover {
        background: #2563eb;
        color: white;
    }

    .details-grid {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .vehicle-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .vehicle-image-container {
        position: relative;
        background: #f9fafb;
        padding: 2rem;
    }

    .vehicle-image-container img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .no-image-placeholder {
        width: 100%;
        height: 350px;
        background: #f3f4f6;
        border-radius: 6px;
        border: 2px dashed #d1d5db;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #9ca3af;
    }

    .no-image-placeholder i {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .specs-section {
        padding: 2rem;
    }

    .spec-row {
        display: flex;
        justify-content: space-between;
        padding: 1.25rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .spec-row:last-child {
        border-bottom: none;
    }

    .spec-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .spec-value {
        font-weight: 700;
        color: #111827;
        font-size: 1.05rem;
    }

    .financial-highlight {
        background: #eff6ff;
        margin: 0 -2rem;
        padding: 1.5rem 2rem;
        border-top: 2px solid #2563eb;
        border-bottom: 2px solid #2563eb;
    }

    .financial-highlight .spec-value {
        color: #2563eb;
        font-size: 1.5rem;
    }

    .status-indicator {
        display: inline-block;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-available {
        background: #d1fae5;
        color: #065f46;
    }

    .status-sold {
        background: #fee2e2;
        color: #991b1b;
    }

    .expenses-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .expenses-header {
        padding: 1.5rem 2rem;
        background: #fafafa;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .expenses-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .expense-count {
        background: #111827;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-left: 0.75rem;
    }

    .add-expense-btn {
        background: #2563eb;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .add-expense-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .expenses-table-wrapper {
        overflow-x: auto;
    }

    .expenses-table {
        width: 100%;
        border-collapse: collapse;
    }

    .expenses-table thead {
        background: #f9fafb;
    }

    .expenses-table th {
        text-align: left;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
    }

    .expenses-table td {
        padding: 1.25rem 2rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }

    .expenses-table tbody tr:hover {
        background: #fafafa;
    }

    .expense-amount {
        font-weight: 800;
        color: #dc2626;
        font-size: 1.05rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        opacity: 0.4;
    }

    .empty-state h3 {
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 1024px) {
        .details-grid {
            grid-template-columns: 1fr;
        }

        .details-wrapper {
            padding: 1.5rem;
        }
    }

    @media (max-width: 640px) {
        .vehicle-title-section h1 {
            font-size: 1.75rem;
        }

        .vehicle-meta {
            flex-direction: column;
            gap: 0.75rem;
        }

        .top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .expenses-table th,
        .expenses-table td {
            padding: 1rem;
        }
    }
</style>

<div class="details-wrapper">
    <div class="top-bar">
        <div class="vehicle-title-section">
            <h1><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h1>
            <div class="vehicle-meta">
                <span><i class="bi bi-calendar3"></i> <?php echo $vehicle['year']; ?></span>
                <span><i class="bi bi-hash"></i> VIN: <?php echo htmlspecialchars($vehicle['vin'] ?: 'Not provided'); ?></span>
            </div>
        </div>
        <a href="inventory.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <?php echo $message; ?>

    <div class="details-grid">
        <!-- Vehicle Info Card -->
        <div class="vehicle-card">
            <div class="vehicle-image-container">
                <?php if ($vehicle['image']): ?>
                    <img src="<?php echo htmlspecialchars($vehicle['image']); ?>" alt="Vehicle">
                <?php else: ?>
                    <div class="no-image-placeholder">
                        <i class="bi bi-camera"></i>
                        <span>No image available</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="specs-section">
                <div class="spec-row">
                    <span class="spec-label">Make & Model</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Year</span>
                    <span class="spec-value"><?php echo $vehicle['year']; ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Color</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['color'] ?: '—'); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Purchase Price</span>
                    <span class="spec-value">₦<?php echo number_format($vehicle['purchase_price'], 2); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Total Expenses</span>
                    <span class="spec-value" style="color: #dc2626;">₦<?php echo number_format($total_expenses, 2); ?></span>
                </div>
                
                <div class="financial-highlight">
                    <div class="spec-row" style="border: none;">
                        <span class="spec-label">Total Investment</span>
                        <span class="spec-value">₦<?php echo number_format($total_cost, 2); ?></span>
                    </div>
                </div>

                <div class="spec-row">
                    <span class="spec-label">Purchase Date</span>
                    <span class="spec-value"><?php echo date('M j, Y', strtotime($vehicle['purchase_date'])); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Status</span>
                    <span class="status-indicator status-<?php echo strtolower($vehicle['status']); ?>">
                        <?php echo htmlspecialchars($vehicle['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Expenses Card -->
        <div class="expenses-card">
            <div class="expenses-header">
                <h2 class="expenses-title">
                    Expense History
                    <span class="expense-count"><?php echo count($expenses); ?></span>
                </h2>
                <button class="add-expense-btn" onclick="openExpenseModal()">
                    <i class="bi bi-plus-lg"></i>
                    Add Expense
                </button>
            </div>

            <?php if (count($expenses) > 0): ?>
                <div class="expenses-table-wrapper">
                    <table class="expenses-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($expense['description']); ?></strong></td>
                                <td class="expense-amount">₦<?php echo number_format($expense['amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h3>No expenses yet</h3>
                    <p>Start tracking costs for this vehicle by adding your first expense.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight: 700;">Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" 
                               name="description" 
                               class="form-control" 
                               required 
                               placeholder="e.g., Oil Change, Tire Replacement">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount ($)</label>
                        <input type="number" 
                               step="0.01" 
                               name="amount" 
                               class="form-control" 
                               required
                               placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" 
                               name="expense_date" 
                               class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_expense" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openExpenseModal() {
    const modal = new bootstrap.Modal(document.getElementById('addExpenseModal'));
    modal.show();
}
</script>

<?php include 'inc/footer.php'; ?>