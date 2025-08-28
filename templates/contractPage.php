<?php

session_start();
require_once __DIR__ . '/../src/Repository/ContractRepository.php';
require_once __DIR__ . '/../sql/Database/SqlConnection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

try {
    $conn = new \EasyLoc\Database\SqlConnection();
    $contractRepo = new \EasyLoc\Repository\ContractRepository($conn->getConnection());
    $contractId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($contractId === null || $contractId <= 0) {
        throw new Exception("ID de contrat invalide.");
    }

    $contract = $contractRepo->find($contractId);

    if (!$contract) {
        throw new Exception("Contrat non trouvé.");
    }
} catch (\Exception $e) {
    $error = "Une erreur est survenue lors de la récupération du contrat: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css">
    <title>Détails du contrat - EasyLoc</title>
<link rel="stylesheet" href="../styles/contract.css">

</head>
<body>
    <div class="nav-container">
        <a href="home.php">Accueil</a>
        <a href="contract_list.php">Contrats</a>
        <a href="customer_profile.php">Clients</a>
        <a href="vehicle_overview.php">Véhicules</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <div class="contract-details">
            <h2>Détails du contrat #<?= htmlspecialchars($contract['id']) ?></h2>
            <p><strong>Véhicule:</strong> <?= htmlspecialchars($contract['vehicle_uid']) ?></p>
            <p><strong>Client:</strong> <?= htmlspecialchars($contract['customer_uid']) ?></p>
            <p><strong>Date de signature:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($contract['SignDate']))) ?></p>
            <p><strong>Date de début:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($contract['StartDate']))) ?></p>
            <p><strong>Date de fin:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($contract['EndDate']))) ?></p>
            <p><strong>Date de retour:</strong> <?= $contract['ReturnDate'] ? htmlspecialchars(date('d/m/Y', strtotime($contract['ReturnDate']))) : '-' ?></p>
            <p><strong>Prix:</strong> <?= htmlspecialchars(number_format($contract['Price'], 2, ',', ' ')) ?> €</p>

            <a href="contract_edit.php?id=<?= htmlspecialchars($contract['id']) ?>" class="btn-edit">Modifier le contrat</a>
        </div>
    <?php endif; ?>