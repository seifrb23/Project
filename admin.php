<!DOCTYPE html>

<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration - Commandes</title>
  <link rel="stylesheet" href="../css/admin.css" />
</head>

<body>

  <header class="admin-header">
    <div class="container">
      <h1>🔧 Panneau d'administration</h1>
    </div>
  </header>
  <?php
  session_start();
  ?>

  <!-- Display Success or Error message -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="popup-message popup-error"><?php echo $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="popup-message popup-success"><?php echo $_SESSION['success'];
                                              unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <!-- Script to hide the message after 4 seconds -->
  <script>
    setTimeout(() => {
      const popup = document.querySelector('.popup-message');
      if (popup) {
        popup.style.opacity = '0';
        setTimeout(() => popup.remove(), 500);
      }
    }, 4000);
  </script>

  <main class="container">
    <section class="commandes-section">
      <h2>📦 Commandes en attente</h2>

      <!-- Loading indicator -->
      <div id="loadingIndicator" class="loading">
        <p>Chargement des commandes...</p>
      </div>

      <!-- Container where the orders will be listed dynamically -->
      <!-- Container where the orders will be listed dynamically -->
      <div id="commandesContainer" class="commandes-liste">
        <?php
        // Include your database connection
        include("connexion.php");

        // Query to fetch pending orders with product details
        $sql = "SELECT 
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
        ORDER BY c.id_commande";

        $result = mysqli_query($conn, $sql);

        $commandes = [];

        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['id_commande'];
          if (!isset($commandes[$id])) {
            $commandes[$id] = [
              'id_commande' => $row['id_commande'],
              'date_commande' => $row['date_commande'],
              'statut' => $row['statut'],
              'motif_refus' => $row['motif_refus'],
              'nom_client' => $row['nom_client'],
              'produits' => []
            ];
          }

          $commandes[$id]['produits'][] = [
            'nom_produit' => $row['nom_produit'],
            'quantite' => $row['quantite'],
            'prix_unitaire' => $row['prix_unitaire']
          ];
        }

        if (empty($commandes)) {
          echo '<p>Aucune commande en attente.</p>';
        } else {
          foreach ($commandes as $commande) {
            echo '<div class="commande-item">';
            echo '<h4>Commande #' . $commande['id_commande'] . '</h4>';
            echo '<p><strong>Client:</strong> ' . htmlspecialchars($commande['nom_client']) . '</p>';
            echo '<p><strong>Date:</strong> ' . htmlspecialchars($commande['date_commande']) . '</p>';
            echo '<p><strong>Statut:</strong> ' . htmlspecialchars($commande['statut']) . '</p>';

            // If refused (shouldn't happen since we filtered for "en attente"), show refusal reason
            if ($commande['statut'] == 'refusée' && $commande['motif_refus']) {
              echo '<p><strong>Motif de refus:</strong> ' . htmlspecialchars($commande['motif_refus']) . '</p>';
            }

            // Product list
            echo '<p><strong>Produits:</strong><ul>';
            foreach ($commande['produits'] as $produit) {
              echo '<li>';
              echo htmlspecialchars($produit['nom_produit']) . ' - ';
              echo 'Quantité: ' . htmlspecialchars($produit['quantite']) . ' | ';
              echo 'Prix unitaire: ' . htmlspecialchars($produit['prix_unitaire']) . ' DA';
              echo '</li>';
            }
            echo '</ul></p>';

            // Acknowledge button
            echo '<form action="acknowledge_order.php" method="POST" style="display:inline;">
            <input type="hidden" name="order_id" value="' . $commande['id_commande'] . '" />
            <button type="submit" class="ok-btn">OK</button>
            </form>';



            echo '</div>';
          }
        }
        ?>
      </div>

    </section>



  </main>

  <div class="modal" id="refusModal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h3>Raison du refus</h3>
      <textarea id="raisonRefus" placeholder="Indiquez la raison..."></textarea>
      <button id="confirmerRefusBtn" class="btn-confirm">Confirmer</button>
    </div>
  </div>

  <section class="questions-section">
    <h2>💬 Questions des clients</h2>
    <div id="questionsContainer">
      <?php
      $query = "SELECT * FROM messages_contact ORDER BY date_message DESC";
      $result = mysqli_query($conn, $query);

      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

          // Only show if no admin reply
          if (empty($row['reponse_admin'])) {
            echo '<div class="question-item">';
            echo '<p><strong>Nom:</strong> ' . htmlspecialchars($row['nom']) . '</p>';
            echo '<p><strong>Email:</strong> ' . htmlspecialchars($row['email']) . '</p>';
            echo '<p><strong>Téléphone:</strong> ' . htmlspecialchars($row['telephone']) . '</p>';
            echo '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($row['message'])) . '</p>';
            echo '<p><strong>Date:</strong> ' . $row['date_message'] . '</p>';

            // Reply form
            echo '<form action="repondre.php" method="POST">';
            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
            echo '<textarea name="reponse" placeholder="Votre réponse ici..." required></textarea><br>';
            echo '<button type="submit" class="btn-confirm">Répondre</button>';
            echo '</form>';

            // Delete button
            echo '<form method="POST" action="supprimer_message.php" style="display:inline;">
              <input type="hidden" name="id" value="' . $row['id'] . '">
              <button type="submit" class="btn btn-delete" onclick="return confirm(\'Supprimer ce message ?\')">Supprimer</button>
            </form>';

            echo '</div>'; // End .question-item
          }

          // If there's a response, hide the block completely
        }
      } else {
        echo "<p>Aucune question trouvée.</p>";
      }
      ?>


    </div>
  </section>



  <section class="produits-section">
    <h2>🛒 Gestion des produits</h2>
    <form class="manage_product-form" action="manage_products.php" method="POST">
      <input type="hidden" name="id_produit" value="<?= $product['id_produit'] ?>">
      <button type="submit" class="manage-btn">Gérer les produits </button>
    </form>
    <form class="ajout-produit-form" method="POST" action="add_produit.php" enctype="multipart/form-data">
      <h2>🛒 Ajouter un produit</h2>

      <input type="text" name="nom_produit" placeholder="Nom du produit" required>
      <textarea name="description" placeholder="Description" required></textarea>
      <input type="number" name="prix" step="0.01" placeholder="Prix" required>
      <input type="number" name="stock" placeholder="Stock" required>
      <select name="categorie_id" required>
        <option value="">--Choisir une catégorie--</option>
        <?php
        include("connexion.php");
        $query = "SELECT id_categorie, nom_categorie FROM categories";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id_categorie'] . '">' . htmlspecialchars($row['nom_categorie']) . '</option>';
          }
        } else {
          echo '<option value="">Aucune catégorie trouvée</option>';
        }
        ?>
      </select>


      <label>Image :</label>
      <input type="file" name="image">



      <button type="submit" name="submit">Ajouter le produit</button>
    </form>

    <ul id="listeProduits">
    </ul>
  </section>





  <script src="../js/admin.js"></script>

</body>

</html>