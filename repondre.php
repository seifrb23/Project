<?php
include("connexion.php");


if (isset($_POST['id'], $_POST['reponse'])) {
    $id = intval($_POST['id']);
    $reponse = trim($_POST['reponse']);

    $stmt = $conn->prepare("UPDATE messages_contact SET reponse_admin = ? WHERE id = ?");
    $stmt->bind_param("si", $reponse, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin.php"); // redirect back to admin page
exit();
