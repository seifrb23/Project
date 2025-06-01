<?php
session_start();
$_SESSION['panier'] = [];
echo json_encode(['success' => true, 'message' => "Panier annulé."]);
