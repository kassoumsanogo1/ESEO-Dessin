<?php
session_start();
require_once('../includes/config.php');
checkUserRole('president');

// Récupération des informations du président et du club
$stmt = $db->prepare("
    SELECT u.*, c.*, p.prime
    FROM Utilisateur u
    JOIN President p ON u.numUtilisateur = p.numPresident
    JOIN Club c ON u.numClub = c.numClub
    WHERE u.numUtilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$presidentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des concours organisés par le président
$stmt = $db->prepare("
    SELECT c.*, 
           COUNT(DISTINCT pc.numClub) as nb_clubs_participants,
           COUNT(DISTINCT pcomp.numCompetiteur) as nb_participants
    FROM Concours c
    LEFT JOIN ParticipeClub pc ON c.numConcours = pc.numConcours
    LEFT JOIN ParticipeCompetiteur pcomp ON c.numConcours = pcomp.numConcours
    WHERE c.numPresident = ?
    GROUP BY c.numConcours
    ORDER BY c.dateDebut DESC
");
$stmt->execute([$_SESSION['user_id']]);
$concours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des statistiques du club
$stmt = $db->prepare("
    SELECT 
        COUNT(DISTINCT comp.numCompetiteur) as total_competiteurs,
        COUNT(DISTINCT e.numEvaluateur) as total_evaluateurs
    FROM Club c
    LEFT JOIN Utilisateur u ON c.numClub = u.numClub
    LEFT JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
    LEFT JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur
    WHERE c.numClub = ?
");
$stmt->execute([$presidentInfo['numClub']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Président - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(45deg, #000428, #004e92);
            color: white;
            padding: 2rem;
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .concours-list {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #004e92, #000428);
            color: white;
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
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: #004e92;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }

            .main-content {
                padding: 1rem;
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
            <a href="#dashboard">Tableau de bord</a>
            <a href="#concours">Concours</a>
            <a href="#evaluateurs">Évaluateurs</a>
            <a href="#profil">Profil</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="president-info">
                <h2>Informations du président</h2>
                <div class="info-grid">
                    <div class="stat-card">
                        <h4>Nom</h4>
                        <p><?= htmlspecialchars($presidentInfo['nom']) ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Prénom</h4>
                        <p><?= htmlspecialchars($presidentInfo['prenom']) ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Club</h4>
                        <p><?= htmlspecialchars($presidentInfo['nomClub']) ?></p>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= $presidentInfo['nombreAdherents'] ?></div>
                    <div class="stat-label">Membres totaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_competiteurs'] ?></div>
                    <div class="stat-label">Compétiteurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_evaluateurs'] ?></div>
                    <div class="stat-label">Évaluateurs</div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary">Nouveau concours</button>
                <button class="btn btn-primary">Gérer les évaluateurs</button>
            </div>

            <div class="concours-list">
                <h2>Concours en cours</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Thème</th>
                            <th>Saison</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concours as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['theme']); ?></td>
                                <td><?php echo htmlspecialchars($c['saison']); ?></td>
                                <td><?php echo $c['dateDebut']; ?></td>
                                <td><?php echo $c['dateFin']; ?></td>
                                <td><?php echo htmlspecialchars($c['etat']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ... Scripts existants ...
    </script>
</body>
</html>
