<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$app_id = $_GET['app_id'] ?? 0;
$docs = $pdo->prepare("SELECT * FROM documents WHERE application_id = ?");
$docs->execute([$app_id]);
echo json_encode($docs->fetchAll());
?>