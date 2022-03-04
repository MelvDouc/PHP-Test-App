-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 04 mars 2022 à 19:11
-- Version du serveur : 10.4.21-MariaDB
-- Version de PHP : 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `test_app`
--

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'vêtements', 'Articles de vêture en tout genre', 'default.jpg', '2022-03-03 19:59:11');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 1,
  `is_verified` tinyint(4) NOT NULL DEFAULT 0,
  `verif_string` varchar(128) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `role`, `is_verified`, `verif_string`, `created_at`) VALUES
(1, 'admin', 'melv.douc@gmail.com', '$2y$10$UuUm7B0vpV2EdMY/JMAr8u4N12DBVHC8WQjLQI37ByqOhALNkfnl2', 0, 1, '', '2022-02-28 11:25:38'),
(2, 'user1', 'user1@mail.com', '$2y$10$zGqjpGrNTXn8Cv0wFkGfi.JTCUT2VTOUg0twOVPxr9eOhChR5O0ZO', 1, 0, '', '2022-02-28 11:33:53'),
(3, 'user2', 'user2@mail.com', '$2y$10$FEDpzoOHFXTMcdG5AdtGyeoDcdk38OsmvHgTkIW4.bgwPr3OQfmdu', 1, 0, '', '2022-02-28 11:49:55'),
(4, 'user3', 'user3@mail.com', '$2y$10$EZifdYNiE98K/mgKXOyOn.tAv9u74RNgZCsd4malPb4/9JI43lRMe', 1, 0, '', '2022-02-28 12:11:48'),
(5, 'user4', 'user4@gmail.com', '$2y$10$.y3YLJbMh1TrH5GjeZcVQ.Ko1LnuqGNhJ2bEyOMaVlw8NQLppv5Hq', 1, 0, '', '2022-02-28 12:14:54'),
(6, 'user5', 'user5@gmail.com', '$2y$10$4Zvce2fmTUq1WF6Y215xbOB8JZI1PKOOGf/YWOb8k2bsGPDCRlUXW', 1, 0, '', '2022-02-28 12:34:49'),
(16, 'melvdouc', 'melvin.doucet@yahoo.fr', '$2y$10$3l4gfJRQ5GQCP.Sbh9WmzO1c.QFxRRpfaV.t14FH.tuJU5jjOe6sO', 1, 0, '', '2022-03-01 13:56:46'),
(18, 'lorem', 'lorem-ipsum-dolor-sin-amet@protonmail.com', '$2y$10$RS6MfBJe3hc675G2uCEkpuUR7efu9UETpirkNSYbSnRj.IPE9Z5VO', 1, 1, NULL, '2022-03-01 15:20:09'),
(19, 'user10', 'user10@mail.com', '$2y$10$Yriol/oV7461IgqChYFAKOHWVRH0InU3w0lldL5NwsQMo1YDbZwGS', 1, 0, '5nXBWuSIZXMkUjp4bRFVCEmgWswxAQAwpqozy9dWo9b9APGMED3rVI8fgx_ZUEuWvsCEzlFk_3JkULOkYRklAkGyj9sNcJ2sH1WSIQx7vo1fn2pSEc4vEyooVLMyEkbs', '2022-03-03 19:51:53');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
