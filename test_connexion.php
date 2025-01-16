<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('includes/config.php');

try {
    // Test simple de la connexion
    $result = $db->query("SELECT 'Test de connexion réussi' as message")->fetch();
    echo $result['message'];

    // Test de la persistance de la connexion
    for ($i = 0; $i < 5; $i++) {
        $db = checkConnection($db);
        $result = $db->query("SELECT COUNT(*) as count FROM Utilisateur")->fetch();
        echo "<br>Test #" . ($i + 1) . ": Nombre d'utilisateurs: " . $result['count'];
        sleep(1); // Pause d'une seconde entre chaque requête
    }

} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
