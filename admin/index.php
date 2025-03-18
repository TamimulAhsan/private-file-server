<?php
session_start();

// Include database connection for user management
try {
    $db = new PDO('sqlite:../users.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle user creation
if (isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_id = uniqid();
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $db->prepare('INSERT INTO users (id, username, password_hash, last_login) VALUES (:id, :username, :password_hash, NULL)');
        $stmt->bindValue(':id', $user_id);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password_hash', $password_hash);
        $stmt->execute();

        $success_message = "User  created successfully!";
    } catch(PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry
            $error_message = "Username already exists!";
        } else {
            $error_message = "Error creating user: " . $e->getMessage();
        }
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $username = $_POST['username'];

    try {
        $stmt = $db->prepare('DELETE FROM users WHERE username = :username');
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        $success_message = "User  deleted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error deleting user: " . $e->getMessage();
    }
}

// Handle admin password change
try {
    $dba = new PDO('sqlite:../admin.db');
    $dba->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
if (isset($_POST['change_admin_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Verify current password
        $stmt = $dba->prepare("SELECT password_hash FROM admins WHERE id = :id");
        $stmt->bindValue(':id', $_SESSION['user_id']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($current_password, $admin['password_hash'])) {
            // Passwords match, proceed with change
            if ($new_password === $confirm_password) {
                $new_hash = password_hash($new_password, PASSWORD_BCRYPT);

                $update_stmt = $dba->prepare("UPDATE admins SET password_hash = :password_hash WHERE id = :id");
                $update_stmt->bindValue(':password_hash', $new_hash);
                $update_stmt->bindValue(':id', $_SESSION['user_id']);
                $update_stmt->execute();

                $success_message = "Password changed successfully!";
            } else {
                $error_message = "New passwords do not match!";
            }
        } else {
            $error_message = "Current password is incorrect!";
        }
    } catch(PDOException $e) {
        $error_message = "Error changing password: " . $e->getMessage();
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: /admin_login');
    exit;
}

// Fetch users for display
try {
    $users = $db->query('SELECT username, last_login FROM users');
} catch(PDOException $e) {
    $error_message = "Error fetching users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel-NAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col justify-between">
    <div class="flex justify-center p-6">
        <h1 class="text-3xl font-bold">Admin Panel</h1>
    </div>
    <div class="flex justify-between items-start p-10 space-x-10">
        <!-- User Table -->
        <div class="w-1/2">
            <table class="border-collapse border border-purple-500 text-center w-full">
                <thead>
                    <tr class="bg-purple-600">
                        <th class="border border-purple-500 px-4 py-2">Username</th>
                        <th class="border border-purple-500 px-4 py-2">Last Login </th>
                        <th class="border border-purple-500 px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="bg-gray-800">
                        <td class="border border-purple-500 px-4 py-2"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="border border-purple-500 px-4 py-2"><?php echo htmlspecialchars($user['last_login']); ?></td>
                        <td class="border border-purple-500 px-4 py-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <button type="submit" name="delete_user" class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Forms -->
        <div class="space-y-10 w-1/2">
            <!-- Add User Form -->
            <div class="border border-white p-6 rounded-lg">
                <h2 class="text-xl font-bold mb-4 text-center">Add User</h2>
                <form method="POST" class="flex flex-col">
                    <div class="mb-4 flex items-center">
                        <label class="w-32">Username:</label>
                        <input type="text" name="username" class="w-full px-4 py-2 rounded border border-gray-300 text-black" required>
                    </div>
                    <div class="mb-4 flex items-center">
                        <label class="w-32">Password:</label>
                        <input type="password" name="password" class="w-full px-4 py-2 rounded border border-gray-300 text-black" required>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" name="create_user" class="bg-purple-600 text-white px-4 py-2 rounded">Add</button>
                    </div>
                </form>
                <?php if (isset($success_message)): ?>
                    <p class="text-green-500 text-center"><?php echo $success_message; ?></p>
                <?php elseif (isset($error_message)): ?>
                    <p class="text-red-500 text-center"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </div>

            <!-- Change Admin Password Form -->
            <div class="border border-white p-6 rounded-lg">
                <h2 class="text-xl font-bold mb-4 text-center">Change Admin Password</h2>
                <form method="POST" class="flex flex-col">
                    <div class="mb-4 flex items-center">
                        <label class="w-32">Current:</label>
                        <input type="password" name="current_password" class="w-full px-4 py-2 rounded border border-gray-300 text-black" required>
                    </div>
                    <div class="mb-4 flex items-center">
                        <label class="w-32">New:</label>
                        <input type="password" name="new_password" class="w-full px-4 py-2 rounded border border-gray-300 text-black" required>
                    </div>
                    <div class="mb-4 flex items-center">
                        <label class="w-32">Confirm:</label>
                        <input type="password" name="confirm_password" class="w-full px-4 py-2 rounded border border-gray-300 text-black" required>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" name="change_admin_password" class="bg-purple-600 text-white px-4 py-2 rounded">Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="flex justify-start p-10">
        <form method="POST">
            <button type="submit" name="logout" class="bg-red-600 text-white px-4 py-2 rounded">Logout</button>
        </form>
    </div>
</body>
</html>