<?php
require_once 'database/db.php';

$msg = '';
$msg_type = '';
$breakdown = array(); 
$breakdown_total = 0;

$r = mysqli_query($conn, "SELECT id, username, full_name FROM users WHERE status='active' ORDER BY username");
$active_users = array();
while ($row = mysqli_fetch_assoc($r)) {
    $active_users[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id     = intval($_POST['user_id']);
    $amount      = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $safe_desc   = mysqli_real_escape_string($conn, $description);

    //VALIDATE 
    $errors = array();

    if ($user_id <= 0) {
        $errors[] = "Select a seller.";
    } else {
        $r = mysqli_query($conn, "SELECT id, status FROM users WHERE id=$user_id");
        $seller = mysqli_fetch_assoc($r);
        if (!$seller) {
            $errors[] = "User not found.";
        } elseif ($seller['status'] != 'active') {
            $errors[] = "User not active.";
        }
    }

    if (empty($amount) || !is_numeric($amount)) {
        $errors[] = "Valid amount required.";
    } elseif (floatval($amount) <= 0) {
        $errors[] = "Amount must be positive.";
    } elseif (floatval($amount) > 9999999999.99) {
        $errors[] = "Amount too large.";
    }

    $amount = round(floatval($amount), 2);

    if (!empty($errors)) {
        $msg = implode('<br>', $errors);
        $msg_type = 'err';
    } else {

        $desc_val = !empty($description) ? "'$safe_desc'" : "NULL";
        $sql = "INSERT INTO sales (user_id, amount, description, status)
                VALUES ($user_id, $amount, $desc_val, 'completed')";

        if (!mysqli_query($conn, $sql)) {
            $msg = "Sale insert failed: " . mysqli_error($conn);
            $msg_type = 'err';
        } else {
            $sale_id = mysqli_insert_id($conn);

            // Check if already distributed
            $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM commissions WHERE sale_id=$sale_id");
            $already = mysqli_fetch_assoc($r)['c'];

            if ($already > 0) {
                $msg = "Sale #$sale_id recorded but commissions already exist.";
                $msg_type = 'err';
            } else {

                $rates = array();
                $rates[1] = 10.00;
                $rates[2] = 5.00;
                $rates[3] = 3.00;
                $rates[4] = 2.00;
                $rates[5] = 1.00;

                mysqli_begin_transaction($conn);

               
                mysqli_query($conn, "SELECT id FROM sales WHERE id=$sale_id FOR UPDATE");

                $current_id  = $user_id;  
                $level       = 0;
                $total_paid  = 0;
                $all_ok      = true;

                while ($level < 5) {

                    $sql = "SELECT p.id, p.username, p.full_name, p.parent_id, p.status
                            FROM users c
                            JOIN users p ON p.id = c.parent_id
                            WHERE c.id = $current_id";

                    $r = mysqli_query($conn, $sql);

                    if (!$r) {
                        $all_ok = false;
                        break;
                    }

                    $parent = mysqli_fetch_assoc($r);

                    if (!$parent) {
                        break;
                    }

                    $level = $level + 1;

                    $rate = $rates[$level];

                    $comm = round($amount * $rate / 100, 2);

                    if ($parent['status'] == 'active') {

                        $pid = intval($parent['id']);

                        $sql = "INSERT INTO commissions
                                (sale_id, beneficiary_id, source_user_id,
                                 level, sale_amount, commission_rate,
                                 commission_amount, status)
                                VALUES
                                ($sale_id, $pid, $user_id,
                                 $level, $amount, $rate,
                                 $comm, 'pending')";

                        $ir = mysqli_query($conn, $sql);

                        if (!$ir) {
                            $all_ok = false;
                            break;
                        }

                        $b = array();
                        $b['id'] = mysqli_insert_id($conn);
                        $b['who'] = $parent['username'];
                        $b['name'] = $parent['full_name'];
                        $b['level'] = $level;
                        $b['rate'] = $rate;
                        $b['amount'] = $comm;
                        $breakdown[] = $b;

                        $total_paid = $total_paid + $comm;
                    }

                    $current_id = intval($parent['id']);
                }

                if ($all_ok) {
                    mysqli_commit($conn);
                    $breakdown_total = $total_paid;
                    $msg = "Sale #$sale_id recorded (₹ " . number_format($amount,2) . "). "
                         . "Distributed ₹ " . number_format($total_paid,2)
                         . " across " . count($breakdown) . " level(s).";
                    $msg_type = 'ok';
                } else {
                    mysqli_rollback($conn);
                    $breakdown = array();
                    $msg = "Sale recorded but commission failed. Rolled back. Error: " . mysqli_error($conn);
                    $msg_type = 'err';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Sale</title>
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
            max-width:650px;
             margin:25px auto;
              padding:0 15px; 
            }
        .card {
             background:#fff;
              padding:24px;
               border-radius:10px;
                box-shadow:0 2px 6px rgba(0,0,0,0.07);
                 margin-bottom:18px;
                 }
        .card h2 {
             font-size:16px; 
             color:#2c3e50; 
             margin-bottom:14px;
              padding-bottom:8px;
               border-bottom:2px solid #27ae60;
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
        .fg input, .fg select, .fg textarea { 
            width:100%; 
            padding:9px 12px; 
            border:2px solid #ddd;
             border-radius:5px; 
             font-size:14px; 
            }
        .fg input:focus, .fg select:focus, .fg textarea:focus { 
            outline:none; 
            border-color:#27ae60; 
        }
        .btn { 
            width:100%;
             padding:11px;
              background:#27ae60;
               color:#fff;
                border:none;
                 border-radius:5px;
                  font-size:15px;
                   font-weight:bold;
                    cursor:pointer;
                 }
        .btn:hover {
             background:#219a52;
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
        .info {
             background:#e8f5e9;
              border:1px solid #c8e6c9;
               padding:10px 14px; 
               border-radius:5px; 
               margin-bottom:16px;
                font-size:12px; 
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
        .r {
             text-align:right;
             }
        .total { 
            background:#e8f5e9;
             font-weight:bold; 
            }
        .back { 
            display:inline-block;
             margin-bottom:12px;
              color:#27ae60; 
              text-decoration:none;
               font-size:13px; 
            }
        a.lk { 
            color:#3498db; 
            text-decoration:none; 
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
        <a href="record_sale.php" class="on">Record Sale</a>
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

    <!-- Show breakdown after successful sale -->
    <?php if (count($breakdown) > 0): ?>
    <div class="card">
        <h2>Commission Breakdown</h2>
        <table>
            <tr>
                <th>Level</th>
                <th>Who Gets Paid</th>
                <th class="r">Rate</th>
                <th class="r">Commission</th>
            </tr>
            <?php foreach($breakdown as $b): ?>
            <tr>
                <td>Level <?php echo $b['level']; ?></td>
                <td><?php echo htmlspecialchars($b['who']); ?> (<?php echo htmlspecialchars($b['name']); ?>)</td>
                <td class="r"><?php echo number_format($b['rate'],2); ?>%</td>
                <td class="r">₹<?php echo number_format($b['amount'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td colspan="3">Total Distributed</td>
                <td class="r">₹<?php echo number_format($breakdown_total,2); ?></td>
            </tr>
        </table>
        <?php if (isset($sale_id)): ?>
        <p style="margin-top:8px;font-size:12px"><a class="lk" href="sale_detail.php?id=<?php echo $sale_id; ?>">View full sale detail →</a></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>Record a Sale</h2>
        <div class="info">
            <strong>Commission Rates:</strong> L1: 10% | L2: 5% | L3: 3% | L4: 2% | L5: 1%
        </div>
        <form method="POST">
            <div class="fg">
                <label>Seller *</label>
                <select name="user_id" required>
                    <option value="">-- Select Seller --</option>
                    <?php foreach($active_users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"
                        <?php echo (isset($user_id) && $user_id==$u['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['username']); ?>
                        (<?php echo htmlspecialchars($u['full_name']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Amount (₹) *</label>
                <input type="number" name="amount" required min="0.01" max="9999999999.99" step="0.01"
                       placeholder="1000.00"
                       value="<?php echo (isset($amount) && $amount>0) ? $amount : ''; ?>">
            </div>
            <div class="fg">
                <label>Description (optional)</label>
                <textarea name="description" rows="2" maxlength="500"
                          placeholder="Product purchased"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn">Record Sale & Distribute Commissions</button>
        </form>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>