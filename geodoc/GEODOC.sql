-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  jeu. 27 déc. 2018 à 11:00
-- Version du serveur :  10.1.30-MariaDB
-- Version de PHP :  7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `GEODOC`
--
CREATE DATABASE IF NOT EXISTS `geodoc` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `geodoc`;

-- --------------------------------------------------------

--
-- Structure de la table `CLIENT`
--

CREATE TABLE IF NOT EXISTS `client` (
  `cliID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliNom` char(50) NOT NULL,
  `cliPrenom` char(50) NOT NULL,
  `cliMail` char(50) NOT NULL,
  `cliTel` int(10) UNSIGNED NOT NULL,
  `cliLogin` char(50) NOT NULL,
  `cliMDP` char(50) NOT NULL,
  `cliNumSecu` char(50) NOT NULL,
  `cliLocID` int(11) NOT NULL,
  PRIMARY KEY (`cliID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `CLIENT`
--

INSERT INTO `client` (`cliID`, `cliNom`, `cliPrenom`, `cliMail`, `cliTel`, `cliLogin`, `cliMDP`, `cliNumSecu`, `cliLocID`) VALUES
(1, 'Borde', 'Corentin', 'corentin.borde@edu.univ-fcomte.fr', 770451299, 'cborde', '1697918c7f9551712f531143df2f8a37', '198082538825272', 0);

-- --------------------------------------------------------

--
-- Structure de la table `CRENEAU`
--

CREATE TABLE IF NOT EXISTS `creneau` (
  `creMedID` int(11) UNSIGNED NOT NULL ,
  `creJour` int(2) NOT NULL,
  `creDebut` time NOT NULL,
  `creFin` time NOT NULL,
  PRIMARY KEY (`creMedID`, `creJour`, `creDebut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `LOCALITE`
--

CREATE TABLE IF NOT EXISTS `localite` (
  `localiteID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `locNumero` int(4) UNSIGNED NOT NULL,
  `locRue` char(100) NOT NULL,
  `locCP` int(5) UNSIGNED NOT NULL,
  `locVille` char(50) NOT NULL,
  `locPays` char(50) NOT NULL,
  `locComplement` char(50) DEFAULT NULL,
  PRIMARY KEY (`localiteID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `LOCALITE`
--

INSERT INTO `localite` (`localiteID`, `locNumero`, `locRue`, `locCP`, `locVille`, `locPays`, `locComplement`) VALUES
(1, 4, 'Quai Henri Bugnet', 25000, 'Besançon', 'France', ''),
(2, 2, 'Rue des Lilas', 25000, 'Besançon', 'France', 'Premier étage'),
(3, 12, 'Rue de Lorraine', 25000, 'Besançon', 'France', ''),
(4, 18, 'Chemin de Canot', 25000, 'Besançon', 'France', ''),
(5, 1, 'A Avenue Denfert Rochereau', 25000, 'Besançon', 'France', '1A'),
(6, 23, 'Rue de Vesoul', 25000, 'Besançon', 'France', ''),
(7, 9, 'Place des Lumières', 25000, 'Besançon', 'France', ''),
(8, 72, 'Rue des Granges', 25000, 'Besançon', 'France', '');

-- --------------------------------------------------------

--
-- Structure de la table `MEDECIN`
--

CREATE TABLE IF NOT EXISTS `medecin` (
  `medID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `medNom` char(50) NOT NULL,
  `medPrenom` char(50) NOT NULL,
  `medMail` char(50) NOT NULL,
  `medTel` int(10) UNSIGNED NOT NULL,
  `medLogin` char(50) NOT NULL,
  `medMDP` char(50) NOT NULL,
  `medNumRPPS` int(11) UNSIGNED NOT NULL,
  `medSpecialiteID` int(11) UNSIGNED NOT NULL,
  `medAccepteCB` tinyint(1) NOT NULL,
  `medAccepteTiersPayant` tinyint(1) NOT NULL,
  `medDureeConsultation` char(50) NOT NULL,
  `medLocID` int(10) NOT NULL,
  PRIMARY KEY (`medID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `MEDECIN`
--

INSERT INTO `medecin` (`medID`, `medNom`, `medPrenom`, `medMail`, `medTel`, `medLogin`, `medMDP`, `medNumRPPS`, `medSpecialiteID`, `medAccepteCB`, `medAccepteTiersPayant`, `medDureeConsultation`, `medLocID`) VALUES
(1, 'Harel', 'Matthieu', 'drharel@dr.fr', 381000000, 'harel', '107ec2d54d384e24cf310c78b9386ecd', 123456789, 1, 1, 1, '00:20:00', 1),
(2, 'Braud', 'Anne', 'drbraud@dr.fr', 381000001, 'braud', '1697918c7f9551712f531143df2f8a37', 123456788, 1, 1, 1, '00:25:00', 2),
(3, 'Maignien', 'Jean-Pierre', 'drmaignien@dr.fr', 381000002, 'maignien', '1697918c7f9551712f531143df2f8a37', 123456787, 2, 1, 1, '00:50:00', 3),
(4, 'Rame', 'Jean-Marc', 'drrame@dr.fr', 381000003, 'rame', '1697918c7f9551712f531143df2f8a37', 123456786, 3, 1, 0, '00:30:00', 4),
(5, 'Ernest', 'Sandrine', 'drernest@dr.fr', 3810000004, 'ernest', '1697918c7f9551712f531143df2f8a37', 123456785, 4, 0, 0, '00:20:00', 5),
(6, 'Vibratte', 'Françoise', 'drvibratte@dr.fr', 381000005, 'vibratte', '1697918c7f9551712f531143df2f8a37', 123456784, 5, 1, 0, '00:40:00', 6),
(7, 'Portha', 'Claudine', 'drportha@dr.fr', 381000006, 'portha', '1697918c7f9551712f531143df2f8a37', 123456783, 6, 1, 1, '01:20:00', 7),
(8, 'Abdi', 'Alain', 'drabdi@dr.dr', 381000007, 'abdi', '1697918c7f9551712f531143df2f8a37', 123456782, 7, 1, 0, '00:20:00', 8);

-- --------------------------------------------------------

--
-- Structure de la table `RDV`
--

CREATE TABLE IF NOT EXISTS `rdv` (
  `rdvID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdvCliID` int(11) UNSIGNED NOT NULL,
  `rdvMedID` int(11) UNSIGNED NOT NULL,
  `rdvHoraire` time NOT NULL,
  `rdvDate` date NOT NULL,
  `rdvJour` int(2) UNSIGNED NOT NULL,
  `rdvUrgent` tinyint(1) NOT NULL DEFAULT 0,
  `rdvDescription` char(200) DEFAULT NULL,
  `rdvIntitule` char(200) DEFAULT NULL,
  `rdvResume` char(200) DEFAULT NULL,
  `rdvPrix` int(10) DEFAULT NULL,
  `rdvHonore` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`rdvID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `rdv`
--

INSERT INTO `rdv` (`rdvID`, `rdvCliID`, `rdvMedID`, `rdvHoraire`, `rdvDate`, `rdvJour`, `rdvUrgent`, `rdvDescription`, `rdvIntitule`, `rdvResume`, `rdvPrix`, `rdvHonore`) VALUES
(1, 4, 6, '14:30:00', '2019-05-16', 4, 0, NULL, NULL, NULL, NULL, 1),
(2, 4, 2, '18:00:00', '2018-12-27', 4, 0, NULL, NULL, NULL, NULL, 1),
(3, 5, 6, '10:20:00', '2018-08-21', 2, 1, NULL, NULL, NULL, NULL, 1),
(4, 1, 3, '11:00:00', '2019-01-03', 4, 1, NULL, 'Traitement d’hémorroïdes', '', NULL, 1),
(5, 4, 1, '15:15:00', '2017-02-18', 6, 0, NULL, NULL, NULL, NULL, 1),
(6, 4, 6, '17:25:00', '2016-03-30', 3, 1, NULL, 'Traitement d’hémorroïdes', 'Ce fut fastidieux', NULL, 1),
(7, 5, 4, '14:30:00', '2019-01-05', 6, 1, NULL, NULL, NULL, NULL, 1),
(8, 1, 3, '13:30:00', '2016-12-25', 0, 0, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `SPECIALITE`
--

CREATE TABLE IF NOT EXISTS `specialite` (
  `speID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `speNom` char(50) NOT NULL,
  PRIMARY KEY (`speID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8; 

--
-- Déchargement des données de la table `SPECIALITE`
--

INSERT INTO `specialite` (`speID`, `speNom`) VALUES
(1, 'Generaliste'),
(2, 'Cardiologue'),
(3, 'Alergologue'),
(4, 'Ophtalmologue'),
(5, 'Dermatologue'),
(6, 'Neurologue'),
(7, 'Dentiste'),
(8, 'Pédiatre');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
