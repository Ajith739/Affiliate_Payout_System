<?php
require_once 'database/db.php';

$r = mysqli_query($conn, "SELECT u.*, p.username as parent_name
                           FROM users u
                           LEFT JOIN users p ON p.id = u.parent_id
                           ORDER BY u.id");
$users = array();
while (
    $row = mysqli_fetch_assoc($r)) 
    { 
        $users[] = $row; 
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
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
            max-width:1100px;
             margin:25px auto; 
             padding:0 15px; 
            }
        .card {
             background:#fff;
              padding:22px;
               border-radius:10px;
                box-shadow:0 2px 6px rgba(0,0,0,0.07); 
            }
        .card h2 {
             font-size:16px;
              color:#2c3e50;
               margin-bottom:12px;
                padding-bottom:8px; 
                border-bottom:2px solid #3498db; 
            }
        table {
             width:100%;
              border-collapse:collapse;
             }
        th,td {
             padding:8px 10px; 
             text-align:left; 
             border-bottom:1px solid #eee;
              font-size:13px; 
            }
        th { 
            background:#f8f9fa;
             color:#666; 
             font-size:11px; 
             text-transform:uppercase;
              font-weight:600; 
            }
        tr:hover { 
            background:#fafafa;
         }
        .pill { 
            display:inline-block; 
            padding:2px 8px; 
            border-radius:10px;
             font-size:10px; 
             font-weight:bold; 
            }
        .pill-g { 
            background:#d4edda; 
            color:#155724; 
        }
        .pill-y { 
            background:#fff3cd; 
            color:#856404; 
        }
        .pill-r { 
            background:#f8d7da; 
            color:#721c24; 
        }
        a.lk {
             color:#3498db; 
             text-decoration:none; 
            }
        a.lk:hover {
             text-decoration:underline; 
            }
        .muted { 
            color:#999;
         }
        .sm {
             font-size:11px; 
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
        <a href="view_users.php" class="on">Users</a>
        <a href="add_user.php">Add User</a>
        <a href="record_sale.php">Record Sale</a>
        <a href="view_sales.php">Sales</a>
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="index.php" class="back">← Dashboard</a>
    <div class="card">
        <h2>All Users (<?php echo count($users); ?>)</h2>
        <table>
            <tr>
                <th>ID</th> 
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Referred By</th>
            <th>Status</th>
            <th>Joined</th>
            <th></th>
        </tr>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <?php if ($u['parent_name']): ?>
                        <a class="lk" href="user_detail.php?id=<?php echo $u['parent_id']; ?>"><?php echo htmlspecialchars($u['parent_name']); ?></a>
                    <?php else: ?>
                        <span class="muted">ROOT</span>
                    <?php endif; ?>
                </td>
                                <td class="sm"><?php echo $u['created_at']; ?></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $u['id']; ?>">Details</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>