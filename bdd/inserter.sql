-- Réinitialisation des tables

-- Insertion des Clubs
INSERT INTO Club VALUES
(1, 'Club Art Paris', '15 rue de la Paix, Paris', '0123456789', 50, 'Paris', '75', 'Ile-de-France'),
(2, 'Atelier Lyon', '23 rue Victor Hugo, Lyon', '0445678912', 45, 'Lyon', '69', 'Rhône-Alpes'),
(3, 'Club Nantes', '8 rue de la Loire, Nantes', '0278912345', 40, 'Nantes', '44', 'Pays de la Loire'),
(4, 'Art Marseille', '12 rue du Port, Marseille', '0491234567', 55, 'Marseille', '13', 'PACA'),
(5, 'Club Lille', '45 rue Nationale, Lille', '0320123456', 35, 'Lille', '59', 'Hauts-de-France'),
(6, 'Angers Art', '25 rue de la Maine, Angers', '0241789123', 42, 'Angers', '49', 'Pays de la Loire'),
(7, 'Tours Dessin', '18 rue Nationale, Tours', '0247456789', 38, 'Tours', '37', 'Centre-Val de Loire'),
(8, 'Club Bordeaux', '56 cours Victor Hugo, Bordeaux', '0556789123', 48, 'Bordeaux', '33', 'Nouvelle-Aquitaine');

-- Insertion des Utilisateurs (avec mots de passe hachés)
INSERT INTO Utilisateur VALUES
-- Présidents (1-3)
(1, 1, 'Dubois', 'Jean', 'Paris', 'jean.dubois', 'hash123', 45),
(2, 2, 'Martin', 'Sophie', 'Lyon', 'sophie.martin', 'hash124', 42),
(3, 3, 'Bernard', 'Pierre', 'Nantes', 'pierre.bernard', 'hash125', 48),

-- Administrateurs (4-6)
(4, NULL, 'Admin', 'System', 'Paris', 'admin.system', 'admin123', 35),
(5, NULL, 'Tech', 'Support', 'Lyon', 'tech.support', 'tech123', 32),
(6, NULL, 'Master', 'Admin', 'Nantes', 'master.admin', 'master123', 38),

-- Directeurs (7-10)
(7, 1, 'Petit', 'Marie', 'Paris', 'marie.petit', 'dir123', 41),
(8, 2, 'Durand', 'Paul', 'Lyon', 'paul.durand', 'dir124', 39),
(9, 3, 'Moreau', 'Anne', 'Nantes', 'anne.moreau', 'dir125', 44),
(10, 4, 'Robert', 'Michel', 'Marseille', 'michel.robert', 'dir126', 46),

-- Évaluateurs (11-20)
(11, 1, 'Eval1', 'Pierre', 'Paris', 'eval1', 'eval123', 35),
(12, 1, 'Eval2', 'Marie', 'Paris', 'eval2', 'eval124', 42),
(13, 2, 'Eval3', 'Jean', 'Lyon', 'eval3', 'eval125', 38),
(14, 2, 'Eval4', 'Sophie', 'Lyon', 'eval4', 'eval126', 36),
(15, 3, 'Eval5', 'Marc', 'Nantes', 'eval5', 'eval127', 45),
(16, 3, 'Eval6', 'Julie', 'Nantes', 'eval6', 'eval128', 39),
(17, 4, 'Eval7', 'Thomas', 'Marseille', 'eval7', 'eval129', 41),
(18, 4, 'Eval8', 'Laura', 'Marseille', 'eval8', 'eval130', 37),
(19, 5, 'Eval9', 'Nicolas', 'Lille', 'eval9', 'eval131', 44),
(20, 5, 'Eval10', 'Emma', 'Lille', 'eval10', 'eval132', 38),

-- Compétiteurs (21-50)
(21, 1, 'Comp1', 'Alice', 'Paris', 'comp1', 'comp123', 25),
(22, 1, 'Comp2', 'Bob', 'Paris', 'comp2', 'comp124', 28),
(23, 1, 'Comp3', 'Charlie', 'Paris', 'comp3', 'comp125', 30),
(24, 1, 'Comp4', 'David', 'Paris', 'comp4', 'comp126', 29),
(25, 1, 'Comp5', 'Eva', 'Paris', 'comp5', 'comp127', 31),
(26, 1, 'Comp6', 'Felix', 'Paris', 'comp6', 'comp128', 27),
(27, 2, 'Comp7', 'Gabriel', 'Lyon', 'comp7', 'comp129', 26),
(28, 2, 'Comp8', 'Hugo', 'Lyon', 'comp8', 'comp130', 29),
(29, 2, 'Comp9', 'Iris', 'Lyon', 'comp9', 'comp131', 31),
(30, 2, 'Comp10', 'Jules', 'Lyon', 'comp10', 'comp132', 28),
(31, 2, 'Comp11', 'Kevin', 'Lyon', 'comp11', 'comp133', 30),
(32, 2, 'Comp12', 'Laura', 'Lyon', 'comp12', 'comp134', 27),
(33, 3, 'Comp13', 'Marc', 'Nantes', 'comp13', 'comp135', 32),
(34, 3, 'Comp14', 'Nina', 'Nantes', 'comp14', 'comp136', 29),
(35, 3, 'Comp15', 'Oscar', 'Nantes', 'comp15', 'comp137', 31),
(36, 3, 'Comp16', 'Paul', 'Nantes', 'comp16', 'comp138', 28),
(37, 3, 'Comp17', 'Quentin', 'Nantes', 'comp17', 'comp139', 30),
(38, 3, 'Comp18', 'Rachel', 'Nantes', 'comp18', 'comp140', 27),
(39, 4, 'Comp19', 'Sarah', 'Marseille', 'comp19', 'comp141', 33),
(40, 4, 'Comp20', 'Thomas', 'Marseille', 'comp20', 'comp142', 29),
(41, 4, 'Comp21', 'Ulysse', 'Marseille', 'comp21', 'comp143', 31),
(42, 4, 'Comp22', 'Victor', 'Marseille', 'comp22', 'comp144', 28),
(43, 4, 'Comp23', 'William', 'Marseille', 'comp23', 'comp145', 30),
(44, 4, 'Comp24', 'Xavier', 'Marseille', 'comp24', 'comp146', 27),
(45, 5, 'Comp25', 'Yves', 'Lille', 'comp25', 'comp147', 29),
(46, 5, 'Comp26', 'Zoe', 'Lille', 'comp26', 'comp148', 30),
(47, 6, 'Comp27', 'Arthur', 'Angers', 'comp27', 'comp149', 28),
(48, 6, 'Comp28', 'Bruno', 'Angers', 'comp28', 'comp150', 27),
(49, 7, 'Comp29', 'Celine', 'Tours', 'comp29', 'comp151', 29),
(50, 8, 'Comp30', 'Zoe', 'Bordeaux', 'comp30', 'comp154', 33);

-- Insertion des rôles spécifiques
INSERT INTO President VALUES
(1, 1000.00),
(2, 1200.00),
(3, 1100.00);

INSERT INTO Administrateur VALUES
(4, '2023-01-01'),
(5, '2023-02-01'),
(6, '2023-03-01');

-- Insertion des directeurs
INSERT INTO Directeur VALUES
(7, 1, '2023-01-01'),
(8, 2, '2023-01-01'),
(9, 3, '2023-01-01'),
(10, 4, '2023-01-01');

-- Insertion des Evaluateurs avec leurs spécialités
INSERT INTO Evaluateur VALUES
(11, 'Portrait', 0),
(12, 'Paysage', 0),
(13, 'Art moderne', 0),
(14, 'Nature morte', 0),
(15, 'Art abstrait', 0),
(16, 'Art urbain', 0),
(17, 'Aquarelle', 0),
(18, 'Digital art', 0),
(19, 'Sculpture', 0),
(20, 'Art contemporain', 0);

-- Insertion des Competiteurs
INSERT INTO Competiteur VALUES
(21, '2023-01-15', 0),
(22, '2023-02-01', 0),
(23, '2023-01-20', 0),
(24, '2023-01-21', 0),
(25, '2023-01-22', 0),
(26, '2023-01-23', 0),
(27, '2023-01-24', 0),
(28, '2023-01-25', 0),
(29, '2023-01-26', 0),
(30, '2023-01-27', 0),
(31, '2023-01-28', 0),
(32, '2023-01-29', 0),
(33, '2023-01-30', 0),
(34, '2023-02-01', 0),
(35, '2023-02-02', 0),
(36, '2023-02-03', 0),
(37, '2023-02-04', 0),
(38, '2023-02-05', 0),
(39, '2023-02-06', 0),
(40, '2023-02-07', 0),
(41, '2023-02-08', 0),
(42, '2023-02-09', 0),
(43, '2023-02-10', 0),
(44, '2023-02-11', 0),
(45, '2023-02-12', 0),
(46, '2023-02-13', 0),
(47, '2023-02-14', 0),
(48, '2023-02-15', 0),
(49, '2023-02-16', 0),
(50, '2023-03-15', 0);

-- Insertion des Concours (1 par saison)
INSERT INTO Concours VALUES
(1, 1, 'Nature Printanière', 'Printemps', '2024-03-20', '2024-06-20', 'en cours'),
(2, 2, 'Chaleurs Estivales', 'Été', '2024-06-21', '2024-09-21', 'pas commencé'),
(3, 3, 'Couleurs d\'Automne', 'Automne', '2024-09-22', '2024-12-21', 'pas commencé'),
(4, 1, 'Paysages d\'Hiver', 'Hiver', '2024-12-22', '2025-03-19', 'pas commencé');

-- Insertion des participations des clubs aux concours (minimum 6 clubs par concours)
INSERT INTO ParticipeClub VALUES
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1),
(1, 2), (2, 2), (3, 2), (4, 2), (5, 2), (6, 2),
(2, 3), (3, 3), (4, 3), (5, 3), (6, 3), (7, 3), (8, 3),
(1, 4), (3, 4), (4, 4), (5, 4), (6, 4), (8, 4);

-- Insertion des jurys (2 évaluateurs par concours)
INSERT INTO Jury VALUES
(11, 1), (12, 1),
(13, 2), (14, 2),
(15, 3), (16, 3),
(17, 4), (18, 4);

-- Insertion des participations des compétiteurs
INSERT INTO ParticipeCompetiteur VALUES
(21, 1), (22, 1), (23, 1), (24, 1), (25, 1), (26, 1),
(27, 2), (28, 2), (29, 2), (30, 2), (31, 2), (32, 2),
(33, 3), (34, 3), (35, 3), (36, 3), (37, 3), (38, 3),
(39, 4), (40, 4), (41, 4), (42, 4), (43, 4), (44, 4);

-- Insertion initiale de quelques dessins
INSERT INTO Dessin (numDessin, numConcours, numCompetiteur, commentaire, dateRemise) VALUES
(1, 1, 21, 'Paysage printanier', '2024-03-25'),
(2, 1, 22, 'Fleurs sauvages', '2024-03-26'),
(3, 1, 23, 'Oiseaux migrateurs', '2024-03-27');

-- Insertion des évaluations
INSERT INTO Evaluation VALUES
(1, 11, '2024-03-30', 8.5, 'Belle composition'),
(1, 12, '2024-03-31', 9.0, 'Excellent travail des couleurs'),
(2, 11, '2024-03-30', 7.5, 'Bonne technique'),
(2, 12, '2024-03-31', 8.0, 'Perspective intéressante');
