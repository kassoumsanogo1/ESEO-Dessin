<?php
session_start();
require_once('../includes/config.php');

// Récupération des informations de l'utilisateur
$stmt = $db->prepare("
    SELECT c.*, u.nom, u.prenom, cl.nomClub, e.specialite as evalSpecialite 
    FROM Utilisateur u
    LEFT JOIN Club cl ON u.numClub = cl.numClub
    LEFT JOIN Competiteur c ON c.numCompetiteur = u.numUtilisateur
    LEFT JOIN Evaluateur e ON e.numEvaluateur = u.numUtilisateur
    WHERE u.numUtilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des concours où l'utilisateur est compétiteur
$stmtCompetiteur = $db->prepare("
    SELECT c.*, COUNT(d.numDessin) as nb_dessins_soumis,
           CASE 
               WHEN j.numEvaluateur IS NOT NULL THEN 'evaluateur'
               WHEN pc.numCompetiteur IS NOT NULL THEN 'competiteur'
           END as role
    FROM Concours c
    LEFT JOIN Dessin d ON c.numConcours = d.numConcours 
        AND d.numCompetiteur = ?
    LEFT JOIN Jury j ON c.numConcours = j.numConcours 
        AND j.numEvaluateur = ?
    LEFT JOIN ParticipeCompetiteur pc ON c.numConcours = pc.numConcours 
        AND pc.numCompetiteur = ?
    WHERE c.etat = 'en cours'
        AND (j.numEvaluateur IS NOT NULL OR pc.numCompetiteur IS NOT NULL)
    GROUP BY c.numConcours
");
$stmtCompetiteur->execute([
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id']
]);
$concours = $stmtCompetiteur->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Compétiteur - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f0f2f5;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #004e92, #000428);
            color: white;
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
            color: white;
            background: none;
            -webkit-background-clip: initial;
            -webkit-text-fill-color: initial;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            margin-left: 0;
            text-decoration: none;
            color: white;
            opacity: 0.9;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: white;
            opacity: 1;
            transform: translateY(-2px);
        }

        .dashboard {
            margin-top: 80px;
            width: 100%;
        }

        .main-content {
            margin-left: 0;
            padding: 2rem 3rem;
            width: 100%;
            background-color: #f0f2f5;
            min-height: 100vh;
        }

        .main-content h1 {
            color: #004e92;
            margin-bottom: 2rem;
            font-size: 2.2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(0, 78, 146, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #004e92;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .stat-card p {
            color: #2d3436;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .concours-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 2rem;
            border: 1px solid rgba(0, 78, 146, 0.1);
            transition: transform 0.3s ease;
        }

        .concours-card:hover {
            transform: translateY(-5px);
        }

        .concours-card h3 {
            color: #004e92;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .upload-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border: 1px solid rgba(0, 78, 146, 0.1);
        }

        .dessin-upload {
            margin: 1rem 0;
            padding: 1.5rem;
            border: 2px dashed #004e92;
            border-radius: 8px;
            text-align: center;
            background: white;
            transition: border-color 0.3s ease;
        }

        .dessin-upload:hover {
            border-color: #000428;
        }

        .btn-primary {
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #000428, #004e92);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="../images/eseo_logo.png" alt="ESEO Logo" class="logo-img">
            <div class="logo">ESEO'Dessin</div>
        </div>
        <div class="nav-links">
            <a href="#">Tableau de bord</a>
            <a href="#">Mes dessins</a>
            <a href="#">Concours</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </nav>

    <div class="dashboard">
        <main class="main-content">
            <h1>Bienvenue, <?php echo htmlspecialchars($userInfo['prenom']); ?></h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Nom</h3>
                    <p><?php echo htmlspecialchars($userInfo['nom']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Prénom</h3>
                    <p><?php echo htmlspecialchars($userInfo['prenom']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Club</h3>
                    <p><?php echo htmlspecialchars($userInfo['nomClub']); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Dessins soumis</h3>
                    <p><?php echo $userInfo['nbDessinSoumis']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Date d'inscription</h3>
                    <p><?php echo $userInfo['datePremiereParticipation']; ?></p>
                </div>
            </div>

            <h2>Concours actifs</h2>
            <?php foreach ($concours as $c): ?>
                <div class="concours-card">
                    <h3><?php echo htmlspecialchars($c['theme']); ?></h3>
                    <?php if ($c['role'] === 'competiteur'): ?>
                        <p>Dessins soumis: <?php echo $c['nb_dessins_soumis']; ?>/3</p>
                        <?php if ($c['nb_dessins_soumis'] < 3): ?>
                            <form class="upload-form" method="post" action="soumettre_dessin.php" enctype="multipart/form-data">
                                <input type="hidden" name="numConcours" value="<?php echo $c['numConcours']; ?>">
                                <div class="dessin-upload">
                                    <input type="file" name="dessin" required accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary">Soumettre</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Vous êtes évaluateur pour ce concours</p>
                        <a href="../evaluateur/evaluation.php?concours=<?php echo $c['numConcours']; ?>" class="btn btn-primary">
                            Accéder aux évaluations
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>
