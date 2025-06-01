<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=commerce;charset=utf8mb4', 'root', '');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    if ($action === 'delete') {
        $id = $_POST['id_produit'] ?? null;
        if ($id) {
            // Delete promotions first
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id_produit = ?");
            $stmt->execute([$id]);

            // Delete product
            $stmt = $pdo->prepare("DELETE FROM produits WHERE id_produit = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Product deleted successfully.";
            echo json_encode(['status' => 'success']);
            exit;
        } else {
            $_SESSION['error'] = "Invalid product ID for deletion.";
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }
    }

    if ($action === 'promo') {
        $id_produit = $_POST['id_produit'] ?? null;
        $taux_remise = $_POST['taux_remise'] ?? null;
        $date_debut = $_POST['date_debut'] ?? null;
        $date_fin = $_POST['date_fin'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$id_produit || !$taux_remise || !$date_debut || !$date_fin || !$description) {
            $_SESSION['error'] = "All promotion fields are required.";
            echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
            exit;
        }

        // Check if promotion exists
        $check = $pdo->prepare("SELECT id_promotion FROM promotions WHERE id_produit = ?");
        $check->execute([$id_produit]);

        if ($check->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE promotions SET taux_remise = ?, date_debut = ?, date_fin = ?, description = ? WHERE id_produit = ?");
            $stmt->execute([$taux_remise, $date_debut, $date_fin, $description, $id_produit]);
            $_SESSION['success'] = "Promotion updated successfully.";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO promotions (id_produit, taux_remise, date_debut, date_fin, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_produit, $taux_remise, $date_debut, $date_fin, $description]);
            $_SESSION['success'] = "Promotion added successfully.";
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

// Fetch all products for display
$stmt = $pdo->query("SELECT id_produit, nom_produit FROM produits ORDER BY id_produit DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Products and Promotions</title>
    <link rel="stylesheet" href="../css/manage_products.css" />

</head>

<body>

    <h2>Manage Products</h2>

    <!-- Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="popup-message popup-error"><?= $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="popup-message popup-success"><?= $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $p): ?>
            <tr data-product-id="<?= $p['id_produit'] ?>" data-product-name="<?= htmlspecialchars($p['nom_produit']) ?>">
                <td><?= $p['id_produit'] ?></td>
                <td><?= htmlspecialchars($p['nom_produit']) ?></td>
                <td>
                    <button class="openPromoBtn">Add/Update Promotion</button>
                    <button class="deleteBtn" >Delete Product</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div id="overlay"></div>

    <!-- Promotion Form Popup -->
    <div id="promoFormContainer">
        <h3>Add/Update Promotion for <span id="promoProductName"></span></h3>
        <form id="promoForm">
            <input type="hidden" name="id_produit" id="promoProductId">
            <label>Discount %:
                <input type="number" name="taux_remise" step="0.01" required>
            </label>
            <label>Start Date:
                <input type="datetime-local" name="date_debut" required>
            </label>
            <label>End Date:
                <input type="datetime-local" name="date_fin" required>
            </label>
            <label>Description:
                <input type="text" name="description" required>
            </label>
            <button type="submit">Submit Promotion</button>
            <button type="button" id="closePromoForm">Cancel</button>
        </form>
    </div>
    <script>
        const promoFormContainer = document.getElementById('promoFormContainer');
        const overlay = document.getElementById('overlay');
        const promoForm = document.getElementById('promoForm');
        const promoProductName = document.getElementById('promoProductName');
        const promoProductId = document.getElementById('promoProductId');

        // Create a function to show popup messages
        function showPopupMessage(message, isSuccess = true) {
            const popup = document.createElement('div');
            popup.className = 'popup-message ' + (isSuccess ? 'popup-success' : 'popup-error');
            popup.textContent = message;
            document.body.appendChild(popup);

            // Fade out after 4 seconds
            setTimeout(() => {
                popup.style.opacity = '0';
                setTimeout(() => popup.remove(), 500);
            }, 4000);
        }

        // Show any existing messages and fade them out
        setTimeout(() => {
            const existingPopup = document.querySelector('.popup-message');
            if (existingPopup) {
                existingPopup.style.opacity = '0';
                setTimeout(() => existingPopup.remove(), 500);
            }
        }, 4000);

        // Open promotion form
        document.querySelectorAll('.openPromoBtn').forEach(button => {
            button.addEventListener('click', () => {
                const tr = button.closest('tr');
                const productName = tr.dataset.productName;
                const productId = tr.dataset.productId;

                promoProductName.textContent = productName;
                promoProductId.value = productId;

                promoForm.reset();
                promoFormContainer.style.display = 'block';
                overlay.style.display = 'block';
            });
        });

        // Close promo form
        document.getElementById('closePromoForm').addEventListener('click', () => {
            promoFormContainer.style.display = 'none';
            overlay.style.display = 'none';
        });

        // Submit promotion form with AJAX
        promoForm.addEventListener('submit', e => {
            e.preventDefault();

            const formData = new FormData(promoForm);
            formData.append('action', 'promo');

            fetch('', { // same page
                    method: 'POST',
                    body: formData
                }).then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showPopupMessage('Promotion saved successfully.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showPopupMessage('Error: ' + (data.message || 'Unknown error'), false);
                    }
                }).catch(() => showPopupMessage('AJAX error', false));

            promoFormContainer.style.display = 'none';
            overlay.style.display = 'none';
        });

        // Delete product with confirmation and AJAX
        document.querySelectorAll('.deleteBtn').forEach(button => {
            button.addEventListener('click', () => {
                if (!confirm('Are you sure you want to delete this product?')) return;

                const tr = button.closest('tr');
                const productId = tr.dataset.productId;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id_produit', productId);

                fetch('', {
                        method: 'POST',
                        body: formData
                    }).then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showPopupMessage('Product deleted successfully.');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showPopupMessage('Error: ' + (data.message || 'Unknown error'), false);
                        }
                    }).catch(() => showPopupMessage('AJAX error', false));
            });
        });
    </script>

</body>

</html>