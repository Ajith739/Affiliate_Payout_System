<?php
require_once 'database/db.php';

$sid = intval($_GET['id']);
if ($sid <= 0) die("Invalid ID.");

$r = mysqli_query($conn, "SELECT s.*, u.username, u.full_name
                           FROM sales s JOIN users u ON u.id = s.user_id
                           WHERE s.id=$sid");
$sale = mysqli_fetch_assoc($r);
if (!$sale) die("Not found.");

$r = mysqli_query($conn, "SELECT c.*, u.username as who, u.full_name as who_name
                           FROM commissions c JOIN users u ON u.id = c.beneficiary_id
                           WHERE c.sale_id=$sid ORDER BY c.level");
$comms = array();
$ctotal = 0;
while ($row = mysqli_fetch_assoc($r)) {
    $comms[] = $row;
    $ctotal += $row['commission_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale #<?php echo $sale['id']; ?></title>
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
        .wrap {
             max-width:750px; 
             margin:25px auto; 
             padding:0 15px; 
            }
        .card {
             background:#fff;
              padding:22px; 
              border-radius:10px;
               box-shadow:0 2px 6px rgba(0,0,0,0.07);
                margin-bottom:16px;
             }
        .card h2 { 
            font-size:15px; 
            color:#2c3e50;
             margin-bottom:10px; 
             padding-bottom:6px;
              border-bottom:2px solid #27ae60;
             }
        .grid2 {
             display:grid;
              grid-template-columns:1fr 1fr;
               gap:8px; 
               margin-bottom:10px;
             }
        .gi label { 
            font-weight:bold;
             color:#555; 
             font-size:11px; 
             display:block; 
            }
        .gi span {
             font-size:13px;
             }
        table { 
            width:100%; 
            border-collapse:collapse;
         }
        th,td { 
            padding:9px 10px;
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
        .r { 
            text-align:right;
         }
        .total {
             background:#e8f5e9; 
             font-weight:bold; 
            }
        .pill {
             display:inline-block;
              padding:2px 7px;
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
        .flow { 
            padding:9px 0;
             border-bottom:1px dashed #eee;
              display:flex; 
              align-items:center; 
            }
        .fl {
             width:65px; 
             font-weight:bold; 
             color:#3498db;
              font-size:13px; 
            }
        .fa {
             width:25px; 
             color:#27ae60;
              font-size:16px;
               text-align:center;
             }
        .fu { 
            flex:1;
             font-size:12px; 
            }
        .fm {
             width:100px;
              text-align:right;
               font-weight:bold; 
               font-size:14px; 
               color:#27ae60;
             }
        .muted { 
            color:#999;
             font-size:12px;
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
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="view_sales.php" class="back">← Sales</a>

    <div class="card">
        <h2>Sale #<?php echo $sale['id']; ?></h2>
        <div class="grid2">
            <div class="gi"><label>Seller</label><span><a class="lk" href="user_detail.php?id=<?php echo $sale['user_id']; ?>"><?php echo htmlspecialchars($sale['username']); ?> (#<?php echo $sale['user_id']; ?>)</a></span></div>
            <div class="gi"><label>Amount</label><span style="font-size:20px;font-weight:bold;color:#27ae60">₹<?php echo number_format($sale['amount'],2); ?></span></div>
            <div class="gi"><label>Description</label><span><?php echo $sale['description'] ? htmlspecialchars($sale['description']) : 'N/A'; ?></span></div>
            <div class="gi"><label>Date</label><span><?php echo $sale['created_at']; ?></span></div>
            <div class="gi"><label>Status</label><span class="pill pill-g"><?php echo $sale['status']; ?></span></div>
            <div class="gi"><label>Total Paid Out</label><span style="font-weight:bold">₹<?php echo number_format($ctotal,2); ?> (<?php echo $sale['amount']>0 ? number_format(($ctotal/$sale['amount'])*100,1) : 0; ?>%)</span></div>
        </div>
    </div>

    <div class="card">
        <h2>Commission Payout Flow</h2>
        <div style="background:#f8f9fa;padding:8px 12px;border-radius:5px;margin-bottom:10px;font-size:11px;">
            <strong><?php echo htmlspecialchars($sale['username']); ?></strong> sold ₹<?php echo number_format($sale['amount'],2); ?> → <?php echo count($comms); ?> level(s) paid
        </div>

        <?php if (count($comms)==0): ?>
            <p class="muted" style="padding:12px 0">No commissions — no upstream referrers.</p>
        <?php else: ?>
            <?php foreach($comms as $c): ?>
            <div class="flow">
                <div class="fl">Level <?php echo $c['level']; ?></div>
                <div class="fa">→</div>
                <div class="fu">
                    <a class="lk" href="user_detail.php?id=<?php echo $c['beneficiary_id']; ?>"><?php echo htmlspecialchars($c['who']); ?></a>
                    <span style="color:#aaa">(<?php echo htmlspecialchars($c['who_name']); ?>)</span>
                    <span style="color:#999;font-size:10px;margin-left:6px"><?php echo number_format($c['commission_rate'],2); ?>%</span>
                </div>
                <div class="fm">₹<?php echo number_format($c['commission_amount'],2); ?></div>
            </div>
            <?php endforeach; ?>

            <div class="flow" style="background:#e8f5e9;border-radius:5px;padding:10px;margin-top:6px;border:none;">
                <div class="fl" style="color:#155724">TOTAL</div>
                <div class="fa"></div>
                <div class="fu" style="font-weight:bold;color:#155724"><?php echo count($comms); ?> beneficiaries</div>
                <div class="fm" style="color:#155724;font-size:16px">₹<?php echo number_format($ctotal,2); ?></div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($comms)>0): ?>
    <div class="card">
        <h2>Detail Table</h2>
        <table>
            <tr>
                <th>Level</th>
                <th>Who</th>
                <th class="r">Sale Amt</th>
                <th class="r">Rate</th>
                <th class="r">Commission</th>
                <th>Status</th>
            </tr>
            <?php foreach($comms as $c): ?>
            <tr>
                <td>Level <?php echo $c['level']; ?></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $c['beneficiary_id']; ?>"><?php echo htmlspecialchars($c['who']); ?></a> (#<?php echo $c['beneficiary_id']; ?>)</td>
                <td class="r">₹<?php echo number_format($c['sale_amount'],2); ?></td>
                <td class="r"><?php echo number_format($c['commission_rate'],2); ?>%</td>
                <td class="r"><strong>₹<?php echo number_format($c['commission_amount'],2); ?></strong></td>
                <td><span class="pill pill-y"><?php echo $c['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total"><td colspan="4">Total</td><td class="r">₹<?php echo number_format($ctotal,2); ?></td><td></td></tr>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>