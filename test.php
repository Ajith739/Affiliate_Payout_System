<?php
require_once 'database/db.php';

$pass = 0;
$fail = 0;

function ok($cond, $label) {
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "<div style='color:#155724;padding:3px 10px;font-size:13px;'>✔ $label</div>";
    } else {
        $fail++;
        echo "<div style='color:#721c24;padding:3px 10px;font-weight:bold;'>✘ $label</div>";
    }
}

function eq($exp, $act, $label) {
    ok($exp == $act, "$label (expected: $exp, got: $act)");
}

// Commission distribution function (same logic as record_sale.php)
function test_distribute($conn, $sale_id) {
    $sale_id = intval($sale_id);

    $r = mysqli_query($conn, "SELECT s.id, s.user_id, s.amount, s.status, u.username
                               FROM sales s JOIN users u ON u.id = s.user_id
                               WHERE s.id=$sale_id");
    $sale = mysqli_fetch_assoc($r);
    if (!$sale) return array('ok'=>false, 'msg'=>'No sale', 'comms'=>array(), 'total'=>0);
    if ($sale['status']!='completed') return array('ok'=>false, 'msg'=>'Not completed', 'comms'=>array(), 'total'=>0);

    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM commissions WHERE sale_id=$sale_id");
    if (mysqli_fetch_assoc($r)['c'] > 0) return array('ok'=>false, 'msg'=>'Already distributed', 'comms'=>array(), 'total'=>0);

    $rates = array(1=>10, 2=>5, 3=>3, 4=>2, 5=>1);
    $uid = intval($sale['user_id']);
    $amt = floatval($sale['amount']);

    mysqli_begin_transaction($conn);
    mysqli_query($conn, "SELECT id FROM sales WHERE id=$sale_id FOR UPDATE");

    $curr = $uid;
    $lv = 0;
    $tp = 0;
    $comms = array();
    $good = true;

    while ($lv < 5) {
        $r = mysqli_query($conn, "SELECT p.id, p.username, p.full_name, p.parent_id, p.status
                                   FROM users c JOIN users p ON p.id = c.parent_id
                                   WHERE c.id=$curr");
        if (!$r) { $good = false; break; }
        $par = mysqli_fetch_assoc($r);
        if (!$par) break;

        $lv++;
        $rate = $rates[$lv];
        $comm = round($amt * $rate / 100, 2);

        if ($par['status'] == 'active') {
            $pid = intval($par['id']);
            $ir = mysqli_query($conn, "INSERT INTO commissions
                    (sale_id, beneficiary_id, source_user_id, level, sale_amount, commission_rate, commission_amount, status)
                    VALUES ($sale_id, $pid, $uid, $lv, $amt, $rate, $comm, 'pending')");
            if (!$ir) { $good = false; break; }

            $c = array();
            $c['who'] = $par['username'];
            $c['name'] = $par['full_name'];
            $c['level'] = $lv;
            $c['rate'] = $rate;
            $c['commission'] = $comm;
            $comms[] = $c;
            $tp += $comm;
        }
        $curr = intval($par['id']);
    }

    if ($good) {
        mysqli_commit($conn);
        return array('ok'=>true, 'msg'=>'OK', 'comms'=>$comms, 'total'=>$tp);
    } else {
        mysqli_rollback($conn);
        return array('ok'=>false, 'msg'=>mysqli_error($conn), 'comms'=>array(), 'total'=>0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tests</title>
    <style>
        body { font-family:Arial,sans-serif; max-width:850px; margin:25px auto; padding:0 15px; background:#f0f2f5; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.07); margin-bottom:16px; }
        h1 { color:#2c3e50; margin-bottom:16px; font-size:22px; }
        h3 { color:#2c3e50; margin:16px 0 6px; padding-bottom:4px; border-bottom:2px solid #3498db; font-size:13px; }
        pre { background:#1a1a2e; color:#dcdcdc; padding:12px; border-radius:5px; font-size:12px; overflow-x:auto; }
        a { color:#3498db; }
    </style>
</head>
<body>
<h1>🧪 Test Suite</h1>
<div class="card">
<?php
// Clean
mysqli_query($conn, "DELETE FROM commissions");
mysqli_query($conn, "DELETE FROM sales");

// ============ TEST 1 ============
echo "<h3>TEST 1: Hank (#8) sells \$1,000 — 5 levels paid</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, description, status) VALUES (8, 1000, 'Test Hank', 'completed')");
$sid1 = mysqli_insert_id($conn);
$r1 = test_distribute($conn, $sid1);

ok($r1['ok'], 'Distribution succeeded');
eq(5, count($r1['comms']), '5 levels');
eq(100, $r1['comms'][0]['commission'], 'L1: Grace $100 (10%)');
eq(50,  $r1['comms'][1]['commission'], 'L2: Frank $50 (5%)');
eq(30,  $r1['comms'][2]['commission'], 'L3: Eve $30 (3%)');
eq(20,  $r1['comms'][3]['commission'], 'L4: Diana $20 (2%)');
eq(10,  $r1['comms'][4]['commission'], 'L5: Charlie $10 (1%)');
eq(210, $r1['total'], 'Total $210');

$r = mysqli_query($conn, "SELECT COALESCE(SUM(commission_amount),0) as t FROM commissions WHERE beneficiary_id=2");
eq(0, floatval(mysqli_fetch_assoc($r)['t']), 'Bob (#2) = $0');
$r = mysqli_query($conn, "SELECT COALESCE(SUM(commission_amount),0) as t FROM commissions WHERE beneficiary_id=1");
eq(0, floatval(mysqli_fetch_assoc($r)['t']), 'Alice (#1) = $0');

// ============ TEST 2 ============
echo "<h3>TEST 2: Frank (#6) sells \$500 — 4 ancestors</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (6, 500, 'completed')");
$sid2 = mysqli_insert_id($conn);
$r2 = test_distribute($conn, $sid2);

ok($r2['ok'], 'Succeeded');
eq(4, count($r2['comms']), '4 levels');
eq(50, $r2['comms'][0]['commission'], 'L1: Eve $50');
eq(25, $r2['comms'][1]['commission'], 'L2: Diana $25');
eq(15, $r2['comms'][2]['commission'], 'L3: Charlie $15');
eq(10, $r2['comms'][3]['commission'], 'L4: Bob $10');

// ============ TEST 3 ============
echo "<h3>TEST 3: Ivan (#9) sells \$200 — 2 ancestors</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (9, 200, 'completed')");
$sid3 = mysqli_insert_id($conn);
$r3 = test_distribute($conn, $sid3);

ok($r3['ok'], 'Succeeded');
eq(2, count($r3['comms']), '2 levels');
eq(20, $r3['comms'][0]['commission'], 'L1: Bob $20');
eq(10, $r3['comms'][1]['commission'], 'L2: Alice $10');

// ============ TEST 4 ============
echo "<h3>TEST 4: Alice (#1 ROOT) sells \$100 — no parent</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (1, 100, 'completed')");
$sid4 = mysqli_insert_id($conn);
$r4 = test_distribute($conn, $sid4);

ok($r4['ok'], 'Succeeded');
eq(0, count($r4['comms']), 'Zero commissions');

// ============ TEST 5 ============
echo "<h3>TEST 5: Duplicate prevention</h3>";
$r5 = test_distribute($conn, $sid1);
ok(!$r5['ok'], 'Blocked');
ok(strpos($r5['msg'], 'Already')!==false, 'Correct message');

// ============ TEST 6 ============
echo "<h3>TEST 6: Non-existent sale</h3>";
$r6 = test_distribute($conn, 999999);
ok(!$r6['ok'], 'Rejected');

// ============ TEST 7 ============
echo "<h3>TEST 7: Inactive parent skipped</h3>";
mysqli_query($conn, "UPDATE users SET status='inactive' WHERE id=7");
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (8, 1000, 'completed')");
$sid7 = mysqli_insert_id($conn);
$r7 = test_distribute($conn, $sid7);

ok($r7['ok'], 'Succeeded');
eq(4, count($r7['comms']), '4 paid (Grace skipped)');
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM commissions WHERE sale_id=$sid7 AND beneficiary_id=7");
eq(0, mysqli_fetch_assoc($r)['c'], 'Grace got nothing');
mysqli_query($conn, "UPDATE users SET status='active' WHERE id=7");

// ============ TEST 8 ============
echo "<h3>TEST 8: Bob (#2) sells \$800 — 1 ancestor</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (2, 800, 'completed')");
$sid8 = mysqli_insert_id($conn);
$r8 = test_distribute($conn, $sid8);

ok($r8['ok'], 'Succeeded');
eq(1, count($r8['comms']), '1 level');
eq(80, $r8['comms'][0]['commission'], 'L1: Alice $80');

// ============ TEST 9 ============
echo "<h3>TEST 9: Jane (#10) sells \$300</h3>";
mysqli_query($conn, "INSERT INTO sales (user_id, amount, status) VALUES (10, 300, 'completed')");
$sid9 = mysqli_insert_id($conn);
$r9 = test_distribute($conn, $sid9);

ok($r9['ok'], 'Succeeded');
eq(1, count($r9['comms']), '1 level');
eq(30, $r9['comms'][0]['commission'], 'L1: Alice $30');
?>

<hr style="margin:18px 0">
<?php
$total = $pass + $fail;
$bg = ($fail==0) ? '#d4edda' : '#f8d7da';
$cl = ($fail==0) ? '#155724' : '#721c24';
?>
<div style="padding:18px;background:<?php echo $bg; ?>;color:<?php echo $cl; ?>;border-radius:8px;text-align:center;">
    <h2><?php echo ($fail==0) ? '✔ ALL TESTS PASSED!' : "✘ $fail FAILED"; ?></h2>
    <p>Passed: <?php echo $pass; ?> | Failed: <?php echo $fail; ?> | Total: <?php echo $total; ?></p>
</div>

<h3>Expected: $1,000 Sale by Hank (#8)</h3>
<pre>
Hank (#8) sells $1,000

L1: Grace   (#7) → 10% → $100.00 ✔
L2: Frank   (#6) →  5% →  $50.00 ✔
L3: Eve     (#5) →  3% →  $30.00 ✔
L4: Diana   (#4) →  2% →  $20.00 ✔
L5: Charlie (#3) →  1% →  $10.00 ✔
── STOP ─────────────────────────
Bob     (#2) → $0 (level 6)
Alice   (#1) → $0 (level 7)
─────────────────────────────────
TOTAL: $210.00 (21%)
</pre>

</div>

<p style="text-align:center;margin:16px"><a href="index.php">← Dashboard</a> | <a href="record_sale.php">Record Sale →</a></p>
</body>
</html>
<?php mysqli_close($conn); ?>