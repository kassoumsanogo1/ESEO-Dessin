<?php
session_start();
require_once('../includes/config.php');
checkUserRole('directeur');

// Informations du directeur
$stmt = $db->prepare("
    SELECT u.*, d.dateDebut as debut_direction
    FROM Utilisateur u
    JOIN Directeur d ON u.numUtilisateur = d.numDirecteur
    WHERE u.numUtilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$directeurInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Informations du club dirigé
$stmt = $db->prepare("
    SELECT c.*,
        (SELECT COUNT(*) FROM Utilisateur WHERE numClub = c.numClub) as total_membres,
        (SELECT COUNT(*) FROM Utilisateur u 
         JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur 
         WHERE u.numClub = c.numClub) as total_competiteurs,
        (SELECT COUNT(*) FROM Utilisateur u 
         JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur 
         WHERE u.numClub = c.numClub) as total_evaluateurs
    FROM Club c
    JOIN Directeur d ON c.numClub = d.numClub
    WHERE d.numDirecteur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$clubInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Concours actuels du club
$stmt = $db->prepare("
    SELECT c.*, COUNT(DISTINCT pc.numClub) as nb_clubs_participants
    FROM Concours c
    JOIN ParticipeClub pc ON c.numConcours = pc.numConcours
    WHERE pc.numClub = ? AND c.etat = 'en cours'
    GROUP BY c.numConcours
");
$stmt->execute([$clubInfo['numClub']]);
$concours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Directeur - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #004e92;
            --secondary-color: #000428;
            --background-color: #f8f9fa;
            --card-color: #ffffff;
        }

        body {
            background-color: var(--background-color);
            min-height: 100vh;
            margin: 0;
            padding: 0;
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
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            opacity: 0.9;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 1;
            transform: translateY(-2px);
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem;
            width: 100%;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .director-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .stat-card {
            background: var(--card-color);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .chart-container {
            background: var(--card-color);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .concours-list {
            background: var(--card-color);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 1200px) {
            .chart-container,
            .concours-list {
                grid-column: span 12;
            }
        }

        @media (max-width: 1024px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 1rem;
            }
        }

        .info-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid rgba(0, 78, 146, 0.1);
        }

        .info-item h4 {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .info-item p {
            color: #004e92;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        .concours-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .concours-item:last-child {
            border-bottom: none;
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
            <a href="#membres">Gestion des membres</a>
            <a href="#concours">Concours</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="director-info">
                <h2>Informations du directeur</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <h4>Nom</h4>
                        <p><?= htmlspecialchars($directeurInfo['nom']) ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Prénom</h4>
                        <p><?= htmlspecialchars($directeurInfo['prenom']) ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Club dirigé</h4>
                        <p><?= htmlspecialchars($clubInfo['nomClub']) ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Date de début</h4>
                        <p><?= htmlspecialchars($directeurInfo['debut_direction']) ?></p>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= $clubInfo['total_membres'] ?></div>
                    <div class="stat-label">Membres totaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $clubInfo['total_competiteurs'] ?></div>
                    <div class="stat-label">Compétiteurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $clubInfo['total_evaluateurs'] ?></div>
                    <div class="stat-label">Évaluateurs</div>
                </div>
            </div>

            <div class="dashboard-cards">
                <div class="chart-container">
                    <canvas id="membersChart"></canvas>
                </div>

                <div class="concours-list">
                    <h3>Concours en cours</h3>
                    <?php foreach ($concours as $c): ?>
                        <div class="concours-item">
                            <div>
                                <h4><?= htmlspecialchars($c['nomConcours']) ?></h4>
                                <p>Clubs participants: <?= $c['nb_clubs_participants'] ?></p>
                            </div>
                            <div>
                                <button onclick="viewConcours(<?= $c['numConcours'] ?>)" class="btn-view">
                                    Voir détails
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Création du graphique
        const ctx = document.getElementById('membersChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Évolution des membres',
                    data: [
                        <?= $clubInfo['total_membres'] - 5 ?>, 
                        <?= $clubInfo['total_membres'] - 3 ?>, 
                        <?= $clubInfo['total_membres'] - 2 ?>, 
                        <?= $clubInfo['total_membres'] - 1 ?>, 
                        <?= $clubInfo['total_membres'] ?>
                    ],
                    borderColor: '#004e92',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Évolution du nombre de membres'
                    }
                }
            }
        });

        function viewConcours(id) {
            // Fonction pour voir les détails d'un concours
            window.location.href = `concours-details.php?id=${id}`;
        }
    </script>
</body>
</html>
