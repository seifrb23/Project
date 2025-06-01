<?php
session_start();
require 'connexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Split fullname into nom and prenom
    $names = explode(" ", $fullname, 2);
    $nom = $names[0];
    $prenom = isset($names[1]) ? $names[1] : '';

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email déjà utilisé.";
        header("Location: home0.php");
        exit;
    }

    $stmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, numero_telephone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nom, $prenom, $email, $password, $phone);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Compte créé avec succès. Vous pouvez vous connecter.";
    } else {
        $_SESSION['error'] = "Erreur lors de la création du compte.";
    }

    $stmt->close();
    header("Location: home0.php");
    exit;
}
