<?php
include_once('inc/session_manager.php');
include_once('inc/access_log.php');

// Log dashboard access
log_access('VIEW_DASHBOARD', 'dashboard.php');

// Fetching all receipts
$num_invoices_all    = $pdo->query("SELECT COUNT(*) FROM main_receipt WHERE visibility='yes'")->fetchColumn();
$num_invoices_paid   = $pdo->query("SELECT COUNT(*) FROM main_receipt WHERE payment_type='full' AND visibility='yes'")->fetchColumn();
$num_invoices_unpaid = $pdo->query("SELECT COUNT(*) FROM main_receipt WHERE payment_type='installment' AND visibility='yes'")->fetchColumn();
$num_admin           = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();

if (isset($_GET['inf'])) {
  $comment = '<div class="alert alert-danger">Receipt number not found</div>';
}

// Prepare last-30-days data for line chart
$dates_last_30  = [];
$counts_last_30 = [];
for ($i = 29; $i >= 0; $i--) {
  $d = (new DateTime("-{$i} days"))->format('Y-m-d');
  $dates_last_30[]  = (new DateTime($d))->format('M j');
  $stmt = $pdo->prepare(
    "SELECT COUNT(*) 
       FROM main_receipt 
      WHERE visibility='yes' 
        AND DATE(time_created)=?"
  );
  $stmt->execute([$d]);
  $counts_last_30[] = (int)$stmt->fetchColumn();
}

// Set path_to_root for header/footer includes
$path_to_root = '';
include('inc/header.php');
?>

<style>
  .dashboard-container {
    padding: 2rem;
    max-width: 100%;
  }
  
  .announcement {
    background: #38880B;
    color: #fff;
    border-radius: .5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
  }
  
  .stat-card {
    border: none;
    border-radius: .5rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    transition: transform .2s;
    background: white;
  }
  
  .stat-card:hover {
    transform: translateY(-4px);
  }
  
  .stat-card .card-body {
    display: flex;
    align-items: center;
    padding: 1.25rem;
  }
  
  .stat-card .fa {
    font-size: 2rem;
    margin-right: .75rem;
  }
  
  .stat-number {
    font-size: 2.25rem;
    margin: 0;
    font-weight: 700;
  }
  
  .stat-label {
    color: #666;
    font-size: .875rem;
    margin: 0;
  }
  
  .menu-card {
    background: #fff;
    border-radius: .5rem;
    padding: 1rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .menu-card .nav-link {
    color: #333;
    margin-bottom: .5rem;
    border-radius: .25rem;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .menu-card .nav-link.active,
  .menu-card .nav-link:hover {
    background: #007bff;
    color: #fff;
  }
  
  .chart-card {
    background: white;
    border-radius: .5rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    overflow: hidden;
  }
  
  .chart-card .card-header {
    background: #f8f9fa;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
  }
  
  .chart-card .card-body {
    padding: 1.5rem;
  }
  
  /* Breadcrumb styling */
  .au-breadcrumb2 {
    background: white;
    padding: 1rem;
    border-radius: .5rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
  }
  
  .breadcrumb {
    margin: 0;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .dashboard-container {
      padding: 1rem;
    }
    
    .stat-number {
      font-size: 1.75rem;
    }
  }
</style>

<div class="dashboard-container">
  <!-- Breadcrumb + Search -->
  <section class="au-breadcrumb2 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Admin Dashboard</li>
        </ol>
      </nav>
      <?php include_once('inc/invoice_search.php'); ?>
    </div>
    <?php if (!empty($comment)) echo '<div class="mt-2">'.$comment.'</div>'; ?>
  </section>

  <!-- Announcement -->
  <div class="announcement">
    <strong>Welcome <?php echo $the_user ?? 'Admin'; ?>!</strong> Manage receipts efficiently and keep track of your transactions.
  </div>

  <!-- KPI Cards -->
  <div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <i class="fa fa-file-text text-primary"></i>
          <div>
            <p class="stat-number"><?= $num_invoices_all ?></p>
            <p class="stat-label">Total Receipts</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <i class="fa fa-check-circle text-success"></i>
          <div>
            <p class="stat-number"><?= $num_invoices_paid ?></p>
            <p class="stat-label">Full Payments</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <i class="fa fa-hourglass-half text-warning"></i>
          <div>
            <p class="stat-number"><?= $num_invoices_unpaid ?></p>
            <p class="stat-label">Installment Payments</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <i class="fa fa-users text-info"></i>
          <div>
            <p class="stat-number"><?= $num_admin ?></p>
            <p class="stat-label">Total Users</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions Menu -->
  <div class="row">
    <div class="col-12">
      <div class="menu-card">
        <h5 class="mb-3" style="font-weight: 600; color: #374151;">
          <i class="fa fa-bolt"></i> Quick Actions
        </h5>
        <div class="row">
          <div class="col-md-6 col-lg-4">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" href="newreceipt.php">
                  <i class="fa fa-plus-circle"></i> Generate new receipt
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="view_all_receipts.php">
                  <i class="fa fa-eye"></i> View receipts
                </a>
              </li>
            </ul>
          </div>
          <div class="col-md-6 col-lg-4">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" href="edit_receipt_all.php">
                  <i class="fa fa-edit"></i> Edit receipts
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="delete_receipt_all.php">
                  <i class="fa fa-trash"></i> Delete receipt
                </a>
              </li>
            </ul>
          </div>
          <div class="col-md-6 col-lg-4">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" href="signature_manager.php">
                  <i class="fa fa-pencil"></i> Signature manager
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row mb-4">
    <!-- 30-Day Line Chart -->
    <div class="col-lg-6 mb-4">
      <div class="chart-card">
        <div class="card-header">
          <i class="fa fa-line-chart"></i> Receipts (Last 30 Days)
        </div>
        <div class="card-body">
          <canvas id="chartReceipts30" height="250"></canvas>
        </div>
      </div>
    </div>

    <!-- Doughnut Chart: Payment Type Split -->
    <div class="col-lg-6 mb-4">
      <div class="chart-card">
        <div class="card-header">
          <i class="fa fa-pie-chart"></i> Payment Type Distribution
        </div>
        <div class="card-body">
          <canvas id="chartPaymentType" height="250"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- jQuery UI for autocomplete -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
  // Line Chart (30 days)
  new Chart(document.getElementById('chartReceipts30'), {
    type: 'line',
    data: {
      labels: <?= json_encode($dates_last_30) ?>,
      datasets: [{
        label: 'Receipts',
        data: <?= json_encode($counts_last_30) ?>,
        fill: false,
        borderColor: '#007bff',
        backgroundColor: '#007bff',
        tension: 0.3,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });

  // Doughnut Chart (payment split)
  new Chart(document.getElementById('chartPaymentType'), {
    type: 'doughnut',
    data: {
      labels: ['Full Payments', 'Installments'],
      datasets: [{
        data: [<?= $num_invoices_paid ?>, <?= $num_invoices_unpaid ?>],
        backgroundColor: ['#28a745','#ffc107'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });

  // Autocomplete for search
  $(function(){
    $("#searchInput").autocomplete({
      source: function(request, response) {
        $.getJSON("search_receipt.php", { term: request.term }, response);
      },
      minLength: 2,
      select: function(event, ui) {
        window.location.href = "view_receipt.php?prefix_receipt_number="
                              + encodeURIComponent(ui.item.enc);
        return false;
      }
    });
  });
</script>

<?php include('inc/footer.php'); ?>