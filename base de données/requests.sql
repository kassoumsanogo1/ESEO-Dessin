-- Requête 4 : Compétiteurs ayant participé à tous les concours
SELECT DISTINCT u.nom, u.prenom, u.age
FROM Utilisateur u
JOIN Competiteur c ON u.numUtilisateur = c.numCompetiteur
WHERE NOT EXISTS (
    SELECT co.numConcours
    FROM Concours co
    WHERE NOT EXISTS (
        SELECT pc.numConcours
        FROM ParticipeCompetiteur pc
        WHERE pc.numCompetiteur = c.numCompetiteur
        AND pc.numConcours = co.numConcours
    )
)
ORDER BY u.age;

-- Requête 6 : Statistiques des évaluateurs
SELECT 
    u.nom,
    u.prenom,
    COUNT(e.numDessin) as nombre_evaluations,
    ROUND(AVG(e.note), 2) as moyenne_notes
FROM Utilisateur u
JOIN Evaluateur ev ON u.numUtilisateur = ev.numEvaluateur
LEFT JOIN Evaluation e ON ev.numEvaluateur = e.numEvaluateur
GROUP BY u.numUtilisateur, u.nom, u.prenom
ORDER BY nombre_evaluations DESC;

-- Requête 8 : Statistiques des concours par saison
SELECT 
    c.saison,
    COUNT(DISTINCT c.numConcours) as nombre_concours,
    COUNT(d.numDessin) as nombre_dessins,
    ROUND(AVG(e.note), 2) as moyenne_notes
FROM Concours c
LEFT JOIN Dessin d ON c.numConcours = d.numConcours
LEFT JOIN Evaluation e ON d.numDessin = e.numDessin
GROUP BY c.saison
ORDER BY 
    CASE c.saison
        WHEN 'Printemps' THEN 1
        WHEN 'Été' THEN 2
        WHEN 'Automne' THEN 3
        WHEN 'Hiver' THEN 4
    END;
