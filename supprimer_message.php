<?php
// Connect to DB
$conn = mysqli_connect("localhost", "root", "", "commerce");

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    $stmt = mysqli_prepare($conn, "DELETE FROM messages_contact WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

// Redirect back to admin page
header("Location: admin.php");
exit;
