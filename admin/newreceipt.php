<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Fetch available signatures
$sigStmt = $pdo->query("SELECT id, signature_name FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate New Receipt | FC Superwheels</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link href="css/theme.css" rel="stylesheet">
  <script>
    function validateNumbers() {
      const price = parseFloat(document.getElementById("vehicle_price").value);
      const paid = parseFloat(document.getElementById("amount_paid").value);
      const type = document.getElementById("payment_type").value;

      if (!isNaN(price) && paid > price) {
        alert("Amount paid cannot be greater than vehicle price.");
        return false;
      }
      if (type === "full" && !isNaN(price) && paid < price) {
        alert("Full payment must match the vehicle price.");
        return false;
      }
      return true;
    }
  </script>
  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --success: #059669;
      --danger: #dc2626;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-900: #111827;
    }

    .receipt-wrapper {
      padding: 2rem;
      max-width: 860px;
      margin: 0 auto;
    }

    .receipt-page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1.25rem;
      border-bottom: 3px solid var(--primary);
      margin-top: 80px;
    }

    .receipt-page-header h1 {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--gray-900);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.625rem;
    }

    .receipt-page-header h1 i { color: var(--primary); }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.875rem;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-back:hover { background: var(--primary-dark); color: white; text-decoration: none; }

    .form-card {
      background: white;
      border: 1px solid var(--gray-200);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
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
      font-size: 0.9375rem;
      font-weight: 700;
      color: var(--gray-900);
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .form-card-header .section-icon {
      width: 30px;
      height: 30px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
    }

    .icon-blue  { background: #dbeafe; color: var(--primary); }
    .icon-green { background: #d1fae5; color: var(--success); }
    .icon-amber { background: #fef3c7; color: #d97706; }
    .icon-purple { background: #ede9fe; color: #7c3aed; }

    .form-card-body { padding: 1.5rem; }

    .field-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .field-full { grid-column: 1 / -1; }

    .field-group { margin-bottom: 0; }

    .field-group label {
      display: block;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--gray-700);
      margin-bottom: 0.375rem;
      text-transform: uppercase;
      letter-spacing: 0.03em;
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

    .field-group textarea { resize: vertical; min-height: 90px; }

    .field-group input:focus,
    .field-group textarea:focus,
    .field-group select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
      outline: none;
    }

    .field-group input::placeholder,
    .field-group textarea::placeholder { color: #9ca3af; }

    .field-group .helper-text {
      font-size: 0.8rem;
      color: var(--gray-600);
      margin-top: 0.3rem;
    }

    .btn-generate {
      width: 100%;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
    .btn-generate:hover { filter: brightness(1.08); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(5,150,105,0.3); }

    .no-sig-msg {
      font-size: 0.875rem;
      color: var(--danger);
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    @media (max-width: 640px) {
      .field-grid-2 { grid-template-columns: 1fr; }
      .receipt-wrapper { padding: 1rem; }
      .receipt-page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/header.php'); ?>
  <div class="page-content--bgf7">
    <div class="receipt-wrapper">

      <!-- Page Header -->
      <div class="receipt-page-header">
        <h1>
          <i class="fas fa-file-invoice"></i>
          Generate New Receipt
        </h1>
        <a href="dashboard.php" class="btn-back">
          <i class="fas fa-arrow-left"></i> Dashboard
        </a>
      </div>

      <form method="POST" action="receipt_processor.php" onsubmit="return validateNumbers()" enctype="multipart/form-data">

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
                <input type="text" name="customer_name" placeholder="Customer's full name" required>
              </div>
              <div class="field-group field-full">
                <label>Address *</label>
                <textarea name="customer_address" placeholder="Customer's full address" required></textarea>
              </div>
              <div class="field-group">
                <label>Phone Number *</label>
                <input type="text" name="customer_phone" placeholder="+234 000 000 0000" required>
              </div>
              <div class="field-group">
                <label>Email Address <span style="font-weight:400; text-transform: none;">(optional)</span></label>
                <input type="email" name="customer_email" placeholder="customer@email.com">
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
                <input type="text" name="vehicle_make" placeholder="e.g. Toyota" required>
              </div>
              <div class="field-group">
                <label>Model *</label>
                <input type="text" name="vehicle_model" placeholder="e.g. Camry" required>
              </div>
              <div class="field-group">
                <label>Year *</label>
                <input type="text" name="vehicle_year" placeholder="e.g. 2020" required>
              </div>
              <div class="field-group">
                <label>Chassis No *</label>
                <input type="text" name="vehicle_chasis" placeholder="Chassis number" required>
              </div>
              <div class="field-group">
                <label>Color *</label>
                <input type="text" name="vehicle_color" placeholder="e.g. Pearl White" required>
              </div>
              <div class="field-group">
                <label>Vehicle Price <span style="font-weight:400; text-transform: none;">(optional)</span></label>
                <input type="number" name="vehicle_price" id="vehicle_price" placeholder="₦ 0.00">
              </div>
              <div class="field-group field-full">
                <label>Additional Vehicle Info <span style="font-weight:400; text-transform: none;">(optional)</span></label>
                <input type="text" name="add_vehicle" placeholder="Any other details about the vehicle">
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
                  <option value="">Select payment type…</option>
                  <option value="full">Full Payment</option>
                  <option value="installment">Installment</option>
                </select>
              </div>
              <div class="field-group">
                <label>Payment Method *</label>
                <input type="text" name="payment_method" placeholder="e.g. Bank Transfer, Cash" required>
              </div>
              <div class="field-group">
                <label>Payment Reference <span style="font-weight:400; text-transform: none;">(optional)</span></label>
                <input type="text" name="payment_reference" placeholder="Transaction reference">
              </div>
              <div class="field-group">
                <label>Amount Paid (₦) *</label>
                <input type="number" name="amount_paid" id="amount_paid" placeholder="0.00" required>
              </div>
              <div class="field-group">
                <label>Payment Date *</label>
                <input type="date" name="payment_date" required>
              </div>
              <div class="field-group">
                <label>Additional Payment Info <span style="font-weight:400; text-transform: none;">(optional)</span></label>
                <input type="text" name="add_payment" placeholder="Any other payment notes">
              </div>
            </div>
          </div>
        </div>

        <!-- Signature -->
        <div class="form-card">
          <div class="form-card-header">
            <span class="section-icon icon-purple"><i class="fas fa-signature"></i></span>
            <h2>Authorized Signature</h2>
          </div>
          <div class="form-card-body">
            <div class="field-group">
              <label>Select Signature</label>
              <select name="signature_id" id="signature_id" <?= empty($signatures) ? 'disabled' : '' ?>>
                <option value="">-- No Signature --</option>
                <?php foreach ($signatures as $sig): ?>
                  <option value="<?= $sig['id'] ?>"><?= htmlspecialchars($sig['signature_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($signatures)): ?>
                <p class="no-sig-msg">
                  <i class="fas fa-exclamation-circle"></i>
                  No signatures found. <a href="signature_manager.php" style="color: var(--primary); font-weight: 600;">Upload one here</a>.
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" name="receipt_submit" class="btn-generate">
          <i class="fas fa-file-invoice-dollar"></i>
          Generate Receipt
        </button>

      </form>

    </div>
  </div>

  <?php include_once('inc/footer.php'); ?>
</div>

<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
