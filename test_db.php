<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new PDO('mysql:host=13.69.208.103;port=5619;dbname=db_site', 'db_etu', 'N3twork!');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données!";
    
    // Test d'une requête simple
    $stmt = $db->query("SELECT COUNT(*) FROM Utilisateur");
    $count = $stmt->fetchColumn();
    echo "<br>Nombre d'utilisateurs: " . $count;
} catch(PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}
?>
