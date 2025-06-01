<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
  header('Location: home0.php');
  exit();
}

include("connexion.php");

$userId = $_SESSION['user']['id'];

// Debugging: print the user ID
$user = [];
$userSql = "SELECT nom, email, numero_telephone, adresse FROM utilisateurs WHERE id_utilisateur = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $user = $result->fetch_assoc();
}
// Fetch order history with totals and status
$orders = [];
$orderSql = "
  SELECT 
    c.id_commande, 
    c.date_commande, 
    c.statut,
    IFNULL(SUM(d.quantite * d.prix_unitaire), 0) AS total_amount
  FROM commandes c
  LEFT JOIN details_commande d ON c.id_commande = d.id_commande
  WHERE c.id_utilisateur = ?
  GROUP BY c.id_commande, c.date_commande, c.statut
  ORDER BY c.date_commande DESC
";

$stmt = $conn->prepare($orderSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
  }
}

// Fetch messages and admin responses
$messages = [];
$messageSql = "
  SELECT id, nom, email, message, reponse_admin, date_message 
  FROM messages_contact 
  WHERE email = ?
  ORDER BY date_message DESC
";
$stmt = $conn->prepare($messageSql);
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mon Compte - EduMat</title>
  <link rel="stylesheet" href="../css/compte.css" />
  <link rel="stylesheet" href="../css/home0.css" />
</head>

<body>

  <!-- ===== En-tête ===== -->
  <header class="header">
    <div class="container header-content">
      <h1>👤 Mon Compte</h1>
      <div class="header-actions">
        <a href="home0.php" class="home-btn">🏠 Accueil</a>
        <form action="logout.php" method="post" style="display:inline;">
        </form>
      </div>
    </div>
  </header>

  <!-- ===== Contenu principal ===== -->
  <main class="container compte-container">

    <!-- Informations personnelles -->
    <section class="compte-card" id="userInfoSection">
      <h2>Informations personnelles</h2>
      <form id="editForm" method="POST" action="update_user_info.php">
        <div class="info">
          <label>Nom :</label>
          <span id="nomUser"><?php echo htmlspecialchars($user['nom']); ?></span>
          <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" class="edit-field" style="display:none;">
        </div>
        <div class="info">
          <label>Email :</label>
          <span id="emailUser"><?php echo htmlspecialchars($user['email']); ?></span>
          <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="edit-field" style="display:none;">
        </div>
        <div class="info">
          <label>Téléphone :</label>
          <span id="telUser"><?php echo htmlspecialchars($user['numero_telephone']); ?></span>
          <input type="text" name="tel" value="<?php echo htmlspecialchars($user['numero_telephone']); ?>" class="edit-field" style="display:none;">
        </div>
        <div class="info">
          <label>Adresse :</label>
          <span id="adresseUser"><?php echo !empty($user['adresse']) ? htmlspecialchars($user['adresse']) : 'Non précisé'; ?></span>
          <input type="text" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>" class="edit-field" style="display:none;">
        </div>

        <button type="button" class="edit-btn" onclick="enableEdit()">✏️ Modifier mes informations</button>
        <button type="submit" class="save-btn" style="display:none;">💾 Enregistrer</button>
        <button type="button" class="cancel-btn" style="display:none;" onclick="cancelEdit()">❌ Annuler</button>
      </form>
      <script>
        const originalValues = {};

        function enableEdit() {
          const spans = document.querySelectorAll('#userInfoSection span');
          const inputs = document.querySelectorAll('#userInfoSection .edit-field');
          const saveBtn = document.querySelector('.save-btn');
          const cancelBtn = document.querySelector('.cancel-btn');
          const editBtn = document.querySelector('.edit-btn');

          spans.forEach((span, i) => {
            span.style.display = 'none';
            inputs[i].style.display = 'inline-block';
            originalValues[inputs[i].name] = inputs[i].value; // save original values
          });

          editBtn.style.display = 'none';
          saveBtn.style.display = 'inline-block';
          cancelBtn.style.display = 'inline-block';
        }

        function cancelEdit() {
          const spans = document.querySelectorAll('#userInfoSection span');
          const inputs = document.querySelectorAll('#userInfoSection .edit-field');
          const saveBtn = document.querySelector('.save-btn');
          const cancelBtn = document.querySelector('.cancel-btn');
          const editBtn = document.querySelector('.edit-btn');

          inputs.forEach((input, i) => {
            input.style.display = 'none';
            input.value = originalValues[input.name]; // restore original value
            spans[i].style.display = 'inline-block';
          });

          editBtn.style.display = 'inline-block';
          saveBtn.style.display = 'none';
          cancelBtn.style.display = 'none';
        }
      </script>
    </section>

    <!-- Historique des commandes -->
    <section class="compte-card">
      <h2>🛍 Historique des commandes</h2>
      <div id="historiqueCommandes">
        <?php if (count($orders) > 0): ?>
          <?php foreach ($orders as $order): ?>
            <div class="order">
              <p>
                <strong>Commande #<?= $order['id_commande']; ?></strong> -
                <?= $order['date_commande']; ?> -
                Statut: <em><?= htmlspecialchars($order['statut']); ?></em> -
                Total: <?= number_format($order['total_amount'], 2); ?> DA
                <button class="btn-download-pdf" onclick="window.location.href='../pdf/generate_pdf.php?id=<?= $order['id_commande']; ?>'">
                  🧾 Télécharger PDF
                </button>
              </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Aucune commande trouvée.</p>
        <?php endif; ?>
      </div>


    </section>

    <!-- Messages et réponses admin -->
    <section class="compte-card">
      <h2>📬 Mes Messages</h2>
      <div id="messages">
        <?php if (count($messages) > 0): ?>
          <?php foreach ($messages as $message): ?>
            <div class="message">
              <p><strong>Message de: <?= htmlspecialchars($message['nom']); ?> (<?= htmlspecialchars($message['email']); ?>)</strong></p>
              <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($message['message'])); ?></p>
              <p><strong>Date:</strong> <?= $message['date_message']; ?></p>

              <!-- Show the admin's response, if it exists -->
              <?php if (!empty($message['reponse_admin'])): ?>
                <div class="admin-response">
                  <p><strong>Réponse de l'admin:</strong> <?= nl2br(htmlspecialchars($message['reponse_admin'])); ?></p>
                </div>
              <?php else: ?>
                <p><strong>Réponse de l'admin:</strong> Pas encore de réponse.</p>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Aucun message trouvé.</p>
        <?php endif; ?>
      </div>
    </section>


  </main>

  <!-- ===== Pied de page ===== -->
  <footer class="footer">
    <p>&copy; 2025 EduMat. Tous droits réservés.</p>
  </footer>

</body>

</html>