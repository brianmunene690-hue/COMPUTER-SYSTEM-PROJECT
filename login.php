<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
?>
<?php
header("Content-Type: application/json");

$host = "localhost";
$dbname = "kamau inventory";
$user = "root";
$password = "";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$username = trim($_POST["username"] ?? "");
$passwordInput = trim($_POST["password"] ?? "");
$role = trim($_POST["role"] ?? "");

if ($username == "" || $passwordInput == "" || $role == "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

$sql = "SELECT * FROM users WHERE username=? AND role=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $role);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid username or role."
    ]);
    exit;
}

$user = $result->fetch_assoc();

/*
If passwords are stored using password_hash()
*/

if (!password_verify($passwordInput, $user["password"])) {

    echo json_encode([
        "success" => false,
        "message" => "Incorrect password."
    ]);
    exit;
}

unset($user["password"]);

echo json_encode([
    "success" => true,
    "user" => $user
]);

$stmt->close();
$conn->close();
?>