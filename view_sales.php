<?php
require_once 'database/db.php';

$r = mysqli_query($conn, "SELECT s.*, u.username,
       (SELECT COALESCE(SUM(commission_amount),0) FROM commissions WHERE sale_id=s.id) as comm_total,
       (SELECT COUNT(*) FROM commissions WHERE sale_id=s.id) as comm_count
       FROM sales s JOIN users u ON u.id = s.user_id
       ORDER BY s.created_at DESC");
$sales = array();
while ($row = mysqli_fetch_assoc($r)) { $sales[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
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
             background:#27ae60; 
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
                 border-bottom:2px solid #27ae60;
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
        .r { 
            text-align:right;
         }
        .c { 
            text-align:center;
         }
        .muted { 
            color:#ccc; 
        }
        .sm { 
            font-size:11px;
         }
        a.lk { 
            color:#3498db;
             text-decoration:none; 
            }
        a.lk:hover {
             text-decoration:underline; 
            }
        .back { 
            display:inline-block;
             margin-bottom:12px;
              color:#27ae60;
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
        <a href="add_user.php">Add User</a>
        <a href="record_sale.php">Record Sale</a>
        <a href="view_sales.php" class="on">Sales</a>
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="index.php" class="back">← Dashboard</a>
    <div class="card">
        <h2>All Sales (<?php echo count($sales); ?>)</h2>
        <?php if (count($sales)==0): ?>
            <p style="color:#999">No sales. <a href="record_sale.php" class="lk">Record one →</a></p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
            <th>Seller</th>
            <th class="r">Amount</th>
            <th>Description</th>
            <th class="c">Levels</th>
            <th class="r">Paid Out</th>
            <th>Date</th>
            <th></th>
        </tr>
            <?php foreach($sales as $s): ?>
            <tr>
                <td>#<?php echo $s['id']; ?></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $s['user_id']; ?>"><?php echo htmlspecialchars($s['username']); ?></a></td>
                <td class="r"><strong>₹<?php echo number_format($s['amount'],2); ?></strong></td>
                <td><?php echo $s['description'] ? htmlspecialchars(substr($s['description'],0,35)) : '<span class="muted">—</span>'; ?></td>
                <td class="c"><?php echo $s['comm_count']; ?></td>
                <td class="r">₹<?php echo number_format($s['comm_total'],2); ?></td>
                <td class="sm"><?php echo $s['created_at']; ?></td>
                <td><a class="lk" href="sale_detail.php?id=<?php echo $s['id']; ?>">View →</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>