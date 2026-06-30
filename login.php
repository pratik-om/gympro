<?php
session_start();

/* ============================
   DATABASE CONFIGURATION
   ============================ */
$host = "sql313.infinityfree.com";
$dbname = "if0_42297052_store2";
$dbuser = "if0_42297052";
$dbpass = "1ck4ElaGPHVLb";

/* ============================
   CONNECT DATABASE
   ============================ */
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $dbuser,
        $dbpass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed!");
}

/* ============================
   LOGIN PROCESS
   ============================ */
$error = "";

if(isset($_POST['login'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username=? LIMIT 1");
    $stmt->execute([$username]);

    if($stmt->rowCount() > 0){

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if(password_verify($password,$admin['password'])){

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];

            header("Location: dashboard.php");
            exit;

        }else{
            $error = "Invalid Password";
        }

    }else{
        $error = "Invalid Username";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Gym Management Login</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,Helvetica,sans-serif;
}

body{

background:linear-gradient(135deg,#4facfe,#00f2fe);

height:100vh;

display:flex;
justify-content:center;
align-items:center;

}

.login-box{

width:370px;

background:#fff;

padding:40px;

border-radius:15px;

box-shadow:0 10px 30px rgba(0,0,0,.2);

}

.logo{

font-size:35px;

text-align:center;

margin-bottom:10px;

}

h2{

text-align:center;

margin-bottom:30px;

color:#333;

}

input{

width:100%;

padding:14px;

margin-bottom:18px;

border:1px solid #ccc;

border-radius:8px;

font-size:16px;

}

button{

width:100%;

padding:14px;

background:#007bff;

color:#fff;

border:none;

border-radius:8px;

font-size:17px;

cursor:pointer;

transition:.3s;

}

button:hover{

background:#0056b3;

}

.error{

background:#ffd7d7;

color:red;

padding:10px;

margin-bottom:15px;

border-radius:5px;

text-align:center;

}

.footer{

margin-top:20px;

text-align:center;

color:#777;

font-size:14px;

}

</style>

</head>

<body>

<div class="login-box">

<div class="logo">🏋️</div>

<h2>Gym Management</h2>

<?php
if($error!=""){
echo "<div class='error'>$error</div>";
}
?>

<form method="post">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button
type="submit"
name="login">

Login

</button>

</form>

<div class="footer">

© <?php echo date("Y"); ?>

Gym Management System

</div>

</div>

</body>
</html>
