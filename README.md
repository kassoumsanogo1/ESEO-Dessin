# 🎨 ESEO'Dessin

Bienvenue sur **ESEO'Dessin**, une plateforme dédiée aux concours de dessin pour les étudiants de l'ESEO. Libérez votre créativité et participez à des concours passionnants tout au long de l'année !

## 🚀 Fonctionnalités

- **Gestion des concours** : Organisez et participez à des concours de dessin.
- **Évaluations** : Les dessins sont évalués par des jurys composés de deux évaluateurs.
- **Statistiques** : Suivez les performances des clubs et des compétiteurs.
- **Tableau de bord** : Interface dédiée pour les présidents de clubs pour gérer les concours et les membres.

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web Apache

## 🛠️ Installation

1. Clonez le dépôt :
    ```bash
    git clone https://github.com/kassoumsanogo1/eseo-dessin.git
    ```

2. Configurez la base de données :
    - Importez les fichiers `bdd/base.sql` et `bdd/inserter.sql` dans votre base de données MySQL.

3. Installez les dépendances :
    ```bash
    composer install
    ```


## 📂 Structure du projet

- `index.php` : Page d'accueil du site.
- `login.php` : Page de connexion des utilisateurs.
- `president/dashboard.php` : Tableau de bord pour les présidents de clubs.
- `competiteur/dashboard.php` : Tableau de bord pour les compétiteurs.
- `evaluateur/dashboard.php` : Tableau de bord pour les évaluateurs.
- `admin/dashboard.php` : Tableau de bord pour les administrateurs.
- `includes/config.php` : Configuration de la base de données et fonctions utilitaires.
- `bdd/requete_exercice.sql` : Script SQL pour créer les vues et les contraintes de la base de données.

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Contact

Pour toute question ou suggestion, veuillez contacter [kassoum_sanogo@reseau.eseo.fr](mailto:kassoum_sanogo@reseau.eseo.fr).

---

Fait avec ❤️ par l'équipe ESEO'Dessin
