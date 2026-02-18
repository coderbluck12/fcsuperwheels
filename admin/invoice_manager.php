<?php
// invoice_manager.php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// — Basic HTTP auth for master admin
$valid_username = 'admin';
$valid_password = 'password123';
if (!isset($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])
  || $_SERVER['PHP_AUTH_USER']!==$valid_username
  || $_SERVER['PHP_AUTH_PW']!==$valid_password
) {
    header('WWW-Authenticate: Basic realm="Protected Page"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Unauthorized Access.');
}

// — Flash messages
$alerts = [
  'update-success'=>'Invoice visibility changed',
  'delete-success'=>'Invoice deleted',
  'edit-success'  =>'Invoice edited',
  'delete-fail'   =>'Failed to delete',
  'edit-fail'     =>'Failed to edit'
];
$comment = '';
foreach($alerts as $k=>$m){
  if(isset($_GET[$k])){
    $type = strpos($k,'fail')!==false?'danger':'success';
    $comment = "<div class=\"alert alert-{$type}\">{$m}</div>";
    break;
  }
}

// — Search & pagination
$search = trim($_GET['search']??'');
$limit  = (int)($_GET['limit']??10);
$page   = max(1,(int)($_GET['page']??1));
$offset = ($page-1)*$limit;

// — WHERE clause
$where = '1';
$params = [];
if($search!==''){
  $where .= " AND (
    CONCAT(prefix_invoice_number,id) LIKE :s OR
    customer_name LIKE :s OR
    CONCAT(vehicle_make,' ',vehicle_model,' ',vehicle_year) LIKE :s
  )";
  $params[':s'] = "%{$search}%";
}

// — total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM main_invoice WHERE {$where}");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$total_pages = ceil($total/$limit);

// — fetch page
$sql = "SELECT * FROM main_invoice
        WHERE {$where}
        ORDER BY id DESC
        LIMIT :l OFFSET :o";
$stmt = $pdo->prepare($sql);
foreach($params as $k=>$v) $stmt->bindValue($k,$v,PDO::PARAM_STR);
$stmt->bindValue(':l',$limit,PDO::PARAM_INT);
$stmt->bindValue(':o',$offset,PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MASTER LIST OF ALL INVOICES</title>
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/navthing.css">
  <style>
    .search-input{max-width:300px;}
    .noprint{display:block;}
    @media print{.noprint{display:none!important;}}
  </style>
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
                  <li class="list-inline-item">Master Invoices</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <!-- optional extra admin menu -->
        <?php  include_once('inc/admin_menu.php'); ?>
      </div>
    </section>

    <div class="container watermark mt-4">
      <?= $comment ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Master List of Invoices</h2>
        <div class="noprint">
          <a href="new_invoice.php" class="btn btn-sm btn-warning">New Master Invoice</a>
        </div>
      </div>

      <form id="searchForm" class="form-inline mb-3">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               class="form-control search-input mr-2" placeholder="Search…" onkeyup="debounceSubmit()">
        <label class="mr-2">Per page:</label>
        <select name="limit" class="form-control mr-auto" onchange="this.form.submit()">
          <?php foreach([10,20,50,100] as $n): ?>
            <option value="<?=$n?>" <?=$limit==$n?'selected':''?>><?=$n?></option>
          <?php endforeach ?>
        </select>
        <input type="hidden" name="page" id="pageInput" value="<?=$page?>">
      </form>

      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>No</th>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Invoice Date</th>
              <th>Due Date</th>
              <th class="text-right">Total</th>
              <th class="noprint">Actions</th>
              <th>Vis.</th>
            </tr>
          </thead>
          <tbody>
          <?php $cnt=$offset+1; foreach($invoices as $inv):
            $key  = urlencode(encryptData($inv['id'],'31081990'));
            $icon = $inv['visibility']==='yes' ? 'on.png' : 'off.png';
            $func = $inv['visibility']==='yes' ? 'openHideModal' : 'openShowModal';
          ?>
            <tr>
              <td><?= $cnt++ ?></td>
              <td><?= htmlspecialchars($inv['prefix_invoice_number'].$inv['id']) ?></td>
              <td><?= htmlspecialchars($inv['customer_name']) ?></td>
              <td><?= htmlspecialchars("{$inv['vehicle_make']} {$inv['vehicle_model']} {$inv['vehicle_year']}") ?></td>
              <td><?= date('M j, Y',strtotime($inv['invoice_date'])) ?></td>
              <td class="text-center"><?php
  $due = $inv['due_date'];
  if ($due && $due !== '0000-00-00 00:00:00') {
      echo date('M j, Y', strtotime($due));
  } else {
      echo '-'; // or “—” if you prefer
  }
?>
</td>
              <td class="text-right"><?= number_format($inv['total_amount'],2) ?></td>
              <td class="noprint">
                <a href="view_invoice.php?prefix_invoice_number=<?=$key?>" class="btn btn-success btn-sm">View</a>
                <a href="invoice_edit.php?prefix_invoice_number=<?=$key?>" class="btn btn-warning btn-sm">Edit</a>
                <button
  class="btn btn-danger btn-sm"
  data-toggle="modal"
  data-target="#confirmDeleteModal"
  data-href="delete_invoice.php?prefix_invoice_number=<?= $key ?>"
>
  Delete
</button>

              </td>
              <td class="text-center">
                <a href="javascript:void(0)" onclick="<?=$func?>('<?=$key?>')">
                  <img src="<?=$icon?>" width="24">
                </a>
              </td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
      </div>

      <!-- pagination -->
      <nav class="d-flex justify-content-between align-items-center mt-3">
        <button class="btn btn-outline-secondary btn-sm" onclick="goPage(<?=max(1,$page-1)?>)"
                <?=$page<=1?'disabled':''?>>
          &laquo; Prev
        </button>
        <ul class="pagination mb-0">
          <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?=$i==$page?'active':''?>">
              <a class="page-link" href="?<?= http_build_query(['search'=>$search,'limit'=>$limit,'page'=>$i]) ?>">
                <?=$i?>
              </a>
            </li>
          <?php endfor ?>
        </ul>
        <button class="btn btn-outline-secondary btn-sm" onclick="goPage(<?=min($total_pages,$page+1)?>)"
                <?=$page>=$total_pages?'disabled':''?>>
          Next &raquo;
        </button>
      </nav>

    </div><!-- /.container -->
  </div><!-- /.page-content -->

  <?php include_once('inc/footer.php'); ?>
</div><!-- /.page-wrapper -->
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
     aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger" id="confirmDeleteLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this invoice?<br>
        This action <strong>cannot</strong> be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm"
                data-dismiss="modal">Cancel</button>
        <a href="#" id="deleteConfirmBtn" class="btn btn-danger btn-sm">Delete</a>
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
  function debounceSubmit(){
    clearTimeout(timer);
    timer = setTimeout(()=>$('#searchForm').submit(),300);
  }
  function confirmDelete(url){
    if(confirm('Delete this invoice forever?')) window.location = url;
  }
  function openHideModal(id){
    window.location = 'invoice_hide.php?prefix_invoice_number=' + id;
  }
  function openShowModal(id){
    window.location = 'invoice_unhide.php?prefix_invoice_number=' + id;
  }
</script>
<script>
  $('#confirmDeleteModal').on('show.bs.modal', function (e) {
    // Button that triggered the modal
    var btn = $(e.relatedTarget);
    // Extract the URL to call
    var href = btn.data('href');
    // Update the modal's Delete link
    $(this).find('#deleteConfirmBtn').attr('href', href);
  });
</script>

</body>
</html>
