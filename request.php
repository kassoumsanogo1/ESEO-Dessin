<?php
require_once 'includes/config.php';

// Vérification de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérification du rôle administrateur
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM Administrateur 
    WHERE numAdministrateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$isAdmin = $stmt->fetchColumn();

if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    try {
        // Utilise la connexion existante depuis config.php
        $db = checkConnection($db);
        $stmt = $db->query($_POST['query']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requêtes SQL - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            height: 40px;
            width: auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            background: linear-gradient(45deg, #004e92, #000428);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links a {
            margin-left: 2rem;
            text-decoration: none;
            color: #004e92;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .query-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .query-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .query-input {
            width: 100%;
            min-height: 150px;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
        }

        .submit-btn {
            padding: 1rem 2rem;
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            align-self: flex-start;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .back-btn {
            padding: 1rem 2rem;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            background: #5a6268;
        }

        .result-container {
            margin-top: 2rem;
            overflow-x: auto;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }

        .result-table th,
        .result-table td {
            padding: 1rem;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        .result-table th {
            background: #004e92;
            color: white;
        }

        .result-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .error-message {
            padding: 1rem;
            background: #fee;
            border-left: 4px solid #f44;
            margin-top: 1rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="images/eseo_logo.png" alt="ESEO Logo" class="logo-img">
            <div class="logo">ESEO'Dessin</div>
        </div>
        <div class="nav-links">
            <a href="request.php">Requêtes SQL</a>
            <a href="logout.php" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">Déconnexion</a>
        </div>
    </nav>

    <div class="query-container">
        <a href="admin/dashboard.php" class="back-btn">Retour à la page Admin</a>
        <h2>Exécuter une requête SQL</h2>
        <p class="info">Base de données connectée: db_site</p>
        <form class="query-form" method="POST">
            <textarea 
                name="query" 
                class="query-input" 
                placeholder="Entrez votre requête SQL ici..."
                required
            ><?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?></textarea>
            <button type="submit" class="submit-btn">Exécuter la requête</button>
        </form>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($result): ?>
            <div class="result-container">
                <?php if (empty($result)): ?>
                    <p>La requête a été exécutée avec succès mais n'a retourné aucun résultat.</p>
                <?php else: ?>
                    <table class="result-table">
                        <thead>
                            <tr>
                                <?php foreach($result[0] as $column => $value): ?>
                                    <th><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($result as $row): ?>
                                <tr>
                                    <?php foreach($row as $value): ?>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
