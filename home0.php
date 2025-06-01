<?php
session_start();
include "connexion.php";
$isLoggedIn = isset($_SESSION['user']);

/* ---------- Active promotions + product data ---------- */
$promoSQL = "
  SELECT
      p.id_promotion,
      p.taux_remise,
      p.date_debut,
      p.date_fin,
      p.description,
      pr.id_produit,
      pr.nom_produit,
      pr.prix,
      pr.stock,
      pr.image
  FROM promotions p
  JOIN produits pr ON p.id_produit = pr.id_produit
  WHERE NOW() BETWEEN p.date_debut AND p.date_fin
";

$res = $conn->query($promoSQL);
$promotions = [];

if ($res && $res->num_rows) {
  while ($row = $res->fetch_assoc()) {

    // base64 image or fallback
    $row['image_base64'] = $row['image']
      ? base64_encode($row['image'])
      : 'default-image';

    // promo price
    $row['prix_promo'] = $row['prix'] - ($row['prix'] * $row['taux_remise'] / 100);

    $promotions[] = $row;
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
            <!-- If the user is logged in, show the user logo/icon -->
            <li>
              <a href="<?= ($_SESSION['user']['role'] === 'admin') ? 'admin.php' : 'compte.php'; ?>">
                <img src="../imgs/user.avif" alt="User Logo" class="user-logo">
              </a>
            </li>
            <li><strong><?php echo $_SESSION['user']['prenom']; ?></strong></li>
            <li><a href="../php/logout.php">logout</a></li>
          <?php else: ?>
            <!-- If the user is not logged in, show the login button -->
            <a href="#" class="btn green" id="open-modal">Se connecter</a>
          <?php endif; ?>


        </ul>
      </nav>
    </div>

  </header>

  <section class="produits-page">
    <div class="container">
      <div id="loginModal" class="modal">
        <div class="modal-content">
          <span class="close">&times;</span>

          <!-- Onglets -->
          <div class="tabs">
            <button class="tab active" id="tab-login">Connexion</button>
            <button class="tab" id="tab-register">Inscription</button>
          </div>

          <!-- Formulaire de connexion -->
          <form id="loginForm" class="form active" method="POST" action="compte1.php">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe"
              pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
              title="Minimum 8 caractères, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial"
              required>
            <button type="submit" class="btn green">Se connecter</button>
          </form>

          <!-- Formulaire d'inscription -->
          <form id="registerForm" class="form" method="POST" action="register.php">
            <input type="text" name="fullname" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="tel" name="phone" placeholder="Numéro de téléphone"
              pattern="^0[5-7][0-9]{8}$"
              title="Entrez un numéro algérien valide commençant par 05, 06 ou 07, suivi de 8 chiffres"
              required>

            <input type="password" name="password" placeholder="Mot de passe"
              pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
              title="Minimum 8 caractères, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial"
              required>
            <button type="submit" class="btn blue">Créer un compte</button>
          </form>

        </div>
      </div>




      <?php if (isset($_SESSION['error'])): ?>
        <div class="popup-message popup-error"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="popup-message popup-success"><?php echo $_SESSION['success'];
                                                  unset($_SESSION['success']); ?></div>
      <?php endif; ?>



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
    <!-- Displaying promotions -->
    <!-- PROMOTIONS SECTION -->
    <!-- ========== PROMOTIONS SECTION ========== -->
    <section class="promotions-section">
      <div class="container">
        <h2 class="section-title">Des remises imbattables sur vos produits préférés</h2>

        <div class="promotions-grid">
          <?php foreach ($promotions as $promo): ?>
            <div class="promotion-card">

              <!-- image -->
              <div class="promotion-image">
                <?php if (!empty($promo['taux_remise'])): ?>
                  <span class="promo-label">
                    -<?= rtrim(rtrim(number_format($promo['taux_remise'], 2), '0'), '.'); ?>%
                  </span>
                <?php endif; ?>
                <?php if ($promo['image_base64'] !== 'default-image'): ?>
                  <img src="data:image/jpeg;base64,<?= $promo['image_base64']; ?>" alt="Image promotion">
                <?php else: ?>
                  <img src="images/default-image.jpg" alt="No image available">
                <?php endif; ?>
              </div>

              <!-- body -->
              <div class="promotion-body">
                <h3 class="promotion-title"><?= htmlspecialchars($promo['nom_produit']); ?></h3>
                <p class="promotion-description"><?= htmlspecialchars($promo['description']); ?></p>
                <p class="old-price"><?= number_format($promo['prix'], 2, ',', ' '); ?> DA</p>
                <p class="new-price"><?= number_format($promo['prix_promo'], 2, ',', ' '); ?> DA</p>
                <?php if (!empty($product['taux_remise'])): ?>
                  <span class="promo-label">-<?= rtrim(rtrim(number_format($product['taux_remise'], 2), '0'), '.'); ?>%</span>
                <?php endif; ?>
                <?php if (!empty($promo['lien'])): ?>
                  <a href="<?= htmlspecialchars($promo['lien']); ?>" class="btn blue" target="_blank">Voir plus</a>
                <?php endif; ?>
              </div>

              <!-- add-to-cart -->
              <div class="actions">
                <input type="number"
                  min="1"
                  max="<?= $promo['stock'] ?? 99; ?>"
                  value="1"
                  class="quantite-input">

                <button class="ajouter-panier-btn"
                  data-id="<?= $promo['id_produit']; ?>"
                  data-nom="<?= htmlspecialchars($promo['nom_produit']); ?>"
                  data-prix="<?= $promo['prix_promo']; ?>">
                  Ajouter au panier
                </button>
              </div>

            </div>
          <?php endforeach; ?>

          <?php if (empty($promotions)): ?>
            <p class="no-promotion">Aucune promotion disponible pour le moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
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
              alert(d.message);
              if (d.success) {
                panierModal.style.display = 'none';
                contenuPanier.innerHTML = "<p>Votre panier est vide.</p>";
              }
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


    </footer>

    <script src="../js/home0.js"></script>

</body>

</html>