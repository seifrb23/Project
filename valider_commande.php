<?php
session_start();
include("../php/connexion.php");

if (empty($_SESSION['panier'])) {
    echo json_encode(['success' => false, 'message' => "Votre panier est vide."]);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => "Utilisateur non connecté."]);
    exit;
}

// Insert into commandes
$stmt = $conn->prepare("INSERT INTO commandes (id_utilisateur) VALUES (?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$commande_id = $stmt->insert_id;
$stmt->close();

// Prepare statements
$insertDetail = $conn->prepare(
    "INSERT INTO details_commande (id_commande, id_produit, quantite, prix_unitaire)
     VALUES (?, ?, ?, ?)"
);

$updateStock = $conn->prepare(
    "UPDATE produits SET stock = stock - ? WHERE id_produit = ? AND stock >= ?"
);

// Process each cart item
foreach ($_SESSION['panier'] as $item) {
    $id_produit = (int)$item['id_produit'];
    $quantite = (int)$item['quantite'];
    $prix = (float)$item['prix'];

    // Check product exists
    $check = $conn->query("SELECT stock FROM produits WHERE id_produit = $id_produit");
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => "Produit $id_produit introuvable."]);
        exit;
    }

    $row = $check->fetch_assoc();
    if ($row['stock'] < $quantite) {
        echo json_encode(['success' => false, 'message' => "Stock insuffisant pour le produit ID $id_produit."]);
        exit;
    }

    // Insert detail
    $insertDetail->bind_param("iiid", $commande_id, $id_produit, $quantite, $prix);
    $insertDetail->execute();

    // Decrease stock
    $updateStock->bind_param("iii", $quantite, $id_produit, $quantite);
    $updateStock->execute();
}

$insertDetail->close();
$updateStock->close();

$_SESSION['panier'] = []; // Clear cart
echo json_encode(['success' => true, 'message' => "Commande validée avec succès."]);
