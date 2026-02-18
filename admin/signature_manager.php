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
            $comment = '<div class="alert alert-success">Signature uploaded successfully.</div>';
        } else {
            $comment = '<div class="alert alert-danger">Failed to upload file.</div>';
        }
    } else {
        $comment = '<div class="alert alert-danger">Invalid file upload.</div>';
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
        $comment = '<div class="alert alert-success">Signature deleted.</div>';
    } else {
        $comment = '<div class="alert alert-danger">Signature not found.</div>';
    }
}

// Rename Logic
if (isset($_POST['rename_signature'])) {
    $id = (int)$_POST['sig_id'];
    $newname = trim($_POST['new_signature_name']);
    $stmt = $pdo->prepare("UPDATE signatures SET signature_name = ? WHERE id = ?");
    $stmt->execute([$newname, $id]);
    $comment = '<div class="alert alert-info">Signature renamed.</div>';
}

// Get all signatures
$signatures = $pdo->query("SELECT * FROM signatures ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signature Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link href="css/navthing.css" rel="stylesheet">
  <style>
    .signature-thumb { height: 60px; border: 1px solid #ccc; padding: 3px; background: #fff; }
    .actions { white-space: nowrap; }
  </style>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/header.php'); ?>
  <div class="container mt-4">
    <h3>Signature Manager</h3>
    <a href="dashboard.php" class="btn btn-sm btn-primary mb-3">Back to Dashboard</a>
    <?= $comment ?? '' ?>

    <!-- Upload New Signature -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
      <div class="form-group">
        <label for="signature_name">Signature Name:</label>
        <input type="text" name="signature_name" id="signature_name" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="signature_file">Upload Signature Image:</label>
        <input type="file" name="signature_file" id="signature_file" class="form-control-file" accept="image/*" required>
      </div>
      <button type="submit" name="upload_signature" class="btn btn-success">Upload Signature</button>
    </form>

    <!-- Signature Table -->
    <table class="table table-bordered table-striped">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>Preview</th>
          <th>Name</th>
          <th>Uploaded</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($signatures) == 0): ?>
          <tr><td colspan="5" class="text-center">No signatures found.</td></tr>
        <?php endif; ?>
        <?php foreach ($signatures as $index => $sig): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><img src="<?= $sig['signature_file'] ?>" class="signature-thumb" alt="Signature"></td>
            <td><?= htmlspecialchars($sig['signature_name']) ?></td>
            <td><?= date('M j, Y g:ia', strtotime($sig['created_at'])) ?></td>
            <td class="actions">
              <!-- Rename -->
              <form method="POST" class="form-inline d-inline-block">
                <input type="hidden" name="sig_id" value="<?= $sig['id'] ?>">
                <input type="text" name="new_signature_name" class="form-control form-control-sm mr-1" placeholder="Rename…" required>
                <button type="submit" name="rename_signature" class="btn btn-sm btn-info">Rename</button>
              </form>
              <!-- Delete -->
              <button
                type="button"
                class="btn btn-sm btn-danger"
                data-toggle="modal"
                data-target="#confirmDeleteModal"
                data-id="<?= $sig['id'] ?>"
                data-name="<?= htmlspecialchars($sig['signature_name']) ?>"
              >
                Delete
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
  
   <!-- Footer -->
      <?php include_once('inc/footer.php'); ?>
  
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="deleteMessage">Are you sure you want to delete this signature?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm">Delete</a>
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
