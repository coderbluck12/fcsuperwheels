<?php
// 1. ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. INCLUDE DATABASE CONNECTION
if (file_exists('admin/inc/functions.php')) {
    require_once('admin/inc/functions.php');
} elseif (file_exists('inc/functions.php')) {
    require_once('inc/functions.php');
} else {
    // Fallback: Manual Connection if file not found
    try {
        $db_host = 'localhost';
        $db_user = 'tertgxyp_seyi';
        $db_password = 'Fcnest001@';
        $database = 'tertgxyp_fcsuperwheels';
        $pdo = new PDO("mysql:host=$db_host;dbname=$database;charset=utf8", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database Connection Failed: " . $e->getMessage());
    }
}

// 3. FETCH VEHICLES (Public View logic)
try {
    // Fetch Available cars first
    $stmt = $pdo->query("SELECT * FROM vehicles ORDER BY CASE WHEN status = 'Available' THEN 1 ELSE 2 END, created_at DESC");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching vehicles: " . $e->getMessage());
}

// 4. CALCULATE PUBLIC STATS
$available_count = 0;
$sold_count = 0;
foreach($vehicles as $v) {
    if($v['status'] == 'Available') $available_count++;
    if($v['status'] == 'Sold') $sold_count++;
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>Our Inventory | Firstchoice Superwheels</title>
      <meta name="keywords" content="Car inventory, Auto sales, Nigeria cars, Browse cars, Firstchoice Superwheels">
      <meta name="description" content="Browse our premium selection of locally used and imported cars at Firstchoice Superwheels. Quality vehicles at competitive prices.">
      <meta name="author" content="Firstchoice Superwheels">
      <!-- bootstrap css -->
      <link rel="stylesheet" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" href="css/style.css">
      <!-- Responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- fevicon -->
      <link rel="shortcut icon" href="favicon.png">
      <!-- Scrollbar Custom CSS -->
      <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
      <!-- Tweaks for older IEs-->
      <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
      
      <style>
      /* Custom styles for inventory page */
      .inventory-header {
          text-align: center;
          padding: 60px 0 40px;
          background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
          color: white;
      }
      
      .inventory-header h1 {
          font-size: 2.5rem;
          font-weight: 700;
          margin-bottom: 1rem;
      }
      
      .inventory-header p {
          font-size: 1.1rem;
          opacity: 0.9;
          max-width: 600px;
          margin: 0 auto;
      }
      
      .stats-section {
          background: #f8f9fa;
          padding: 40px 0;
      }
      
      .stats-container {
          display: flex;
          justify-content: center;
          gap: 3rem;
          flex-wrap: wrap;
      }
      
      .stat-box {
          text-align: center;
          background: white;
          padding: 25px;
          border-radius: 10px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          min-width: 150px;
      }
      
      .stat-number {
          font-size: 2.5rem;
          font-weight: 700;
          color: #1e3c72;
          margin-bottom: 5px;
      }
      
      .stat-label {
          font-size: 1rem;
          color: #666;
          text-transform: uppercase;
          letter-spacing: 1px;
      }
      
      .filter-section {
          background: white;
          padding: 30px 0;
          border-bottom: 1px solid #eee;
      }
      
      .filter-buttons {
          display: flex;
          justify-content: center;
          gap: 1rem;
          flex-wrap: wrap;
      }
      
      .filter-btn {
          padding: 10px 25px;
          border: 2px solid #1e3c72;
          background: white;
          color: #1e3c72;
          border-radius: 25px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
          text-decoration: none;
      }
      
      .filter-btn:hover, .filter-btn.active {
          background: #1e3c72;
          color: white;
          transform: translateY(-2px);
      }
      
      .vehicles-section {
          padding: 60px 0;
          background: #ffffff;
      }
      
      .vehicle-grid {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 1.2rem;
          margin-top: 2rem;
      }
      
      .vehicle-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #eee;
      }
      
      .vehicle-card:hover {
          transform: translateY(-3px);
          box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      }
      
      .vehicle-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
       background: #f8f9fa;
      }
      
      .vehicle-details {
        padding: 0.9rem 1rem;
      }
      
      .vehicle-title {
    font-size: 1rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 6px;
}
      
      .vehicle-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
}
      
      .spec-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #666;
}
      
      .spec-item i {
          color: #1e3c72;
      }
      
      .vehicle-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e3c72;
    margin-bottom: 8px;
}
      
      .vehicle-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
      
      .status-available {
          background: #d4edda;
          color: #155724;
      }
      
      .status-sold {
          background: #f8d7da;
          color: #721c24;
      }
      
      .no-vehicles {
          text-align: center;
          padding: 60px 20px;
          color: #666;
      }
      
      .no-vehicles i {
          font-size: 4rem;
          color: #ddd;
          margin-bottom: 20px;
      }
      
      .no-vehicles h3 {
          font-size: 1.5rem;
          margin-bottom: 10px;
      }
      
      /* Responsive adjustments */
      @media (max-width: 1200px) {
    .vehicle-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .inventory-header h1 {
        font-size: 2rem;
    }
    
    .stats-container {
        gap: 1.5rem;
    }
    
    .stat-box {
        min-width: 120px;
        padding: 20px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .vehicle-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
    }
    
    .filter-buttons {
        gap: 0.5rem;
    }
    
    .filter-btn {
        padding: 8px 20px;
        font-size: 0.9rem;
    }
    
    .vehicle-image {
        height: 130px;
    }
    
    .vehicle-details {
        padding: 0.75rem;
    }
}
      
      @media (max-width: 576px) {
    .vehicle-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
    }
    
    .vehicle-image {
        height: 110px;
    }
    
    .vehicle-details {
        padding: 0.6rem;
    }
    
    .vehicle-title {
        font-size: 0.88rem;
    }
    
    .vehicle-price {
        font-size: 0.95rem;
    }
}
      </style>
   </head>
   <!-- body -->
   <body class="main-layout">
      <!-- header -->
      <header>
         <!-- header inner -->
         <div class="header">
            <div class="container">
               <div class="row">
                  <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                     <div class="full">
                        <div class="center-desk">
                           <div class="logo">
                              <a href="index.php"><img src="images/logo.png" alt="#" width="65%"/></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                     <nav class="navigation navbar navbar-expand-md navbar-dark ">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                           <ul class="navbar-nav mr-auto">
                              <li class="nav-item">
                                 <a class="nav-link" href="index.php"> Home  </a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="index.php#about">About</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="index.php#request">Request car</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="index.php#contact">Contact us</a>
                              </li>
                              <li class="nav-item active">
                                 <a class="nav-link" href="inventory.php">Browse Cars</a>
                              </li>
                           </ul>
                        </div>
                     </nav>
                  </div>
               </div>
            </div>
         </div>
      </header>
      <!-- end header inner -->
      <!-- end header -->
      
      <!-- Inventory Header -->
      <section class="inventory-header">
         <div class="container">
            <h1>Our Vehicle Inventory</h1>
            <p>Explore our premium selection of quality vehicles. From affordable options to luxury rides, we have the perfect car waiting for you.</p>
         </div>
      </section>
      
      <!-- Stats Section -->
      <section class="stats-section">
         <div class="container">
            <div class="stats-container">
               <div class="stat-box">
                   <div class="stat-number"><?php echo count($vehicles); ?></div>
                   <div class="stat-label">Total Vehicles</div>
               </div>
               <div class="stat-box">
                   <div class="stat-number"><?php echo $available_count; ?></div>
                   <div class="stat-label">Available</div>
               </div>
               <div class="stat-box">
                   <div class="stat-number"><?php echo $sold_count; ?></div>
                   <div class="stat-label">Sold</div>
               </div>
            </div>
         </div>
      </section>
      
      <!-- Filter Section -->
      <section class="filter-section">
         <div class="container">
            <div class="filter-buttons">
               <a href="inventory.php" class="filter-btn <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">All Vehicles</a>
               <a href="inventory.php?status=Available" class="filter-btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'Available') ? 'active' : ''; ?>">Available</a>
               <a href="inventory.php?status=Sold" class="filter-btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'Sold') ? 'active' : ''; ?>">Sold</a>
            </div>
         </div>
      </section>
      
      <!-- Vehicles Section -->
      <section class="vehicles-section">
         <div class="container">
            <?php if (empty($vehicles)): ?>
               <div class="no-vehicles">
                   <i class="fa fa-car"></i>
                   <h3>No Vehicles Available</h3>
                   <p>Check back soon as we regularly update our inventory with new arrivals.</p>
               </div>
            <?php else: ?>
               <div class="vehicle-grid">
                   <?php foreach ($vehicles as $vehicle): ?>
                       <?php 
                       // Filter by status if specified
                       if (isset($_GET['status']) && $vehicle['status'] !== $_GET['status']) {
                           continue;
                       }
                       ?>
                       <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="vehicle-card">
                           <?php 
                           $image_path = '';
                           $show_image = false;
                           
                           if (!empty($vehicle['image'])) {
                               // Try different path formats
                               $possible_paths = [
                                   $vehicle['image'],
                                   'admin/' . $vehicle['image'],
                                   '../' . $vehicle['image'],
                                   'images/' . basename($vehicle['image'])
                               ];
                               
                               foreach ($possible_paths as $path) {
                                   if (file_exists($path)) {
                                       $image_path = $path;
                                       $show_image = true;
                                       break;
                                   }
                               }
                           }
                           
                           if ($show_image): ?>
                               <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>" class="vehicle-image">
                           <?php else: ?>
                               <img src="images/car_img2.jpg" alt="Vehicle Image" class="vehicle-image">
                           <?php endif; ?>
                           
                           <div class="vehicle-details">
                               <h3 class="vehicle-title"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                               
                               <div class="vehicle-specs">
                                   <div class="spec-item">
                                       <i class="fa fa-calendar"></i>
                                       <?php echo htmlspecialchars($vehicle['year']); ?>
                                   </div>
                                   <?php if ($vehicle['color']): ?>
                                   <div class="spec-item">
                                       <i class="fa fa-palette"></i>
                                       <?php echo htmlspecialchars($vehicle['color']); ?>
                                   </div>
                                   <?php endif; ?>
                                   <?php if ($vehicle['vin']): ?>
                                   <div class="spec-item">
                                       <i class="fa fa-barcode"></i>
                                       VIN: <?php echo htmlspecialchars(substr($vehicle['vin'], 0, 8)); ?>...
                                   </div>
                                   <?php endif; ?>
                               </div>
                               
                               <?php if ($vehicle['sale_price']): ?>
                                   <div class="vehicle-price">₦<?php echo number_format($vehicle['sale_price'], 2); ?></div>
                               <?php elseif ($vehicle['purchase_price']): ?>
                                   <div class="vehicle-price">₦<?php echo number_format($vehicle['purchase_price'], 2); ?></div>
                               <?php endif; ?>
                               
                               <span class="vehicle-status <?php echo $vehicle['status'] === 'Available' ? 'status-available' : 'status-sold'; ?>">
                                   <?php echo htmlspecialchars($vehicle['status']); ?>
                               </span>
                           </div>
                       </div>
                    </a>
                   <?php endforeach; ?>
               </div>
            <?php endif; ?>
         </div>
      </section>
      
      <!-- Contact Section -->
      <hr />
      <section class="contact-section" style="padding: 60px 0; background: #f8f9fa;">
         <div class="container">
            <div class="row">
               <div class="col-md-12 text-center">
                   <h2 style="color: #1e3c72; margin-bottom: 20px;">Interested in a Vehicle?</h2>
                   <p style="font-size: 1.1rem; margin-bottom: 30px;">Contact us today to schedule a viewing or get more information about any of our vehicles.</p>
                   <a href="index.php#contact" class="btn btn-lg" style="background: #1e3c72; color: white; padding: 15px 30px; border-radius: 25px; font-weight: 600;">
                       <i class="fa fa-phone"></i> Contact Us
                   </a>
               </div>
            </div>
         </div>
      </section>
      
      <!--  footer -->
     <footer>
         <div id="contact" class="footer">
            <div class="container">
               <div class="row">
                  <div class="col-md-12">
                     <div class="cont_call">
                        <h3><strong class="multi color_chang"> Call Us Now</strong><br> +2347016754887</h3>
                        <h5><a href="mailto:info@fcsuperwheels.com">info@fcsuperwheels.com</a></h5>
                     </div>
                     <div class="cont">
                        <h3><strong class="multi">Visit Us Today</strong> <br>Plot 10, Opposite Osun State Secretariat, Abere, Osun State</h3>
                     </div>
                  </div>
                  <div class="google-maps col-md-12">
                     <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d63256.80600633253!2d4.513971!3d7.731292!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103829b9aaea714b%3A0x83d4d651ba7d6c0a!2s1st%20Choice%20Superwheels%20Auto!5e0!3m2!1sen!2sie!4v1732649271403!5m2!1sen!2sie" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                  </div>
               </div>
            </div>
            <div class="copyright">
               <div class="container">
                  <div class="row">
                     <div class="col-md-12">
                        <p>&copy; 2024 Firstchoice Superwheels. All Rights Reserved.</p>
                         <h6><a href="https://www.pentagonware.com" target="_blank">Managed by Pentaonware Technologies.</a></h6>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </footer>
      <!-- end footer -->
      <!-- Javascript files-->
      <script src="js/jquery.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/jquery-3.0.0.min.js"></script>
      <script src="js/plugin.js"></script>
      <!-- sidebar -->
      <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
      <script src="js/custom.js"></script>
      <script src="https:cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
   </body>
</html>