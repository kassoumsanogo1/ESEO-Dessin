-- Création des tables

DROP TABLE IF EXISTS Evaluation;
DROP TABLE IF EXISTS Dessin;
DROP TABLE IF EXISTS ParticipeCompetiteur;
DROP TABLE IF EXISTS ParticipeClub;
DROP TABLE IF EXISTS Jury;
DROP TABLE IF EXISTS Concours;
DROP TABLE IF EXISTS Competiteur;
DROP TABLE IF EXISTS Evaluateur;
DROP TABLE IF EXISTS President;
DROP TABLE IF EXISTS Directeur;
DROP TABLE IF EXISTS Administrateur;
DROP TABLE IF EXISTS Utilisateur;
DROP TABLE IF EXISTS Club;



CREATE TABLE Club (
    numClub INT PRIMARY KEY,
    nomClub VARCHAR(100) NOT NULL,
    adresse VARCHAR(200),
    numTelephone VARCHAR(15),
    nombreAdherents INT,
    ville VARCHAR(100),
    departement VARCHAR(100),
    region VARCHAR(100)
);

CREATE TABLE Utilisateur (
    numUtilisateur INT PRIMARY KEY,
    numClub INT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    adresse VARCHAR(200),
    login VARCHAR(50) UNIQUE NOT NULL,
    motDePasse VARCHAR(100) NOT NULL,
    age INT,
    FOREIGN KEY (numClub) REFERENCES Club(numClub)
);

CREATE TABLE President (
    numPresident INT PRIMARY KEY,
    prime DECIMAL(10,2),
    FOREIGN KEY (numPresident) REFERENCES Utilisateur(numUtilisateur)
);

CREATE TABLE Administrateur (
    numAdministrateur INT PRIMARY KEY,
    dateDebut DATE,
    FOREIGN KEY (numAdministrateur) REFERENCES Utilisateur(numUtilisateur)
);

CREATE TABLE Directeur (
    numDirecteur INT,
    numClub INT,
    dateDebut DATE,
    PRIMARY KEY (numDirecteur),
    FOREIGN KEY (numDirecteur) REFERENCES Utilisateur(numUtilisateur),
    FOREIGN KEY (numClub) REFERENCES Club(numClub)
);

CREATE TABLE Evaluateur (
    numEvaluateur INT PRIMARY KEY,
    specialite VARCHAR(100),
    nbDessinsEvalues INT DEFAULT 0,
    FOREIGN KEY (numEvaluateur) REFERENCES Utilisateur(numUtilisateur)
);

CREATE TABLE Concours (
    numConcours INT PRIMARY KEY,
    numPresident INT,
    theme VARCHAR(200),
    saison VARCHAR(50),
    dateDebut DATE,
    dateFin DATE,
    etat ENUM('pas commencé', 'en cours', 'attente', 'résultat', 'évalué'),
    FOREIGN KEY (numPresident) REFERENCES Utilisateur(numUtilisateur)
);

CREATE TABLE Competiteur (
    numCompetiteur INT PRIMARY KEY,
    datePremiereParticipation DATE,
    nbDessinSoumis INT DEFAULT 0,
    FOREIGN KEY (numCompetiteur) REFERENCES Utilisateur(numUtilisateur)
);

CREATE TABLE ParticipeCompetiteur (
    numCompetiteur INT,
    numConcours INT,
    PRIMARY KEY (numCompetiteur, numConcours),
    FOREIGN KEY (numCompetiteur) REFERENCES Competiteur(numCompetiteur),
    FOREIGN KEY (numConcours) REFERENCES Concours(numConcours)
);

CREATE TABLE Dessin (
    numDessin INT PRIMARY KEY,
    numConcours INT,
    numCompetiteur INT,
    commentaire TEXT,
    classement INT,
    dateRemise DATE,
    leDessin BLOB,
    FOREIGN KEY (numConcours) REFERENCES Concours(numConcours),
    FOREIGN KEY (numCompetiteur) REFERENCES Competiteur(numCompetiteur)
);

CREATE TABLE Evaluation (
    numDessin INT,
    numEvaluateur INT,
    dateEvaluation DATE,
    note DECIMAL(4,2),
    commentaire TEXT,
    PRIMARY KEY (numDessin, numEvaluateur),
    FOREIGN KEY (numDessin) REFERENCES Dessin(numDessin),
    FOREIGN KEY (numEvaluateur) REFERENCES Evaluateur(numEvaluateur)
);

CREATE TABLE ParticipeClub (
    numClub INT,
    numConcours INT,
    PRIMARY KEY (numClub, numConcours),
    FOREIGN KEY (numClub) REFERENCES Club(numClub),
    FOREIGN KEY (numConcours) REFERENCES Concours(numConcours)
);

CREATE TABLE Jury (
    numEvaluateur INT,
    numConcours INT,
    PRIMARY KEY (numEvaluateur, numConcours),
    FOREIGN KEY (numEvaluateur) REFERENCES Evaluateur(numEvaluateur),
    FOREIGN KEY (numConcours) REFERENCES Concours(numConcours)
);


