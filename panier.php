<?php
session_start();
$panier = $_SESSION['panier'] ?? [];

if (empty($panier)) {
    echo "<p>Votre panier est vide.</p>";
    exit;
}

$total = 0;
echo "<ul>";
foreach ($panier as $item) {
    $ligne = htmlspecialchars($item['nom']) .
        " — {$item['quantite']} × " .
        number_format($item['prix'], 2) . " DA";
    echo "<li>$ligne</li>";
    $total += $item['prix'] * $item['quantite'];
}
echo "</ul>";
echo "<div class='panier-total'><strong>Total : "
    . number_format($total, 2) . " DA</strong></div>";
