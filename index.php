<?php
session_start();
$database = 'users.db'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $db = new PDO("sqlite:$database");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
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
                $stmt = $db->prepare("UPDATE users SET session_id = :session_id, id = :id WHERE username = :username");
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
                'secure' => false, // Set to false if using HTTP
                'httponly' => true,
                'samesite' => 'Lax' // Or 'Strict' depending on your needs
            ]); 

            // Update last_login time
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("UPDATE users SET last_login = :last_login WHERE id = :id");
            $stmt->bindParam(':last_login', $now);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();

            // Redirect to main page
            header("Location: /files");  
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

<?php 
$resultMessage = ''; // Initialize the variable to avoid undefined variable warning
if (isset($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login-NAS</title>
    <!-- Google Font -->
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <!-- Stylesheet -->
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="container">
      <form action="" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" placeholder="" required/>
        <label for="password">Password:</label>
        <div class="passwordContainer">
          <input type="password" name="password" id="password" placeholder="" required/>
          <i class="far fa-eye" id="togglePassword"></i>
        </div>
        <button>Log In</button>
      </form>
      <div class="ear-l"></div>
      <div class="ear-r"></div>
      <div class="panda-face">
        <div class="blush-l"></div>
        <div class="blush-r"></div>
        <div class="eye-l">
          <div class="eyeball-l"></div>
        </div>
        <div class="eye-r">
          <div class="eyeball-r"></div>
        </div>
        <div class="nose"></div>
        <div class="mouth"></div>
      </div>
      <div class="hand-l"></div>
      <div class="hand-r"></div>
      <div class="paw-l"></div>
      <div class="paw-r"></div>
    </div>
    <!-- Script -->
    <script src="script.js"></script>
    <?php if ($resultMessage): ?>
    <script>
        alert("<?php echo $error; ?>");
    </script>
    <?php endif; ?>
  </body>
</html>