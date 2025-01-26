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

// Récupération des concours organisés par le président avec statistiques détaillées
$stmt = $db->prepare("
    SELECT 
        c.*,
        COUNT(DISTINCT pc.numClub) as nb_clubs_participants,
        COUNT(DISTINCT pcomp.numCompetiteur) as nb_participants,
        COUNT(DISTINCT d.numDessin) as nb_dessins,
        COUNT(DISTINCT e.numEvaluateur) as nb_evaluateurs,
        AVG(ev.note) as moyenne_notes
    FROM Concours c
    LEFT JOIN ParticipeClub pc ON c.numConcours = pc.numConcours
    LEFT JOIN ParticipeCompetiteur pcomp ON c.numConcours = pcomp.numConcours
    LEFT JOIN Dessin d ON c.numConcours = d.numConcours
    LEFT JOIN Jury j ON c.numConcours = j.numConcours
    LEFT JOIN Evaluateur e ON j.numEvaluateur = e.numEvaluateur
    LEFT JOIN Evaluation ev ON d.numDessin = ev.numDessin
    WHERE c.numPresident = ?
    GROUP BY c.numConcours
    ORDER BY c.dateDebut DESC
");
$stmt->execute([$_SESSION['user_id']]);
$concours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques du club incluant les résultats des concours
$stmt = $db->prepare("
    SELECT 
        COUNT(DISTINCT comp.numCompetiteur) as total_competiteurs,
        COUNT(DISTINCT e.numEvaluateur) as total_evaluateurs,
        COUNT(DISTINCT d.numDessin) as total_dessins,
        (SELECT COUNT(*) 
         FROM Dessin d2 
         JOIN Competiteur comp2 ON d2.numCompetiteur = comp2.numCompetiteur
         JOIN Utilisateur u2 ON comp2.numCompetiteur = u2.numUtilisateur
         WHERE u2.numClub = c.numClub AND d2.classement <= 3) as nb_podiums,
        (SELECT AVG(note)
         FROM Evaluation ev
         JOIN Dessin d3 ON ev.numDessin = d3.numDessin
         JOIN Competiteur comp3 ON d3.numCompetiteur = comp3.numCompetiteur
         JOIN Utilisateur u3 ON comp3.numCompetiteur = u3.numUtilisateur
         WHERE u3.numClub = c.numClub) as moyenne_club
    FROM Club c
    LEFT JOIN Utilisateur u ON c.numClub = u.numClub
    LEFT JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
    LEFT JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur
    LEFT JOIN Dessin d ON comp.numCompetiteur = d.numCompetiteur
    WHERE c.numClub = ?
    GROUP BY c.numClub
");
$stmt->execute([$presidentInfo['numClub']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des meilleurs compétiteurs du club
$stmt = $db->prepare("
    SELECT 
        u.nom,
        u.prenom,
        COUNT(d.numDessin) as nb_participations,
        AVG(ev.note) as moyenne_notes,
        COUNT(CASE WHEN d.classement <= 3 THEN 1 END) as nb_podiums
    FROM Utilisateur u
    JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
    LEFT JOIN Dessin d ON comp.numCompetiteur = d.numCompetiteur
    LEFT JOIN Evaluation ev ON d.numDessin = ev.numDessin
    WHERE u.numClub = ?
    GROUP BY u.numUtilisateur
    ORDER BY moyenne_notes DESC
    LIMIT 5
");
$stmt->execute([$presidentInfo['numClub']]);
$topCompetiteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['moyenne_club'], 2) ?></div>
                    <div class="stat-label">Moyenne du club</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['nb_podiums'] ?></div>
                    <div class="stat-label">Podiums obtenus</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_dessins'] ?></div>
                    <div class="stat-label">Dessins soumis</div>
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

            <div class="top-competiteurs">
                <h2>Meilleurs compétiteurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Participations</th>
                            <th>Moyenne</th>
                            <th>Podiums</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topCompetiteurs as $comp): ?>
                            <tr>
                                <td><?= htmlspecialchars($comp['nom']) ?></td>
                                <td><?= htmlspecialchars($comp['prenom']) ?></td>
                                <td><?= $comp['nb_participations'] ?></td>
                                <td><?= number_format($comp['moyenne_notes'], 2) ?></td>
                                <td><?= $comp['nb_podiums'] ?></td>
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
