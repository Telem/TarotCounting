-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.37-0ubuntu0.13.10.1 - (Ubuntu)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for tarot
CREATE DATABASE IF NOT EXISTS `tarot` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `tarot`;


-- Dumping structure for table tarot.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kind` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `base_score` int(11) NOT NULL,
  `hand_score` int(11) NOT NULL,
  `personal_score` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_achievements_achievement_kind` (`kind`),
  CONSTRAINT `FK_achievements_achievement_kind` FOREIGN KEY (`kind`) REFERENCES `achievement_kind` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.achievements: ~9 rows (approximately)
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
INSERT IGNORE INTO `achievements` (`id`, `kind`, `name`, `base_score`, `hand_score`, `personal_score`) VALUES
	(1, 2, 'Misère d\'atout', 0, 0, 10),
	(2, 2, 'Misère d\'habillé', 0, 0, 10),
	(3, 4, 'Petit au bout', 10, 0, 0),
	(4, 1, 'Unannounced Slam', 0, 200, 0),
	(5, 1, 'Successful Slam', 0, 400, 0),
	(6, 1, 'Failed Slam', 0, -200, 0),
	(7, 3, 'Handful', 0, 20, 0),
	(8, 3, 'Double Handful', 0, 40, 0),
	(9, 3, 'Triple Handful', 0, 60, 0);
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;


-- Dumping structure for table tarot.achievement_kind
CREATE TABLE IF NOT EXISTS `achievement_kind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.achievement_kind: ~4 rows (approximately)
/*!40000 ALTER TABLE `achievement_kind` DISABLE KEYS */;
INSERT IGNORE INTO `achievement_kind` (`id`, `name`) VALUES
	(1, 'slam'),
	(2, 'misere'),
	(3, 'handful'),
	(4, 'petit_au_bout');
/*!40000 ALTER TABLE `achievement_kind` ENABLE KEYS */;


-- Dumping structure for table tarot.bids
CREATE TABLE IF NOT EXISTS `bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `multiplier` tinyint(4) NOT NULL DEFAULT '1',
  `base` tinyint(4) NOT NULL DEFAULT '25',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.bids: ~6 rows (approximately)
/*!40000 ALTER TABLE `bids` DISABLE KEYS */;
INSERT IGNORE INTO `bids` (`id`, `name`, `multiplier`, `base`) VALUES
	(1, 'Pass', 0, 0),
	(2, 'Take', 1, 25),
	(3, 'Push', 1, 40),
	(4, 'Guard', 2, 25),
	(5, 'Guard Without', 4, 25),
	(6, 'Guard Against', 6, 25);
/*!40000 ALTER TABLE `bids` ENABLE KEYS */;


-- Dumping structure for table tarot.contracts
CREATE TABLE IF NOT EXISTS `contracts` (
  `value` tinyint(4) NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.contracts: ~4 rows (approximately)
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
INSERT IGNORE INTO `contracts` (`value`, `name`) VALUES
	(36, '3 bouts (36)'),
	(41, '2 bouts (41)'),
	(51, '1 bout (51)'),
	(56, '0 bout (56)');
/*!40000 ALTER TABLE `contracts` ENABLE KEYS */;


-- Dumping structure for table tarot.games
CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contract` tinyint(4) NOT NULL,
  `score` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_games_contracts` (`contract`),
  CONSTRAINT `FK_games_contracts` FOREIGN KEY (`contract`) REFERENCES `contracts` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping structure for table tarot.game_achievements
CREATE TABLE IF NOT EXISTS `game_achievements` (
  `achievement_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`achievement_id`,`game_id`,`player_id`),
  KEY `FK_game_achievements_game_players` (`game_id`,`player_id`),
  CONSTRAINT `FK_game_achievements_game_players` FOREIGN KEY (`game_id`, `player_id`) REFERENCES `game_players` (`game_id`, `player_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__achievements` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping structure for view tarot.game_insight
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `game_insight` (
	`date` TIMESTAMP NOT NULL,
	`player` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`bid` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
	`role` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
	`contract` TINYINT(4) NOT NULL,
	`score` TINYINT(4) NOT NULL,
	`player_score` INT(11) NULL,
	`game_id` INT(11) NOT NULL,
	`player_id` INT(11) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for table tarot.game_players
CREATE TABLE IF NOT EXISTS `game_players` (
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `bid` int(11) NOT NULL COMMENT 'indicate what bid was made, but it may not be the winning bid for that game',
  `role` int(11) NOT NULL,
  PRIMARY KEY (`game_id`,`player_id`),
  KEY `FK_game_players_players` (`player_id`),
  KEY `FK_game_players_bids` (`bid`),
  KEY `FK_game_players_roles` (`role`),
  CONSTRAINT `FK_game_players_bids` FOREIGN KEY (`bid`) REFERENCES `bids` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_game_players_games` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_game_players_players` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_game_players_roles` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping structure for function tarot.Hand_Score
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `Hand_Score`(`game_id` INT) RETURNS int(11)
BEGIN
	DECLARE game_diff INT;
	DECLARE bid_base INT;
	DECLARE bid_multiplier INT;
	DECLARE achievement_base INT DEFAULT 0;
	DECLARE petit_au_bout INT DEFAULT 0;
	DECLARE achievement_bonus INT DEFAULT 0;
	
	SELECT base, multiplier 
		FROM bids
		WHERE bids.id = (SELECT MAX(bid) 
			FROM game_players 
			WHERE game_players.game_id = game_id 
		)
		INTO bid_base, bid_multiplier;
		
	SELECT ABS(score - contract) 
		FROM games
		WHERE id = game_id
		INTO game_diff;
	
	SELECT SUM(achievements.base_score), SUM(achievements.hand_score)
		FROM game_achievements
			JOIN achievements ON (game_achievements.achievement_id = achievements.id)
		WHERE game_achievements.game_id = game_id
			AND game_achievements.achievement_id != 3
		INTO achievement_base, achievement_bonus;
	
	SELECT IF((game_players.role = 1 OR game_players.role = 3) XOR games.score < games.contract,10,-10)
		FROM game_achievements
			JOIN games ON (game_achievements.game_id = games.id)
			JOIN game_players ON (game_achievements.game_id = game_players.game_id AND game_achievements.player_id = game_players.player_id)
		WHERE game_achievements.game_id = game_id
			AND game_achievements.achievement_id = 3
		INTO petit_au_bout;
	
		
	RETURN (bid_base + game_diff + COALESCE(achievement_base, 0) + COALESCE(petit_au_bout, 0)) * bid_multiplier + COALESCE(achievement_bonus, 0);
END//
DELIMITER ;


-- Dumping structure for table tarot.lifetime_achievements
CREATE TABLE IF NOT EXISTS `lifetime_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.lifetime_achievements: ~24 rows (approximately)
/*!40000 ALTER TABLE `lifetime_achievements` DISABLE KEYS */;
INSERT IGNORE INTO `lifetime_achievements` (`id`, `name`, `description`) VALUES
	(1, 'Superb victory', 'Win with a score difference of 30 points or more'),
	(2, 'Narcissistic', 'Calling yourself as your partner (on purpose)'),
	(3, 'Pitiful loss', 'Lose with a score difference of 30 points or more'),
	(4, 'Thanks for the support', 'Calling yourself as your partner (not on purpose)'),
	(5, 'Slam it down', 'Succeeding in a Slam'),
	(6, 'Ouch.', 'Losing all rounds when attacking'),
	(7, 'So close yet so far', 'Lose by 1 point'),
	(8, 'Minimum wage', 'Win by 0 points'),
	(9, 'Level 1 warrior', 'Attack once'),
	(10, 'Level 5 mage', 'Get called 5 times'),
	(11, 'Level 10 tank', 'Defend 10 times'),
	(12, 'Level 20 warrior', 'Attack 20 times'),
	(13, 'Level 20 mage', 'Get called 20 times'),
	(14, 'Level 5 warrior', 'Attack 5 times'),
	(15, 'Level 30 tank', 'Defend 30 times'),
	(16, 'Level 60 tank', 'Defend 60 times'),
	(17, 'Level 100 tank', 'Defend 100 times'),
	(18, 'I play therefore I am', 'Play 1 game'),
	(19, 'Get playin\'', 'Play 50 games'),
	(20, 'Getting the hang of it', 'Play 100 games'),
	(21, 'Cruisin\'', 'Win 5 games in a row'),
	(22, 'Bloodthirsty', 'Attack 5 times in a row'),
	(23, 'Killing spree', 'Win 10 games in a row'),
	(24, 'Rampage', 'Attack 10 times in a row');
/*!40000 ALTER TABLE `lifetime_achievements` ENABLE KEYS */;


-- Dumping structure for table tarot.players
CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.players: ~7 rows (approximately)
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT IGNORE INTO `players` (`id`, `name`) VALUES
	(1, 'Mathieu'),
	(2, 'Balazs'),
	(3, 'Mark'),
	(4, 'Simon'),
	(5, 'Alberto'),
	(6, 'Raimon'),
	(7, 'Chris');
/*!40000 ALTER TABLE `players` ENABLE KEYS */;


-- Dumping structure for function tarot.Player_Game_Score
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `Player_Game_Score`(`game_id` INT, `player_id` INT) RETURNS int(11)
BEGIN
	DECLARE success INT;
	DECLARE player_share INT;
	DECLARE hand_score INT;
	DECLARE personal_bonus INT;
	DECLARE personal_malus INT;
	
	SELECT Hand_Score(game_id) INTO hand_score;
	
	SELECT (score >= contract) AS success 
		FROM games
		WHERE games.id = game_id
		INTO success;
	
	SELECT IF(success,contract_success_share,contract_failure_share) AS player_share 
		FROM game_players
			JOIN roles ON (game_players.role = roles.id)
			JOIN role_shares ON (roles.id = role_shares.role_id)
		WHERE game_players.game_id = game_id 
		AND game_players.player_id = player_id
		AND player_count = (SELECT COUNT(*) FROM game_players AS gp WHERE gp.game_id = game_id)
		AND callee_count = (SELECT COUNT(*) FROM game_players AS gp WHERE gp.game_id = game_id AND gp.role = 3)
		INTO player_share;
		
	SELECT 
		SUM(achievements.personal_score) * 
			(SELECT COUNT(*)-1 FROM game_players WHERE game_players.game_id = game_id)
		FROM game_achievements JOIN achievements ON (game_achievements.achievement_id = achievements.id)
		WHERE game_achievements.game_id = game_id
			AND game_achievements.player_id = player_id
		INTO personal_bonus;
		
	SELECT SUM(achievements.personal_score)
		FROM game_achievements JOIN achievements ON (game_achievements.achievement_id = achievements.id)
		WHERE game_achievements.game_id = game_id
			AND game_achievements.player_id != player_id
		INTO personal_malus;

	RETURN player_share * hand_score + COALESCE(personal_bonus,0) - COALESCE(personal_malus, 0);
END//
DELIMITER ;


-- Dumping structure for view tarot.player_insight
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `player_insight` (
	`player_id` INT(11) NOT NULL,
	`game_id` INT(11) NOT NULL,
	`bid` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
	`role` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
	`hand_score` INT(11) NULL,
	`player_score` INT(11) NULL
) ENGINE=MyISAM;


-- Dumping structure for table tarot.player_lifetime_achievements
CREATE TABLE IF NOT EXISTS `player_lifetime_achievements` (
  `player_id` int(11) DEFAULT NULL,
  `achievement_id` int(11) DEFAULT NULL,
  KEY `FK__players` (`player_id`),
  KEY `FK__lifetime_achievements` (`achievement_id`),
  CONSTRAINT `FK__players` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK__lifetime_achievements` FOREIGN KEY (`achievement_id`) REFERENCES `lifetime_achievements` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.player_lifetime_achievements: ~0 rows (approximately)
/*!40000 ALTER TABLE `player_lifetime_achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_lifetime_achievements` ENABLE KEYS */;


-- Dumping structure for table tarot.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `side` enum('attack','defense') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.roles: ~3 rows (approximately)
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT IGNORE INTO `roles` (`id`, `side`, `name`) VALUES
	(1, 'attack', 'Attacker'),
	(2, 'defense', 'Defender'),
	(3, 'attack', 'Callee');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;


-- Dumping structure for table tarot.role_shares
CREATE TABLE IF NOT EXISTS `role_shares` (
  `role_id` int(11) NOT NULL,
  `player_count` int(11) NOT NULL,
  `callee_count` int(11) NOT NULL,
  `contract_success_share` int(11) NOT NULL,
  `contract_failure_share` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`player_count`,`callee_count`),
  CONSTRAINT `FK__roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table tarot.role_shares: ~14 rows (approximately)
/*!40000 ALTER TABLE `role_shares` DISABLE KEYS */;
INSERT IGNORE INTO `role_shares` (`role_id`, `player_count`, `callee_count`, `contract_success_share`, `contract_failure_share`) VALUES
	(1, 3, 0, 2, -2),
	(1, 4, 0, 3, -3),
	(1, 5, 0, 4, -4),
	(1, 5, 1, 2, -2),
	(1, 6, 0, 5, -5),
	(1, 6, 1, 2, -2),
	(2, 3, 0, -1, 1),
	(2, 4, 0, -1, 1),
	(2, 5, 0, -1, 1),
	(2, 5, 1, -1, 1),
	(2, 6, 0, -1, 1),
	(2, 6, 1, -1, 1),
	(3, 5, 1, 1, -1),
	(3, 6, 1, 2, -2);
/*!40000 ALTER TABLE `role_shares` ENABLE KEYS */;


-- Dumping structure for view tarot.game_insight
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `game_insight`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `game_insight` AS select `games`.`date` AS `date`,`players`.`name` AS `player`,`bids`.`name` AS `bid`,`roles`.`name` AS `role`,`games`.`contract` AS `contract`,`games`.`score` AS `score`,`Player_Game_Score`(`game_players`.`game_id`,`game_players`.`player_id`) AS `player_score`,`game_players`.`game_id` AS `game_id`,`game_players`.`player_id` AS `player_id` from ((((`games` join `game_players` on((`games`.`id` = `game_players`.`game_id`))) join `players` on((`players`.`id` = `game_players`.`player_id`))) join `bids` on((`bids`.`id` = `game_players`.`bid`))) join `roles` on((`roles`.`id` = `game_players`.`role`))) order by `games`.`id`;


-- Dumping structure for view tarot.player_insight
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `player_insight`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `player_insight` AS select `game_players`.`player_id` AS `player_id`,`game_players`.`game_id` AS `game_id`,`bids`.`name` AS `bid`,`roles`.`name` AS `role`,`Hand_Score`(`game_players`.`game_id`) AS `hand_score`,`Player_Game_Score`(`game_players`.`game_id`,`game_players`.`player_id`) AS `player_score` from (((`game_players` join `bids` on((`bids`.`id` = `game_players`.`bid`))) join `roles` on((`roles`.`id` = `game_players`.`role`))) left join `game_achievements` on(((`game_achievements`.`game_id` = `game_players`.`game_id`) and (`game_achievements`.`player_id` = `game_players`.`player_id`))));
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
