<?php
session_start();
include("connexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Secure inputs
    $nom = $_POST['nom_produit'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $stock = $_POST['stock'];
    $categorie_id = $_POST['categorie_id'];

    // Image
    $imageData = NULL;
    if (!empty($_FILES['image']['tmp_name'])) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Insert product
    $stmt = $conn->prepare("INSERT INTO produits (nom_produit, description, prix, stock, categorie_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $null = NULL; // This must be set before bind_param
    $stmt->bind_param("ssdiib", $nom, $description, $prix, $stock, $categorie_id, $null);
    $stmt->send_long_data(5, $imageData);

    if ($stmt->execute()) {
        $id_produit = $conn->insert_id;
        $_SESSION['success'] = "✅ Produit ajouté sans promotion.";
    } else {
        $_SESSION['error'] = "❌ Erreur lors de l'ajout du produit: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect after setting session messages
    header("Location: admin.php");
    exit();  // Ensure no further code is executed
}
