<?php
session_start();
require 'connexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check for hardcoded admin credentials
    if ($email === "admin@gmail.com" && $password === "AdminAdmin1@") {
        $_SESSION['user'] = [
            'id' => 0,
            'nom' => 'Admin',
            'prenom' => 'Admin',
            'email' => $email,
            'role' => 'admin'
        ];
        $_SESSION['success'] = "Connexion administrateur réussie. Bienvenue, seif !";
        header("Location: admin.php");
        exit;
    }

    // Otherwise, check against database
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Login successful
        $_SESSION['user'] = [
            'id' => $user['id_utilisateur'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $_SESSION['success'] = "Connexion réussie. Bienvenue, " . htmlspecialchars($user['prenom']) . "!";
        header("Location: home0.php");
        exit;
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: home0.php");
        exit;
    }
}
