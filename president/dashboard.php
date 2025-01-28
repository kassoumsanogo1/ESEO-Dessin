<?php
session_start();
require_once('../includes/config.php');
checkUserRole('president');

// Récupération des informations du président
$stmt = $db->prepare("
    SELECT u.*, p.prime
    FROM Utilisateur u
    JOIN President p ON u.numUtilisateur = p.numPresident
    WHERE u.numUtilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$presidentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des concours présidés avec statistiques détaillées
$stmt = $db->prepare("
    SELECT 
        c.*,
        COUNT(DISTINCT pc.numClub) as nb_clubs,
        COUNT(DISTINCT pcomp.numCompetiteur) as nb_competiteurs,
        COUNT(DISTINCT d.numDessin) as nb_dessins,
        COUNT(DISTINCT j.numEvaluateur) as nb_evaluateurs,
        AVG(e.note) as moyenne_notes,
        MIN(e.note) as note_min,
        MAX(e.note) as note_max,
        (
            SELECT COUNT(*)
            FROM Dessin d2
            WHERE d2.numConcours = c.numConcours
            AND EXISTS (
                SELECT 1 
                FROM Evaluation e2 
                WHERE e2.numDessin = d2.numDessin
            )
        ) as dessins_evalues
    FROM Concours c
    LEFT JOIN ParticipeClub pc ON c.numConcours = pc.numConcours
    LEFT JOIN ParticipeCompetiteur pcomp ON c.numConcours = pcomp.numConcours
    LEFT JOIN Dessin d ON c.numConcours = d.numConcours
    LEFT JOIN Evaluation e ON d.numDessin = e.numDessin
    LEFT JOIN Jury j ON c.numConcours = j.numConcours
    WHERE c.numPresident = ?
    GROUP BY c.numConcours
    ORDER BY c.dateDebut DESC
");
$stmt->execute([$_SESSION['user_id']]);
$concours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des meilleurs dessins tous concours confondus
$stmt = $db->prepare("
    SELECT 
        d.numDessin,
        d.leDessin,
        d.dateRemise,
        c.theme,
        u.nom,
        u.prenom,
        cl.nomClub,
        AVG(e.note) as moyenne_note
    FROM Concours c
    JOIN Dessin d ON c.numConcours = d.numConcours
    JOIN Competiteur comp ON d.numCompetiteur = comp.numCompetiteur
    JOIN Utilisateur u ON comp.numCompetiteur = u.numUtilisateur
    JOIN Club cl ON u.numClub = cl.numClub
    JOIN Evaluation e ON d.numDessin = e.numDessin
    WHERE c.numPresident = ?
    GROUP BY d.numDessin
    HAVING COUNT(e.numEvaluateur) >= 2
    ORDER BY moyenne_note DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$meilleursDessinsTousTemps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des statistiques des évaluateurs
$stmt = $db->prepare("
    SELECT 
        u.nom,
        u.prenom,
        COUNT(DISTINCT e.numDessin) as nb_evaluations,
        AVG(e.note) as moyenne_notes_donnees,
        ev.specialite
    FROM Jury j
    JOIN Evaluateur ev ON j.numEvaluateur = ev.numEvaluateur
    JOIN Utilisateur u ON ev.numEvaluateur = u.numUtilisateur
    JOIN Evaluation e ON ev.numEvaluateur = e.numEvaluateur
    JOIN Dessin d ON e.numDessin = d.numDessin
    JOIN Concours c ON d.numConcours = c.numConcours
    WHERE c.numPresident = ?
    GROUP BY ev.numEvaluateur
    ORDER BY nb_evaluations DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$topEvaluateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        .concours-list, .top-competiteurs {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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

        .top-competiteurs h2 {
            color: #004e92;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .top-competiteurs table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #fff;
        }

        .top-competiteurs th {
            background: #f8f9fa;
            color: #004e92;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
        }

        .top-competiteurs td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .top-competiteurs tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .top-competiteurs tr:last-child td {
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
                        <h4>Prime</h4>
                        <p><?= number_format($presidentInfo['prime'], 2) ?> €</p>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($concours) ?></div>
                    <div class="stat-label">Concours présidés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= array_sum(array_column($concours, 'nb_clubs')) ?></div>
                    <div class="stat-label">Clubs participants total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= array_sum(array_column($concours, 'nb_dessins')) ?></div>
                    <div class="stat-label">Dessins soumis total</div>
                </div>
            </div>

            <div class="concours-list">
                <h2>Détails des concours présidés</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Thème</th>
                            <th>Saison</th>
                            <th>Période</th>
                            <th>État</th>
                            <th>Clubs</th>
                            <th>Compétiteurs</th>
                            <th>Dessins</th>
                            <th>Évaluateurs</th>
                            <th>Moyenne</th>
                            <th>% Évalués</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concours as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['theme']) ?></td>
                                <td><?= htmlspecialchars($c['saison']) ?></td>
                                <td><?= $c['dateDebut'] ?> au <?= $c['dateFin'] ?></td>
                                <td><?= htmlspecialchars($c['etat']) ?></td>
                                <td><?= $c['nb_clubs'] ?></td>
                                <td><?= $c['nb_competiteurs'] ?></td>
                                <td><?= $c['nb_dessins'] ?></td>
                                <td><?= $c['nb_evaluateurs'] ?></td>
                                <td><?= number_format($c['moyenne_notes'], 2) ?></td>
                                <td><?= $c['nb_dessins'] ? number_format(($c['dessins_evalues'] / $c['nb_dessins']) * 100, 1) : 0 ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="top-competiteurs">
                <h2>Meilleurs dessins tous concours confondus</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Dessin</th>
                            <th>Thème</th>
                            <th>Artiste</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Note moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meilleursDessinsTousTemps as $dessin): ?>
                            <tr>
                                <td><?= htmlspecialchars($dessin['leDessin']) ?></td>
                                <td><?= htmlspecialchars($dessin['theme']) ?></td>
                                <td><?= htmlspecialchars($dessin['prenom']) ?> <?= htmlspecialchars($dessin['nom']) ?></td>
                                <td><?= htmlspecialchars($dessin['nomClub']) ?></td>
                                <td><?= $dessin['dateRemise'] ?></td>
                                <td><?= number_format($dessin['moyenne_note'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="top-competiteurs">
                <h2>Statistiques des évaluateurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Spécialité</th>
                            <th>Nombre d'évaluations</th>
                            <th>Moyenne des notes données</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topEvaluateurs as $eval): ?>
                            <tr>
                                <td><?= htmlspecialchars($eval['nom']) ?></td>
                                <td><?= htmlspecialchars($eval['prenom']) ?></td>
                                <td><?= htmlspecialchars($eval['specialite']) ?></td>
                                <td><?= $eval['nb_evaluations'] ?></td>
                                <td><?= number_format($eval['moyenne_notes_donnees'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
