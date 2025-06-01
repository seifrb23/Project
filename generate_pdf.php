<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../php/connexion.php'; // Adjust this path as needed



// Get order ID from URL
if (!isset($_GET['id'])) {
  die("ID de commande manquant.");
}
$orderId = (int)$_GET['id'];

// Fetch order data
$sql = "
SELECT 
  c.id_commande, 
  c.date_commande, 
  c.statut, 
  c.motif_refus, 
  u.nom AS nom_client, 
  p.nom_produit, 
  d.quantite, 
  d.prix_unitaire
FROM commandes c
LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
LEFT JOIN details_commande d ON c.id_commande = d.id_commande
LEFT JOIN produits p ON d.id_produit = p.id_produit
WHERE c.id_commande = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Commande introuvable.");
}

// Grouping results
$orderInfo = null;
$products = [];

while ($row = $result->fetch_assoc()) {
  if (!$orderInfo) {
    $orderInfo = [
      'id_commande' => $row['id_commande'],
      'date_commande' => $row['date_commande'],
      'statut' => $row['statut'],
      'motif_refus' => $row['motif_refus'],
      'nom_client' => $row['nom_client']
    ];
  }

  $products[] = [
    'nom_produit' => $row['nom_produit'],
    'quantite' => $row['quantite'],
    'prix_unitaire' => $row['prix_unitaire']
  ];
}

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Détails de la Commande'), 0, 1);
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(0, 10, utf8_decode("Commande #: {$orderInfo['id_commande']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Date: {$orderInfo['date_commande']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Client: {$orderInfo['nom_client']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Statut: {$orderInfo['statut']}"), 0, 1);
if ($orderInfo['statut'] === 'Refusée' && !empty($orderInfo['motif_refus'])) {
  $pdf->MultiCell(0, 10, utf8_decode("Motif de refus: {$orderInfo['motif_refus']}"));
}

// Add a line break
$pdf->Ln(5);

// Product table header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, utf8_decode('Produit'), 1);
$pdf->Cell(30, 10, 'Quantité', 1);
$pdf->Cell(40, 10, 'Prix Unitaire', 1);
$pdf->Ln();

// Product data
// Product data
$pdf->SetFont('Arial', '', 12);
$total = 0;

foreach ($products as $prod) {
  $prixTotalProduit = $prod['quantite'] * $prod['prix_unitaire'];
  $total += $prixTotalProduit;

  $pdf->Cell(80, 10, utf8_decode($prod['nom_produit']), 1);
  $pdf->Cell(30, 10, $prod['quantite'], 1);
  $pdf->Cell(40, 10, number_format($prod['prix_unitaire'], 2) . ' DA', 1);
  $pdf->Ln();
}

// Total row
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(110, 10, 'Total', 1);
$pdf->Cell(40, 10, number_format($total, 2) . ' DA', 1);
$pdf->Ln();


// Output PDF
$pdf->Output('D', "commande_{$orderId}.pdf"); 