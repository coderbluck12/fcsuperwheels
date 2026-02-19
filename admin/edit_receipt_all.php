<?php
// edit_receipt_all.php

include_once('inc/session_manager.php');
include_once('inc/functions.php');

// 1) Get parameters
$search = trim($_GET['search'] ?? '');
$limit  = (int)($_GET['limit']  ?? 10);
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// 2) Build WHERE clause & params
$where  = "`visibility`='yes'";
$params = [];
if ($search !== '') {
    $where .= " AND (
      CONCAT(prefix_receipt_number, id) LIKE :s OR
      customer_name                        LIKE :s OR
      CONCAT(vehicle_make,' ',vehicle_model,' ',vehicle_year) LIKE :s
    )";
    $params[':s'] = "%$search%";
}

// 3) Total count
$totalQ = "SELECT COUNT(*) FROM main_receipt WHERE $where";
$stmt   = $pdo->prepare($totalQ);
$stmt->execute($params);
$total  = (int)$stmt->fetchColumn();
$total_pages = (int)ceil($total / $limit);

// 4) Fetch receipts page
$dataQ = "SELECT * FROM main_receipt
          WHERE $where
          ORDER BY id DESC
          LIMIT :l OFFSET :o";
$stmt = $pdo->prepare($dataQ);
foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
$stmt->bindValue(':l', $limit,  PDO::PARAM_INT);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT);
$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Flash messages
$comment = '';
if (isset($_GET['delete-success'])) $comment = ['type'=>'success','msg'=>'Receipt deleted successfully.'];
if (isset($_GET['edit-success']))   $comment = ['type'=>'success','msg'=>'Receipt updated successfully.'];
if (isset($_GET['delete-fail']))    $comment = ['type'=>'danger', 'msg'=>'Failed to delete receipt.'];
if (isset($_GET['edit-fail']))      $comment = ['type'=>'danger', 'msg'=>'Failed to edit receipt.'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Receipts | FC Superwheels</title>
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-4.7/css/font-awesome.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/navthing.css">
  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --success: #059669;
      --danger: #dc2626;
      --warning: #d97706;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-900: #111827;
    }

    .receipts-wrapper {
      padding: 2rem;
      max-width: 1300px;
      margin: 0 auto;
    }

    .page-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1.25rem;
      border-bottom: 3px solid var(--warning);
      margin-top: 80px;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-topbar h1 {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--gray-900);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.625rem;
    }

    .page-topbar h1 i { color: var(--warning); }

    .topbar-actions { display: flex; gap: 0.625rem; flex-wrap: wrap; }

    .btn-dash {
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
    .btn-dash:hover { background: var(--primary-dark); color: white; text-decoration: none; }

    .btn-new {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      background: var(--success);
      color: white;
      border: none;
      padding: 0.6rem 1.125rem;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.875rem;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-new:hover { background: #047857; color: white; text-decoration: none; }

    .alert-custom {
      border-radius: 8px;
      padding: 0.875rem 1.25rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
      font-size: 0.9375rem;
    }
    .alert-custom.success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
    .alert-custom.danger  { background: #fee2e2; color: #7f1d1d; border-left: 4px solid var(--danger); }

    .controls-bar {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
    }

    .search-box {
      position: relative;
      flex: 1;
      min-width: 200px;
      max-width: 400px;
    }

    .search-box i {
      position: absolute;
      left: 0.875rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray-600);
      font-size: 0.875rem;
    }

    .search-box input {
      width: 100%;
      padding: 0.65rem 0.875rem 0.65rem 2.25rem;
      border: 1.5px solid var(--gray-300);
      border-radius: 7px;
      font-size: 0.9375rem;
      transition: all 0.2s;
      background: white;
    }

    .search-box input:focus {
      border-color: var(--warning);
      box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
      outline: none;
    }

    .perpage-select {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: var(--gray-600);
    }

    .perpage-select select {
      border: 1.5px solid var(--gray-300);
      border-radius: 6px;
      padding: 0.55rem 0.75rem;
      font-size: 0.875rem;
      background: white;
      cursor: pointer;
    }

    .nav-btns { display: flex; gap: 0.375rem; margin-left: auto; }

    .nav-btn {
      background: white;
      border: 1.5px solid var(--gray-300);
      border-radius: 6px;
      padding: 0.55rem 0.875rem;
      color: var(--gray-700);
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .nav-btn:hover:not(:disabled) { background: var(--gray-100); }
    .nav-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    .table-card {
      background: white;
      border: 1px solid var(--gray-200);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
      margin-bottom: 1.5rem;
    }

    /* Edit mode banner */
    .edit-mode-banner {
      background: linear-gradient(90deg, #fef3c7 0%, #fffbeb 100%);
      border-bottom: 1px solid #fde68a;
      padding: 0.75rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.625rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: #92400e;
    }

    .receipts-table {
      width: 100%;
      border-collapse: collapse;
    }

    .receipts-table thead th {
      background: var(--gray-50);
      color: var(--gray-600);
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      padding: 0.875rem 1rem;
      border-bottom: 2px solid var(--gray-200);
      text-align: left;
      white-space: nowrap;
    }

    .receipts-table thead th.amount-col { text-align: right; }

    .receipts-table tbody tr { transition: background 0.15s; }
    .receipts-table tbody tr:hover { background: #fffbeb; }
    .receipts-table tbody tr:not(:last-child) { border-bottom: 1px solid var(--gray-100); }

    .receipts-table tbody td {
      padding: 0.875rem 1rem;
      font-size: 0.875rem;
      color: var(--gray-700);
      vertical-align: middle;
    }

    .receipt-no {
      font-weight: 700;
      color: var(--primary);
      font-family: monospace;
      font-size: 0.875rem;
    }

    .customer-name {
      font-weight: 600;
      color: var(--gray-900);
    }

    .vehicle-text { font-size: 0.8125rem; color: var(--gray-600); }

    .type-badge {
      display: inline-block;
      padding: 0.25rem 0.625rem;
      border-radius: 9999px;
      font-size: 0.71rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .type-full        { background: #d1fae5; color: #065f46; }
    .type-installment { background: #fef3c7; color: #92400e; }

    .date-text { font-size: 0.78rem; color: var(--gray-600); }

    .amount-text {
      text-align: right;
      font-weight: 700;
      color: var(--gray-900);
      font-size: 0.9375rem;
    }

    .btn-edit-r {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.45rem 0.875rem;
      border-radius: 6px;
      font-size: 0.8125rem;
      font-weight: 700;
      text-decoration: none;
      border: none;
      cursor: pointer;
      background: #fef3c7;
      color: var(--warning);
      transition: all 0.15s;
    }
    .btn-edit-r:hover { background: #fde68a; color: #b45309; text-decoration: none; }

    .pagination-bar {
      display: flex;
      justify-content: center;
      gap: 0.375rem;
      flex-wrap: wrap;
      margin-top: 0.5rem;
    }

    .page-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      color: var(--gray-700);
      border: 1.5px solid var(--gray-200);
      background: white;
      transition: all 0.15s;
    }
    .page-num:hover { background: var(--gray-100); text-decoration: none; }
    .page-num.active { background: var(--warning); color: white; border-color: var(--warning); }

    .results-info {
      font-size: 0.8125rem;
      color: var(--gray-600);
      text-align: center;
      margin-top: 0.75rem;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: var(--gray-600);
    }
    .empty-state .empty-icon { font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; }

    .modal-content { border-radius: 10px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-header.danger-header { background: var(--danger); color: white; border-radius: 10px 10px 0 0; }
    .modal-header.danger-header .close { color: white; opacity: 0.85; }

    @media (max-width: 768px) {
      .receipts-wrapper { padding: 1rem; }
      .controls-bar { flex-direction: column; align-items: stretch; }
      .search-box { max-width: 100%; }
      .nav-btns { margin-left: 0; }
    }
  </style>
</head>
<body>
  <div class="page-wrapper">
    <?php include_once('inc/header.php'); ?>
    <div class="page-content--bgf7">
      <div class="receipts-wrapper">

        <!-- Page Header -->
        <div class="page-topbar">
          <h1>
            <i class="fas fa-pen-square"></i>
            Edit Receipts
          </h1>
          <div class="topbar-actions">
            <a href="dashboard.php" class="btn-dash">
              <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <a href="newreceipt.php" class="btn-new">
              <i class="fas fa-plus"></i> New Receipt
            </a>
          </div>
        </div>

        <!-- Flash Message -->
        <?php if ($comment): ?>
          <div class="alert-custom <?= $comment['type'] ?>">
            <i class="fas fa-<?= $comment['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($comment['msg']) ?>
          </div>
        <?php endif; ?>

        <!-- Controls Bar -->
        <form id="searchForm">
          <div class="controls-bar">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search by receipt no, customer, vehicle…"
                onkeyup="debounceSubmit()"
              >
            </div>
            <div class="perpage-select">
              <span>Show:</span>
              <select name="limit" onchange="this.form.submit()">
                <?php foreach ([10,20,50,100] as $n): ?>
                  <option value="<?= $n ?>" <?= $limit==$n?'selected':'' ?>><?= $n ?></option>
                <?php endforeach ?>
              </select>
              <span>per page</span>
            </div>
            <div class="nav-btns">
              <input type="hidden" name="page" id="pageInput" value="<?= $page ?>">
              <button type="button" class="nav-btn" onclick="changePage(<?= $page-1 ?>)" <?= $page<=1?'disabled':'' ?>>
                &laquo; Prev
              </button>
              <button type="button" class="nav-btn" onclick="changePage(<?= $page+1 ?>)" <?= $page>=$total_pages?'disabled':'' ?>>
                Next &raquo;
              </button>
            </div>
          </div>
        </form>

        <!-- Table Card -->
        <div class="table-card">
          <div class="edit-mode-banner">
            <i class="fas fa-info-circle"></i>
            Click <strong>Edit</strong> next to any receipt to modify its details.
          </div>

          <?php if (empty($receipts)): ?>
            <div class="empty-state">
              <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
              <p style="font-weight: 600; font-size: 1rem; color: var(--gray-700); margin-bottom: 0.25rem;">No receipts found</p>
              <p style="font-size: 0.875rem;">Try adjusting your search or create a new receipt.</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table class="receipts-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Receipt No</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Paid On</th>
                    <th class="amount-col">Amount</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="receiptsTable">
                <?php
                  $count = $offset + 1;
                  foreach ($receipts as $r):
                    $key = urlencode(encryptData($r['id'],'31081990'));
                    $vehicle = "{$r['vehicle_make']} {$r['vehicle_model']} {$r['vehicle_year']}";
                    $created = date('M j, Y g:i a', strtotime($r['time_created']));
                    $paid = date('M j, Y', strtotime($r['payment_date']));
                    $amt = '₦' . number_format($r['amount_paid'],2,'.',',');
                    $typeClass = $r['payment_type'] === 'full' ? 'type-full' : 'type-installment';
                ?>
                  <tr>
                    <td style="color: var(--gray-600); font-weight: 600;"><?= $count++ ?></td>
                    <td><span class="receipt-no"><?= htmlspecialchars($r['prefix_receipt_number'].$r['id']) ?></span></td>
                    <td><span class="customer-name"><?= htmlspecialchars($r['customer_name']) ?></span></td>
                    <td><span class="vehicle-text"><?= htmlspecialchars($vehicle) ?></span></td>
                    <td><span class="type-badge <?= $typeClass ?>"><?= ucfirst($r['payment_type']) ?></span></td>
                    <td><span class="date-text"><?= $created ?></span></td>
                    <td><span class="date-text"><?= $paid ?></span></td>
                    <td><span class="amount-text"><?= $amt ?></span></td>
                    <td>
                      <a href="modifyreceipt.php?prefix_receipt_number=<?= $key ?>" class="btn-edit-r">
                        <i class="fas fa-pen"></i> Edit
                      </a>
                    </td>
                  </tr>
                <?php endforeach ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
          <nav class="pagination-bar">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
              <a class="page-num <?= $i==$page?'active':'' ?>"
                 href="?<?= http_build_query(['search'=>$search,'limit'=>$limit,'page'=>$i]) ?>">
                <?= $i ?>
              </a>
            <?php endfor ?>
          </nav>
        <?php endif; ?>
        <p class="results-info">
          Showing <?= $offset+1 ?>–<?= min($offset+$limit, $total) ?> of <?= $total ?> receipts
        </p>

        <!-- Footer -->
        <div class="row mt-4">
          <div class="col-12">
            <?php include_once('inc/footer.php'); ?>
          </div>
        </div>

      </div><!-- /.receipts-wrapper -->
    </div><!-- /.page-content -->

    <!-- Delete Modal (kept for JS consistency) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header danger-header">
            <h5 class="modal-title" id="deleteModalLabel">
              <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 1.5rem;">
            <p style="margin:0; color: var(--gray-700);">Are you sure you want to delete this receipt?<br>
            <small style="color: var(--gray-600);">This action cannot be undone.</small></p>
          </div>
          <div class="modal-footer" style="padding: 1rem 1.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm">
              <i class="fas fa-trash-alt mr-1"></i>Delete
            </a>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.page-wrapper -->

  <!-- Scripts -->
  <script src="vendor/jquery-3.2.1.min.js"></script>
  <script src="vendor/bootstrap-4.1/popper.min.js"></script>
  <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
  <script>
    let _timer;
    function debounceSubmit(){
      clearTimeout(_timer);
      _timer = setTimeout(()=> document.getElementById('searchForm').submit(), 400);
    }
    function changePage(p){
      if(p<1) p=1;
      if(p> <?= $total_pages ?>) p=<?= $total_pages ?>;
      document.getElementById('pageInput').value = p;
      document.getElementById('searchForm').submit();
    }
    $(function(){
      $('.delete-btn').on('click', function(){
        let href = $(this).data('href');
        $('#confirmDeleteBtn').attr('href', href);
      });
    });
  </script>
</body>
</html>
