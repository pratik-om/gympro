<?php
include 'db.php';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $enroll_date = $_POST['enrollment_date'];
    $fee = $_POST['monthly_fee'];
    
    // Automatically calculate expiry date (1 month later)
    $expiry_date = date('Y-m-d', strtotime($enroll_date . ' + 1 month'));

    $stmt = $conn->prepare("INSERT INTO members (full_name, phone, enrollment_date, expiry_date, monthly_fee) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $name, $phone, $enroll_date, $expiry_date, $fee);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all members
$result = $conn->query("SELECT * FROM members ORDER BY enrollment_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Monthly Records</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f4f9; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; padding: 15px; background: #eee; border-radius: 5px; }
        input, button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        button { background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        .status-active { color: green; font-weight: bold; }
        .status-expired { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>🏋️‍♂️ Gym Monthly Enrollment Records</h2>
    
    <form method="POST" action="">
        <input type="text" name="full_name" placeholder="Member Full Name" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="date" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>" required>
        <input type="number" name="monthly_fee" placeholder="Monthly Fee ($)" step="0.01" required>
        <button type="submit" name="add_member">Enroll Member</button>
    </form>

    <h3>Active & Past Members</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Enrollment Date</th>
                <th>Expiry Date</th>
                <th>Fee Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        // Dynamically update status if the membership expired
                        $current_date = date('Y-m-d');
                        $status = ($current_date > $row['expiry_date']) ? 'Expired' : 'Active';
                        $status_class = ($status == 'Active') ? 'status-active' : 'status-expired';
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo $row['enrollment_date']; ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td>$<?php echo number_format($row['monthly_fee'], 2); ?></td>
                        <span class="<?php echo $status_class; ?>"><?php echo $status; ?></span>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No members enrolled yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
