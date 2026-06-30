<?php
// ============================================================
// GYM MANAGEMENT SYSTEM - SINGLE FILE VERSION
// Update DB credentials below, import the SQL block once, then
// just upload this one file to your server.
// ============================================================

// ---------- DATABASE CONNECTION ----------
$host = "localhost";
$dbname = "gym_db";
$dbuser = "root";
$dbpass = "";

try {
    // Connect to MySQL server first (no database selected yet)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist yet
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");

    // Now connect to the actual database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables if they don't exist yet
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            address VARCHAR(255),
            plan VARCHAR(50) DEFAULT 'Monthly',
            fee_amount DECIMAL(10,2) DEFAULT 0,
            join_date DATE NOT NULL,
            status ENUM('Active','Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            month_year VARCHAR(7) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            paid_date DATE,
            payment_status ENUM('Paid','Unpaid') DEFAULT 'Unpaid',
            notes VARCHAR(255),
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            UNIQUE KEY unique_member_month (member_id, month_year)
        )
    ");

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ---------- ROUTING ----------
$page = $_GET['page'] ?? 'list';
$id = $_GET['id'] ?? 0;

// ---------- HANDLE: ADD MEMBER ----------
if ($page === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $plan = $_POST['plan'];
    $fee = $_POST['fee_amount'];
    $join_date = $_POST['join_date'];

    if ($name && $phone && $join_date) {
        $stmt = $pdo->prepare("INSERT INTO members (name, phone, email, address, plan, fee_amount, join_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$name, $phone, $email, $address, $plan, $fee, $join_date]);
        $memberId = $pdo->lastInsertId();
        $monthYear = date('Y-m', strtotime($join_date));
        $stmt2 = $pdo->prepare("INSERT IGNORE INTO payments (member_id, month_year, amount, payment_status) VALUES (?,?,?, 'Unpaid')");
        $stmt2->execute([$memberId, $monthYear, $fee]);
        header("Location: ?page=profile&id=$memberId");
        exit;
    } else {
        $error = "Name, phone, and join date are required.";
    }
}

// ---------- HANDLE: ADD/UPDATE PAYMENT ----------
if ($page === 'profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $month_year = $_POST['month_year'];
    $amount = $_POST['amount'];
    $status = $_POST['payment_status'];
    $paid_date = $status === 'Paid' ? ($_POST['paid_date'] ?: date('Y-m-d')) : null;

    $stmt = $pdo->prepare("INSERT INTO payments (member_id, month_year, amount, payment_status, paid_date)
                            VALUES (?,?,?,?,?)
                            ON DUPLICATE KEY UPDATE amount=?, payment_status=?, paid_date=?");
    $stmt->execute([$id, $month_year, $amount, $status, $paid_date, $amount, $status, $paid_date]);
    header("Location: ?page=profile&id=$id");
    exit;
}

// ---------- HANDLE: DELETE MEMBER ----------
if ($page === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ?page=list");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Gym Manager</title>
<style>
* { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
body { background: #f4f6f8; margin: 0; color: #222; }
header { background: #1f2937; color: #fff; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
header a { color: #fff; text-decoration: none; margin-left: 16px; font-size: 14px; }
.container { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
.card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
th { background: #f9fafb; }
.btn { display: inline-block; background: #2563eb; color: #fff; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
.btn.gray { background: #6b7280; }
.btn.red { background: #dc2626; }
.btn.green { background: #16a34a; }
input, select { padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%; margin-bottom: 12px; font-size: 14px; }
label { font-size: 13px; font-weight: bold; color: #374151; }
.badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; color: #fff; }
.badge.Paid, .badge.Active { background: #16a34a; }
.badge.Unpaid, .badge.Inactive { background: #dc2626; }
.row { display: flex; gap: 16px; }
.row > div { flex: 1; }
</style>
</head>
<body>
<header>
    <strong>Gym Manager</strong>
    <div>
        <a href="?page=list">Members</a>
        <a href="?page=add">+ Add Member</a>
    </div>
</header>
<div class="container">

<?php if ($page === 'list'): ?>
    <?php
    $search = $_GET['search'] ?? '';
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE name LIKE ? OR phone LIKE ? ORDER BY id DESC");
        $like = "%$search%";
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query("SELECT * FROM members ORDER BY id DESC");
    }
    $members = $stmt->fetchAll();
    ?>
    <div class="card">
        <form method="get" style="display:flex; gap:10px;">
            <input type="hidden" name="page" value="list">
            <input type="text" name="search" placeholder="Search by name or phone" value="<?= htmlspecialchars($search) ?>">
            <button class="btn" type="submit">Search</button>
        </form>
    </div>
    <div class="card">
        <h3>Members (<?= count($members) ?>)</h3>
        <table>
            <tr><th>Name</th><th>Phone</th><th>Plan</th><th>Join Date</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['phone']) ?></td>
                <td><?= htmlspecialchars($m['plan']) ?></td>
                <td><?= htmlspecialchars($m['join_date']) ?></td>
                <td><span class="badge <?= $m['status'] ?>"><?= $m['status'] ?></span></td>
                <td>
                    <a class="btn" href="?page=profile&id=<?= $m['id'] ?>">View / Pay</a>
                    <a class="btn red" href="?page=delete&id=<?= $m['id'] ?>" onclick="return confirm('Delete this member?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($members)): ?>
            <tr><td colspan="6">No members found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

<?php elseif ($page === 'add'): ?>
    <div class="card">
        <h3>Add New Member</h3>
        <?php if (!empty($error)): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post" action="?page=add">
            <label>Full Name</label>
            <input type="text" name="name" required>
            <label>Phone</label>
            <input type="text" name="phone" required>
            <label>Email</label>
            <input type="email" name="email">
            <label>Address</label>
            <input type="text" name="address">
            <div class="row">
                <div>
                    <label>Plan</label>
                    <select name="plan">
                        <option>Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                    </select>
                </div>
                <div>
                    <label>Monthly Fee (₹)</label>
                    <input type="number" step="0.01" name="fee_amount" required>
                </div>
            </div>
            <label>Join Date</label>
            <input type="date" name="join_date" value="<?= date('Y-m-d') ?>" required>
            <button class="btn green" type="submit">Save Member</button>
            <a class="btn gray" href="?page=list">Cancel</a>
        </form>
    </div>

<?php elseif ($page === 'profile'): ?>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    if (!$member) { die("Member not found."); }

    $stmt = $pdo->prepare("SELECT * FROM payments WHERE member_id = ? ORDER BY month_year DESC");
    $stmt->execute([$id]);
    $payments = $stmt->fetchAll();
    ?>
    <div class="card">
        <h3><?= htmlspecialchars($member['name']) ?> <span class="badge <?= $member['status'] ?>"><?= $member['status'] ?></span></h3>
        <p>Phone: <?= htmlspecialchars($member['phone']) ?> &nbsp; | &nbsp; Email: <?= htmlspecialchars($member['email']) ?></p>
        <p>Plan: <?= htmlspecialchars($member['plan']) ?> &nbsp; | &nbsp; Fee: ₹<?= htmlspecialchars($member['fee_amount']) ?> &nbsp; | &nbsp; Joined: <?= htmlspecialchars($member['join_date']) ?></p>
    </div>
    <div class="card">
        <h3>Add / Update Monthly Payment</h3>
        <form method="post" action="?page=profile&id=<?= $id ?>">
            <div class="row">
                <div>
                    <label>Month</label>
                    <input type="month" name="month_year" value="<?= date('Y-m') ?>" required>
                </div>
                <div>
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($member['fee_amount']) ?>" required>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>Status</label>
                    <select name="payment_status">
                        <option value="Paid">Paid</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>
                <div>
                    <label>Paid Date</label>
                    <input type="date" name="paid_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button class="btn green" type="submit">Save Record</button>
        </form>
    </div>
    <div class="card">
        <h3>Monthly Payment History</h3>
        <table>
            <tr><th>Month</th><th>Amount</th><th>Status</th><th>Paid Date</th></tr>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['month_year']) ?></td>
                <td>₹<?= htmlspecialchars($p['amount']) ?></td>
                <td><span class="badge <?= $p['payment_status'] ?>"><?= $p['payment_status'] ?></span></td>
                <td><?= htmlspecialchars($p['paid_date'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($payments)): ?>
            <tr><td colspan="4">No payment records yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
<?php endif; ?>

</div>
</body>
</html>

