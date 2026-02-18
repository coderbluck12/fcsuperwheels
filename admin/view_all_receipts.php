<?php
// view_all_receipts.php

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
if (isset($_GET['delete-success'])) $comment = '<div class="alert alert-success">Receipt deleted successfully</div>';
if (isset($_GET['edit-success']))   $comment = '<div class="alert alert-success">Receipt edited successfully</div>';
if (isset($_GET['delete-fail']))    $comment = '<div class="alert alert-danger">Failed to delete</div>';
if (isset($_GET['edit-fail']))      $comment = '<div class="alert alert-danger">Failed to edit</div>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>List of All Receipts</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <!-- FontAwesome -->
  <link rel="stylesheet" href="vendor/font-awesome-4.7/css/font-awesome.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <!-- Theme CSS -->
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/navthing.css">
  <style>
    body { background: #f8f9fa; }
    .container-lg { max-width:1200px; margin:1.5rem auto; }
    .search-input { max-width:400px; }
    .action-btn { margin-right:4px; }
    .pagination { justify-content:center; }
  </style>
</head>
<body>
  <div class="page-wrapper">
    <?php include_once('inc/header.php'); ?>
    <div class="page-content--bgf7">
      <div class="container-lg">

        <!-- Breadcrumb + Buttons -->
        <div class="d-flex flex-wrap align-items-center mb-3">
          <nav aria-label="breadcrumb" class="flex-grow-1">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">All Receipts</li>
            </ol>
          </nav>
          <div>
            <a href="dashboard.php" class="btn btn-sm btn-primary">Back to Dashboard</a>
            <a href="newreceipt.php" class="btn btn-sm btn-warning text-dark">New Receipt</a>
          </div>
        </div>

        <!-- Flash Message -->
        <?= $comment ?>

        <!-- Search & Controls Form -->
        <form id="searchForm" class="form-inline mb-3">
          <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            class="form-control mr-2 search-input"
            placeholder="Search receipts…"
            onkeyup="debounceSubmit()"
          >
          <label class="mr-2">Per page:</label>
          <select
            name="limit"
            class="form-control mr-auto"
            onchange="this.form.submit()"
          >
            <?php foreach ([10,20,50,100] as $n): ?>
              <option value="<?= $n ?>" <?= $limit==$n?'selected':'' ?>><?= $n ?></option>
            <?php endforeach ?>
          </select>
          <input type="hidden" name="page" id="pageInput" value="<?= $page ?>">
          <div class="btn-group btn-group-sm">
            <button
              type="button"
              class="btn btn-outline-secondary"
              onclick="changePage(<?= $page-1 ?>)"
              <?= $page<=1?'disabled':'' ?>
            >&laquo; Prev</button>
            <button
              type="button"
              class="btn btn-outline-secondary"
              onclick="changePage(<?= $page+1 ?>)"
              <?= $page>=$total_pages?'disabled':'' ?>
            >Next &raquo;</button>
          </div>
        </form>

        <!-- Receipts Table -->
        <div class="table-responsive">
          <table class="table table-striped table-bordered bg-white">
            <thead class="thead-dark">
              <tr>
                <th>#</th><th>Receipt No</th><th>Customer</th>
                <th>Vehicle</th><th>Type</th><th>Created</th>
                <th>Paid On</th><th class="text-right">Amount</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="receiptsTable">
            <?php
              $count = $offset + 1;
              foreach ($receipts as $r):
                $key = urlencode(encryptData($r['id'],'31081990'));
                $vehicle = "{$r['vehicle_make']} {$r['vehicle_model']} {$r['vehicle_year']}";
                $created = date('F j, Y, g:i a', strtotime($r['time_created']));
                $paid = date('F j, Y, g:i a', strtotime($r['payment_date']));
                $amt = number_format($r['amount_paid'],2,'.',',');
            ?>
              <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($r['prefix_receipt_number'].$r['id']) ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($vehicle) ?></td>
                <td><?= ucfirst($r['payment_type']) ?></td>
                <td><?= $created ?></td>
                <td><?= $paid ?></td>
                <td class="text-right"><?= $amt ?></td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="view_receipt.php?prefix_receipt_number=<?= $key ?>" class="btn btn-outline-success">View</a>
                    <a href="modifyreceipt.php?prefix_receipt_number=<?= $key ?>" class="btn btn-outline-warning">Edit</a>
                    <a href="#"
                       class="btn btn-outline-danger delete-btn"
                       data-toggle="modal"
                       data-target="#deleteModal"
                       data-href="delete_receipt.php?prefix_receipt_number=<?= $key ?>"
                    >Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
            </tbody>
          </table>
        </div>
		<br />
        <!-- Pagination Links -->
        <nav>
          <ul class="pagination">
          <?php for($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>">
              <a class="page-link"
                 href="?<?= http_build_query(['search'=>$search,'limit'=>$limit,'page'=>$i]) ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor ?>
          </ul>
        </nav>
   <!-- Footer -->
        <div class="row mt-4">
          <div class="col-12">
            <?php include_once('inc/footer.php'); ?>
          </div>
        </div>
      </div> <!-- /.container-lg -->
    </div> <!-- /.page-content -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
         aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger" id="deleteModalLabel">Confirm Delete</h5>
            <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this receipt?<br>This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm"
                    data-dismiss="modal">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm">Delete</a>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /.page-wrapper -->

  <!-- Scripts -->
  <script src="vendor/jquery-3.2.1.min.js"></script>
  <script src="vendor/bootstrap-4.1/popper.min.js"></script>
  <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
  <script>
    // Debounce search
    let _timer;
    function debounceSubmit(){
      clearTimeout(_timer);
      _timer = setTimeout(()=> document.getElementById('searchForm').submit(), 400);
    }
    // Change page
    function changePage(p){
      if(p<1) p=1;
      if(p> <?= $total_pages ?>) p=<?= $total_pages ?>;
      document.getElementById('pageInput').value = p;
      document.getElementById('searchForm').submit();
    }
    // Modal delete hookup
    $(function(){
      $('.delete-btn').on('click', function(){
        let href = $(this).data('href');
        $('#confirmDeleteBtn').attr('href', href);
      });
    });
  </script>
</body>
</html>
