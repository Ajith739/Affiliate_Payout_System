<?php
require_once 'database/db.php';

// Stats
$r = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE status='active'");
$total_users = mysqli_fetch_assoc($r)['cnt'];

$r = mysqli_query($conn, "SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM sales WHERE status='completed'");
$d = mysqli_fetch_assoc($r);
$total_sales = $d['cnt'];
$total_revenue = $d['total'];

$r = mysqli_query($conn, "SELECT COALESCE(SUM(commission_amount),0) as total FROM commissions WHERE status='pending'");
$pending_comm = mysqli_fetch_assoc($r)['total'];

$r = mysqli_query($conn, "SELECT COALESCE(SUM(commission_amount),0) as total FROM commissions");
$total_comm = mysqli_fetch_assoc($r)['total'];

// Recent sales
$r = mysqli_query($conn, "SELECT s.*, u.username
                           FROM sales s
                           JOIN users u ON u.id = s.user_id
                           ORDER BY s.created_at DESC LIMIT 10");
$recent_sales = array();
while ($row = mysqli_fetch_assoc($r)) {
    $recent_sales[] = $row;
}

// Recent commissions
$r = mysqli_query($conn, "SELECT c.*, u1.username as who_gets, u2.username as from_sale_by
                           FROM commissions c
                           JOIN users u1 ON u1.id = c.beneficiary_id
                           JOIN users u2 ON u2.id = c.source_user_id
                           ORDER BY c.created_at DESC LIMIT 10");
$recent_comms = array();
while ($row = mysqli_fetch_assoc($r)) {
    $recent_comms[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Payout System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; color: #333; }

        .topbar {
            background: #2c3e50; padding: 12px 25px;
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap;
        }
        .topbar h1 { color: #fff; font-size: 18px; }
        .topbar a {
            color: #bdc3c7; text-decoration: none; font-size: 13px;
            padding: 5px 12px; border-radius: 4px; margin-left: 8px;
        }
        .topbar a:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .topbar a.on { background: #3498db; color: #fff; }

        .wrap { max-width: 1200px; margin: 20px auto; padding: 0 15px; }

        .actions { margin-bottom: 18px; }
        .actions a {
            display: inline-block; padding: 8px 18px; color: #fff;
            text-decoration: none; border-radius: 5px; font-size: 13px;
            margin-right: 8px; margin-bottom: 5px;
        }
        .bg-blue { background: #3498db; }
        .bg-green { background: #27ae60; }
        .bg-orange { background: #e67e22; }

        .boxes { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 18px; margin-bottom: 25px; }
        .box {
            background: #fff; padding: 22px; border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07); text-align: center;
        }
        .box .num { font-size: 30px; font-weight: bold; }
        .box .lbl { font-size: 12px; color: #888; margin-top: 4px; }

        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

        .card {
            background: #fff; padding: 22px; border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07); margin-bottom: 18px;
        }
        .card h2 {
            font-size: 16px; color: #2c3e50; margin-bottom: 12px;
            padding-bottom: 8px; border-bottom: 2px solid #3498db;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; color: #666; font-size: 11px; text-transform: uppercase; font-weight: 600; }
        tr:hover { background: #fafafa; }

        .r { text-align: right; }
        .c { text-align: center; }
        .muted { color: #999; }
        .sm { font-size: 11px; }

        .pill {
            display: inline-block; padding: 2px 8px; border-radius: 10px;
            font-size: 10px; font-weight: bold;
        }
        .pill-g { background: #d4edda; color: #155724; }
        .pill-y { background: #fff3cd; color: #856404; }

        a.lk { color: #3498db; text-decoration: none; }
        a.lk:hover { text-decoration: underline; }

        @media(max-width:768px) { .row2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="topbar">
    <h1>💰 Affiliate Payout System</h1>
    <div>
        <a href="index.php" class="on">Dashboard</a>
        <a href="view_users.php">Users</a>
        <a href="add_user.php">Add User</a>
        <a href="record_sale.php">Record Sale</a>
        <a href="view_sales.php">Sales</a>
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php">Tree</a>
    </div>
</div>

<div class="wrap">

    <div class="actions">
        <a href="add_user.php" class="bg-blue">+ Add User</a>
        <a href="record_sale.php" class="bg-green">+ Record Sale</a>
        
    </div>

    <div class="boxes">
        <div class="box"><div class="num" style="color:#3498db"><?php echo $total_users; ?></div><div class="lbl">Active Users</div></div>
        <div class="box"><div class="num" style="color:#27ae60"><?php echo $total_sales; ?></div><div class="lbl">Total Sales</div></div>
        <div class="box"><div class="num" style="color:#e67e22">₹<?php echo number_format($total_revenue,2); ?></div><div class="lbl">Revenue</div></div>
        <div class="box"><div class="num" style="color:#9b59b6">₹<?php echo number_format($pending_comm,2); ?></div><div class="lbl">Pending Commissions</div></div>
    </div>

    <div class="row2">
        <!-- Recent Sales -->
        <div class="card">
            <h2>Recent Sales</h2>
            <?php if (count($recent_sales) == 0): ?>
                <p class="muted">No sales yet. <a href="record_sale.php" class="lk">Record one →</a></p>
            <?php else: ?>
            <table>
                <tr><th>ID</th><th>Seller</th><th class="r">Amount</th><th>Date</th></tr>
                <?php foreach($recent_sales as $s): ?>
                <tr>
                    <td><a class="lk" href="sale_detail.php?id=<?php echo $s['id']; ?>">#<?php echo $s['id']; ?></a></td>
                    <td><a class="lk" href="user_detail.php?id=<?php echo $s['user_id']; ?>"><?php echo htmlspecialchars($s['username']); ?></a></td>
                    <td class="r">₹<?php echo number_format($s['amount'],2); ?></td>
                    <td class="sm"><?php echo $s['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p style="margin-top:8px"><a class="lk" href="view_sales.php">View all →</a></p>
            <?php endif; ?>
        </div>

        <!-- Recent Commissions -->
        <div class="card">
            <h2>Recent Commissions</h2>
            <?php if (count($recent_comms) == 0): ?>
                <p class="muted">No commissions yet.</p>
            <?php else: ?>
            <table>
                <tr><th>Gets Paid</th><th class="c">Level</th><th class="r">Amount</th><th>Status</th></tr>
                <?php foreach($recent_comms as $c): ?>
                <tr>
                    <td><a class="lk" href="user_detail.php?id=<?php echo $c['beneficiary_id']; ?>"><?php echo htmlspecialchars($c['who_gets']); ?></a></td>
                    <td class="c">L<?php echo $c['level']; ?></td>
                    <td class="r">₹<?php echo number_format($c['commission_amount'],2); ?></td>
                    <td><span class="pill pill-y"><?php echo $c['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p style="margin-top:8px"><a class="lk" href="view_commissions.php">View all →</a></p>
            <?php endif; ?>
        </div>
    </div>


</div>
</body>
</html>
<?php mysqli_close($conn); ?>