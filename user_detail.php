<?php
require_once 'database/db.php';

$uid = intval($_GET['id']);
if ($uid <= 0) ydie("Invalid ID.");

$r = mysqli_query($conn, "SELECT u.*, p.username as parent_name
                           FROM users u LEFT JOIN users p ON p.id = u.parent_id
                           WHERE u.id = $uid");

                        //    echo"SELECT u.*, p.username as parent_name
                        //    FROM users u LEFT JOIN users p ON p.id = u.parent_id
                        //    WHERE u.id = $uid";
$user = mysqli_fetch_assoc($r);
if (!$user) die("Not found.");

// Referrals
$r = mysqli_query($conn, "SELECT * FROM users WHERE parent_id=$uid ORDER BY id");
$refs = array();
while ($row = mysqli_fetch_assoc($r)) { $refs[] = $row; }

// Earnings
$r = mysqli_query($conn, "SELECT COALESCE(SUM(commission_amount),0) as total,
       COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending
       FROM commissions WHERE beneficiary_id=$uid");
    //    echo "SELECT COALESCE(SUM(commission_amount),0) as total,
    //    COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending
    //    FROM commissions WHERE beneficiary_id=$uid";
$earn = mysqli_fetch_assoc($r);

// Commissions received
$r = mysqli_query($conn, "SELECT c.*, u.username as from_who
                           FROM commissions c JOIN users u ON u.id = c.source_user_id
                           WHERE c.beneficiary_id=$uid ORDER BY c.created_at DESC");
$my_comms = array();
while ($row = mysqli_fetch_assoc($r)) { $my_comms[] = $row; }

// Upline
$ancestors = array();
$curr = $uid;
$depth = 0;
while ($depth < 20) {
    $r = mysqli_query($conn, "SELECT p.id, p.username, p.full_name
                               FROM users c JOIN users p ON p.id = c.parent_id
                               WHERE c.id=$curr");
                            //    echo "SELECT p.id, p.username, p.full_name
                            //    FROM users c JOIN users p ON p.id = c.parent_id
                            //    WHERE c.id=$curr";
    $a = mysqli_fetch_assoc($r);
    if (!$a) break;
    $depth++;
    $a['lv'] = $depth;
    $ancestors[] = $a;
    $curr = intval($a['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?> - Detail</title>
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
             max-width:900px; 
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
                border-bottom:2px solid #3498db;
             }
        .grid2 {
             display:grid; 
             grid-template-columns:1fr 1fr;
              gap:8px; 
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
        .stats3 {
             display:grid; 
             grid-template-columns:repeat(3,1fr);
              gap:10px;
               margin-bottom:16px;
             }
        .sb {
             background:#f8f9fa; 
             padding:14px; 
             border-radius:8px; 
             text-align:center;
             }
        .sb .n {
             font-size:20px; 
             font-weight:bold; 
            }
        .sb .l {
             font-size:10px; 
             color:#888;
              margin-top:3px; 
            }
        table { 
            width:100%;
             border-collapse:collapse;
             }
        th,td { 
            padding:7px 9px; 
            text-align:left;
             border-bottom:1px solid #eee;
              font-size:12px; 
            }
        th { 
            background:#f8f9fa; 
            color:#666; 
            font-size:10px;
             text-transform:uppercase;
              font-weight:600; 
            }
        .r { 
            text-align:right; 
        }
        .c { 
            text-align:center;
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
             color:#3498db;
             text-decoration:none; 
             font-size:13px; 
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
    <a href="view_users.php" class="back">← Users</a>

    <div class="card">
        <h2>👤 <?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['id']; ?>)</h2>
        <div class="grid2">
            <div class="gi"><label>Username</label><span><?php echo htmlspecialchars($user['username']); ?></span></div>
            <div class="gi"><label>Full Name</label><span><?php echo htmlspecialchars($user['full_name']); ?></span></div>
            <div class="gi"><label>Email</label><span><?php echo htmlspecialchars($user['email']); ?></span></div>
            <div class="gi"><label>Status</label><span class="pill pill-g"><?php echo $user['status']; ?></span></div>
            <div class="gi"><label>Referred By</label><span><?php echo $user['parent_name'] ? htmlspecialchars($user['parent_name']).' ('.$user['parent_id'].')' : 'None (Root)'; ?></span></div>
            <div class="gi"><label>Joined</label><span><?php echo $user['created_at']; ?></span></div>
        </div>
    </div>

    <div class="stats3">
        <div class="sb"><div class="n" style="color:#27ae60">₹<?php echo number_format($earn['total'],2); ?></div><div class="l">Total Earned</div></div>
        <div class="sb"><div class="n" style="color:#f39c12">₹<?php echo number_format($earn['pending'],2); ?></div><div class="l">Pending</div></div>
        <div class="sb"><div class="n" style="color:#3498db"><?php echo count($refs); ?></div><div class="l">Direct Referrals</div></div>
    </div>

    <?php if (count($ancestors)>0): ?>
    <div class="card">
        <h2>↑ Upline (<?php echo count($ancestors); ?> levels up)</h2>
        <table>
            <tr>
                <th>Level</th>
                <th>User</th>
                <th>Name</th>
            </tr>
            <?php foreach($ancestors as $a): ?>
            <tr>
                <td>↑ <?php echo $a['lv']; ?></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['username']); ?> (<?php echo $a['id']; ?>)</a></td>
                <td><?php echo htmlspecialchars($a['full_name']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>↓ Referrals (<?php echo count($refs); ?>)</h2>
        <?php if (count($refs)==0): ?>
            <p class="muted">None yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Status</th>
        </tr>
            <?php foreach($refs as $ref): ?>
            <tr>
                <td><?php echo $ref['id']; ?></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $ref['id']; ?>"><?php echo htmlspecialchars($ref['username']); ?></a></td>
                <td><?php echo htmlspecialchars($ref['full_name']); ?></td>
                <td><span class="pill pill-g"><?php echo $ref['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>💰 Commissions Earned (<?php echo count($my_comms); ?>)</h2>
        <?php if (count($my_comms)==0): ?>
            <p class="muted">None yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Sale</th>
            <th>From</th>
            <th class="c">Level</th>
            <th class="r">Rate</th>
            <th class="r">Commission</th>
            <th>Status</th>
        </tr>
            <?php foreach($my_comms as $c): ?>
            <tr>
                <td><a class="lk" href="sale_detail.php?id=<?php echo $c['sale_id']; ?>"><?php echo $c['sale_id']; ?></a></td>
                <td><a class="lk" href="user_detail.php?id=<?php echo $c['source_user_id']; ?>"><?php echo htmlspecialchars($c['from_who']); ?></a></td>
                <td class="c">L<?php echo $c['level']; ?></td>
                <td class="r"><?php echo number_format($c['commission_rate'],2); ?>%</td>
                <td class="r"><strong>₹<?php echo number_format($c['commission_amount'],2); ?></strong></td>
                <td><span class="pill pill-y"><?php echo $c['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>