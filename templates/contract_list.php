<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../sql/Database/SqlConnection.php';
require_once __DIR__ . '/../src/Repository/ContractRepository.php';

try {
    $conn = new \EasyLoc\Database\SqlConnection();
    $contractRepo = new \EasyLoc\Repository\ContractRepository($conn->getConnection());
    $contracts = $contractRepo->findAll();
} catch (\Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des contrats: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css">
    <title>Liste des contrats - EasyLoc</title>
</head>
<body>
    <div class="nav-container">
        <a href="home.php">Accueil</a>
        <a href="customer_profile.php">Clients</a>
        <a href="vehicle_overview.php">Véhicules</a>
    </div>

    <h1>Liste des contrats</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($contracts)): ?>
        <table class="contract-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Véhicule</th>
                    <th>Client</th>
                    <th>Date de signature</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Retour</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?= htmlspecialchars($contract['id']) ?></td>
                        <td><?= htmlspecialchars($contract['vehicle_uid']) ?></td>
                        <td><?= htmlspecialchars($contract['customer_uid']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($contract['SignDate']))) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($contract['StartDate']))) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($contract['EndDate']))) ?></td>
                        <td><?= $contract['ReturnDate'] ? htmlspecialchars(date('d/m/Y', strtotime($contract['ReturnDate']))) : '-' ?></td>
                        <td><?= htmlspecialchars(number_format($contract['Price'], 2, ',', ' ')) ?> €</td>
                        <td>
                            <a href="contractPage.php?id=<?= htmlspecialchars($contract['id']) ?>" class="btn-view">Voir</a>
                            <a href="contract_edit.php?id=<?= htmlspecialchars($contract['id']) ?>" class="btn-edit">Modifier</a>
                            <a href="contract_delete.php?id=<?= htmlspecialchars($contract['id']) ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun contrat trouvé.</p>
    <?php endif; ?>

    <div class="actions">
        <a href="contract_new.php" class="btn-new">Nouveau contrat</a>
    </div>
</body>
</html>


    
</body>
</html>