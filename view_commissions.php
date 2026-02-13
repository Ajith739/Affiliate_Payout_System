<?php
require_once 'database/db.php';

$r = mysqli_query($conn, "SELECT c.*, u1.username as who_gets, u2.username as from_who
                           FROM commissions c
                           JOIN users u1 ON u1.id = c.beneficiary_id
                           JOIN users u2 ON u2.id = c.source_user_id
                           ORDER BY c.created_at DESC");
$comms = array();
$sum_all = 0;
$sum_pending = 0;
while ($row = mysqli_fetch_assoc($r)) {
    $comms[] = $row;
    $sum_all += $row['commission_amount'];
    if ($row['status']=='pending') $sum_pending += $row['commission_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissions</title>
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
              color:#fff; }
        .topbar a.on { 
            background:#9b59b6; 
            color:#fff; 
        }
        .wrap { 
            max-width:1100px; 
            margin:25px auto; 
            padding:0 15px; 
        }
        .sums { 
            display:grid;
             grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
              gap:12px; 
              margin-bottom:18px; 
            }
        .sb {
             background:#fff; 
             padding:16px;
              border-radius:8px;
               box-shadow:0 2px 6px rgba(0,0,0,0.06); 
               text-align:center;
             }
        .sb .n { 
            font-size:22px; 
            font-weight:bold; 
        }
        .sb .l {
             font-size:11px; 
             color:#888;
              margin-top:3px;
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
             border-bottom:2px solid #9b59b6;
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
        .pill { 
            display:inline-block;
             padding:2px 8px;
              border-radius:10px; 
              font-size:10px; 
              font-weight:bold;
             }
        .pill-y {
             background:#fff3cd;
              color:#856404;
             }
        .pill-g {
             background:#d4edda;
              color:#155724; 
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
        .back { 
            display:inline-block;
             margin-bottom:12px; 
             color:#9b59b6; 
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
        <a href="view_sales.php">Sales</a>
        <a href="view_commissions.php" class="on">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="index.php" class="back">← Dashboard</a>
    <div class="sums">
        <div class="sb"><div class="n" style="color:#e67e22">₹<?php echo number_format($sum_all,2); ?></div><div class="l">Total</div></div>
        <div class="sb"><div class="n" style="color:#f39c12">₹<?php echo number_format($sum_pending,2); ?></div><div class="l">Pending</div></div>
        <div class="sb"><div class="n" style="color:#3498db"><?php echo count($comms); ?></div><div class="l">Records</div></div>
    </div>
    <div class="card">
        <h2>All Commissions</h2>
        <?php if (count($comms)==0): ?>
            <p style="color:#999">None yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Sale ID</th>
                <th>Gets Paid</th>
                <th>From Sale By</th>
                <th class="c">Level</th>
                <th class="r">Rate</th>
                <th class="r">Commission</th>
                <th>Status</th>
            </tr>
            <?php foreach($comms as $c): ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td><a class="lk" href="sale_detail.php?id=<?php echo $c['sale_id']; ?>">#<?php echo $c['sale_id']; ?></a></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $c['beneficiary_id']; ?>"><?php echo htmlspecialchars($c['who_gets']); ?></a></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $c['source_user_id']; ?>"><?php echo htmlspecialchars($c['from_who']); ?></a></td>
                <td class="c">L<?php echo $c['level']; ?></td>
                <td class="r"><?php echo number_format($c['commission_rate'],2); ?>%</td>
                <td class="r"><strong>₹<?php echo number_format($c['commission_amount'],2); ?></strong></td>
                <td>
                    <?php
                    if ($c['status']=='pending') echo '<span class="pill pill-y">Pending</span>';
                    elseif ($c['status']=='paid') echo '<span class="pill pill-g">Paid</span>';
                    else echo '<span class="pill pill-r">Cancelled</span>';
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>