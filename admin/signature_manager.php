<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Upload Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_signature'])) {
    $name = trim($_POST['signature_name']);
    $file = $_FILES['signature_file'];

    if ($file['error'] === 0) {
        $filename = time() . '_' . basename($file['name']);
        $target = "uploads/signatures/$filename";
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO signatures (signature_name, signature_file) VALUES (?, ?)");
            $stmt->execute([$name, $target]);
            $comment = ['type' => 'success', 'msg' => 'Signature uploaded successfully.'];
        } else {
            $comment = ['type' => 'danger', 'msg' => 'Failed to upload file.'];
        }
    } else {
        $comment = ['type' => 'danger', 'msg' => 'Invalid file upload.'];
    }
}

// Delete Logic
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sig = $pdo->prepare("SELECT * FROM signatures WHERE id = ?");
    $sig->execute([$id]);
    $row = $sig->fetch();
    if ($row) {
        if (file_exists($row['signature_file'])) unlink($row['signature_file']);
        $pdo->prepare("DELETE FROM signatures WHERE id = ?")->execute([$id]);
        $comment = ['type' => 'success', 'msg' => 'Signature deleted successfully.'];
    } else {
        $comment = ['type' => 'danger', 'msg' => 'Signature not found.'];
    }
}

// Rename Logic
if (isset($_POST['rename_signature'])) {
    $id = (int)$_POST['sig_id'];
    $newname = trim($_POST['new_signature_name']);
    $stmt = $pdo->prepare("UPDATE signatures SET signature_name = ? WHERE id = ?");
    $stmt->execute([$newname, $id]);
    $comment = ['type' => 'info', 'msg' => 'Signature renamed successfully.'];
}

// Get all signatures
$signatures = $pdo->query("SELECT * FROM signatures ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signature Manager | FC Superwheels</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link href="css/navthing.css" rel="stylesheet">
  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --success: #059669;
      --danger: #dc2626;
      --info: #0891b2;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-900: #111827;
    }

    .sig-wrapper {
      padding: 2rem;
      max-width: 1100px;
      margin: 0 auto;
    }

    .sig-page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1.25rem;
      border-bottom: 3px solid var(--primary);
      margin-top: 80px;
    }

    .sig-page-header h1 {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--gray-900);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.625rem;
    }

    .sig-page-header h1 i { color: var(--primary); }

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

    .alert-custom {
      border-radius: 8px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
      font-size: 0.9375rem;
    }
    .alert-custom.success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
    .alert-custom.danger  { background: #fee2e2; color: #7f1d1d; border-left: 4px solid var(--danger); }
    .alert-custom.info    { background: #cffafe; color: #164e63; border-left: 4px solid var(--info); }

    .card-panel {
      background: white;
      border: 1px solid var(--gray-200);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 2rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    .card-panel-header {
      background: var(--gray-50);
      border-bottom: 1px solid var(--gray-200);
      padding: 1.125rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-panel-header h2 {
      font-size: 1rem;
      font-weight: 700;
      color: var(--gray-900);
      margin: 0;
    }

    .card-panel-body { padding: 1.5rem; }

    .form-row-grid {
      display: grid;
      grid-template-columns: 1fr 1fr auto;
      gap: 1rem;
      align-items: end;
    }

    .form-field label {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--gray-700);
      margin-bottom: 0.4rem;
    }

    .form-field input[type="text"],
    .form-field input[type="file"] {
      width: 100%;
      border: 1.5px solid var(--gray-300);
      border-radius: 6px;
      padding: 0.65rem 0.875rem;
      font-size: 0.9375rem;
      transition: all 0.2s;
      background: white;
    }

    .form-field input[type="text"]:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
      outline: none;
    }

    .btn-upload {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      background: var(--success);
      color: white;
      border: none;
      padding: 0.65rem 1.25rem;
      border-radius: 6px;
      font-weight: 700;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
    }
    .btn-upload:hover { background: #047857; transform: translateY(-1px); }

    /* Table */
    .sig-table { width: 100%; border-collapse: collapse; }

    .sig-table thead th {
      background: var(--gray-50);
      color: var(--gray-600);
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 0.875rem 1.25rem;
      border-bottom: 2px solid var(--gray-200);
      text-align: left;
    }

    .sig-table tbody tr { transition: background 0.15s; }
    .sig-table tbody tr:hover { background: var(--gray-50); }
    .sig-table tbody tr:not(:last-child) { border-bottom: 1px solid var(--gray-100); }

    .sig-table tbody td {
      padding: 1rem 1.25rem;
      color: var(--gray-700);
      font-size: 0.9rem;
      vertical-align: middle;
    }

    .sig-thumb {
      height: 56px;
      max-width: 120px;
      border-radius: 6px;
      border: 1px solid var(--gray-200);
      object-fit: contain;
      background: var(--gray-50);
      padding: 4px;
    }

    .sig-name-text {
      font-weight: 600;
      color: var(--gray-900);
    }

    .date-text {
      font-size: 0.8125rem;
      color: var(--gray-600);
    }

    .rename-inline {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .rename-inline input {
      border: 1.5px solid var(--gray-300);
      border-radius: 6px;
      padding: 0.45rem 0.75rem;
      font-size: 0.8125rem;
      width: 160px;
      transition: all 0.2s;
    }
    .rename-inline input:focus {
      border-color: var(--info);
      outline: none;
      box-shadow: 0 0 0 2px rgba(8,145,178,0.15);
    }

    .btn-rename {
      background: #0891b2;
      color: white;
      border: none;
      padding: 0.45rem 0.875rem;
      border-radius: 6px;
      font-size: 0.8125rem;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
    }
    .btn-rename:hover { background: #0e7490; }

    .btn-del {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      background: #fee2e2;
      color: var(--danger);
      border: none;
      padding: 0.45rem 0.875rem;
      border-radius: 6px;
      font-size: 0.8125rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-del:hover { background: var(--danger); color: white; }

    .empty-state {
      text-align: center;
      padding: 3rem;
      color: var(--gray-600);
    }
    .empty-state i { font-size: 2.5rem; color: var(--gray-300); margin-bottom: 1rem; display: block; }

    /* Modal refinements */
    .modal-content { border-radius: 10px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-header.danger-header { background: var(--danger); color: white; border-radius: 10px 10px 0 0; }
    .modal-header.danger-header .close { color: white; opacity: 0.85; }
    .modal-body { padding: 1.5rem; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); }

    @media (max-width: 768px) {
      .form-row-grid { grid-template-columns: 1fr; }
      .sig-wrapper { padding: 1rem; }
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/header.php'); ?>

  <div class="sig-wrapper">

    <!-- Page Header -->
    <div class="sig-page-header">
      <h1>
        <i class="fas fa-signature"></i>
        Signature Manager
      </h1>
      <a href="dashboard.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Dashboard
      </a>
    </div>

    <!-- Flash Message -->
    <?php if (isset($comment)): ?>
      <div class="alert-custom <?= $comment['type'] ?>">
        <i class="fas fa-<?= $comment['type'] === 'success' ? 'check-circle' : ($comment['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($comment['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Upload Card -->
    <div class="card-panel">
      <div class="card-panel-header">
        <i class="fas fa-cloud-upload-alt" style="color: var(--success);"></i>
        <h2>Upload New Signature</h2>
      </div>
      <div class="card-panel-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="form-row-grid">
            <div class="form-field">
              <label for="signature_name"><i class="fas fa-tag"></i> Signature Name</label>
              <input type="text" name="signature_name" id="signature_name" placeholder="e.g. Manager Signature" required>
            </div>
            <div class="form-field">
              <label for="signature_file"><i class="fas fa-image"></i> Signature Image</label>
              <input type="file" name="signature_file" id="signature_file" accept="image/*" required>
            </div>
            <div>
              <button type="submit" name="upload_signature" class="btn-upload">
                <i class="fas fa-upload"></i> Upload
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Signatures Table Card -->
    <div class="card-panel">
      <div class="card-panel-header">
        <i class="fas fa-list" style="color: var(--primary);"></i>
        <h2>All Signatures <span style="color: var(--gray-600); font-weight: 500;">(<?= count($signatures) ?>)</span></h2>
      </div>
      <?php if (count($signatures) === 0): ?>
        <div class="empty-state">
          <i class="fas fa-file-signature"></i>
          <p style="font-size: 1rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.25rem;">No signatures yet</p>
          <p style="font-size: 0.875rem;">Upload a signature image using the form above.</p>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table class="sig-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Preview</th>
                <th>Name</th>
                <th>Uploaded</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($signatures as $index => $sig): ?>
                <tr>
                  <td style="font-weight: 700; color: var(--gray-600);"><?= $index + 1 ?></td>
                  <td>
                    <img src="<?= htmlspecialchars($sig['signature_file']) ?>" class="sig-thumb" alt="Signature">
                  </td>
                  <td><span class="sig-name-text"><?= htmlspecialchars($sig['signature_name']) ?></span></td>
                  <td><span class="date-text"><?= date('M j, Y g:ia', strtotime($sig['created_at'])) ?></span></td>
                  <td>
                    <div style="display: flex; gap: 0.625rem; flex-wrap: wrap; align-items: center;">
                      <!-- Rename -->
                      <form method="POST" class="rename-inline">
                        <input type="hidden" name="sig_id" value="<?= $sig['id'] ?>">
                        <input type="text" name="new_signature_name" placeholder="New name…" required>
                        <button type="submit" name="rename_signature" class="btn-rename">
                          <i class="fas fa-pen"></i> Rename
                        </button>
                      </form>
                      <!-- Delete -->
                      <button
                        type="button"
                        class="btn-del"
                        data-toggle="modal"
                        data-target="#confirmDeleteModal"
                        data-id="<?= $sig['id'] ?>"
                        data-name="<?= htmlspecialchars($sig['signature_name']) ?>"
                      >
                        <i class="fas fa-trash-alt"></i> Delete
                      </button>
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

  <?php include_once('inc/footer.php'); ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header danger-header">
        <h5 class="modal-title" id="confirmDeleteLabel">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="deleteMessage" style="margin: 0; color: var(--gray-700);">Are you sure you want to delete this signature?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm">
          <i class="fas fa-trash-alt mr-1"></i>Delete
        </a>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/popper.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script>
  $('#confirmDeleteModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const sigId = button.data('id');
    const sigName = button.data('name');

    const modal = $(this);
    modal.find('#deleteMessage').text(`Are you sure you want to delete the signature: "${sigName}"?`);
    modal.find('#confirmDeleteBtn').attr('href', `signature_manager.php?delete=${sigId}`);
  });
</script>
</body>
</html>
