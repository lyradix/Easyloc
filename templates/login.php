<?php
session_start();
require_once __DIR__ . '/../src/Repository/SecurityRepository.php';
require_once __DIR__ . '/../sql/Database/SqlConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new \EasyLoc\Database\SqlConnection();
    $securityRepo = new \EasyLoc\Repository\SecurityRepository($conn->getConnection());
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = $securityRepo->login($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        header('Location: home.php');
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';">
    <title>Connexion - EasyLoc</title>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>EasyLoc</h1>
        </div>
        <form method="POST" action="">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="username" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="submit-button">Se connecter</button>
        </form>
    </div>
</body>
</html>