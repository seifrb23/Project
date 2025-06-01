<?php
include("connexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']);

    // Optional: Define a new status like "vu" or "acknowledged"
    $newStatus = 'vu';

    $sql = "UPDATE commandes SET statut = ? WHERE id_commande = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderId);
        if (mysqli_stmt_execute($stmt)) {
            // Redirect back to the orders page
            header("Location: admin.php"); // change this to your actual orders page
            exit;
        } else {
            echo "Erreur lors de la mise à jour de la commande.";
        }
    } else {
        echo "Erreur de préparation de la requête.";
    }
} else {
    echo "Requête invalide.";
}
