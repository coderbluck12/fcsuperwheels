<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Basic HTTP authentication for master admin
$valid_username = 'admin';
$valid_password = 'password123';
if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
    || $_SERVER['PHP_AUTH_USER'] !== $valid_username
    || $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    header('WWW-Authenticate: Basic realm="Protected Page"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized Access.';
    exit;
}

// Flash messages
$alerts = [
    'update-success' => 'Receipt visibility changed successfully',
    'delete-success' => 'Receipt deleted successfully',
    'edit-success'   => 'Receipt edited successfully',
    'delete-fail'    => 'Failed to delete',
    'edit-fail'      => 'Failed to edit'
];
$comment = '';
foreach ($alerts as $key => $msg) {
    if (isset($_GET[$key])) {
        $type = strpos($key, 'fail') !== false ? 'danger' : 'success';
        $comment = "<div class=\"alert alert-{$type}\">{$msg}</div>";
        break;
    }
}

// Search & pagination params
$search = trim($_GET['search'] ?? '');
$limit  = (int)($_GET['limit']  ?? 10);
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = '1';
$params = [];
if ($search !== '') {
    $where .= " AND (CONCAT(prefix_receipt_number,id) LIKE :s OR customer_name LIKE :s OR CONCAT(vehicle_make,' ',vehicle_model,' ',vehicle_year) LIKE :s)";
    $params[':s'] = "%{$search}%";
}

// Total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM main_receipt WHERE {$where}");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$total_pages = (int)ceil($total / $limit);

// Fetch paginated results
$stmt = $pdo->prepare("SELECT * FROM main_receipt WHERE {$where} ORDER BY id DESC LIMIT :l OFFSET :o");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':l', $limit, PDO::PARAM_INT);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT);
$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MASTER LIST OF ALL RECEIPTS</title>
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <style>
    .search-input { max-width:300px; }
    .noprint { display:block; }
    @media print { .noprint{ display:none!important; } }
  </style>
   <link href="css/navthing.css" rel="stylesheet" media="all">
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/menu.php'); ?>
  <div class="page-content--bgf7 watermark">
    <section class="au-breadcrumb2">
      <div class="container watermark">
        <div class="row">
          <div class="col-md-12">
            <div class="au-breadcrumb-content">
              <div class="au-breadcrumb-left">
                <span class="au-breadcrumb-span">You are here:</span>
                <ul class="list-inline au-breadcrumb__list mb-0">
                  <li class="list-inline-item"><a href="dashboard.php">General Dashboard</a></li>
                  <li class="list-inline-item seprate"><span>/</span></li>
				  
                  <li class="list-inline-item">Master Receipts</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
		<?php include_once('inc/admin_menu.php'); ?>
      </div>
    </section>
    <div class="container watermark mt-4">
      <?= $comment ?>
      <h2 class="mb-3">Master List of Receipts</h2>
      <form id="searchForm" class="form-inline mb-3">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               class="form-control search-input mr-2" placeholder="Search…" onkeyup="debounceSubmit()">
        <label class="mr-2">Per page:</label>
        <select name="limit" class="form-control mr-auto" onchange="this.form.submit()">
          <?php foreach ([10,20,50,100] as $n): ?>
            <option value="<?= $n ?>" <?= $limit==$n?'selected':'' ?>><?= $n ?></option>
          <?php endforeach ?>
        </select>
        <input type="hidden" name="page" id="pageInput" value="<?= $page ?>">
      </form>
      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>#</th><th>No</th><th>Customer</th><th>Vehicle</th><th>Type</th>
              <th>Created</th><th>Paid On</th><th class="text-right">Amount</th>
              <th>Actions</th><th>Visibility</th>
            </tr>
          </thead>
          <tbody>
            <?php $cnt=$offset+1; foreach ($receipts as $r):
              $key = urlencode(encryptData($r['id'],'31081990'));
              $icon = $r['visibility']=='yes' ? 'on.png' : 'off.png';
              $func = $r['visibility']=='yes' ? 'openHideModal' : 'openShowModal';
            ?>
            <tr>
              <td><?= $cnt++ ?></td>
              <td><?= htmlspecialchars($r['prefix_receipt_number'].$r['id']) ?></td>
              <td><?= htmlspecialchars($r['customer_name']) ?></td>
              <td><?= htmlspecialchars("{$r['vehicle_make']} {$r['vehicle_model']} {$r['vehicle_year']}") ?></td>
              <td><?= ucfirst($r['payment_type']) ?></td>
              <td><?= date('M j, Y', strtotime($r['time_created'])) ?></td>
              <td><?= date('M j, Y', strtotime($r['payment_date'])) ?></td>
              <td class="text-right"><?= number_format($r['amount_paid'],2) ?></td>
              <td class="noprint">
                <a href="master_view_receipt.php?prefix_receipt_number=<?= $key ?>" class="btn btn-success btn-sm">View</a>
                <a href="mastermodifyreceipt.php?prefix_receipt_number=<?= $key ?>" class="btn btn-warning btn-sm">Edit</a>
               <a href="#"
                       class="btn btn-danger btn-sm delete-btn"
                       data-toggle="modal"
                       data-target="#deleteModal"
                       data-href="master_delete_receipt.php?prefix_receipt_number=<?= $key ?>"
                    >Delete</a>
              </td>
              <td class="text-center"><a href="javascript:void(0)" onclick="<?= $func ?>('<?= $key ?>')"><img src="<?= $icon ?>" width="30"></a></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
	  <br />
      <nav class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <button class="btn btn-outline-secondary btn-sm" onclick="goPage(<?= max(1,$page-1) ?>)" <?= $page<=1?'disabled':'' ?>>&laquo; Prev</button>
        </div>
        <ul class="pagination mb-0">

        <?php for ($i=1; $i<=$total_pages; $i++): ?>
          <li class="page-item <?= $i==$page?'active':'' ?>">
            <a class="page-link" href="?<?= http_build_query(['search'=>$search,'limit'=>$limit,'page'=>$i]) ?>"><?= $i ?></a>
          </li>
        <?php endfor ?>
              </ul>
        <div>
          <button class="btn btn-outline-secondary btn-sm" onclick="goPage(<?= min($total_pages,$page+1) ?>)" <?= $page>=$total_pages?'disabled':'' ?>>Next &raquo;</button>
        </div>
      </nav>
    </div>
  </div>
     <!-- Footer -->
        <div class="row mt-4">
          <div class="col-12">
            <?php include_once('inc/footer.php'); ?>
          </div>
        </div>
</div>


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


<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script>
  let timer;
  function goPage(p){
    document.getElementById('pageInput').value = p;
    document.getElementById('searchForm').submit();
  }
  
  function debounceSubmit() { clearTimeout(timer); timer=setTimeout(()=>document.getElementById('searchForm').submit(),400); }
  function confirmDelete(url) { if(confirm('Confirm delete?')) window.location=url; }
  function openHideModal(id) { window.location='hide_receipt.php?prefix_receipt_number='+id; }
  function openShowModal(id) { window.location='unhide_receipt.php?prefix_receipt_number='+id; }
</script>


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
