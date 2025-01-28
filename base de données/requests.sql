-- Requête 1 : Compétiteurs ayant participé à un concours en 2023
SELECT DISTINCT 
    u.nom,
    u.adresse,
    u.age,
    c.theme as description_concours,
    c.dateDebut,
    c.dateFin,
    cl.nomClub,
    cl.departement,
    cl.region
FROM Utilisateur u
JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
JOIN ParticipeCompetiteur pc ON comp.numCompetiteur = pc.numCompetiteur
JOIN Concours c ON pc.numConcours = c.numConcours
LEFT JOIN Club cl ON u.numClub = cl.numClub
WHERE YEAR(c.dateDebut) = 2023;

-- Requête 2 : Dessins évalués en 2022 par ordre de note
SELECT 
    d.numDessin,
    e.note,
    u.nom as nom_competiteur,
    c.theme as description_concours,
    c.theme
FROM Dessin d
JOIN Evaluation e ON d.numDessin = e.numDessin
JOIN Competiteur comp ON d.numCompetiteur = comp.numCompetiteur
JOIN Utilisateur u ON comp.numCompetiteur = u.numUtilisateur
JOIN Concours c ON d.numConcours = c.numConcours
WHERE YEAR(e.dateEvaluation) = 2022
ORDER BY e.note ASC;

-- Requête 3 : Informations complètes sur les dessins évalués
SELECT 
    d.numDessin,
    YEAR(c.dateDebut) as annee_concours,
    c.theme as description_concours,
    u_comp.nom as nom_competiteur,
    d.numDessin,
    d.commentaire as commentaire_dessin,
    e.note,
    e.commentaire as commentaire_evaluation,
    u_eval.nom as nom_evaluateur
FROM Dessin d
JOIN Concours c ON d.numConcours = c.numConcours
JOIN Competiteur comp ON d.numCompetiteur = comp.numCompetiteur
JOIN Utilisateur u_comp ON comp.numCompetiteur = u_comp.numUtilisateur
JOIN Evaluation e ON d.numDessin = e.numDessin
JOIN Evaluateur eval ON e.numEvaluateur = eval.numEvaluateur
JOIN Utilisateur u_eval ON eval.numEvaluateur = u_eval.numUtilisateur
ORDER BY d.numDessin;

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

-- Requête 5 : Région avec la meilleure moyenne des notes
SELECT 
    c.region,
    ROUND(AVG(e.note), 2) as moyenne_notes
FROM Club c
JOIN Utilisateur u ON c.numClub = u.numClub
JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
JOIN Dessin d ON comp.numCompetiteur = d.numCompetiteur
JOIN Evaluation e ON d.numDessin = e.numDessin
GROUP BY c.region
HAVING AVG(e.note) >= ALL (
    SELECT AVG(e2.note)
    FROM Club c2
    JOIN Utilisateur u2 ON c2.numClub = u2.numClub
    JOIN Competiteur comp2 ON u2.numUtilisateur = comp2.numCompetiteur
    JOIN Dessin d2 ON comp2.numCompetiteur = d2.numCompetiteur
    JOIN Evaluation e2 ON d2.numDessin = e2.numDessin
    GROUP BY c2.region
);

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

-- Requête 7 : Top 3 des clubs les plus actifs
SELECT 
    c.nomClub,
    c.ville,
    c.region,
    COUNT(DISTINCT pc.numConcours) as nombre_participations,
    COUNT(DISTINCT d.numDessin) as nombre_dessins_soumis
FROM Club c
JOIN ParticipeClub pc ON c.numClub = pc.numClub
JOIN Utilisateur u ON c.numClub = u.numClub
JOIN Competiteur comp ON u.numUtilisateur = comp.numCompetiteur
LEFT JOIN Dessin d ON comp.numCompetiteur = d.numCompetiteur
GROUP BY c.numClub, c.nomClub, c.ville, c.region
ORDER BY nombre_participations DESC, nombre_dessins_soumis DESC
LIMIT 3;

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


