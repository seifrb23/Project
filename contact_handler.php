<?php
// Check if form submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get data
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $message = $_POST['message'] ?? '';

    // 2. Basic validation
    if (empty($nom) || empty($email) || empty($message)) {
        // Redirect with error
        header("Location: ../pages/contact.php?error=1");
        exit;
    }

    // 3. Connect to DB
    $host = 'localhost';
    $dbname = 'commerce';
    $username = 'root';
    $password = ''; // Change this if needed

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 4. Insert query
        $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, telephone, message, date_message) 
                               VALUES (:nom, :email, :telephone, :message, NOW())");

        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':message' => $message
        ]);

        // 5. Redirect with success
        header("Location: ../php/contact.php?sent=1");
        exit;
    } catch (PDOException $e) {
        // Debug error (only in dev)
        error_log("DB ERROR: " . $e->getMessage());
        header("Location: ../php/contact.php?error=2");
        exit;
    }
} else {
    // If not POST, redirect to form
    header("Location: ../php/contact.php");
    exit;
}
