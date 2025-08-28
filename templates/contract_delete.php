<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../sql/Database/SqlConnection.php';
require_once __DIR__ . '/../src/Repository/ContractRepository.php';

$contractId = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    $conn = new \EasyLoc\Database\SqlConnection();
    $contractRepo = new \EasyLoc\Repository\ContractRepository($conn->getConnection());

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        // Delete the contract
        $contractRepo->delete($contractId);
        $_SESSION['success_message'] = "Le contrat a été supprimé avec succès.";
        header('Location: contract_list.php');
        exit;
    }

    // Get contract details for confirmation
    $contract = $contractRepo->find($contractId);
    if (!$contract) {
        throw new Exception("Contrat non trouvé.");
    }

} catch (\Exception $e) {
    error_log("Contract delete error: " . $e->getMessage());
    $error = "Une erreur est survenue: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css">
    <title>Supprimer le contrat - EasyLoc</title>
    <style>
        .delete-confirmation {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .warning-message {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .contract-summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-confirm-delete {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-confirm-delete:hover {
            background-color: #c82333;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <a href="home.php">Accueil</a>
        <a href="contract_list.php">Contrats</a>
        <a href="customer_profile.php">Clients</a>
        <a href="vehicle_overview.php">Véhicules</a>
    </div>

    <div class="delete-confirmation">
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <h2>Confirmer la suppression</h2>
            <div class="warning-message">
                Attention ! Cette action est irréversible.
            </div>

            <div class="contract-summary">
                <p><strong>Contrat #<?= htmlspecialchars($contract['id']) ?></strong></p>
                <p><strong>Véhicule:</strong> <?= htmlspecialchars($contract['vehicle_uid']) ?></p>
                <p><strong>Client:</strong> <?= htmlspecialchars($contract['customer_uid']) ?></p>
                <p><strong>Date de début:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($contract['StartDate']))) ?></p>
                <p><strong>Date de fin:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($contract['EndDate']))) ?></p>
            </div>

            <form method="POST" class="buttons">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn-confirm-delete">Confirmer la suppression</button>
                <a href="contract_list.php" class="btn-cancel">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
