-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1
-- Généré le: Jeu 30 Mai 2013 à 00:08
-- Version du serveur: 5.5.25a-log
-- Version de PHP: 5.4.4

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--

-- --------------------------------------------------------

--
-- Structure de la table `accueil_paragraphes`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `accueil_paragraphes`;
CREATE TABLE IF NOT EXISTS `accueil_paragraphes` (
  `IdParagraphe` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Texte` longtext NOT NULL,
  PRIMARY KEY (`IdParagraphe`),
  UNIQUE KEY `IdParagraphe` (`IdParagraphe`),
  KEY `IdParagraphe_2` (`IdParagraphe`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Structure de la table `autorisations`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Lun 27 Mai 2013 à 21:19
--

DROP TABLE IF EXISTS `autorisations`;
CREATE TABLE IF NOT EXISTS `autorisations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(60) NOT NULL DEFAULT '',
  `Sensible` tinyint(1) unsigned DEFAULT '0',
  `MotDePasse` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `autorisations_blacklist`
--
-- Création: Mar 28 Mai 2013 à 20:30
--

DROP TABLE IF EXISTS `autorisations_blacklist`;
CREATE TABLE IF NOT EXISTS `autorisations_blacklist` (
  `ip` varchar(128) NOT NULL,
  `lastDate` datetime NOT NULL,
  `tries` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `ip` (`ip`),
  KEY `time` (`lastDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coordonnees`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
--

DROP TABLE IF EXISTS `coordonnees`;
CREATE TABLE IF NOT EXISTS `coordonnees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Adresse` varchar(255) DEFAULT NULL,
  `CodePostal` varchar(10) DEFAULT NULL,
  `Ville` varchar(200) DEFAULT NULL,
  `Pays` varchar(127) DEFAULT NULL,
  `Tel` varchar(25) DEFAULT NULL,
  `Fax` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- Structure de la table `discussions`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Mer 29 Mai 2013 à 19:35
-- Dernière vérification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `discussions`;
CREATE TABLE IF NOT EXISTS `discussions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `discussions_categories_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Sujet` varchar(255) NOT NULL DEFAULT '',
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `CreateDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sticky` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sticky` (`sticky`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=218 ;

--
-- RELATIONS POUR LA TABLE `discussions`:
--   `discussions_categories_id`
--       `discussions_categories` -> `id`
--   `personnes_id`
--       `personnes` -> `id`
--

-- --------------------------------------------------------

--
-- Structure de la table `discussions_categories`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `discussions_categories`;
CREATE TABLE IF NOT EXISTS `discussions_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categorie` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `discussions_messages`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
--

DROP TABLE IF EXISTS `discussions_messages`;
CREATE TABLE IF NOT EXISTS `discussions_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `discussions_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DateMessage` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Message` longtext,
  PRIMARY KEY (`id`),
  KEY `IdMessage` (`id`,`discussions_id`,`DateMessage`,`personnes_id`),
  KEY `discussions_id` (`discussions_id`,`DateMessage`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1941 ;

-- --------------------------------------------------------

--
-- Structure de la table `newsletters`
--
-- Création: Lun 27 Mai 2013 à 20:26
-- Dernière modification: Lun 27 Mai 2013 à 20:26
--

DROP TABLE IF EXISTS `newsletters`;
CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `personnes_id` bigint(11) unsigned NOT NULL DEFAULT '0',
  `content` longblob,
  `sentDate` datetime DEFAULT NULL,
  `readDate` datetime DEFAULT NULL,
  `error` text,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`sentDate`),
  KEY `personnes` (`personnes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `newsletters_images`
--
-- Création: Lun 27 Mai 2013 à 20:26
-- Dernière modification: Lun 27 Mai 2013 à 20:26
-- Dernière vérification: Lun 27 Mai 2013 à 20:26
--

DROP TABLE IF EXISTS `newsletters_images`;
CREATE TABLE IF NOT EXISTS `newsletters_images` (
  `hash` varchar(255) NOT NULL,
  `photos_id` bigint(20) NOT NULL,
  `newsletters_id` bigint(20) NOT NULL,
  PRIMARY KEY (`hash`,`newsletters_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `personnes`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
--

DROP TABLE IF EXISTS `personnes`;
CREATE TABLE IF NOT EXISTS `personnes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `IdParent1` bigint(20) unsigned DEFAULT NULL,
  `IdParent2` bigint(20) unsigned DEFAULT NULL,
  `Nom` varchar(50) NOT NULL DEFAULT '',
  `NomJF` varchar(50) DEFAULT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Sexe` char(1) NOT NULL DEFAULT 'M',
  `Email` varchar(70) DEFAULT NULL,
  `TelPortable` varchar(15) DEFAULT NULL,
  `DateNaissance` date DEFAULT NULL,
  `DateMort` date DEFAULT NULL,
  `DebutBranche` tinyint(1) unsigned DEFAULT '0',
  `DateSaisie` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `outOfFamily` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=208 ;

-- --------------------------------------------------------

--
-- Structure de la table `personnes_coordonnees`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
-- Dernière vérification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `personnes_coordonnees`;
CREATE TABLE IF NOT EXISTS `personnes_coordonnees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `coordonnees_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IdPersonneCoord_2` (`id`,`personnes_id`,`coordonnees_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=205 ;

-- --------------------------------------------------------

--
-- Structure de la table `personnes_liens_couple`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
--

DROP TABLE IF EXISTS `personnes_liens_couple`;
CREATE TABLE IF NOT EXISTS `personnes_liens_couple` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `IdPersonne1` bigint(20) unsigned NOT NULL DEFAULT '0',
  `IdPersonne2` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DateMariage` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qui1` (`IdPersonne1`),
  KEY `qui2` (`IdPersonne2`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Lun 27 Mai 2013 à 21:59
-- Dernière vérification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE IF NOT EXISTS `photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateCliche` date NOT NULL DEFAULT '0000-00-00',
  `Lieu` varchar(127) DEFAULT NULL,
  `Titre` varchar(200) NOT NULL DEFAULT '',
  `Commentaire` text,
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DateUpload` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `IdPhoto_2` (`id`,`DateCliche`,`DateUpload`,`personnes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2113 ;

-- --------------------------------------------------------

--
-- Structure de la table `photos_du_jour`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
--

DROP TABLE IF EXISTS `photos_du_jour`;
CREATE TABLE IF NOT EXISTS `photos_du_jour` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Jour` date NOT NULL DEFAULT '0000-00-00',
  `photos_id` bigint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotidien` (`Jour`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED AUTO_INCREMENT=2917 ;

-- --------------------------------------------------------

--
-- Structure de la table `photos_presences`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Jeu 23 Mai 2013 à 10:48
-- Dernière vérification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `photos_presences`;
CREATE TABLE IF NOT EXISTS `photos_presences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `photos_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Portrait` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IdPresence_2` (`id`,`photos_id`,`personnes_id`,`Portrait`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED AUTO_INCREMENT=3117 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_logs`
--
-- Création: Jeu 23 Mai 2013 à 10:20
-- Dernière modification: Mer 29 Mai 2013 à 21:32
-- Dernière vérification: Jeu 23 Mai 2013 à 10:20
--

DROP TABLE IF EXISTS `stats_logs`;
CREATE TABLE IF NOT EXISTS `stats_logs` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `personnes_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DateLog` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateDernier` datetime DEFAULT NULL,
  `DureePage` time NOT NULL DEFAULT '00:00:00',
  `NombrePages` int(10) unsigned NOT NULL DEFAULT '1',
  `SessionId` varchar(40) DEFAULT NULL,
  `Ip` varchar(15) NOT NULL DEFAULT '',
  `Hote` varchar(127) DEFAULT NULL,
  `IdOs` bigint(10) NOT NULL DEFAULT '0',
  `IdNavigateur` bigint(10) NOT NULL DEFAULT '0',
  `Resolution` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qui` (`personnes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35200 ;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
