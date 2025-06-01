<?php
session_start();
include("connexion.php");

// Check if the order ID is provided
if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Update the status of the order to "refused"
    $sql = "UPDATE commandes SET statut = 'refusée' WHERE id_commande = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Commande refusée avec succès.';
    } else {
        $_SESSION['error'] = 'Erreur lors du refus de la commande.';
    }

    // Close the connection
    $stmt->close();
    mysqli_close($conn);

    // Redirect back to the admin page
    header('Location: admin.php');
    exit();
} else {
    // If no order ID is set, redirect with an error message
    $_SESSION['error'] = 'ID de commande manquant.';
    header('Location: admin.php');
    exit();
}
