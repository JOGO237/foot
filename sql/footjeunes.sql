-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 28 juil. 2025 à 23:09
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `football_cameroun`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin','moderateur') DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `nom`, `prenom`, `role`, `last_login`, `created_at`) VALUES
(2, 'Joresse', '$2y$10$LD6ZOxrfF/AG7.b.fMdehONgV31Aikm0MCz3DcB0ShQ9TSN/V4BG2', 'jorestchouameni@gmail.com', 'Foundie', 'tchouameni', 'super_admin', '2025-07-28 20:54:18', '2025-07-28 15:14:44');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `age_min` int(11) NOT NULL,
  `age_max` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `age_min`, `age_max`, `description`) VALUES
(1, 'U-13', 11, 13, 'Catégorie moins de 13 ans'),
(2, 'U-15', 13, 15, 'Catégorie moins de 15 ans'),
(3, 'U-17', 15, 17, 'Catégorie moins de 17 ans'),
(4, 'U-19', 17, 19, 'Catégorie moins de 19 ans');

-- --------------------------------------------------------

--
-- Structure de la table `classements`
--

CREATE TABLE `classements` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `saison_id` int(11) NOT NULL,
  `matchs_joues` int(11) DEFAULT 0,
  `victoires` int(11) DEFAULT 0,
  `nuls` int(11) DEFAULT 0,
  `defaites` int(11) DEFAULT 0,
  `buts_pour` int(11) DEFAULT 0,
  `buts_contre` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `departement_id` int(11) NOT NULL,
  `date_creation` date DEFAULT NULL,
  `president` varchar(100) DEFAULT NULL,
  `entraineur_principal` varchar(100) DEFAULT NULL,
  `stade` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `region_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id`, `nom`, `region_id`, `created_at`) VALUES
(1, 'Djérem', 1, '2025-07-28 16:15:30'),
(2, 'Faro-et-Déo', 1, '2025-07-28 16:15:30'),
(3, 'Mayo-Banyo', 1, '2025-07-28 16:15:30'),
(4, 'Mbéré', 1, '2025-07-28 16:15:30'),
(5, 'Vina', 1, '2025-07-28 16:15:30'),
(6, 'Haute-Sanaga', 2, '2025-07-28 16:15:30'),
(7, 'Lekié', 2, '2025-07-28 16:15:30'),
(8, 'Mbam-et-Inoubou', 2, '2025-07-28 16:15:30'),
(9, 'Mbam-et-Kim', 2, '2025-07-28 16:15:30'),
(10, 'Méfou-et-Afamba', 2, '2025-07-28 16:15:30'),
(11, 'Méfou-et-Akono', 2, '2025-07-28 16:15:30'),
(12, 'Mfoundi', 2, '2025-07-28 16:15:30'),
(13, 'Nyong-et-Kéllé', 2, '2025-07-28 16:15:30'),
(14, 'Nyong-et-Mfoumou', 2, '2025-07-28 16:15:30'),
(15, 'Nyong-et-So\'o', 2, '2025-07-28 16:15:30'),
(16, 'Boumba-et-Ngoko', 3, '2025-07-28 16:15:30'),
(17, 'Haut-Nyong', 3, '2025-07-28 16:15:30'),
(18, 'Kadey', 3, '2025-07-28 16:15:30'),
(19, 'Lom-et-Djérem', 3, '2025-07-28 16:15:30'),
(20, 'Diamaré', 4, '2025-07-28 16:15:30'),
(21, 'Logone-et-Chari', 4, '2025-07-28 16:15:30'),
(22, 'Mayo-Danay', 4, '2025-07-28 16:15:30'),
(23, 'Mayo-Kani', 4, '2025-07-28 16:15:30'),
(24, 'Mayo-Sava', 4, '2025-07-28 16:15:30'),
(25, 'Mayo-Tsanaga', 4, '2025-07-28 16:15:30'),
(26, 'Moungo', 5, '2025-07-28 16:15:30'),
(27, 'Nkam', 5, '2025-07-28 16:15:30'),
(28, 'Sanaga-Maritime', 5, '2025-07-28 16:15:30'),
(29, 'Wouri', 5, '2025-07-28 16:15:30'),
(30, 'Bénoué', 6, '2025-07-28 16:15:30'),
(31, 'Faro', 6, '2025-07-28 16:15:30'),
(32, 'Mayo-Louti', 6, '2025-07-28 16:15:30'),
(33, 'Mayo-Rey', 6, '2025-07-28 16:15:30'),
(34, 'Boyo', 7, '2025-07-28 16:15:30'),
(35, 'Bui', 7, '2025-07-28 16:15:30'),
(36, 'Donga-Mantung', 7, '2025-07-28 16:15:30'),
(37, 'Menchum', 7, '2025-07-28 16:15:30'),
(38, 'Mezam', 7, '2025-07-28 16:15:30'),
(39, 'Momo', 7, '2025-07-28 16:15:30'),
(40, 'Ngo-Ketunjia', 7, '2025-07-28 16:15:30'),
(41, 'Bamboutos', 8, '2025-07-28 16:15:30'),
(42, 'Haut-Nkam', 8, '2025-07-28 16:15:30'),
(43, 'Hauts-Plateaux', 8, '2025-07-28 16:15:30'),
(44, 'Koung-Khi', 8, '2025-07-28 16:15:30'),
(45, 'Menoua', 8, '2025-07-28 16:15:30'),
(46, 'Mifi', 8, '2025-07-28 16:15:30'),
(47, 'Mino', 8, '2025-07-28 16:15:30'),
(48, 'Ndé', 8, '2025-07-28 16:15:30'),
(49, 'Noun', 8, '2025-07-28 16:15:30'),
(50, 'Dja-et-Lobo', 9, '2025-07-28 16:15:30'),
(51, 'Mvila', 9, '2025-07-28 16:15:30'),
(52, 'Océan', 9, '2025-07-28 16:15:30'),
(53, 'Vallée-du-Ntem', 9, '2025-07-28 16:15:30'),
(54, 'Fako', 10, '2025-07-28 16:15:30'),
(55, 'Koupé-Manengouba', 10, '2025-07-28 16:15:30'),
(56, 'Lebialem', 10, '2025-07-28 16:15:30'),
(57, 'Manyu', 10, '2025-07-28 16:15:30'),
(58, 'Meme', 10, '2025-07-28 16:15:30'),
(59, 'Ndian', 10, '2025-07-28 16:15:30');

-- --------------------------------------------------------

--
-- Structure de la table `entraineurs`
--

CREATE TABLE `entraineurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `licence_numero` varchar(50) DEFAULT NULL,
  `club_id` int(11) NOT NULL,
  `poste` enum('principal','adjoint','gardien','physique') DEFAULT 'principal',
  `date_debut` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueurs`
--

CREATE TABLE `joueurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `poste` varchar(50) DEFAULT NULL,
  `numero_maillot` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `club_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `taille` decimal(3,2) DEFAULT NULL,
  `poids` decimal(5,2) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `nom_parent` varchar(100) DEFAULT NULL,
  `telephone_parent` varchar(20) DEFAULT NULL,
  `licence_numero` varchar(50) DEFAULT NULL,
  `statut` enum('actif','inactif','blessé','suspendu') DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `matchs`
--

CREATE TABLE `matchs` (
  `id` int(11) NOT NULL,
  `club_domicile_id` int(11) NOT NULL,
  `club_exterieur_id` int(11) NOT NULL,
  `date_match` datetime NOT NULL,
  `stade` varchar(100) DEFAULT NULL,
  `categorie_id` int(11) NOT NULL,
  `saison_id` int(11) NOT NULL,
  `score_domicile` int(11) DEFAULT NULL,
  `score_exterieur` int(11) DEFAULT NULL,
  `statut` enum('programmé','en_cours','terminé','reporté','annulé') DEFAULT 'programmé',
  `arbitre` varchar(100) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `regions`
--

INSERT INTO `regions` (`id`, `nom`, `code`, `created_at`) VALUES
(1, 'Adamaoua', 'AD', '2025-07-28 16:15:30'),
(2, 'Centre', 'CE', '2025-07-28 16:15:30'),
(3, 'Est', 'ES', '2025-07-28 16:15:30'),
(4, 'Extrême-Nord', 'EN', '2025-07-28 16:15:30'),
(5, 'Littoral', 'LT', '2025-07-28 16:15:30'),
(6, 'Nord', 'ND', '2025-07-28 16:15:30'),
(7, 'Nord-Ouest', 'NO', '2025-07-28 16:15:30'),
(8, 'Ouest', 'OU', '2025-07-28 16:15:30'),
(9, 'Sud', 'SU', '2025-07-28 16:15:30'),
(10, 'Sud-Ouest', 'SO', '2025-07-28 16:15:30');

-- --------------------------------------------------------

--
-- Structure de la table `saisons`
--

CREATE TABLE `saisons` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` enum('active','terminee','à_venir') DEFAULT 'à_venir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `saisons`
--

INSERT INTO `saisons` (`id`, `nom`, `date_debut`, `date_fin`, `statut`) VALUES
(1, 'Saison 2024', '2024-01-15', '2024-12-15', 'active');

-- --------------------------------------------------------

--
-- Structure de la table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `type` enum('sponsor','partenaire','investisseur') NOT NULL,
  `description` text DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sponsors`
--

INSERT INTO `sponsors` (`id`, `nom`, `logo`, `type`, `description`, `site_web`, `contact`, `telephone`, `email`, `statut`, `created_at`) VALUES
(1, 'fecafoot', '68864ae4f07c0.jpeg', 'partenaire', 'fédération camerounaise de football', 'https://fecafoot-officiel.com', '+237 222 20 19 28', '+237 222 20 19 28', 'contact@fecafoot.org', 'actif', '2025-07-27 15:51:00'),
(2, 'guinness super league', '68864bb3250bf.jpeg', 'partenaire', '', 'https://fecafoot-officiel.com', '', '', '', 'actif', '2025-07-27 15:54:27'),
(3, 'Mtn elite one', '68864cac6b37e.png', 'partenaire', '', 'https://fecafoot-officiel.com', '', '', '', 'actif', '2025-07-27 15:58:36'),
(4, 'CTFP', '68864d264be48.png', 'partenaire', '', 'https://fecafoot-officiel.com', '', '', '', 'actif', '2025-07-27 16:00:38'),
(5, 'LFFC', '68864d87e926b.jpeg', 'partenaire', '', 'https://fecafoot-officiel.com', '', '', '', 'actif', '2025-07-27 16:02:15'),
(6, '1xbet', '68865d7f25fae.gif', 'sponsor', '', 'https://1xbet.cm?bf=60521fc2adf3a_2026746577', '', '', '', 'actif', '2025-07-27 17:10:23'),
(7, 'Mtn cameroun', '68865f68f0d47.png', 'sponsor', '', 'https://mtn.cm/', '', '', '', 'actif', '2025-07-27 17:18:32'),
(8, 'tiof cameoun', '68865fe60727c.jpeg', 'sponsor', '', 'https://maligah.com/', '', '', '', 'actif', '2025-07-27 17:20:38'),
(9, 'fourteen', '68866193a611e.png', 'sponsor', '', 'https://14fourteen.com/', '', '', 'contact@fourteen.com', 'actif', '2025-07-27 17:27:47'),
(10, 'camtel cameroun', '688665df1f456.png', 'sponsor', '', 'https://camtel.cm', '', '', 'carrier@camtel.cm', 'actif', '2025-07-27 17:46:07'),
(11, 'orange', '688669368c65d.png', 'investisseur', '', 'https://www.orange.cm', '', '', '', 'actif', '2025-07-27 18:00:22'),
(12, 'sodecoton', '688669c7be494.png', 'investisseur', '', 'https://sodecoton.cm/', '', '', '', 'actif', '2025-07-27 18:02:47'),
(13, 'fifa', '68866a79b0f3d.jpeg', 'investisseur', '', 'https://www.fifa.com/', '', '', '', 'actif', '2025-07-27 18:05:45'),
(14, 'top', '68866aefbb80b.jpeg', 'investisseur', '', 'https://www.topcameroun.cm', '', '', '', 'actif', '2025-07-27 18:07:43'),
(15, 'Ksa', '68866b2000bd0.jpeg', 'investisseur', '', 'https://www.ksa-cameroun.cm', '', '', '', 'actif', '2025-07-27 18:08:32');

-- --------------------------------------------------------

--
-- Structure de la table `stats_joueurs`
--

CREATE TABLE `stats_joueurs` (
  `id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `buts` int(11) DEFAULT 0,
  `passes_decisives` int(11) DEFAULT 0,
  `cartons_jaunes` int(11) DEFAULT 0,
  `cartons_rouges` int(11) DEFAULT 0,
  `minutes_jouees` int(11) DEFAULT 0,
  `titulaire` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `trash_items`
--

CREATE TABLE `trash_items` (
  `id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`item_data`)),
  `deleted_by` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `classements`
--
ALTER TABLE `classements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_classement` (`club_id`,`categorie_id`,`saison_id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `saison_id` (`saison_id`);

--
-- Index pour la table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`);

--
-- Index pour la table `entraineurs`
--
ALTER TABLE `entraineurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `licence_numero` (`licence_numero`),
  ADD KEY `club_id` (`club_id`);

--
-- Index pour la table `joueurs`
--
ALTER TABLE `joueurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `licence_numero` (`licence_numero`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_domicile_id` (`club_domicile_id`),
  ADD KEY `club_exterieur_id` (`club_exterieur_id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `saison_id` (`saison_id`);

--
-- Index pour la table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `saisons`
--
ALTER TABLE `saisons`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `stats_joueurs`
--
ALTER TABLE `stats_joueurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `joueur_id` (`joueur_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Index pour la table `trash_items`
--
ALTER TABLE `trash_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted_by` (`deleted_by`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `classements`
--
ALTER TABLE `classements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT pour la table `entraineurs`
--
ALTER TABLE `entraineurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `joueurs`
--
ALTER TABLE `joueurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `matchs`
--
ALTER TABLE `matchs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `saisons`
--
ALTER TABLE `saisons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `stats_joueurs`
--
ALTER TABLE `stats_joueurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `trash_items`
--
ALTER TABLE `trash_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `classements`
--
ALTER TABLE `classements`
  ADD CONSTRAINT `classements_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `classements_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `classements_ibfk_3` FOREIGN KEY (`saison_id`) REFERENCES `saisons` (`id`);

--
-- Contraintes pour la table `clubs`
--
ALTER TABLE `clubs`
  ADD CONSTRAINT `clubs_ibfk_1` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`);

--
-- Contraintes pour la table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Contraintes pour la table `entraineurs`
--
ALTER TABLE `entraineurs`
  ADD CONSTRAINT `entraineurs_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`);

--
-- Contraintes pour la table `joueurs`
--
ALTER TABLE `joueurs`
  ADD CONSTRAINT `joueurs_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `joueurs_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`);

--
-- Contraintes pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD CONSTRAINT `matchs_ibfk_1` FOREIGN KEY (`club_domicile_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `matchs_ibfk_2` FOREIGN KEY (`club_exterieur_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `matchs_ibfk_3` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `matchs_ibfk_4` FOREIGN KEY (`saison_id`) REFERENCES `saisons` (`id`);

--
-- Contraintes pour la table `stats_joueurs`
--
ALTER TABLE `stats_joueurs`
  ADD CONSTRAINT `stats_joueurs_ibfk_1` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`),
  ADD CONSTRAINT `stats_joueurs_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matchs` (`id`);

--
-- Contraintes pour la table `trash_items`
--
ALTER TABLE `trash_items`
  ADD CONSTRAINT `trash_items_ibfk_1` FOREIGN KEY (`deleted_by`) REFERENCES `admin_users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
