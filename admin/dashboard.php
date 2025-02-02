<?php
require_once('../includes/config.php');
checkUserRole('admin');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Récupération des informations de l'administrateur
$stmt = $db->prepare("
    SELECT u.*, a.dateDebut
    FROM Utilisateur u
    JOIN Administrateur a ON u.numUtilisateur = a.numAdministrateur
    WHERE u.numUtilisateur = ?
");
$stmt->execute([$_SESSION['user_id']]);
$adminInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistiques globales améliorées
$stmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM Club) as total_clubs,
        (SELECT COUNT(*) FROM Concours) as total_concours,
        (SELECT COUNT(*) FROM Utilisateur) as total_users,
        (SELECT COUNT(*) FROM Concours WHERE etat = 'en cours') as concours_actifs,
        (SELECT COUNT(*) FROM Dessin) as total_dessins,
        (SELECT COUNT(*) FROM Competiteur) as total_competiteurs,
        (SELECT COUNT(*) FROM Evaluation) as total_evaluations,
        (SELECT COUNT(DISTINCT numClub) FROM ParticipeClub) as clubs_participants
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistiques des concours par mois
$stmt = $db->prepare("
    SELECT 
        DATE_FORMAT(dateDebut, '%Y-%m') as mois,
        COUNT(*) as nombre_concours,
        COUNT(DISTINCT d.numDessin) as nombre_dessins
    FROM Concours c
    LEFT JOIN Dessin d ON c.numConcours = d.numConcours
    WHERE YEAR(dateDebut) = YEAR(CURRENT_DATE)
    GROUP BY DATE_FORMAT(dateDebut, '%Y-%m')
");
$stmt->execute();
$stats_par_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Correction de la requête pour les derniers concours
$stmt = $db->prepare("
    SELECT 
        c.*,
        u.nom as president_nom,
        u.prenom as president_prenom,
        (SELECT COUNT(DISTINCT pc.numClub) 
         FROM ParticipeClub pc 
         WHERE pc.numConcours = c.numConcours) as nb_clubs,
        (SELECT COUNT(DISTINCT d.numDessin) 
         FROM Dessin d 
         WHERE d.numConcours = c.numConcours) as nb_dessins,
        (SELECT COUNT(DISTINCT e.numEvaluateur) 
         FROM Jury e 
         WHERE e.numConcours = c.numConcours) as nb_evaluateurs
    FROM Concours c
    LEFT JOIN President p ON c.numPresident = p.numPresident
    LEFT JOIN Utilisateur u ON p.numPresident = u.numUtilisateur
    ORDER BY c.dateDebut DESC
");
$stmt->execute();
$concours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des évaluations
$stmt = $db->prepare("
    SELECT 
        e.numEvaluateur,
        u.nom,
        u.prenom,
        COUNT(e.numDessin) as nb_evaluations,
        AVG(e.note) as moyenne_notes
    FROM Evaluation e
    JOIN Evaluateur ev ON e.numEvaluateur = ev.numEvaluateur
    JOIN Utilisateur u ON ev.numEvaluateur = u.numUtilisateur
    GROUP BY e.numEvaluateur
    ORDER BY nb_evaluations DESC
    LIMIT 5
");
$stmt->execute();
$top_evaluateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter la section des meilleurs dessins
$stmt = $db->prepare("
    SELECT 
        d.*,
        u.nom as artiste_nom,
        u.prenom as artiste_prenom,
        AVG(e.note) as note_moyenne
    FROM Dessin d
    JOIN Competiteur c ON d.numCompetiteur = c.numCompetiteur
    JOIN Utilisateur u ON c.numCompetiteur = u.numUtilisateur
    LEFT JOIN Evaluation e ON d.numDessin = e.numDessin
    GROUP BY d.numDessin
    HAVING note_moyenne IS NOT NULL
    ORDER BY note_moyenne DESC
    LIMIT 5
");
$stmt->execute();
$top_dessins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - ESEO'Dessin</title>
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
            opacity: 0.9;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 1;
            transform: translateY(-2px);
        }

        .dashboard {
            margin-top: 80px;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            padding: 1.8rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #004e92;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .stat-card p {
            color: #2d3436;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .admin-actions {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
        }

        .btn-primary {
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .admin-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 2rem;
        }

        .admin-table th {
            background: #004e92;
            color: white;
            padding: 1.2rem;
            text-align: left;
            font-weight: 500;
        }

        .admin-table td {
            padding: 1.2rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .admin-table tr:hover {
            background-color: #f8f9fa;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 3rem 0 1rem 0;
        }

        .section-header h2 {
            font-size: 1.8rem;
            color: #004e92;
            font-weight: 600;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(45deg, #004e92, #000428);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
        }

        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-actions {
                flex-direction: column;
            }
        }

        .admin-profile {
            background: linear-gradient(135deg, #004e92, #000428);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }

        .admin-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #004e92;
            font-weight: 600;
        }

        .admin-info {
            display: grid;
            gap: 0.5rem;
        }

        .admin-info h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .admin-info p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .admin-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .admin-stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        /* Ajout de styles pour les badges d'état */
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-en-cours {
            background-color: #3498db;
            color: white;
        }

        .status-évalué {
            background-color: #2ecc71;
            color: white;
        }

        .status-pas-commencé {
            background-color: #95a5a6;
            color: white;
        }

        .status-attente {
            background-color: #f1c40f;
            color: white;
        }

        .status-résultat {
            background-color: #e67e22;
            color: white;
        }

        /* Style pour la pagination si nécessaire */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background: #f0f2f5;
            border-radius: 5px;
            text-decoration: none;
            color: #004e92;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #004e92;
            color: white;
        }

        /* Amélioration du style du tableau */
        .admin-table {
            margin-bottom: 2rem;
        }

        .admin-table th {
            position: sticky;
            top: 80px;
            background: #004e92;
            z-index: 10;
        }

        .admin-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table-actions {
            display: flex;
            gap: 0.3rem;
        }

        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="../images/eseo_logo.png" alt="ESEO Logo" class="logo-img">
            <div class="logo">ESEO'Dessin Admin</div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Tableau de bord</a>
            <a href="club.php">Clubs</a>
            <a href="../request.php">Requêtes SQL</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </nav>

    <div class="dashboard">
        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($adminInfo['prenom'], 0, 1)); ?>
            </div>
            <div class="admin-info">
                <h2><?php echo htmlspecialchars($adminInfo['prenom'] . ' ' . $adminInfo['nom']); ?></h2>
                <p>Administrateur système</p>
                <p>En poste depuis : <?php echo date('d/m/Y', strtotime($adminInfo['dateDebut'])); ?></p>
                <div class="admin-stats">
                    <div class="admin-stat-item">
                        <strong><?php echo htmlspecialchars($stats['total_users']); ?></strong> utilisateurs
                    </div>
                    <div class="admin-stat-item">
                        <strong><?php echo htmlspecialchars($stats['total_clubs']); ?></strong> clubs
                    </div>
                    <div class="admin-stat-item">
                        <strong><?php echo htmlspecialchars($stats['total_concours']); ?></strong> concours
                    </div>
                </div>
            </div>
        </div>

        <h1>Tableau de bord administrateur</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Clubs</h3>
                <p><?php echo htmlspecialchars($stats['total_clubs']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Concours actifs</h3>
                <p><?php echo htmlspecialchars($stats['concours_actifs']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <p><?php echo htmlspecialchars($stats['total_users']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Dessins soumis</h3>
                <p><?php echo htmlspecialchars($stats['total_dessins']); ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <a href="create_club.php" class="btn-primary">+ Nouveau club</a>
            <a href="create_user.php" class="btn-primary">+ Nouvel utilisateur</a>
            <a href="create_contest.php" class="btn-primary">+ Nouveau concours</a>
        </div>

        <div class="section-header">
            <h2>Liste des concours</h2>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 20%">Thème</th>
                    <th style="width: 15%">Président</th>
                    <th style="width: 12%">Date début</th>
                    <th style="width: 12%">Date fin</th>
                    <th style="width: 10%">Clubs</th>
                    <th style="width: 10%">Dessins</th>
                    <th style="width: 11%">État</th>
                    <th style="width: 10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($concours as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['theme']); ?></td>
                    <td><?php echo htmlspecialchars($c['president_nom'] . ' ' . $c['president_prenom']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($c['dateDebut'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($c['dateFin'])); ?></td>
                    <td><?php echo htmlspecialchars($c['nb_clubs']); ?></td>
                    <td><?php echo htmlspecialchars($c['nb_dessins']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $c['etat'])); ?>">
                            <?php echo htmlspecialchars($c['etat']); ?>
                        </span>
                    </td>
                    <td class="table-actions">
                        <a href="edit_concours.php?id=<?php echo $c['numConcours']; ?>" class="btn-small btn-edit">Modifier</a>
                        <a href="delete_concours.php?id=<?php echo $c['numConcours']; ?>" 
                           class="btn-small btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce concours ?')">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Graphique des concours par mois
    const concoursData = <?php echo json_encode($stats_par_mois); ?>;
    new Chart(document.getElementById('concoursChart'), {
        type: 'line',
        data: {
            labels: concoursData.map(item => item.mois),
            datasets: [{
                label: 'Nombre de concours',
                data: concoursData.map(item => item.nombre_concours),
                borderColor: '#004e92',
                tension: 0.1
            }, {
                label: 'Nombre de dessins',
                data: concoursData.map(item => item.nombre_dessins),
                borderColor: '#000428',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution des concours et dessins par mois'
                }
            }
        }
    });

    // Graphique des évaluations
    const evaluateursData = <?php echo json_encode($top_evaluateurs); ?>;
    new Chart(document.getElementById('evaluateursChart'), {
        type: 'bar',
        data: {
            labels: evaluateursData.map(item => item.nom + ' ' + item.prenom),
            datasets: [{
                label: 'Nombre d\'évaluations',
                data: evaluateursData.map(item => item.nb_evaluations),
                backgroundColor: '#004e92'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Top 5 des évaluateurs'
                }
            }
        }
    });
    </script>
</body>
</html>
