<?php
session_start();
require_once('../includes/config.php');
checkUserRole('evaluateur');

// Remplacer la première requête par celle-ci
$stmt = $db->prepare("
    SELECT 
        e.numEvaluateur,
        e.specialite,
        COUNT(DISTINCT ev.numDessin) as nbDessinsEvalues,
        u.nom,
        u.prenom,
        c.nomClub,
        COALESCE(AVG(ev.note), 0) as noteMoyenne
    FROM Evaluateur e
    JOIN Utilisateur u ON e.numEvaluateur = u.numUtilisateur
    LEFT JOIN Club c ON u.numClub = c.numClub
    LEFT JOIN Evaluation ev ON e.numEvaluateur = ev.numEvaluateur
    WHERE e.numEvaluateur = ?
    GROUP BY e.numEvaluateur, e.specialite, u.nom, u.prenom, c.nomClub
");
$stmt->execute([$_SESSION['user_id']]);
$evaluateurInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Optimisation de la requête pour les dessins à évaluer
$stmt = $db->prepare("
    SELECT 
        d.numDessin,
        d.leDessin,
        c.theme,
        CONCAT(u.prenom, ' ', u.nom) as competiteur_nom
    FROM Dessin d
    JOIN Concours c ON d.numConcours = c.numConcours
    JOIN Utilisateur u ON d.numCompetiteur = u.numUtilisateur
    JOIN Jury j ON c.numConcours = j.numConcours
    LEFT JOIN Evaluation e ON d.numDessin = e.numDessin 
        AND e.numEvaluateur = ?
    WHERE j.numEvaluateur = ?
    AND e.numDessin IS NULL
    AND c.etat = 'en cours'
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$dessinsAEvaluer = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Évaluateur - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #004e92;
            --secondary: #000428;
            --accent: #74b9ff;
            --bg-light: #f0f2f5;
            --text-dark: #2d3436;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            min-height: 100vh;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
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

        .evaluator-profile {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }

        .evaluator-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary);
            font-weight: 600;
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Réduction de la largeur minimale */
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-card {
            background: white;
            padding: 1.5rem; /* Réduction du padding */
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 600;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
        }

        .evaluation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Réduction de la largeur minimale */
            gap: 1.5rem; /* Réduction de l'espacement */
            margin-top: 1.5rem;
        }

        .dessin-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            max-width: 400px; /* Limitation de la largeur maximale */
            margin: 0 auto;
        }

        .dessin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .dessin-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .dessin-image {
            width: 100%;
            height: 200px; /* Réduction de la hauteur */
            object-fit: cover;
        }

        .evaluation-form {
            padding: 1rem; /* Réduction du padding */
        }

        .note-input {
            width: 100%;
            padding: 0.8rem; /* Réduction du padding des inputs */
            margin: 0.4rem 0;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .note-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        textarea {
            width: 100%;
            padding: 0.8rem; /* Réduction du padding des inputs */
            margin: 0.4rem 0;
            border: 2px solid #eee;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            transition: all 0.3s ease;
        }

        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn-evaluate {
            width: 100%;
            padding: 0.8rem; /* Réduction du padding du bouton */
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-evaluate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 1200px) {
            .evaluation-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .evaluator-profile {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .evaluator-avatar {
                margin: 0 auto;
            }
            
            .nav-links {
                display: none;
            }

            .evaluation-grid {
                grid-template-columns: 1fr;
            }
            
            .dessin-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="../images/eseo_logo.png" alt="ESEO Logo" class="logo-img">
            <div class="logo">ESEO'Dessin Évaluateur</div>
        </div>
        <div class="nav-links">
            <a href="#dashboard">Tableau de bord</a>
            <a href="#evaluations">Évaluations</a>
            <a href="#historique">Historique</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="evaluator-profile">
            <div class="evaluator-avatar">
                <?= strtoupper(substr($evaluateurInfo['prenom'], 0, 1)) ?>
            </div>
            <div>
                <h2><?= htmlspecialchars($evaluateurInfo['prenom'] . ' ' . $evaluateurInfo['nom']) ?></h2>
                <p>Évaluateur - <?= htmlspecialchars($evaluateurInfo['specialite']) ?></p>
                <p>Club: <?= htmlspecialchars($evaluateurInfo['nomClub']) ?></p>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value" id="dessinsEvalues"><?= $evaluateurInfo['nbDessinsEvalues'] ?></div>
                <div class="stat-label">Dessins évalués</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="aEvaluer"><?= count($dessinsAEvaluer) ?></div>
                <div class="stat-label">À évaluer</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="noteMoyenne"><?= number_format($evaluateurInfo['noteMoyenne'], 1) ?></div>
                <div class="stat-label">Note moyenne donnée</div>
            </div>
        </div>

        <h2 class="section-title">Dessins à évaluer (<?= count($dessinsAEvaluer) ?>)</h2>
        <?php if (empty($dessinsAEvaluer)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>Aucun dessin à évaluer</h3>
                <p>Vous avez évalué tous les dessins qui vous ont été assignés.</p>
            </div>
        <?php else: ?>
        <div class="evaluation-grid">
            <?php foreach ($dessinsAEvaluer as $dessin): ?>
            <div class="dessin-card" data-dessin-id="<?= $dessin['numDessin'] ?>">
                <div class="dessin-header">
                    <h3><?= htmlspecialchars($dessin['theme']) ?></h3>
                    <span class="badge">En attente</span>
                </div>
                <div class="dessin-preview" onclick="openImageModal('<?= htmlspecialchars($dessin['leDessin']) ?>')">
                    <img src="<?= htmlspecialchars($dessin['leDessin']) ?>" 
                         alt="Dessin à évaluer" 
                         class="dessin-image"
                         loading="lazy">
                    <div class="preview-overlay">
                        <span>👁️ Voir en grand</span>
                    </div>
                </div>
                <div class="dessin-info">
                    <p class="artist">Par <?= htmlspecialchars($dessin['competiteur_nom']) ?></p>
                    <div class="criteria-grid">
                        <div class="criteria">
                            <label>Technique</label>
                            <input type="range" min="0" max="10" step="0.5" 
                                   class="range-input" 
                                   data-criteria="technique"
                                   oninput="updateNote(this)">
                            <span class="range-value">5.0</span>
                        </div>
                        <div class="criteria">
                            <label>Créativité</label>
                            <input type="range" min="0" max="10" step="0.5" 
                                   class="range-input" 
                                   data-criteria="creativite"
                                   oninput="updateNote(this)">
                            <span class="range-value">5.0</span>
                        </div>
                    </div>
                </div>
                <form class="evaluation-form" method="post" action="evaluer.php" onsubmit="return validateForm(this)">
                    <input type="hidden" name="numDessin" value="<?= $dessin['numDessin'] ?>">
                    <input type="hidden" name="note" class="final-note">
                    <div class="form-group">
                        <label for="commentaire">Commentaire détaillé</label>
                        <textarea name="commentaire" 
                                  placeholder="Donnez un retour constructif..." 
                                  required
                                  minlength="20"
                                  class="comment-input"></textarea>
                        <div class="textarea-footer">
                            <span class="char-count">0/200</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-evaluate">
                        <span class="btn-icon">✓</span>
                        Soumettre l'évaluation
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Modal pour l'aperçu des images -->
        <div id="imageModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <img id="modalImage" src="" alt="Aperçu du dessin">
            </div>
        </div>

        <style>
            .section-title {
                color: var(--text-dark);
                margin-bottom: 1.5rem;
                font-size: 1.5rem;
            }

            .empty-state {
                text-align: center;
                padding: 3rem;
                background: white;
                border-radius: 15px;
                box-shadow: var(--card-shadow);
            }

            .empty-icon {
                font-size: 3rem;
                margin-bottom: 1rem;
            }

            .dessin-preview {
                position: relative;
                cursor: pointer;
            }

            .preview-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                opacity: 0;
                transition: opacity 0.3s;
            }

            .dessin-preview:hover .preview-overlay {
                opacity: 1;
            }

            .criteria-grid {
                display: grid;
                gap: 1rem;
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 8px;
                margin: 1rem 0;
            }

            .criteria label {
                display: block;
                margin-bottom: 0.5rem;
                color: var(--text-dark);
                font-weight: 500;
            }

            .range-input {
                width: 100%;
                margin: 0.5rem 0;
            }

            .comment-input {
                min-height: 100px;
                padding: 0.8rem;
                border: 2px solid #eee;
                border-radius: 8px;
                width: 100%;
                margin: 0.5rem 0;
                transition: border-color 0.3s;
            }

            .textarea-footer {
                display: flex;
                justify-content: flex-end;
                font-size: 0.8rem;
                color: #666;
            }

            .badge {
                background: var(--accent);
                color: white;
                padding: 0.2rem 0.8rem;
                border-radius: 20px;
                font-size: 0.8rem;
            }

            /* Styles pour la modal */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 1000;
            }

            .modal-content {
                position: relative;
                margin: auto;
                padding: 0;
                width: 90%;
                max-width: 1200px;
                top: 50%;
                transform: translateY(-50%);
            }

            #modalImage {
                width: 100%;
                height: auto;
                max-height: 90vh;
                object-fit: contain;
            }
        </style>

        <script>
            function updateNote(input) {
                const value = input.value;
                input.nextElementSibling.textContent = value;
                
                // Calculer la note moyenne
                const card = input.closest('.dessin-card');
                const ranges = card.querySelectorAll('.range-input');
                let sum = 0;
                ranges.forEach(range => sum += parseFloat(range.value));
                const average = sum / ranges.length;
                
                card.querySelector('.final-note').value = average.toFixed(1);
            }

            function validateForm(form) {
                const commentaire = form.querySelector('textarea').value;
                if (commentaire.length < 20) {
                    alert('Le commentaire doit contenir au moins 20 caractères');
                    return false;
                }
                return true;
            }

            function openImageModal(src) {
                const modal = document.getElementById('imageModal');
                const modalImg = document.getElementById('modalImage');
                modal.style.display = "block";
                modalImg.src = src;
            }

            // Gestionnaires d'événements pour la modal
            document.querySelector('.close-modal').onclick = function() {
                document.getElementById('imageModal').style.display = "none";
            }

            // Mise à jour du compteur de caractères
            document.querySelectorAll('.comment-input').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    const count = this.value.length;
                    this.closest('form').querySelector('.char-count').textContent = 
                        `${count}/200`;
                });
            });
        </script>
    </main>

    <script>
        // Modifier le script d'animation pour éviter la boucle
        function animateValue(id, start, end, duration) {
            if (start === end) return; // Éviter l'animation si start = end
            
            let current = start;
            const range = end - start;
            const stepTime = Math.abs(Math.floor(duration / range)) || 50; // Minimum 50ms
            const increment = end > start ? 1 : -1;
            const obj = document.getElementById(id);
            
            const timer = setInterval(() => {
                current += increment;
                obj.textContent = current;
                if (current === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', () => {
            animateValue("dessinsEvalues", 0, <?= $evaluateurInfo['nbDessinsEvalues'] ?>, 1000);
            animateValue("aEvaluer", 0, <?= count($dessinsAEvaluer) ?>, 1000);
            
            // Animation au scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            document.querySelectorAll('.dessin-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
