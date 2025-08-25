

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css">
    <title>Liste des contrats</title>
</head>
<body>

<p>This is the contract page</p>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Start Date</th>
      <th>End Date</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($contracts as $contract): ?>
      <tr>
        <td><?= $contract->getId() ?></td>
        <td><?= $contract->getStartDate() ?></td>
        <td><?= $contract->getEndDate() ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
    
</body>
</html>