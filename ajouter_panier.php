<?php
session_start();
if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];

$id       = $_POST['id'];                     // id_produit
$nom      = $_POST['nom'];
$prix     = floatval($_POST['prix']);
$quantite = max(1, intval($_POST['quantite']));

if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id]['quantite'] += $quantite;
} else {
    $_SESSION['panier'][$id] = [
        'id_produit' => $id,
        'nom'        => $nom,
        'prix'       => $prix,
        'quantite'   => $quantite
    ];
}

echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier.']);
