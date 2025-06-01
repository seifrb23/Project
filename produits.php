<?php
session_start();
include("connexion.php");

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);

// Get current datetime
$today = date('Y-m-d H:i:s');

// Fetch products with category and active promotions (if any)
$query = "
  SELECT p.id_produit, p.nom_produit, p.description, p.prix, p.stock, p.image, p.date_ajout,
         c.nom_categorie, pr.taux_remise, pr.date_debut, pr.date_fin
  FROM produits p
  LEFT JOIN categories c ON p.categorie_id = c.id_categorie
  LEFT JOIN promotions pr 
      ON p.id_produit = pr.id_produit 
      AND pr.date_debut <= '$today' 
      AND pr.date_fin >= '$today'
";

$result = $conn->query($query);
$products = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    // Convert the binary image data to base64
    if ($row['image']) {
      $imageData = base64_encode($row['image']);
      $imageSrc = 'data:image/jpeg;base64,' . $imageData;
    } else {
      $imageSrc = 'path/to/default-image.jpg'; // Default image if none
    }
    $row['image'] = $imageSrc;

    // Calculate promotional price if a valid promo exists
    if (!empty($row['taux_remise'])) {
      $originalPrice = (float)$row['prix'];
      $discount = (float)$row['taux_remise'];
      $row['prix_promo'] = $originalPrice - ($originalPrice * $discount / 100);
    } else {
      $row['prix_promo'] = null;
    }

    $products[] = $row;
  }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nos Produits - EduMat</title>
  <link rel="stylesheet" href="../css/home0.css" />
  <link rel="stylesheet" href="../css/produits.css" />
</head>

<body>

  <header>
    <div class="container">
      <h1 class="logo">EduMat</h1>
      <nav>
        <ul class="nav-links">
          <li><a href="home0.php">Accueil</a></li>
          <li><a href="produits.php" class="active">Produits</a></li>
          <li><a href="À propos.php" class="btn">À propos</a></li>
          <li><a href="Contact.php">Contact</a></li>
          <?php if ($isLoggedIn): ?>
            <li><a href="compte.php"><img src="../imgs/user.avif" alt="User Logo" class="user-logo"></a></li>
            <li><strong><?php echo $_SESSION['user']['prenom']; ?></strong></li>
            <li><a href="../php/logout.php">Logout</a></li>
          <?php else: ?>
            <a href="#" class="btn green" id="open-modal">Se connecter</a>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <section class="produits-page">
    <div class="container">
      <div class="produits-header">
        <h2> Nos Fournitures Scolaires</h2>

      </div>

      <!-- Bouton fixe pour ouvrir le panier -->
      <button id="voirPanierBtn" class="voir-panier-btn">🛒 Voir le panier</button>

      <!-- Modal panier -->
      <div id="panierModal" class="panierModal" style="display:none;">
        <div class="modal-panierModal">
          <span class="close">&times;</span>
          <h3>🧾 Votre Panier</h3>

          <form id="panierForm">
            <div id="contenuPanier"><!-- Injecté par AJAX --></div>

            <div class="modal-actions">
              <button type="button" id="annulerBtn" class="annuler-btn">❌ Annuler</button>
              <button type="submit" id="submit" class="valider-btn">✅ Valider la commande</button>
            </div>
          </form>
        </div>
      </div>


      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="🔍 Rechercher un produit..." />
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const searchInput = document.getElementById('searchInput');
          const productCards = document.querySelectorAll('.produit-card');

          searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();

            productCards.forEach(card => {
              const name = card.querySelector('h3').textContent.toLowerCase();
              const desc = card.querySelector('p').textContent.toLowerCase();

              if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                card.style.display = 'block';
              } else {
                card.style.display = 'none';
              }
            });
          });
        });
      </script>

      <div class="filter-group">
        <span>Catégorie :</span>
        <button class="filter-btn active" data-category="all">Tous</button>
        <button class="filter-btn" data-category="Cahiers">Cahiers</button>
        <button class="filter-btn" data-category="Stylos">Stylos</button>
        <button class="filter-btn" data-category="Trousses">Trousses</button>
        <button id="toggleMoreBtn" type="button">Voir plus</button>
        <div id="moreCategories" style="display: none; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
          <button class="filter-btn" data-category="Sacs à dos">Sacs à dos</button>
          <button class="filter-btn" data-category="Règles">Règles</button>
          <button class="filter-btn" data-category="Gommes">Gommes</button>
          <button class="filter-btn" data-category="Colles">Colles</button>
          <button class="filter-btn" data-category="Feutres">Feutres</button>
          <button class="filter-btn" data-category="Classeurs">Classeurs</button>
          <button class="filter-btn" data-category="Calculatrices">Calculatrices</button>
          <button class="filter-btn" data-category="Agendas">Agendas</button>
          <button class="filter-btn" data-category="Papier">Papier</button>
          <button class="filter-btn" data-category="Informatique">Informatique</button>
        </div>
      </div>

      <div class="filter-group">
        <label for="sortPrice">Prix :</label>
        <select id="sortPrice">
          <option value="">Par defaut</option>
          <option value="asc">Prix croissant</option>
          <option value="desc">Prix décroissant</option>
        </select>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          document.getElementById('sortPrice').addEventListener('change', function() {
            const direction = this.value;
            const container = document.getElementById('produitsList');
            const cards = Array.from(container.getElementsByClassName('produit-card'));

            const getPrice = card => {
              // Fetch the price based on promo or original price
              const promoPriceElement = card.querySelector('.prix-promo');
              const priceElement = card.querySelector('.prix');

              let price;
              if (promoPriceElement) {
                price = parseFloat(promoPriceElement.textContent.replace(/[^\d.]/g, ''));
              } else {
                price = parseFloat(priceElement.textContent.replace(/[^\d.]/g, ''));
              }

              return price;
            };

            if (direction === 'asc') {
              cards.sort((a, b) => getPrice(a) - getPrice(b)); // Sort in ascending order
            } else if (direction === 'desc') {
              cards.sort((a, b) => getPrice(b) - getPrice(a)); // Sort in descending order
            } else if (direction === '') { // Default case when no sorting is selected
              // Reset to default order (no sorting)
              cards.sort((a, b) => {
                return a.dataset.index - b.dataset.index; // Assuming the original order is stored in data-index
              });
            }

            // Re-append sorted cards
            cards.forEach(card => container.appendChild(card));
          });
        });
      </script>

      <div class="filter-group promo-filter">
        <span>En promotion</span>
        <label class="switch">
          <input type="checkbox" id="promoOnly">
          <span class="slider round"></span>
        </label>
      </div>
    </div>

    <div class="grid-produits" id="produitsList">

      <?php foreach ($products as $product): ?>
        <div class="produit-card" data-category="<?= htmlspecialchars($product['nom_categorie'] ?? ''); ?>">
          <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['nom_produit']); ?>">
          <h3><?= htmlspecialchars($product['nom_produit']); ?></h3>
          <p><?= htmlspecialchars($product['description']); ?></p>

          <?php if (!empty($product['prix_promo'])): ?>
            <span class="prix-promo"><?= number_format($product['prix_promo'], 2); ?> DA</span>
            <span class="prix-original"><?= number_format($product['prix'], 2); ?> DA</span>
            <?php if (!empty($product['taux_remise'])): ?>
              <span class="promo-label">-<?= rtrim(rtrim(number_format($product['taux_remise'], 2), '0'), '.'); ?>%</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="prix"><?= number_format($product['prix'], 2); ?> DA</span>
          <?php endif; ?>


          <span class="stock">Stock: <?= $product['stock']; ?></span>
          <div class="actions">
            <input type="number" min="1" max="<?= $product['stock']; ?>" value="1" class="quantite-input">
            <button class="ajouter-panier-btn"
              data-id="<?= $product['id_produit']; ?>"
              data-nom="<?= htmlspecialchars($product['nom_produit']); ?>"
              data-prix="<?= $product['prix_promo'] ?? $product['prix']; ?>">
              Ajouter au panier
            </button>
          </div>

        </div>
      <?php endforeach; ?>

      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const panierModal = document.getElementById('panierModal');
          const voirPanierBtn = document.getElementById('voirPanierBtn');
          const closeBtn = panierModal.querySelector('.close');
          const contenuPanier = document.getElementById('contenuPanier');

          /* -- Ouvrir le panier -- */
          voirPanierBtn.addEventListener('click', () => {
            fetch('../ajax/panier.php')
              .then(r => r.text())
              .then(html => {
                contenuPanier.innerHTML = html;
                panierModal.style.display = 'block';
              });
          });

          /* -- Fermer -- */
          closeBtn.onclick = () => panierModal.style.display = 'none';
          window.onclick = e => {
            if (e.target === panierModal) panierModal.style.display = 'none';
          };

          /* -- Ajouter au panier -- */
          document.querySelectorAll('.ajouter-panier-btn').forEach(btn => {
            btn.addEventListener('click', () => {
              const id = btn.dataset.id;
              const nom = btn.dataset.nom;
              const prix = btn.dataset.prix;
              const quantite = btn.previousElementSibling.value;

              fetch('../ajax/ajouter_panier.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: `id=${id}&nom=${encodeURIComponent(nom)}&prix=${prix}&quantite=${quantite}`
                })
                .then(r => r.json())
                .then(d => alert(d.message));
            });
          });

          /* -- Valider commande -- */
          document.getElementById('panierForm').addEventListener('submit', e => {
            e.preventDefault();

            fetch('../ajax/valider_commande.php', {
                method: 'POST'
              })
              .then(r => r.json())
              .then(d => {
                alert(d.message); // Always show the message

                if (!d.success) return; // If failed (like not logged in), stop here

                // Only proceed if success
                panierModal.style.display = 'none';
                contenuPanier.innerHTML = "<p>Votre panier est vide.</p>";
              });
          });


          /* -- Annuler panier -- */
          document.getElementById('annulerBtn').addEventListener('click', () => {
            if (!confirm("Annuler le panier ?")) return;
            fetch('../ajax/annuler_panier.php', {
                method: 'POST'
              })
              .then(r => r.json())
              .then(d => {
                alert(d.message);
                panierModal.style.display = 'none';
                contenuPanier.innerHTML = "<p>Votre panier est vide.</p>";
              });
          });
        });
      </script>


    </div>
  </section>

  <footer>
    <div class="container">
      <p>&copy; 2025 EduMat. Tous droits réservés.</p>
    </div>
  </footer>

  <script src="../js/produits.js"></script>
</body>

</html>