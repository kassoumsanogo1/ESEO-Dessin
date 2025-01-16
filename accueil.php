<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Vérification de la connexion
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - ESEO'Dessin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar {
            background: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 1.2rem;
        }

        .logout-btn {
            background: #0984e3;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
        }

        .contest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contest-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .contest-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .contest-info {
            padding: 1rem;
        }

        h1 {
            text-align: center;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="welcome-text">
            Bienvenue <?php echo htmlspecialchars($_SESSION['username']); ?> !
        </div>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </nav>

    <h1>Concours en cours</h1>
    
    <div class="contest-grid" id="contestGrid"></div>

    <script>
        const contests = [
            {
                title: "Nature et Vie Sauvage",
                description: "Illustrez la beauté de la nature",
                deadline: "15 Juin 2024",
                image: "images/nature.png"  // Chemin vers votre image nature
            },
            {
                title: "Art Urbain",
                description: "Capturez l'essence urbaine",
                deadline: "20 Juin 2024",
                image: "images/art.png"     // Chemin vers votre image art
            },
            {
                title: "Portrait Créatif",
                description: "Réinventez le portrait",
                deadline: "25 Juin 2024",
                image: "images/portrait.png" // Chemin vers votre image portrait
            }
        ];

        function showContests() {
            const grid = document.getElementById('contestGrid');
            contests.forEach(contest => {
                grid.innerHTML += `
                    <div class="contest-card">
                        <img src="${contest.image}" class="contest-image" alt="${contest.title}" onerror="this.src='images/default.jpg'">
                        <div class="contest-info">
                            <h3>${contest.title}</h3>
                            <p>${contest.description}</p>
                            <p>Date limite: ${contest.deadline}</p>
                        </div>
                    </div>
                `;
            });
        }

        showContests();
    </script>
</body>
</html>
