<?php
require_once 'database/db.php';

$r = mysqli_query($conn, "SELECT id, username, full_name, parent_id, status FROM users ORDER BY id");
$all = array();
while ($row = mysqli_fetch_assoc($r)) { $all[] = $row; }

// Build children map
$kids = array();
$roots = array();
foreach ($all as $u) {
    if ($u['parent_id'] == '' || $u['parent_id'] === null) {
        $roots[] = $u;
    } else {
        $p = $u['parent_id'];
        if (!isset($kids[$p])) $kids[$p] = array();
        $kids[$p][] = $u;
    }
}

function draw_tree($user, $kids, $depth) {
    $indent = str_repeat('│   ', $depth);
    $prefix = ($depth > 0) ? '├── ' : '';
    $colors = array('#e74c3c','#3498db','#3498db','#27ae60','#27ae60','#9b59b6','#9b59b6','#e67e22','#e67e22');
    $color = isset($colors[$depth]) ? $colors[$depth] : '#888';

    $h = '<div style="font-family:monospace;font-size:13px;line-height:1.8;">';
    $h .= '<span style="color:#555">' . htmlspecialchars($indent) . '</span>';
    $h .= '<span style="color:#888">' . $prefix . '</span>';
    $h .= '<a href="user_detail.php?id=' . $user['id'] . '" style="color:' . $color . ';text-decoration:none;font-weight:bold;">';
    $h .= htmlspecialchars($user['username']);
    $h .= '</a>';
    $h .= ' <span style="color:#777">(#' . $user['id'] . ')</span>';
    if ($depth == 0) {
        $h .= ' <span style="background:#e74c3c;color:#fff;padding:1px 6px;border-radius:8px;font-size:9px">ROOT</span>';
    } else {
        $h .= ' <span style="color:#aaa;font-size:10px">Lv' . $depth . '</span>';
    }
    $h .= '</div>';

    $id = $user['id'];
    if (isset($kids[$id])) {
        foreach ($kids[$id] as $child) {
            $h .= draw_tree($child, $kids, $depth + 1);
        }
    }
    return $h;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tree View</title>
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
            background:#e67e22; 
            color:#fff; 
        }
        .wrap { 
            max-width:850px;
             margin:25px auto; 
             padding:0 15px;
             }
.back { 
    display:inline-block; 
    margin-bottom:12px;
     color:#e67e22; 
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
        <a href="view_commissions.php">Commissions</a>
        <a href="tree_view.php" class="on">Tree</a>
    </div>
</div>
<div class="wrap">
    <a href="index.php" class="back">← Dashboard</a>
    <div class="card">
        <h2>Affiliate Hierarchy</h2>
        <div style="background:#1a1a2e;padding:18px;border-radius:8px;overflow-x:auto;">
            <?php
            foreach ($roots as $root) {
                echo draw_tree($root, $kids, 0);
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>