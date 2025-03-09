<?php
session_start();
$database = '../admin.db'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $db = new PDO("sqlite:$database");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Generate a unique ID for the user if not already set
            $user_id = $user['id'] ?? uniqid(); // Use existing ID or generate a new one
        
            $_SESSION['user_id'] = $user_id;
            
            // Generate a new session ID
            session_regenerate_id(true);
            $session_id = session_id(); 
        
            // Store the session ID and ensure user has a unique ID in the database
            try {
                $stmt = $db->prepare("UPDATE admins SET session_id = :session_id, id = :id WHERE username = :username");
                if (!$stmt->execute()) {
                    $error = "Error updating session ID: " . implode(", ", $stmt->errorInfo());
                }
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':id', $user_id);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
            } catch(PDOException $e) {
                $error = "Error updating session ID: " . $e->getMessage();
            }

            // Set the session ID cookie and check for errors
            setcookie('session_id', $session_id, [
                'expires' => time() + 36000, // Example: expire after 10 hours
                'path' => '/',
                'domain' => '192.168.1.100', // Or your domain name
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true, // Or set to False
                'samesite' => 'Lax' // Or 'Strict' depending on your needs
            ]); 

            // Update last_login time
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("UPDATE admins SET last_login = :last_login WHERE id = :id");
            $stmt->bindParam(':last_login', $now);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();

            // Redirect to main page
            header("Location: /admin");  
            exit();
        } else {
            $error = "Invalid username or password";
        }

    } catch(PDOException $e) {
        $error =  "Error: " . $e->getMessage();
    }
    $db = null; 
}
?>

<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login-NAS</title>

    <!-- Add CSS File -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form action="" method="post">
            <h3 class="title">Admin Login</h3>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="field-checkbox">
                <label for="remember"></label>
            </div>
            <!-- Submit Button -->
            <button>LOGIN</button>
        </form>
    </div>

    <?php if ($resultMessage): ?>
    <script>
        alert("<?php echo $error; ?>");
    </script>
    <?php endif; ?>
</body>
</html>