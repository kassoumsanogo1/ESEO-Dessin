<?php
require_once('../includes/config.php');
checkUserRole('admin');

// Requête corrigée pour compter tous les membres du club
$stmt = $db->prepare("
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM Utilisateur u WHERE u.numClub = c.numClub) as nombre_membres,
        (SELECT COUNT(DISTINCT d.numDirecteur) FROM Directeur d WHERE d.numClub = c.numClub) as nombre_directeurs,
        (SELECT COUNT(DISTINCT u.numUtilisateur) 
         FROM Competiteur comp 
         JOIN Utilisateur u ON comp.numCompetiteur = u.numUtilisateur 
         WHERE u.numClub = c.numClub) as nombre_competiteurs,
        (SELECT COUNT(DISTINCT u.numUtilisateur) 
         FROM Evaluateur e 
         JOIN Utilisateur u ON e.numEvaluateur = u.numUtilisateur 
         WHERE u.numClub = c.numClub) as nombre_evaluateurs,
        (SELECT COUNT(DISTINCT pc.numConcours) 
         FROM ParticipeClub pc 
         WHERE pc.numClub = c.numClub) as nombre_concours_participes
    FROM Club c
    ORDER BY c.nomClub
");
$stmt->execute();
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Modifier la requête pour récupérer les membres d'un club
function getMembresClub($db, $numClub) {
    $stmt = $db->prepare("
        SELECT 
            u.*,
            CASE 
                WHEN p.numPresident IS NOT NULL THEN 'Président'
                WHEN d.numDirecteur IS NOT NULL THEN 'Directeur'
                WHEN e.numEvaluateur IS NOT NULL THEN 'Évaluateur'
                WHEN c.numCompetiteur IS NOT NULL THEN 'Compétiteur'
                ELSE 'Non classé'
            END as role
        FROM Utilisateur u
        LEFT JOIN President p ON u.numUtilisateur = p.numPresident
        LEFT JOIN Directeur d ON u.numUtilisateur = d.numDirecteur
        LEFT JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur
        LEFT JOIN Competiteur c ON u.numUtilisateur = c.numCompetiteur
        WHERE u.numClub = ?
        ORDER BY 
            CASE 
                WHEN p.numPresident IS NOT NULL THEN 1
                WHEN d.numDirecteur IS NOT NULL THEN 2
                WHEN e.numEvaluateur IS NOT NULL THEN 3
                WHEN c.numCompetiteur IS NOT NULL THEN 4
                ELSE 5
            END,
            u.nom, 
            u.prenom
    ");
    $stmt->execute([$numClub]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Traitement de l'ajout d'un membre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_member') {
        $clubId = $_POST['club_id'];
        $userId = $_POST['user_id'];
        
        $stmt = $db->prepare("UPDATE Utilisateur SET numClub = ? WHERE numUtilisateur = ?");
        $stmt->execute([$clubId, $userId]);
        
        header("Location: club.php");
        exit;
    } elseif ($_POST['action'] === 'remove_member') {
        $userId = $_POST['user_id'];
        
        $stmt = $db->prepare("UPDATE Utilisateur SET numClub = NULL WHERE numUtilisateur = ?");
        $stmt->execute([$userId]);
        
        header("Location: club.php");
        exit;
    }
}

// Récupération des utilisateurs sans club pour le formulaire d'ajout
$stmt = $db->prepare("
    SELECT numUtilisateur, nom, prenom 
    FROM Utilisateur 
    WHERE numClub IS NULL 
    ORDER BY nom, prenom
");
$stmt->execute();
$utilisateurs_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clubs - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* ... existing navbar and general styles ... */

        .club-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
            margin-top: 80px;
        }

        .club-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .club-header {
            background: linear-gradient(135deg, #004e92, #000428);
            color: white;
            padding: 1.5rem;
        }

        .club-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .club-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1.5rem;
            background: #f8f9fa;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #004e92;
        }

        .stat-item .label {
            font-size: 0.9rem;
            color: #666;
        }

        .club-details {
            padding: 1.5rem;
        }

        .club-info {
            margin-bottom: 1rem;
        }

        .club-info p {
            margin: 0.5rem 0;
            color: #444;
        }

        .members-list {
            margin-top: 1rem;
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .member-role {
            background: #004e92;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .club-actions {
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            background: #f8f9fa;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #004e92;
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Styles pour le modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .member-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-add-member {
            background: #28a745;
        }

        .btn-remove {
            background: #dc3545;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .select-member {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Styles de la navbar */
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

    <!-- Modal pour ajouter un membre -->
    <div id="addMemberModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3>Ajouter un membre au club</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_member">
                <input type="hidden" name="club_id" id="modal-club-id">
                <select name="user_id" class="select-member" required>
                    <option value="">Sélectionner un utilisateur</option>
                    <?php foreach ($utilisateurs_disponibles as $user): ?>
                        <option value="<?php echo $user['numUtilisateur']; ?>">
                            <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-edit">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="club-grid">
        <?php foreach ($clubs as $club): 
            $membres = getMembresClub($db, $club['numClub']);
        ?>
        <div class="club-card">
            <div class="club-header">
                <h2><?php echo htmlspecialchars($club['nomClub']); ?></h2>
            </div>
            
            <div class="club-stats">
                <div class="stat-item">
                    <div class="value"><?php echo $club['nombre_membres']; ?></div>
                    <div class="label">Adhérents</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $club['nombre_concours_participes']; ?></div>
                    <div class="label">Concours</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $club['nombre_competiteurs']; ?></div>
                    <div class="label">Compétiteurs</div>
                </div>
                <div class="stat-item">
                    <div class="value"><?php echo $club['nombre_evaluateurs']; ?></div>
                    <div class="label">Évaluateurs</div>
                </div>
            </div>

            <div class="club-details">
                <div class="club-info">
                    <p><strong>Adresse:</strong> <?php echo htmlspecialchars($club['adresse']); ?></p>
                    <p><strong>Ville:</strong> <?php echo htmlspecialchars($club['ville']); ?></p>
                    <p><strong>Région:</strong> <?php echo htmlspecialchars($club['region']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($club['numTelephone']); ?></p>
                </div>

                <div class="members-list">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3>Membres du club</h3>
                        <button class="btn btn-add-member" onclick="openModal(<?php echo $club['numClub']; ?>)">
                            + Ajouter un membre
                        </button>
                    </div>
                    <?php foreach ($membres as $membre): ?>
                    <div class="member-item">
                        <span><?php echo htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']); ?></span>
                        <div class="member-actions">
                            <span class="member-role"><?php echo htmlspecialchars($membre['role']); ?></span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove_member">
                                <input type="hidden" name="user_id" value="<?php echo $membre['numUtilisateur']; ?>">
                                <button type="submit" class="btn-remove" onclick="return confirm('Voulez-vous vraiment retirer ce membre du club ?')">
                                    ×
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="club-actions"></div>
                <a href="edit_club.php?id=<?php echo $club['numClub']; ?>" class="btn btn-edit">Modifier</a>
                <a href="delete_club.php?id=<?php echo $club['numClub']; ?>" 
                   class="btn btn-delete" 
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce club ?')">
                    Supprimer
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function openModal(clubId) {
            document.getElementById('modal-club-id').value = clubId;
            document.getElementById('addMemberModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addMemberModal').style.display = 'none';
        }

        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('addMemberModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
