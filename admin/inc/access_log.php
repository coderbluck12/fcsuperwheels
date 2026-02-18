<?php
/**
 * Access Log Manager - FIXED VERSION
 */

// 1. Central Database Connection Function
function get_db_connection() {
    global $pdo;
    
    // If we already have a valid connection, use it
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $db_host = 'localhost';
        $db_user = 'tertgxyp_seyi';
        $db_password = 'Fcnest001@';
        $database = 'tertgxyp_fcsuperwheels';
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$database;charset=utf8", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // Output error to HTML so you can see it immediately
        echo "<div class='alert alert-danger'>DB Connection Error: " . $e->getMessage() . "</div>";
        return null;
    }
}

// 2. Logging Function
function log_access($action, $page = null, $user_id = null, $username = null) {
    $pdo = get_db_connection();
    if (!$pdo) return;
    
    // Default values
    if ($user_id === null && isset($_SESSION['current_user'])) {
        global $admin_id;
        $user_id = $admin_id ?? null;
    }
    
    if ($username === null && isset($_SESSION['current_user'])) {
        $username = $_SESSION['current_user'];
    }

    $page = $page ?? $_SERVER['REQUEST_URI'];

    // Deduplication (Prevent spamming logs)
    try {
        $check = $pdo->prepare("SELECT id FROM access_log WHERE username = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1");
        $check->execute([$username, $action]);
        if ($check->fetch()) return;
    } catch (Exception $e) {}
    
    try {
        $stmt = $pdo->prepare("INSERT INTO access_log (user_id, username, action, page, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, 
            $username, 
            $action, 
            $page, 
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        // Silent fail for logs is okay
    }
}

// 3. Fetch Logs Function (FIXED QUERY LOGIC)
function get_access_logs($limit = 100, $user_filter = '', $action_filter = '', $date_from = '', $date_to = '') {
    $pdo = get_db_connection();
    if (!$pdo) return [];
    
    $sql = "SELECT al.*, u.firstname, u.lastname 
            FROM access_log al 
            LEFT JOIN user u ON al.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    
    // Add filters using only '?' placeholders
    if (!empty($user_filter)) {
        $sql .= " AND (al.username LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
        $term = "%$user_filter%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }
    
    if (!empty($action_filter)) {
        $sql .= " AND al.action LIKE ?";
        $params[] = "%$action_filter%";
    }
    
    if (!empty($date_from)) {
        $sql .= " AND al.created_at >= ?";
        $params[] = $date_from . " 00:00:00";
    }
    
    if (!empty($date_to)) {
        $sql .= " AND al.created_at <= ?";
        $params[] = $date_to . " 23:59:59";
    }
    
    // SAFE LIMIT: Cast to int and append directly. 
    // This avoids the '?' vs ':limit' mix conflict.
    $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Echo the error so we can debug on screen
        echo "<div class='alert alert-danger'>SQL Error: " . $e->getMessage() . "</div>";
        return [];
    }
}

// 4. Stats Function
function get_access_log_stats() {
    $pdo = get_db_connection();
    if (!$pdo) return ['total_logs' => 0, 'today_logs' => 0, 'unique_users' => 0, 'top_actions' => []];
    
    try {
        $total = $pdo->query("SELECT COUNT(*) FROM access_log")->fetchColumn();
        $today = $pdo->query("SELECT COUNT(*) FROM access_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $unique_users = $pdo->query("SELECT COUNT(DISTINCT username) FROM access_log")->fetchColumn();
        $top_actions = $pdo->query("SELECT action, COUNT(*) as count FROM access_log GROUP BY action ORDER BY count DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total_logs' => $total,
            'today_logs' => $today,
            'unique_users' => $unique_users,
            'top_actions' => $top_actions
        ];
    } catch (Exception $e) {
        return ['total_logs' => 0, 'today_logs' => 0, 'unique_users' => 0, 'top_actions' => []];
    }
}
?>