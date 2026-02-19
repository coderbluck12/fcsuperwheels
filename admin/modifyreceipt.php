<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Fetch available signatures
$sigStmt = $pdo->query("SELECT id, signature_name, signature_file FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE main_receipt SET customer_name=?, customer_address=?, customer_phone=?, customer_email=?, vehicle_make=?, vehicle_model=?, vehicle_year=?, vehicle_chasis=?, vehicle_color=?, vehicle_price=?, payment_type=?, payment_method=?, payment_reference=?, amount_paid=?, payment_date=?, add_payment=?, add_vehicle=?, signature_id=? WHERE id=?");

    $signature_id = $_POST['signature_id'] === '' ? null : $_POST['signature_id'];

    $params = [
        $_POST['customer_name'],
        $_POST['customer_address'],
        $_POST['customer_phone'],
        $_POST['customer_email'],
        $_POST['vehicle_make'],
        $_POST['vehicle_model'],
        $_POST['vehicle_year'],
        $_POST['vehicle_chasis'],
        $_POST['vehicle_color'],
        $_POST['vehicle_price'],
        $_POST['payment_type'],
        $_POST['payment_method'],
        $_POST['payment_reference'],
        $_POST['amount_paid'],
        $_POST['payment_date'],
        $_POST['add_payment'],
        $_POST['add_vehicle'],
        $signature_id,
        $_POST['receipt_id']
    ];

    if ($stmt->execute($params)) {
        redirect_to('view_receipt.php?prefix_receipt_number=' . urlencode(encryptData($_POST['receipt_id'], '31081990')) . '&edit-success');
    } else {
        echo "Error updating receipt: " . $stmt->errorInfo()[2];
    }

    $stmt = null;
    $pdo = null;
} else {
    $receipt_id = decryptData($_GET['prefix_receipt_number'], "31081990");
    $stmt = $pdo->prepare("SELECT * FROM main_receipt WHERE id = ?");
    $stmt->execute([$receipt_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $customer_name     = $row['customer_name'];
    $customer_address  = $row['customer_address'];
    $customer_phone    = $row['customer_phone'];
    $customer_email    = $row['customer_email'];
    $vehicle_make      = $row['vehicle_make'];
    $vehicle_model     = $row['vehicle_model'];
    $vehicle_year      = $row['vehicle_year'];
    $vehicle_chasis    = $row['vehicle_chasis'];
    $vehicle_color     = $row['vehicle_color'];
    $vehicle_price     = $row['vehicle_price'];
    $payment_type      = $row['payment_type'];
    $payment_method    = $row['payment_method'];
    $payment_reference = $row['payment_reference'];
    $amount_paid       = $row['amount_paid'];
    $payment_date      = date('Y-m-d', strtotime($row['payment_date']));
    $prefix_receipt_number = $row['prefix_receipt_number'];
    $add_payment       = $row['add_payment'];
    $add_vehicle       = $row['add_vehicle'];
    $current_signature_id = $row['signature_id'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Receipt | FC Superwheels</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        .modify-wrapper {
            padding: 2rem;
            max-width: 920px;
            margin: 0 auto;
        }

        .modify-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.25rem;
            border-bottom: 3px solid var(--warning);
            margin-top: 80px;
        }

        .modify-page-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .modify-page-header h1 i { color: var(--warning); }

        .receipt-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #fef3c7;
            color: #92400e;
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 700;
            font-family: monospace;
            margin-top: 0.375rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.125rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back:hover { background: var(--primary-dark); color: white; text-decoration: none; }

        /* Section Cards */
        .form-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: box-shadow 0.2s;
        }

        .form-card:focus-within {
            box-shadow: 0 4px 16px rgba(0,0,0,0.09);
        }

        .form-card-header {
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .form-card-header h2 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .section-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            flex-shrink: 0;
        }

        .icon-blue   { background: #dbeafe; color: var(--primary); }
        .icon-green  { background: #d1fae5; color: var(--success); }
        .icon-amber  { background: #fef3c7; color: var(--warning); }
        .icon-purple { background: #ede9fe; color: #7c3aed; }

        .form-card-body { padding: 1.5rem; }

        .field-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .field-full { grid-column: 1 / -1; }

        .field-group { }

        .field-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--gray-600);
            margin-bottom: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .field-group input,
        .field-group textarea,
        .field-group select {
            width: 100%;
            border: 1.5px solid var(--gray-300);
            border-radius: 7px;
            padding: 0.7rem 0.9rem;
            font-size: 0.9375rem;
            color: var(--gray-900);
            background: white;
            transition: all 0.2s;
            appearance: none;
        }

        .field-group textarea { resize: vertical; min-height: 85px; }

        .field-group input:focus,
        .field-group textarea:focus,
        .field-group select:focus {
            border-color: var(--warning);
            box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
            outline: none;
        }

        .field-group input::placeholder,
        .field-group textarea::placeholder { color: #9ca3af; }

        .optional-label {
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: var(--gray-600);
        }

        /* Signature Preview */
        .sig-preview-box {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border: 1.5px dashed var(--gray-300);
            border-radius: 8px;
            text-align: center;
        }

        .sig-preview-box.visible { display: block; }

        .sig-preview-img {
            max-height: 70px;
            max-width: 240px;
            object-fit: contain;
            border-radius: 4px;
        }

        .sig-preview-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.5rem;
        }

        /* Submit button */
        .btn-update {
            width: 100%;
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.0625rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            letter-spacing: 0.02em;
            margin-top: 0.5rem;
        }

        .btn-update:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(180,83,9,0.3);
        }

        @media (max-width: 640px) {
            .field-grid-2 { grid-template-columns: 1fr; }
            .modify-wrapper { padding: 1rem; }
            .modify-page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <?php include_once('inc/header.php'); ?>
    <div class="page-content--bgf7">
        <div class="modify-wrapper">

            <!-- Page Header -->
            <div class="modify-page-header">
                <div>
                    <h1>
                        <i class="fas fa-pen-square"></i>
                        Edit Receipt
                    </h1>
                    <span class="receipt-id-badge">
                        <i class="fas fa-hashtag"></i>
                        <?= htmlspecialchars($prefix_receipt_number . $receipt_id) ?>
                    </span>
                </div>
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>

            <form method="POST" action="" onsubmit="return validateNumbers()">

                <!-- Customer Information -->
                <div class="form-card">
                    <div class="form-card-header">
                        <span class="section-icon icon-blue"><i class="fas fa-user"></i></span>
                        <h2>Customer Information</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-grid-2">
                            <div class="field-group field-full">
                                <label>Full Name *</label>
                                <input type="text" name="customer_name" placeholder="Customer's full name" value="<?= htmlspecialchars($customer_name) ?>" required>
                            </div>
                            <div class="field-group field-full">
                                <label>Address *</label>
                                <textarea name="customer_address" placeholder="Customer's full address" required><?= htmlspecialchars($customer_address) ?></textarea>
                            </div>
                            <div class="field-group">
                                <label>Phone Number *</label>
                                <input type="text" name="customer_phone" placeholder="+234 000 000 0000" value="<?= htmlspecialchars($customer_phone) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Email Address <span class="optional-label">(optional)</span></label>
                                <input type="email" name="customer_email" placeholder="customer@email.com" value="<?= htmlspecialchars($customer_email) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information -->
                <div class="form-card">
                    <div class="form-card-header">
                        <span class="section-icon icon-green"><i class="fas fa-car"></i></span>
                        <h2>Vehicle Information</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-grid-2">
                            <div class="field-group">
                                <label>Make *</label>
                                <input type="text" name="vehicle_make" placeholder="e.g. Toyota" value="<?= htmlspecialchars($vehicle_make) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Model *</label>
                                <input type="text" name="vehicle_model" placeholder="e.g. Camry" value="<?= htmlspecialchars($vehicle_model) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Year *</label>
                                <input type="text" name="vehicle_year" placeholder="e.g. 2020" value="<?= htmlspecialchars($vehicle_year) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Chassis No *</label>
                                <input type="text" name="vehicle_chasis" placeholder="Chassis number" value="<?= htmlspecialchars($vehicle_chasis) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Color *</label>
                                <input type="text" name="vehicle_color" placeholder="e.g. Pearl White" value="<?= htmlspecialchars($vehicle_color) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Vehicle Price <span class="optional-label">(optional)</span></label>
                                <input type="number" name="vehicle_price" id="vehicle_price" placeholder="₦ 0.00"
                                    value="<?= ($vehicle_price !== null && $vehicle_price != 0) ? htmlspecialchars($vehicle_price) : '' ?>">
                            </div>
                            <div class="field-group field-full">
                                <label>Additional Vehicle Info <span class="optional-label">(optional)</span></label>
                                <input type="text" name="add_vehicle" placeholder="Any other details about the vehicle" value="<?= htmlspecialchars($add_vehicle) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="form-card">
                    <div class="form-card-header">
                        <span class="section-icon icon-amber"><i class="fas fa-money-bill-wave"></i></span>
                        <h2>Payment Information</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-grid-2">
                            <div class="field-group">
                                <label>Payment Type *</label>
                                <select name="payment_type" id="payment_type" required>
                                    <option value="">Select Payment Type…</option>
                                    <option value="full"        <?= $payment_type == 'full'        ? 'selected' : '' ?>>Full Payment</option>
                                    <option value="installment" <?= $payment_type == 'installment' ? 'selected' : '' ?>>Installment</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label>Payment Method *</label>
                                <input type="text" name="payment_method" placeholder="e.g. Bank Transfer, Cash" value="<?= htmlspecialchars($payment_method) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Payment Reference <span class="optional-label">(optional)</span></label>
                                <input type="text" name="payment_reference" placeholder="Transaction reference" value="<?= htmlspecialchars($payment_reference) ?>">
                            </div>
                            <div class="field-group">
                                <label>Amount Paid (₦) *</label>
                                <input type="number" name="amount_paid" id="amount_paid" placeholder="0.00" value="<?= htmlspecialchars($amount_paid) ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Payment Date *</label>
                                <input type="date" name="payment_date" value="<?= $payment_date ?>" required>
                            </div>
                            <div class="field-group">
                                <label>Additional Payment Info <span class="optional-label">(optional)</span></label>
                                <input type="text" name="add_payment" placeholder="Any other payment notes" value="<?= htmlspecialchars($add_payment) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Authorized Signature -->
                <div class="form-card">
                    <div class="form-card-header">
                        <span class="section-icon icon-purple"><i class="fas fa-signature"></i></span>
                        <h2>Authorized Signature <span class="optional-label" style="font-weight:500;">(optional)</span></h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-group">
                            <label>Select Signature</label>
                            <select name="signature_id" id="signature_id" onchange="updateSignaturePreview()">
                                <option value="">-- No Signature --</option>
                                <?php foreach ($signatures as $sig): ?>
                                    <option
                                        value="<?= $sig['id'] ?>"
                                        <?= $sig['id'] == $current_signature_id ? 'selected' : '' ?>
                                        data-src="<?= htmlspecialchars($sig['signature_file']) ?>"
                                    >
                                        <?= htmlspecialchars($sig['signature_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Signature preview -->
                        <div id="sig-preview" class="sig-preview-box <?= $current_signature_id ? 'visible' : '' ?>">
                            <p class="sig-preview-label"><i class="fas fa-eye"></i> Preview</p>
                            <img id="sig-img" src="<?php
                                foreach ($signatures as $sig) {
                                    if ($sig['id'] == $current_signature_id) {
                                        echo htmlspecialchars($sig['signature_file']);
                                        break;
                                    }
                                }
                            ?>" alt="Signature Preview" class="sig-preview-img">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="receipt_id" value="<?= $receipt_id ?>">

                <!-- Submit -->
                <button type="submit" name="receipt_submit" class="btn-update">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>

            </form>

        </div>

        <!-- Footer -->
        <div class="row mt-4" style="max-width: 920px; margin: 0 auto; padding: 0 2rem;">
            <div class="col-12">
                <?php include_once('inc/footer.php'); ?>
            </div>
        </div>

    </div>
</div>

<script>
function validateNumbers() {
    var vehicle_price = parseFloat(document.getElementById("vehicle_price").value);
    var amount_paid = parseFloat(document.getElementById("amount_paid").value);
    var payment_type = document.getElementById("payment_type").value;

    if (!isNaN(vehicle_price) && amount_paid > vehicle_price) {
        alert("The amount paid cannot be greater than the actual price");
        return false;
    } else if (payment_type === "full" && !isNaN(vehicle_price) && amount_paid < vehicle_price) {
        alert("The amount paid cannot be less than the vehicle price for full payment");
        return false;
    } else {
        return true;
    }
}

function updateSignaturePreview() {
    const select = document.getElementById('signature_id');
    const selected = select.options[select.selectedIndex];
    const src = selected.getAttribute('data-src');
    const preview = document.getElementById('sig-preview');
    const img = document.getElementById('sig-img');

    if (src) {
        preview.classList.add('visible');
        img.src = src;
    } else {
        preview.classList.remove('visible');
        img.src = '';
    }
}
</script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
