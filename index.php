

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
      <title>Firstchoice Superwheels Auto</title>
       <meta name="keywords" content="Car dealership, Auto sales, Nigeria cars, Buy cars, Firstchoice Superwheels">
      <meta name="description" content="Firstchoice Superwheels is a trusted auto dealership specializing in selling premium, locally used, and imported cars in Nigeria.">
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
  .google-maps {
    position: relative;
    padding-bottom: 50%; // This is the aspect ratio
    height: 0;
    overflow: hidden;
  }
  .google-maps iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100% !important;
    height: 100% !important;
  }
   </style>
   <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            let urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Check the 'status' parameter and display an alert accordingly
        window.onload = function() {
            const success = getUrlParameter('success'); // Check if 'success' is present
            const fail = getUrlParameter('fail'); // Check if 'fail' is present

            if (success !== null) {
                alert('Your request was submitted successfully!');
            } else if (fail !== null) {
                alert('There was an error processing your request. Please try again.');
            }
        };
    </script>
   </head>
   <!-- body -->
   <body class="main-layout">
      <!-- loader  -->
      <!--div class="loader_bg">
         <div class="loader"><img src="images/loading.gif" alt="#" /></div>
      </div-->
      <!-- end loader -->
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
                              <a href="index.html"><img src="images/logo.png" alt="#" width="65%"/></a>
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
                                 <a class="nav-link" href="index.html"> Home  </a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="#about">About</a>
                              </li>
							  <li class="nav-item">
                                 <a class="nav-link" href="#request">Request car</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" href="#contact">Contact us</a>
                              </li>
                              <li class="nav-item">
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
      <!-- banner -->
      <section class="banner_main">
         <div class="container">
            <div class="row d_flex">
               <div class="col-md-12">
                  <div class="text-bg">
                     <h1>1st Choice Superwheels</h1>
                     <strong>Find Your Dream Car Today</strong>
                     <span>Premium and imported cars to match your needs</span>
                     <p>
                        Firstchoice Superwheels is your go-to partner for finding the right car. Whether you are looking for affordable options or high-end luxury vehicles, we are committed to providing top-quality service and transparent pricing.
                        <head></head>
                     </p>
                     <a href="#about">Learn More</a>
                  </div>
               </div>
            </div>
         </div>
      </section>
      </div>
      <!-- end banner -->
      <!-- car -->
      <div id="about"  class="car">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage">
                     <h2>About Firstchoice Superwheels</h2>
                     Firstchoice Superwheels, located at <b>Plot 10, Opposite Osun State Secretariat, Abere, Osun State</b>, is a licensed car dealership specializing in the sale of premum and imported vehicles. With years of experience, we pride ourselves on customer service and satisfaction.</p>
                     <p>Whether you're looking for a reliable pre-owned car or a luxury ride, we ensure you get the best deals with transparent pricing. Contact us today to discuss your vehicle requirements!</p>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4 padding_leri">
                  <div class="car_box">
                     <figure><img src="images/car_img1.png" alt="#"/></figure>
                     <h3 style="color:white;">Affordable</h3>
                  </div>
               </div>
               <div class="col-md-4 padding_leri">
                  <div class="car_box">
                     <figure><img src="images/car_img2.png" alt="#"/></figure>
                     <h3 style="color:white;">Comfort</h3>
                  </div>
               </div>
               <div class="col-md-4 padding_leri">
                  <div class="car_box">
                     <figure><img src="images/car_img3.png" alt="#"/></figure>
                     <h3 style="color:white;">Stylish</h3>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end car -->
      <!-- bestCar -->
      <div id="request" class="bestCar">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
               </div>
            </div>
            <div class="row">
               <div class="col-sm-12">
                  <div class="row">
                     <div class="col-md-12">
                        <form method="POST" action="request_processor.php" class="main_form">
                           <div class="titlepage">
                              <h2>Request a Car</h2>
							   <p>If you're looking for a specific vehicle , feel free to request it here. Simply fill out the form with your preferences, and we'll get back to you as soon as possible with available options.</p>
                           </div>
                           <div class="row">
                              <div class="col-md-12 ">
							  <label>Enter your name*</label>
							  <input type="text" name="name" class="contactus" placeholder="Your Name" required/>
							  
                              </div>
                              <div class="col-md-12">
                                 <label>Enter your email*</label>
							  <input type="email" name="email"  class="contactus" placeholder="Your Email" required/>
                              </div>
                              <div class="col-md-12">
                                 <label>Enter your phone number*</label>
							  <input type="number" name="phone" class="contactus" placeholder="Your Phone Number" required/>                         
                              </div>
                              <div class="col-md-12">
                                   <label>Enter car details*</label>
							  <input type="text" name="others" class="contactus" placeholder="Car Make, Model, Year, and Any Other Details" required/>
                                 </select>
                              </div>
                              <div class="col-sm-12">
                                 <button name="request_button" class="find_btn">Submit Request</button>
                              </div>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end bestCar -->
      <!-- choose  section -->
      <!--div class="choose ">
          <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage">
                     <h2>Why Choose Firstchoice Superwheels?</h2>
                     <p>We believe in providing the best car-buying experience for our customers, ensuring trust and satisfaction every step of the way. Here’s why you should choose us:</p>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <div class="choose_box">
                     <span>01</span>
                     <p><strong>Transparent Pricing:</strong> No hidden fees or surprises. What you see is what you pay.</p>
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="choose_box">
                     <span>02</span>
                     <p><strong>Quality Vehicles:</strong> All our cars go through rigorous inspections before putting them on the market</p>
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="choose_box">
                     <span>03</span>
                     <p><strong>Customer Support:</strong> Our team is always available to help you with any inquiries or after-sales support.</p>
                  </div>
               </div>
            </div>
         </div>
      </div-->
      <!-- end choose  section -->
      <!-- cutomer -->
<hr />
      <!-- end cutomer -->
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

