<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('includes/config.php');

// Rediriger si déjà connecté
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: " . getRoleRedirection($_SESSION['role']));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $login = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        // Vérification de la connexion à la base de données
        if (!$db) {
            throw new Exception("La connexion à la base de données n'est pas établie");
        }

        // Test de la connexion avec une requête simple
        $db->query("SELECT 1");

        $stmt = $db->prepare("
            SELECT u.*, 
                CASE 
                    WHEN p.numPresident IS NOT NULL THEN 'president'
                    WHEN a.numAdministrateur IS NOT NULL THEN 'admin'
                    WHEN d.numDirecteur IS NOT NULL THEN 'directeur'
                    WHEN e.numEvaluateur IS NOT NULL THEN 'evaluateur'
                    WHEN c.numCompetiteur IS NOT NULL THEN 'competiteur'
                END as role
            FROM Utilisateur u
            LEFT JOIN President p ON u.numUtilisateur = p.numPresident
            LEFT JOIN Administrateur a ON u.numUtilisateur = a.numAdministrateur
            LEFT JOIN Directeur d ON u.numUtilisateur = d.numDirecteur
            LEFT JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur
            LEFT JOIN Competiteur c ON u.numUtilisateur = c.numCompetiteur
            WHERE u.login = ?
        ");
        
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['motDePasse']) {
            // Initialiser la session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['numUtilisateur'];
            $_SESSION['username'] = $user['login'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['club_id'] = $user['numClub'];

            // Redirection immédiate vers le dashboard approprié
            header("Location: " . getRoleRedirection($user['role']));
            exit();
        } else {
            $error = "Identifiant ou mot de passe incorrects";
        }
    } catch(PDOException $e) {
        error_log("Erreur PDO: " . $e->getMessage());
        $error = "Erreur de connexion à la base de données. Détails: " . $e->getMessage();
    } catch(Exception $e) {
        error_log("Erreur générale: " . $e->getMessage());
        $error = "Une erreur est survenue: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, #000428, #004e92);
            padding: 2rem;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .back-home {
            position: absolute;
            top: -40px;
            left: 0;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .back-home:hover {
            opacity: 1;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }

        .welcome-text {
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-text h2 {
            color: #004e92;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #666;
            font-size: 0.9rem;
        }

        h1 {
            color: #2d3436;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #636e72;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #004e92;
            box-shadow: 0 0 15px rgba(0, 78, 146, 0.1);
            transform: translateY(-2px);
        }

        .error-message {
            color: #d63031;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .login-button {
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 78, 146, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
        }

        .register-link a {
            color: #0984e3;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="index.php" class="back-home">← Retour à l'accueil</a>

        <div class="logo-container">
            <img src="images/eseo_logo.png" alt="Logo ESEO">
            <div class="welcome-text">
                <h2>Bienvenue sur ESEO'Dessin</h2>
                <p>Connectez-vous à votre espace concours pour participer aux challenges créatifs</p>
            </div>
        </div>

        <h1>Connexion</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-button">Se connecter</button>
        </form>

        <div class="register-link">
            Pas encore de compte ? <a href="register.php">S'inscrire</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                event.preventDefault();
                alert('Veuillez remplir tous les champs');
                return false;
            }

            // Validation supplémentaire si nécessaire
            if (username.length < 3) {
                event.preventDefault();
                alert('Le nom d\'utilisateur doit contenir au moins 3 caractères');
                return false;
            }

            if (password.length < 6) {
                event.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>