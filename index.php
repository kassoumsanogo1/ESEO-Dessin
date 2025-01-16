<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESEO'Dessin</title>
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
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.95);
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
            background: linear-gradient(45deg, #004e92, #000428);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links a {
            margin-left: 2rem;
            text-decoration: none;
            color: #004e92;
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
            background: linear-gradient(45deg, #004e92, #000428);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: #000428;
            transform: translateY(-2px);
        }

        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #000428, #004e92);
            position: relative;
            overflow: hidden;
            padding: 2rem;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(0, 78, 146, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(0, 4, 40, 0.4) 0%, transparent 50%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 800px;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            background: linear-gradient(to right, #fff, #74b9ff);
            -webkit-background-clip: text;
            color: transparent;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .cta-button {
            padding: 1.2rem 2.5rem;
            font-size: 1.1rem;
            background: linear-gradient(45deg, #004e92, #000428);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            letter-spacing: 1px;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .contests {
            padding: 4rem 2rem;
        }

        .contests h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: #2d3436;
        }

        .contest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contest-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .contest-card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .contest-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .contest-card:hover .contest-image {
            transform: scale(1.05);
        }

        .contest-info {
            padding: 1.5rem;
        }

        .contest-info h3 {
            margin-bottom: 1rem;
            color: #2d3436;
        }

        .contest-info p {
            color: #636e72;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .nav-links {
                display: none;
            }
        }

        /* Styles pour la modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.5s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h2 {
            color: #004e92;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .modal-content p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .choice-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .choice-button {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-btn {
            background: linear-gradient(45deg, #004e92, #000428);
            color: white;
        }

        .register-btn {
            background: white;
            color: #004e92;
            border: 2px solid #004e92;
        }

        .choice-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .register-btn:hover {
            background: #004e92;
            color: white;
        }

        .close-modal {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
        }

        .close-modal:hover {
            color: #004e92;
            background: rgba(0, 0, 0, 0.1);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="images/eseo_logo.png" alt="ESEO Logo" class="logo-img">
            <div class="logo">ESEO'Dessin</div>
        </div>
        <div class="nav-links">
            <a href="#home">Accueil</a>
            <a href="#contests">Concours</a>
            <a href="#about">À propos</a>
            <a href="login.php">Connexion</a>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Libérez votre créativité</h1>
            <p>Participez à nos concours de dessin et montrez votre talent au monde entier</p>
            <button class="cta-button">Participer maintenant</button>
        </div>
    </section>

    <!-- Mise à jour de la structure de la modal -->
    <div class="modal" id="participateModal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h2>Rejoignez l'aventure</h2>
            <p>Montrez votre talent artistique en participant aux concours ESEO'Dessin</p>
            <div class="choice-buttons">
                <button class="choice-button login-btn" onclick="window.location.href='login.php'">Connexion</button>
                <button class="choice-button register-btn" onclick="window.location.href='register.php'">Inscription</button>
            </div>
        </div>
    </div>

    <section class="contests" id="contests">
        <h2>Concours en cours</h2>
        <div class="contest-grid">
            <!-- Les cartes de concours seront générées dynamiquement par JavaScript -->
        </div>
    </section>

    <script>
        // Données des concours
        const contests = [
            {
                title: "Nature et Vie Sauvage",
                description: "Illustrez la beauté de la nature et de la faune",
                deadline: "15 Juin 2024",
                image: "images/nature.png" 
            },
            {
                title: "Art Urbain",
                description: "Capturez l'essence de la vie urbaine moderne",
                deadline: "20 Juin 2024",
                image: "images/art.png"
            },
            {
                title: "Portrait Créatif",
                description: "Réinventez l'art du portrait",
                deadline: "25 Juin 2024",
                image: "images/portrait.png"
            }
        ];

        // Générer les cartes de concours
        function generateContestCards() {
            const contestGrid = document.querySelector('.contest-grid');
            contests.forEach(contest => {
                const card = `
                    <div class="contest-card">
                        <img src="${contest.image}" alt="${contest.title}" class="contest-image">
                        <div class="contest-info">
                            <h3>${contest.title}</h3>
                            <p>${contest.description}</p>
                            <p>Date limite: ${contest.deadline}</p>
                        </div>
                    </div>
                `;
                contestGrid.innerHTML += card;
            });
        }

        // Animation du scroll fluide
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            generateContestCards();
        });

        // Modal functionality
        const modal = document.getElementById('participateModal');
        const ctaButton = document.querySelector('.cta-button');
        const closeModal = document.querySelector('.close-modal');

        ctaButton.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
