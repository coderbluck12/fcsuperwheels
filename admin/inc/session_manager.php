<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

if(isset($_SESSION['current_user']))
{
  $username = $_SESSION['current_user'];

  // Prepare the query with placeholders
  $query_details = "SELECT * FROM `user` WHERE `username` = :username";
  $stmt = $pdo->prepare($query_details);

  // Bind the parameter
  $stmt->bindParam(':username', $username);

  // Execute the query
  $stmt->execute();

  // Fetch the results
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if($result)
  {
    // Original variables (keep these for backward compatibility)
    $admin_id = $result['id'];
    $admin_email = $result['email'];
    $admin_lastname = $result['lastname'] ?? '';
    $admin_firstname = $result['firstname'] ?? '';
    $admin_level = $result['level'] ?? 'admin';
    $full_name = trim($admin_lastname.', '.$admin_firstname, ', ');
    $full_name = ucwords($full_name ?: $username);
    
    // CRITICAL: Variables that inc/menu.php needs
    // Capitalize the level for display (admin -> Admin)
    $the_user = $full_name;
    $user_role = ucfirst(strtolower($admin_level)); // "admin" becomes "Admin"
    $user_branch = $result['branch'] ?? '';
    $user_post = $result['post'] ?? '';
    
    // Additional compatibility variables
    $level = $admin_level;
    $user_level = $admin_level;
    $user_id = $admin_id;
    $user_email = $admin_email;
    $user_firstname = $admin_firstname;
    $user_lastname = $admin_lastname;
    $username_display = $full_name;
  }
  else
  {
    // User found in session but not in DB (invalid session)
    session_destroy();
    header('Location: index.php');
    exit;
  }
}
else
{
  // No user in session - redirect to login
  header('Location: index.php');
  exit;
}
?>