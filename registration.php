<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db->beginTransaction();

        // Validation des champs requis
        $required_fields = ['nom', 'prenom', 'login', 'password', 'role', 'age', 'adresse'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Tous les champs sont obligatoires");
            }
        }

        // Vérification si le login existe déjà
        $stmt = $db->prepare("SELECT COUNT(*) FROM Utilisateur WHERE login = ?");
        $stmt->execute([$_POST['login']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Ce login est déjà utilisé");
        }

        // Génération du prochain numUtilisateur disponible
        $stmt = $db->query("SELECT MAX(numUtilisateur) FROM Utilisateur");
        $nextId = $stmt->fetchColumn() + 1;

        // Insertion dans la table Utilisateur
        $stmt = $db->prepare("INSERT INTO Utilisateur (numUtilisateur, numClub, nom, prenom, adresse, login, motDePasse, age) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $numClub = isset($_POST['numClub']) ? $_POST['numClub'] : null;
        $stmt->execute([
            $nextId,
            $numClub,
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['adresse'],
            $_POST['login'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['age']
        ]);

        // Insertion dans la table spécifique selon le rôle
        switch($_POST['role']) {
            case 'president':
                $stmt = $db->prepare("INSERT INTO President (numPresident, prime) VALUES (?, 0)");
                $stmt->execute([$nextId]);
                break;
            case 'admin':
                $stmt = $db->prepare("INSERT INTO Administrateur (numAdministrateur, dateDebut) VALUES (?, CURDATE())");
                $stmt->execute([$nextId]);
                break;
            case 'directeur':
                if (empty($_POST['numClub'])) {
                    throw new Exception("Un club doit être sélectionné pour un directeur");
                }
                $stmt = $db->prepare("INSERT INTO Directeur (numDirecteur, numClub, dateDebut) VALUES (?, ?, CURDATE())");
                $stmt->execute([$nextId, $_POST['numClub']]);
                break;
            case 'evaluateur':
                if (empty($_POST['specialite'])) {
                    throw new Exception("Une spécialité doit être sélectionnée pour un évaluateur");
                }
                $stmt = $db->prepare("INSERT INTO Evaluateur (numEvaluateur, specialite, nbDessinsEvalues) VALUES (?, ?, 0)");
                $stmt->execute([$nextId, $_POST['specialite']]);
                break;
            case 'competiteur':
                $stmt = $db->prepare("INSERT INTO Competiteur (numCompetiteur, datePremiereParticipation, nbDessinSoumis) VALUES (?, CURDATE(), 0)");
                $stmt->execute([$nextId]);
                break;
            default:
                throw new Exception("Rôle non valide");
        }

        $db->commit();
        
        // Initialiser la session après inscription réussie
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $nextId;
        $_SESSION['username'] = $_POST['login'];
        $_SESSION['role'] = $_POST['role'];
        $_SESSION['nom'] = $_POST['nom'];
        $_SESSION['prenom'] = $_POST['prenom'];
        $_SESSION['club_id'] = $numClub;

        // Rediriger vers le dashboard approprié
        header("Location: " . getRoleRedirection($_POST['role']));
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Récupération de la liste des clubs pour le formulaire
$stmt = $db->query("SELECT numClub, nomClub FROM Club");
$clubs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ESEO'Dessin</title>
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

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
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

        h2 {
            color: #004e92;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #636e72;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #004e92;
            box-shadow: 0 0 15px rgba(0, 78, 146, 0.1);
            transform: translateY(-2px);
        }

        .error {
            color: #d63031;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(214, 48, 49, 0.1);
            border-radius: 8px;
        }

        .success {
            color: #00b894;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(0, 184, 148, 0.1);
            border-radius: 8px;
        }

        button[type="submit"] {
            width: 100%;
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 78, 146, 0.3);
        }

        p {
            text-align: center;
            margin-top: 1rem;
        }

        p a {
            color: #0984e3;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-home">← Retour à l'accueil</a>
        
        <div class="logo-container">
            <img src="images/eseo_logo.png" alt="Logo ESEO">
        </div>
        
        <h2>Inscription</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="registration.php">
            <div class="form-group">
                <label>Nom:</label>
                <input type="text" name="nom" required>
            </div>

            <div class="form-group">
                <label>Prénom:</label>
                <input type="text" name="prenom" required>
            </div>

            <div class="form-group">
                <label>Login:</label>
                <input type="text" name="login" required>
            </div>

            <div class="form-group">
                <label>Mot de passe:</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Âge:</label>
                <input type="number" name="age" required min="18">
            </div>

            <div class="form-group">
                <label>Adresse:</label>
                <input type="text" name="adresse" required>
            </div>

            <div class="form-group">
                <label>Rôle:</label>
                <select name="role" id="role" required onchange="toggleFields()">
                    <option value="">Sélectionnez un rôle</option>
                    <option value="admin">Administrateur</option>
                    <option value="president">Président</option>
                    <option value="directeur">Directeur</option>
                    <option value="evaluateur">Évaluateur</option>
                    <option value="competiteur">Compétiteur</option>
                </select>
            </div>

            <div class="form-group" id="clubField" style="display: none;">
                <label>Club:</label>
                <select name="numClub">
                    <option value="">Sélectionnez un club</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?php echo $club['numClub']; ?>">
                            <?php echo htmlspecialchars($club['nomClub']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="specialiteField" style="display: none;">
                <label>Spécialité:</label>
                <select name="specialite">
                    <option value="">Sélectionnez une spécialité</option>
                    <option value="Portrait">Portrait</option>
                    <option value="Paysage">Paysage</option>
                    <option value="Art moderne">Art moderne</option>
                    <option value="Nature morte">Nature morte</option>
                    <option value="Art abstrait">Art abstrait</option>
                    <option value="Art urbain">Art urbain</option>
                    <option value="Aquarelle">Aquarelle</option>
                    <option value="Digital art">Digital art</option>
                    <option value="Sculpture">Sculpture</option>
                    <option value="Art contemporain">Art contemporain</option>
                </select>
            </div>

            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà un compte? <a href="login.php">Connectez-vous ici</a></p>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            const required = ['nom', 'prenom', 'login', 'password', 'age', 'adresse', 'role'];
            let error = false;

            required.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (!input.value.trim()) {
                    error = true;
                    input.style.borderColor = '#d63031';
                } else {
                    input.style.borderColor = '#e1e1e1';
                }
            });

            if (error) {
                event.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return false;
            }

            const role = document.getElementById('role').value;
            if (role === 'directeur' && !document.querySelector('[name="numClub"]').value) {
                event.preventDefault();
                alert('Veuillez sélectionner un club pour le directeur');
                return false;
            }

            if (role === 'evaluateur' && !document.querySelector('[name="specialite"]').value) {
                event.preventDefault();
                alert('Veuillez sélectionner une spécialité pour l\'évaluateur');
                return false;
            }
            if (role === 'competiteur' && !document.querySelector('[name="numClub"]').value) {
                event.preventDefault();
                alert('Veuillez sélectionner un club pour le competiteur');
                return false;
            }
            if (role === 'president' && !document.querySelector('[name="numClub"]').value) {
                event.preventDefault();
                alert('Veuillez sélectionner un club pour le président');
                return false;
            }
            

            return true;
        });

        function toggleFields() {
            const role = document.getElementById('role').value;
            const clubField = document.getElementById('clubField');
            const specialiteField = document.getElementById('specialiteField');

            // Afficher le champ club pour les directeurs et présidents
            clubField.style.display = (role === 'directeur' || role === 'president' || role === 'competiteur' || role === 'evaluateur') ? 'block' : 'none';
            
            // Afficher le champ spécialité pour les évaluateurs
            specialiteField.style.display = (role === 'evaluateur') ? 'block' : 'none';

            // Si c'est un administrateur, vider la valeur du club
            if (role === 'admin') {
                document.querySelector('[name="numClub"]').value = 'NULL';
            }
        }
    </script>
</body>
</html>
