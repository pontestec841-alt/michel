-- Create database if not exists (optional, depending on the user's setup)
-- CREATE DATABASE IF NOT EXISTS invitation_db;
-- USE invitation_db;

-- Table structure for table `settings`
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_date` varchar(255) NOT NULL DEFAULT '20 de Dezembro',
  `event_time` varchar(255) NOT NULL DEFAULT '18:00',
  `event_location` varchar(255) NOT NULL DEFAULT 'Castelo Encantado',
  `music_url` varchar(255) DEFAULT '',
  `music_enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if empty
INSERT INTO `settings` (`event_date`, `event_time`, `event_location`, `music_url`, `music_enabled`)
SELECT '20 de Dezembro', '18:00', 'Castelo Encantado', '', 1
WHERE NOT EXISTS (SELECT 1 FROM `settings` LIMIT 1);

-- Table structure for table `guests`
CREATE TABLE IF NOT EXISTS `guests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guest_count` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `gifts`
CREATE TABLE IF NOT EXISTS `gifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `reserved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reserved_by` (`reserved_by`),
  CONSTRAINT `gifts_ibfk_1` FOREIGN KEY (`reserved_by`) REFERENCES `guests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default gifts if empty
INSERT INTO `gifts` (`name`)
SELECT 'Boneca' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Boneca');
INSERT INTO `gifts` (`name`)
SELECT 'Carrinho' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Carrinho');
INSERT INTO `gifts` (`name`)
SELECT 'Quebra-cabeça' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Quebra-cabeça');
INSERT INTO `gifts` (`name`)
SELECT 'Livro Infantil' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Livro Infantil');
INSERT INTO `gifts` (`name`)
SELECT 'Roupinha' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Roupinha');
INSERT INTO `gifts` (`name`)
SELECT 'Urso de Pelúcia' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Urso de Pelúcia');
INSERT INTO `gifts` (`name`)
SELECT 'Patinete' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Patinete');
INSERT INTO `gifts` (`name`)
SELECT 'Kit de Desenho' WHERE NOT EXISTS (SELECT 1 FROM `gifts` WHERE `name` = 'Kit de Desenho');
