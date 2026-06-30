<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_name'])) {
    header("Location: login.php");
    exit();
}

/*
====================================================
Later you will replace these values with database
queries.
====================================================
*/

$totalMembers = 120;
$activeMembers = 96;
$expiredMembers = 24;
$monthlyCollection = 78500;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | Gym Management</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#eef2f7;
}

.sidebar{
position:fixed;
left:0;
top:0;
width:250px;
height:100vh;
background:#1f2937;
color:white;
padding:25px;
}

.sidebar h2{
text-align:center;
margin-bottom:35px;
}

.sidebar a{
display:block;
color:white;
text-decoration:none;
padding:14px;
margin:8px 0;
border-radius:8px;
transition:.3s;
}

.sidebar a:hover{
background:#374151;
}

.main{
margin-left:250px;
padding:30px;
}

.topbar{
background:white;
padding:18px 25px;
border-radius:12px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:25px;
}

.cards{

display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;

}

.card{

background:white;
padding:25px;
border-radius:15px;
box-shadow:0 8px 20px rgba(0,0,0,.08);
transition:.3s;

}

.card:hover{

transform:translateY(-5px);

}

.card h3{

font-size:18px;
color:#666;

}

.card h1{

margin-top:15px;
font-size:35px;

}

.blue{
border-left:8px solid #3498db;
}

.green{
border-left:8px solid #2ecc71;
}

.red{
border-left:8px solid #e74c3c;
}

.orange{
border-left:8px solid #f39c12;
}

.actions{

margin-top:35px;

display:grid;

grid-template-columns:repeat(auto-fit,minmax(200px,1fr));

gap:20px;

}

.btn{

background:#2563eb;

color:white;

text-decoration:none;

padding:18px;

text-align:center;

border-radius:12px;

font-size:17px;

transition:.3s;

}

.btn:hover{

background:#1d4ed8;

}

table{

margin-top:35px;

width:100%;

background:white;

border-collapse:collapse;

border-radius:10px;

overflow:hidden;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

th,td{

padding:15px;

text-align:left;

}

th{

background:#2563eb;

color:white;

}

tr:nth-child(even){

background:#f8f9fa;

}

</style>

</head>

<body>

<div class="sidebar">

<h2>🏋 Gym Admin</h2>

<a href="#">🏠 Dashboard</a>

<a href="add-member.php">➕ Add Member</a>

<a href="members.php">👥 Members</a>

<a href="fees.php">💰 Fee Collection</a>

<a href="reports.php">📊 Reports</a>

<a href="logout.php">🚪 Logout</a>

</div>

<div class="main">

<div class="topbar">

<h2>Welcome, <?php echo $_SESSION['admin_name']; ?></h2>

<div>

<?php echo date("d M Y"); ?>

</div>

</div>

<div class="cards">

<div class="card blue">

<h3>Total Members</h3>

<h1><?php echo $totalMembers; ?></h1>

</div>

<div class="card green">

<h3>Active Members</h3>

<h1><?php echo $activeMembers; ?></h1>

</div>

<div class="card red">

<h3>Expired Members</h3>

<h1><?php echo $expiredMembers; ?></h1>

</div>

<div class="card orange">

<h3>Monthly Collection</h3>

<h1>₹<?php echo number_format($monthlyCollection); ?></h1>

</div>

</div>

<div class="actions">

<a class="btn" href="add-member.php">
➕ Add Member
</a>

<a class="btn" href="members.php">
👥 View Members
</a>

<a class="btn" href="fees.php">
💰 Collect Fees
</a>

<a class="btn" href="reports.php">
📊 Reports
</a>

</div>

<table>

<tr>

<th>Recent Members</th>

<th>Mobile</th>

<th>Plan</th>

<th>Status</th>

</tr>

<tr>

<td>Rahul Sharma</td>

<td>9876543210</td>

<td>Monthly</td>

<td style="color:green;">Active</td>

</tr>

<tr>

<td>Amit Singh</td>

<td>9988776655</td>

<td>Quarterly</td>

<td style="color:red;">Expired</td>

</tr>

<tr>

<td>Rohit Kumar</td>

<td>9123456780</td>

<td>Yearly</td>

<td style="color:green;">Active</td>

</tr>

</table>

</div>

</body>

</html>
