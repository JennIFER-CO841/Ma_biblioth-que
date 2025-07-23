-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 23 juil. 2025 à 13:52
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ma_bibliotheque_vacances`
--
CREATE DATABASE IF NOT EXISTS `ma_bibliotheque_vacances` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `ma_bibliotheque_vacances`;

-- --------------------------------------------------------

--
-- Structure de la table `auteurs`
--

DROP TABLE IF EXISTS `auteurs`;
CREATE TABLE IF NOT EXISTS `auteurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `biographie` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tronquer la table avant d'insérer `auteurs`
--

TRUNCATE TABLE `auteurs`;
--
-- Déchargement des données de la table `auteurs`
--

INSERT INTO `auteurs` (`id`, `nom`, `prenom`, `date_naissance`, `biographie`) VALUES
(1, 'Doe', 'John', '1980-01-01', 'Biographie de John Doe'),
(2, 'Doe', 'Jane', '1990-01-01', 'Biographie de Jane Doe'),
(3, 'Orwell', 'George', NULL, NULL),
(5, 'Noah Harari', 'Yuval', NULL, NULL),
(8, 'Hobb', 'Robin', NULL, NULL),
(9, 'Piketty', 'Thomas', NULL, NULL),
(10, 'Bradbury', 'Ray', '1920-08-22', 'Écrivain américain de science-fiction, de fantasy et d\'horreur. Il est surtout connu pour son roman Fahrenheit 451 (1953), une dystopie sur une société où les livres sont interdits. Ses autres œuvres célèbres incluent Chroniques martiennes et L’Homme illustré. Ray Bradbury a marqué la littérature par son style poétique et son exploration des thèmes de la censure, de la technologie et de la condition humaine.'),
(11, 'Vian', 'Boris', NULL, NULL),
(12, 'Pullman', 'Philip', NULL, NULL),
(14, 'Haldeman', 'Joe', NULL, NULL),
(15, 'Tolle', 'Eckhart', NULL, NULL),
(16, 'L’Homme', 'Erik', NULL, NULL),
(17, 'Inconnu', '', NULL, 'Auteur générique'),
(18, 'Süskind', 'Patrick', NULL, NULL),
(19, 'Kaya', 'Mehmet', NULL, NULL),
(20, 'Rowling', 'Joanne', '1965-07-31', 'Auteure britannique connue pour la série Harry Potter, traduite en plus de 80 langues. Elle a révolutionné la littérature jeunesse et vendu des centaines de millions d’exemplaires.'),
(21, 'Paolini', 'Christopher', NULL, NULL),
(25, 'Rowling', 'J.K.', NULL, NULL),
(26, 'Tolkien', 'J.R.R.', NULL, NULL),
(27, 'Riordan', 'Rick', NULL, NULL),
(28, 'Crichton', 'Michael', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_categorie` (`nom_categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tronquer la table avant d'insérer `categories`
--

TRUNCATE TABLE `categories`;
--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom_categorie`) VALUES
(4, 'Fantasy'),
(1, 'Fiction'),
(2, 'Non-Fiction'),
(9, 'Poésie'),
(6, 'Roman'),
(3, 'Science Fiction'),
(8, 'Théâtre');

-- --------------------------------------------------------

--
-- Structure de la table `emprunts`
--

DROP TABLE IF EXISTS `emprunts`;
CREATE TABLE IF NOT EXISTS `emprunts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `livre_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `date_emprunt` date NOT NULL,
  `date_retour_prevue` date GENERATED ALWAYS AS ((`date_emprunt` + interval 28 day)) STORED,
  `date_retour_reelle` date DEFAULT NULL,
  `statut_emprunt` enum('actif','retourné','en retard') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `livre_id` (`livre_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tronquer la table avant d'insérer `emprunts`
--

TRUNCATE TABLE `emprunts`;
--
-- Déchargement des données de la table `emprunts`
--

INSERT INTO `emprunts` (`id`, `livre_id`, `utilisateur_id`, `date_emprunt`, `date_retour_reelle`, `statut_emprunt`) VALUES
(1, 1, 1, '2023-01-15', '2025-07-20', 'retourné'),
(2, 2, 2, '2023-01-20', NULL, 'retourné'),
(3, 4, 8, '2025-07-17', '2025-07-17', 'retourné'),
(4, 4, 8, '2025-07-17', '2025-07-17', 'retourné'),
(5, 4, 8, '2025-07-17', '2025-07-17', 'retourné'),
(6, 4, 8, '2025-07-17', '2025-07-20', 'retourné'),
(7, 4, 8, '2025-07-17', '2025-07-17', 'retourné'),
(8, 4, 8, '2025-07-17', '2025-07-17', 'retourné'),
(9, 1, 8, '2025-07-18', '2025-07-20', 'retourné'),
(10, 20, 12, '2025-07-18', '2025-07-20', 'retourné'),
(11, 10, 12, '2025-07-18', '2025-07-20', 'retourné'),
(12, 4, 8, '2025-07-20', '2025-07-20', 'retourné'),
(13, 12, 8, '2025-07-20', NULL, 'actif'),
(14, 19, 8, '2025-07-20', NULL, 'actif'),
(15, 23, 8, '2025-07-23', '2025-07-23', 'retourné'),
(16, 11, 8, '2025-07-23', '2025-07-23', 'retourné'),
(17, 11, 8, '2025-07-23', '2025-07-23', 'retourné'),
(18, 4, 8, '2025-07-23', '2025-07-23', 'retourné'),
(19, 4, 8, '2025-07-23', '2025-07-23', 'retourné'),
(20, 10, 8, '2025-07-23', NULL, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `livres`
--

DROP TABLE IF EXISTS `livres`;
CREATE TABLE IF NOT EXISTS `livres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `isbn` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `annee_publication` year NOT NULL,
  `resume` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `nombre_exemplaires_total` int NOT NULL,
  `nombre_exemplaires_disponibles` int NOT NULL,
  `auteur_id` int DEFAULT NULL,
  `categorie_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `nombre_exemplaires` int NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `fk_auteur` (`auteur_id`),
  KEY `fk_categorie` (`categorie_id`),
  KEY `fk_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tronquer la table avant d'insérer `livres`
--

TRUNCATE TABLE `livres`;
--
-- Déchargement des données de la table `livres`
--

INSERT INTO `livres` (`id`, `titre`, `isbn`, `annee_publication`, `resume`, `nombre_exemplaires_total`, `nombre_exemplaires_disponibles`, `auteur_id`, `categorie_id`, `utilisateur_id`, `nombre_exemplaires`, `description`) VALUES
(1, 'Dune', '9780441013593', '1965', 'Un récit épique de science-fiction sur la planète Arrakis.', 5, 6, 1, 1, NULL, 0, NULL),
(2, 'Sapiens: A Brief History of Humankind', '9780062316097', '2011', 'Une exploration de l’histoire de l’humanité.', 3, 3, 2, 2, NULL, 0, NULL),
(4, '1984', '9780451524935', '1949', 'Dans un monde totalitaire dirigé par Big Brother, Winston Smith tente de résister à la manipulation de la vérité et à la surveillance constante du Parti.', 7, 7, 3, 3, 5, 0, NULL),
(6, 'Sapiens : Une brève histoire de l’humanité', '9782070469486', '2011', 'Yuval Noah Harari retrace l’histoire de l’espèce humaine, depuis l’âge de pierre jusqu’à l’ère technologique, en mettant en lumière les grandes révolutions culturelles, sociales et scientifiques.', 9, 9, 5, 2, 5, 0, NULL),
(7, 'Le Grand Meaulnes', '9782070360202', '1913', 'Dans un internat rural, l’arrivée mystérieuse d’Augustin Meaulnes bouleverse la vie du jeune François. Un roman d’aventures initiatiques et de rêves brisés.', 8, 8, 17, 1, 5, 0, NULL),
(8, 'Neuromancien', '9782070415735', '1984', 'Case, un hacker déchu, est recruté pour une mission d\'infiltration dans le cyberespace. Ce roman fondateur du cyberpunk explore l’intelligence artificielle, la réalité virtuelle et les mégacorporations.', 6, 6, 17, 3, 5, 0, NULL),
(9, 'L\'Assassin Royal – L\'Apprenti assassin', '9782266120859', '1995', 'Fitz, un bâtard royal, est initié à l\'art de l’assassinat pour servir la couronne. Il découvre un monde dangereux fait de magie, de trahisons et de choix déchirants.', 11, 11, 8, 4, 5, 0, NULL),
(10, 'Le Capital au XXIe siècle', '9782021082289', '2013', 'L’économiste Thomas Piketty analyse les inégalités économiques sur trois siècles, en montrant l’impact du capital, des héritages et des politiques fiscales.', 7, 6, 9, 2, 5, 0, NULL),
(11, 'Fahrenheit 451', '9782070368222', '1953', 'Dans un futur où les livres sont interdits, Montag est pompier chargé de les brûler. Une rencontre va éveiller sa conscience et bouleverser sa vision du monde.', 10, 10, 10, 3, 5, 0, NULL),
(12, 'L’Écume des jours', '9782070363692', '1947', 'Colin, jeune homme amoureux, vit dans un monde poétique et absurde. L’amour qu’il porte à Chloé se heurte à la maladie, dans un univers qui se dégrade au fil du récit.', 5, 4, 11, 1, 5, 0, NULL),
(15, 'La Guerre éternelle', '9782290351297', '1974', 'Soldat dans une guerre interstellaire contre une espèce alien, William Mandella revient sur Terre après chaque mission, découvrant un monde radicalement transformé par la relativité.', 6, 6, 14, 3, 5, 0, NULL),
(16, 'Le Pouvoir du moment présent', '9782911729330', '1997', 'Un guide spirituel moderne qui invite à vivre pleinement l’instant présent pour se libérer de l’ego, du stress et des souffrances mentales.', 10, 10, 15, 2, 5, 0, NULL),
(17, 'Le Livre des Étoiles – Qadehar le Sorcier', '9782070514391', '2001', 'Guillemot, jeune garçon de 12 ans, découvre qu’il a un don pour la magie. Il est entraîné dans un monde parallèle où il devra affronter des forces obscures.', 9, 9, 16, 4, 5, 0, NULL),
(18, 'Le Parfum', '9782070409307', '1985', 'Jean-Baptiste Grenouille naît avec un odorat hors du commun. Obsédé par la création du parfum parfait, il est prêt à tout pour capturer la fragrance absolue.', 10, 10, 18, 1, 8, 0, NULL),
(19, 'Kuruluş Osman – Le roman de la fondation', '9780000000001', '2023', 'L’histoire romancée d’Osman Bey, le guerrier visionnaire qui défia les Byzantins et les Mongols pour poser les bases d’un empire qui changera l’histoire. Inspiré de la série turque à succès.', 10, 9, 19, 1, 9, 0, NULL),
(20, 'Tétralogie de l’Héritage – L’Aîné', '9780552552103', '2005', 'Suite d’Eragon, ce tome suit la formation de plus en plus difficile du jeune dragonnier face à l’armée de Galbatorix. Loyautés, secrets et batailles s’entrelacent.', 12, 12, 21, 4, 5, 0, NULL),
(21, 'Harry Potter à l\'école des sorciers', '9780747532699', '1997', 'Premier tome de la saga Harry Potter, où un jeune sorcier découvre ses pouvoirs et entre à Poudlard.', 50, 50, 25, 4, 5, 0, NULL),
(22, 'Le Seigneur des anneaux : La Communauté de l\'anneau', '9780261103573', '1954', 'Frodo hérite de l’Anneau Unique et entame un périple pour le détruire afin de sauver la Terre du Milieu.', 40, 40, 26, 4, 5, 0, NULL),
(23, 'Percy Jackson : Le Voleur de foudre', '9780786838653', '2005', 'Percy Jackson découvre qu’il est le fils de Poséidon et se lance dans une quête pour empêcher une guerre entre dieux.', 29, 29, 27, 4, 5, 0, NULL),
(24, 'Jurassic Park', '978-226613506', '1990', 'Des scientifiques recréent des dinosaures sur une île, avec des conséquences catastrophiques.', 50, 50, 28, 3, 5, 0, NULL),
(25, 'Harry Potter et la Chambre des secrets', '978-207064303', '1998', 'Une mystérieuse menace met en danger les élèves de Poudlard.', 54, 54, 25, 4, 5, 0, NULL),
(26, 'Harry Potter et le Prisonnier d’Azkaban', '978-207064304', '1999', 'Harry découvre la vérité sur Sirius Black et son passé.', 59, 59, 25, 4, 5, 0, NULL),
(27, 'Harry Potter et la Coupe de feu', '978-207064305', '2000', 'Harry est sélectionné pour participer au Tournoi des Trois Sorciers.', 62, 62, 25, 4, 5, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('lecteur','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'lecteur',
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_utilisateur` (`nom_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tronquer la table avant d'insérer `utilisateurs`
--

TRUNCATE TABLE `utilisateurs`;
--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom_utilisateur`, `email`, `mot_de_passe`, `role`, `date_inscription`) VALUES
(1, 'jennifer', 'jennifer@example.com', 'e3cd9f6469fc3e1acfb9f2bdbfc5a3d2bbb8e2ad', 'admin', '2025-07-10 13:42:49'),
(2, 'sofia', 'sofia@example.com', 'bb4cc6ab038155f5c550175a090fbb3da5c9b762', 'lecteur', '2025-07-10 13:42:49'),
(3, 'admin', 'admin@hotmail.com', '$2y$10$ahmoHt4CNBa6wfw1Ob671.nzFr/mNbIoExrf4y9yTsyrcXqeYiaX.', 'lecteur', '2025-07-14 13:08:55'),
(5, 'elisa', 'elisa@hotmail.com', '$2y$10$gto17DacTkC8y2ejJGmSoOGatmR726h2IFDZZelkN0qckioYpQQ4i', 'admin', '2025-07-14 15:22:34'),
(7, 'jenni', 'jenni@hotmail.com', '$2y$10$jUrVXyaOrGPz6Xnk0Eqnie6Z30WThJoouwSstPlvK7udBynaxWJnW', 'lecteur', '2025-07-14 15:31:05'),
(8, 'alia', 'alia@hotmail.com', '$2y$10$DuA5YLrND7umf.WRTn/5Ju4qMU07XoUMpAh/9QpGgdFo.HdX3NUTK', 'lecteur', '2025-07-15 11:55:54'),
(9, 'Rabia Bala hatun', 'rabia@hotmail.com', '$2y$10$VlrhbSH4qdSQO7hfCbxKZ.lMm3X315m70ZwDrzL9qRkEKvyjNce0.', 'admin', '2025-07-17 13:53:06'),
(12, 'Inconnu', 'inconnu@example.com', '', 'lecteur', '2025-07-19 13:29:32');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `emprunts`
--
ALTER TABLE `emprunts`
  ADD CONSTRAINT `emprunts_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`),
  ADD CONSTRAINT `emprunts_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
