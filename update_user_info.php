<?php
session_start();
require 'connexion.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$nom = trim($_POST['nom']);
$email = trim($_POST['email']);
$tel = trim($_POST['tel']);
$adresse = trim($_POST['adresse']);

$sql = "UPDATE utilisateurs SET nom = ?, email = ?, numero_telephone = ?, adresse = ? WHERE id_utilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $nom, $email, $tel, $adresse, $userId);

if ($stmt->execute()) {
    $_SESSION['success'] = "Informations mises à jour.";
} else {
    $_SESSION['error'] = "Erreur lors de la mise à jour.";
}

header("Location: compte.php");
exit();
