<?php
session_start();
$database = '../users.db'; 

$session_id = $_COOKIE['session_id'] ?? null;

    // Debugging output to log the session_id
    error_log("Session ID: " . $session_id);
    
    if (!$session_id) {

    header("Location: /"); 
    exit;
}

try {
    $db = new PDO("sqlite:$database");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT * FROM users WHERE session_id = :session_id");
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Debugging output to log successful authentication
        error_log("User authenticated: " . print_r($user, true));
        http_response_code(200); // Authentication successful
        echo 'Authenticated';
    } else {
        http_response_code(401); // Authentication failed
        echo 'Authentication failed.'; 
    }

} catch(PDOException $e) {
    http_response_code(500); 
    echo "Error: " . $e->getMessage();
}
?>