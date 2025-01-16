<?php
session_start();

try {
    $dsn = "mysql:host=127.0.0.1;port=3306;dbname=db_site;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 60,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_FOUND_ROWS => true,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ];

    $db = new PDO($dsn, 'db_etu', 'N3twork!', $options);

    // Configuration supplémentaire après la connexion
    $db->exec("SET SESSION wait_timeout=600");
    $db->exec("SET SESSION interactive_timeout=600");
    $db->exec("SET SESSION net_read_timeout=600");
    $db->exec("SET SESSION net_write_timeout=600");

} catch(PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

function checkConnection($db) {
    try {
        $db->query('SELECT 1');
    } catch (PDOException $e) {
        try {
            $db = new PDO($GLOBALS['dsn'], 'db_etu', 'N3twork!', $GLOBALS['options']);
            return $db;
        } catch (PDOException $e) {
            error_log("Erreur de reconnexion: " . $e->getMessage());
            die("Erreur de connexion à la base de données");
        }
    }
    return $db;
}

function checkUserRole($required_role) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: ../login.php");
        exit();
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: ../access_denied.php");
        exit();
    }
}

function getUserInfo($db, $user_id) {
    $stmt = $db->prepare("
        SELECT u.*, 
            p.prime as president_prime,
            e.specialite as evaluateur_specialite,
            c.datePremiereParticipation,
            cl.nomClub
        FROM Utilisateur u
        LEFT JOIN President p ON u.numUtilisateur = p.numPresident
        LEFT JOIN Evaluateur e ON u.numUtilisateur = e.numEvaluateur
        LEFT JOIN Competiteur c ON u.numUtilisateur = c.numCompetiteur
        LEFT JOIN Club cl ON u.numClub = cl.numClub
        WHERE u.numUtilisateur = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRoleRedirection($role) {
    $redirections = [
        'president' => 'president/dashboard.php',
        'admin' => 'admin/dashboard.php',
        'directeur' => 'directeur/dashboard.php',
        'evaluateur' => 'evaluateur/dashboard.php',
        'competiteur' => 'competiteur/dashboard.php'
    ];
    
    if (isset($redirections[$role])) {
        return $redirections[$role];
    }
    
    header("Location: error.php");
    exit();
}

function checkAccess() {
    if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }
    return true;
}
?>
