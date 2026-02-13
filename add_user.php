<?php
require_once 'database/db.php';

$msg = '';
$msg_type = '';

// Get active users for dropdown
$r = mysqli_query($conn, "SELECT id, username, full_name FROM users WHERE status='active' ORDER BY username");
$active_users = array();
while ($row = mysqli_fetch_assoc($r)) {
    $active_users[] = $row;
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username  = trim($_POST['username']);
    $email     = trim(strtolower($_POST['email']));
    $full_name = trim($_POST['full_name']);
    $parent_id = $_POST['parent_id'];

    $safe_user = mysqli_real_escape_string($conn, $username);
    $safe_email = mysqli_real_escape_string($conn, $email);
    $safe_name = mysqli_real_escape_string($conn, $full_name);

    if ($parent_id == '' || $parent_id == '0') {
        $parent_value = "NULL";
    } else {
        $parent_value = intval($parent_id);
    }

    // Validate
    $errors = array();

    if (empty($username)) {
        $errors[] = "Username required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username: 3-50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username: letters, numbers, underscores only.";
    }

    if (empty($email)) {
        $errors[] = "Email required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name required.";
    }

    // Uniqueness
    if (empty($errors)) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE username='$safe_user'");
        if (mysqli_fetch_assoc($r)['c'] > 0) {
            $errors[] = "Username '$username' taken.";
        }
    }
    if (empty($errors)) {
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE email='$safe_email'");
        if (mysqli_fetch_assoc($r)['c'] > 0) {
            $errors[] = "Email already registered.";
        }
    }

    // Parent check
    if ($parent_value != "NULL" && empty($errors)) {
        $r = mysqli_query($conn, "SELECT id, status FROM users WHERE id=$parent_value");
        $p = mysqli_fetch_assoc($r);
        if (!$p) {
            $errors[] = "Referrer not found.";
        } elseif ($p['status'] != 'active') {
            $errors[] = "Referrer not active.";
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO users (username, email, full_name, parent_id, status)
                VALUES ('$safe_user', '$safe_email', '$safe_name', $parent_value, 'active')";

        if (mysqli_query($conn, $sql)) {
            $new_id = mysqli_insert_id($conn);
            $msg = "User '$username' created! ID: #$new_id";
            $msg_type = 'ok';
            $username = ''; $email = ''; $full_name = '';

            // Refresh list
            $r = mysqli_query($conn, "SELECT id, username, full_name FROM users WHERE status='active' ORDER BY username");
            $active_users = array();
            while ($row = mysqli_fetch_assoc($r)) { $active_users[] = $row; }
        } else {
            $msg = "Error: " . mysqli_error($conn);
            $msg_type = 'err';
        }
    } else {
        $msg = implode('<br>', $errors);
        $msg_type = 'err';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <style>
        * {
             box-sizing:border-box;
              margin:0;
               padding:0;
             }
        body {
             font-family:Arial,sans-serif;
              background:#f0f2f5;
             }
        .topbar { 
            background:#2c3e50;
             padding:12px 25px;
              display:flex;
               justify-content:space-between;
                align-items:center;
                 flex-wrap:wrap; 
                }
        .topbar h1 { 
            color:#fff; 
            font-size:18px;
         }
        .topbar a { 
            color:#bdc3c7;
             text-decoration:none; 
             font-size:13px;
              padding:5px 12px;
               border-radius:4px;
                margin-left:8px;
             }
        .topbar a:hover {
             background:rgba(255,255,255,0.1);
              color:#fff;
             }
        .topbar a.on {
             background:#3498db;
              color:#fff; 
            }
        .wrap { 
            max-width:550px; 
            margin:25px auto; 
            padding:0 15px; 
        }
        .card {
             background:#fff; 
             padding:28px;
              border-radius:10px; 
              box-shadow:0 2px 6px rgba(0,0,0,0.07); 
            }
        .card h2 {
             font-size:17px; 
             color:#2c3e50; 
             margin-bottom:18px;
              padding-bottom:8px; 
              border-bottom:2px solid #3498db;
             }
        .fg {
             margin-bottom:16px; 
            }
        .fg label {
             display:block; 
             margin-bottom:4px;
              font-weight:bold; 
              color:#555;
               font-size:13px; 
            }
        .fg input, .fg select { 
            width:100%;
             padding:9px 12px;
              border:2px solid #ddd;
               border-radius:5px;
                font-size:14px;
             }
        .fg input:focus, .fg select:focus { 
            outline:none;
             border-color:#3498db; 
            }
        .fg small { 
            color:#aaa; 
            font-size:11px; 
        }
        .btn { 
            width:100%;
             padding:11px;
              background:#3498db;
               color:#fff;
                border:none;
                 border-radius:5px; 
                 font-size:15px; 
                 font-weight:bold;
                  cursor:pointer; 
                }
        .btn:hover { 
            background:#2980b9; 
        }
        .alert { 
            padding:10px 16px;
             border-radius:5px; 
             margin-bottom:16px; 
             font-size:13px; 
            }
        .alert-ok {
             background:#d4edda; 
             color:#155724;
              border:1px solid #c3e6cb; 
            }
        .alert-err { 
            background:#f8d7da;
             color:#721c24;
              border:1px solid #f5c6cb;
             }
        .back {
             display:inline-block; 
             margin-bottom:12px; 
             color:#3498db;
              text-decoration:none;
               font-size:13px; 
            }
    </style>
</head>
<body>
<div class="topbar">
    <h1>💰 Affiliate Payout System</h1>
    <div>
        <a href="index.php">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="add_user.php" class="on">Add User</a>
        <a href="record_sale.php">Record Sale</a>
        <a href="view_sales.php">Sales</a>
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="index.php" class="back">← Dashboard</a>

    <?php if ($msg != ''): ?>
        <div class="alert alert-<?php echo ($msg_type=='ok') ? 'ok' : 'err'; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Register New Affiliate</h2>
        <form method="POST">
            <div class="fg">
                <label>Username *</label>
                <input type="text" name="username" required minlength="3" maxlength="50"
                       placeholder="john_doe"
                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                <small>3-50 chars, letters/numbers/underscores</small>
            </div>
            <div class="fg">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="john@example.com"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            <div class="fg">
                <label>Full Name *</label>
                <input type="text" name="full_name" required placeholder="John Doe"
                       value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
            </div>
            <div class="fg">
                <label>Referred By</label>
                <select name="parent_id">
                    <option value="">-- No Referrer (Root) --</option>
                    <?php foreach($active_users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?> (<?php echo htmlspecialchars($u['full_name']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Create User</button>
        </form>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>