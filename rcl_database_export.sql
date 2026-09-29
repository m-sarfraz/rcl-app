-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: rcl
-- ------------------------------------------------------
-- Server version	10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','match_update','player_suspension','result','notice') NOT NULL DEFAULT 'general',
  `edition_id` bigint(20) unsigned DEFAULT NULL,
  `is_ticker` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_edition_id_foreign` (`edition_id`),
  KEY `announcements_created_by_foreign` (`created_by`),
  KEY `announcements_is_active_is_ticker_index` (`is_active`,`is_ticker`),
  CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ball_by_ball_logs`
--

DROP TABLE IF EXISTS `ball_by_ball_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ball_by_ball_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_uuid` char(36) DEFAULT NULL,
  `innings_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `bowler_id` bigint(20) unsigned NOT NULL,
  `batsman_id` bigint(20) unsigned NOT NULL,
  `non_striker_id` bigint(20) unsigned DEFAULT NULL,
  `over_number` int(11) NOT NULL,
  `ball_number` int(11) NOT NULL,
  `runs_scored` int(11) NOT NULL DEFAULT 0,
  `is_wicket` tinyint(1) NOT NULL DEFAULT 0,
  `wicket_type` enum('bowled','caught','run_out','lbw','stumped','hit_wicket','obstructing_field','handled_ball','timed_out','retired_hurt') DEFAULT NULL,
  `out_player_id` bigint(20) unsigned DEFAULT NULL,
  `fielder_id` bigint(20) unsigned DEFAULT NULL,
  `is_wide` tinyint(1) NOT NULL DEFAULT 0,
  `is_no_ball` tinyint(1) NOT NULL DEFAULT 0,
  `is_bye` tinyint(1) NOT NULL DEFAULT 0,
  `is_leg_bye` tinyint(1) NOT NULL DEFAULT 0,
  `is_penalty` tinyint(1) NOT NULL DEFAULT 0,
  `extra_runs` int(11) NOT NULL DEFAULT 0,
  `is_four` tinyint(1) NOT NULL DEFAULT 0,
  `is_six` tinyint(1) NOT NULL DEFAULT 0,
  `batting_team_score_after` int(11) NOT NULL DEFAULT 0,
  `batting_team_wickets_after` int(11) NOT NULL DEFAULT 0,
  `commentary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ball_by_ball_logs_client_uuid_unique` (`client_uuid`),
  KEY `ball_by_ball_logs_bowler_id_foreign` (`bowler_id`),
  KEY `ball_by_ball_logs_batsman_id_foreign` (`batsman_id`),
  KEY `ball_by_ball_logs_non_striker_id_foreign` (`non_striker_id`),
  KEY `ball_by_ball_logs_fielder_id_foreign` (`fielder_id`),
  KEY `ball_by_ball_logs_innings_id_over_number_ball_number_index` (`innings_id`,`over_number`,`ball_number`),
  KEY `ball_by_ball_logs_match_id_bowler_id_index` (`match_id`,`bowler_id`),
  KEY `ball_by_ball_logs_match_id_batsman_id_index` (`match_id`,`batsman_id`),
  KEY `ball_by_ball_logs_out_player_id_foreign` (`out_player_id`),
  CONSTRAINT `ball_by_ball_logs_batsman_id_foreign` FOREIGN KEY (`batsman_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ball_by_ball_logs_bowler_id_foreign` FOREIGN KEY (`bowler_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ball_by_ball_logs_fielder_id_foreign` FOREIGN KEY (`fielder_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ball_by_ball_logs_innings_id_foreign` FOREIGN KEY (`innings_id`) REFERENCES `innings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ball_by_ball_logs_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ball_by_ball_logs_non_striker_id_foreign` FOREIGN KEY (`non_striker_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ball_by_ball_logs_out_player_id_foreign` FOREIGN KEY (`out_player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ball_by_ball_logs`
--

LOCK TABLES `ball_by_ball_logs` WRITE;
/*!40000 ALTER TABLE `ball_by_ball_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ball_by_ball_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banned_bowlers`
--

DROP TABLE IF EXISTS `banned_bowlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banned_bowlers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `reason` text NOT NULL,
  `banned_from` date NOT NULL,
  `banned_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `issued_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banned_bowlers_issued_by_foreign` (`issued_by`),
  KEY `banned_bowlers_player_id_is_active_index` (`player_id`,`is_active`),
  CONSTRAINT `banned_bowlers_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banned_bowlers_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banned_bowlers`
--

LOCK TABLES `banned_bowlers` WRITE;
/*!40000 ALTER TABLE `banned_bowlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `banned_bowlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) DEFAULT NULL,
  `subtitle` varchar(191) DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL,
  `link_url` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banners_is_active_display_order_index` (`is_active`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batting_scorecards`
--

DROP TABLE IF EXISTS `batting_scorecards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batting_scorecards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `innings_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `player_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `batting_position` int(11) DEFAULT NULL,
  `runs_scored` int(11) NOT NULL DEFAULT 0,
  `balls_faced` int(11) NOT NULL DEFAULT 0,
  `fours` int(11) NOT NULL DEFAULT 0,
  `sixes` int(11) NOT NULL DEFAULT 0,
  `strike_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `dismissal_type` enum('bowled','caught','run_out','lbw','stumped','hit_wicket','not_out','retired_hurt','did_not_bat') NOT NULL DEFAULT 'not_out',
  `bowled_by_id` bigint(20) unsigned DEFAULT NULL,
  `caught_by_id` bigint(20) unsigned DEFAULT NULL,
  `is_fifty` tinyint(1) NOT NULL DEFAULT 0,
  `is_century` tinyint(1) NOT NULL DEFAULT 0,
  `hat_trick_sixes` tinyint(1) NOT NULL DEFAULT 0,
  `five_sixes_in_over` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batting_scorecards_innings_id_player_id_unique` (`innings_id`,`player_id`),
  KEY `batting_scorecards_player_id_foreign` (`player_id`),
  KEY `batting_scorecards_team_id_foreign` (`team_id`),
  KEY `batting_scorecards_bowled_by_id_foreign` (`bowled_by_id`),
  KEY `batting_scorecards_caught_by_id_foreign` (`caught_by_id`),
  KEY `batting_scorecards_match_id_player_id_index` (`match_id`,`player_id`),
  CONSTRAINT `batting_scorecards_bowled_by_id_foreign` FOREIGN KEY (`bowled_by_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `batting_scorecards_caught_by_id_foreign` FOREIGN KEY (`caught_by_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `batting_scorecards_innings_id_foreign` FOREIGN KEY (`innings_id`) REFERENCES `innings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batting_scorecards_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batting_scorecards_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batting_scorecards_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batting_scorecards`
--

LOCK TABLES `batting_scorecards` WRITE;
/*!40000 ALTER TABLE `batting_scorecards` DISABLE KEYS */;
/*!40000 ALTER TABLE `batting_scorecards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bowling_scorecards`
--

DROP TABLE IF EXISTS `bowling_scorecards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bowling_scorecards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `innings_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `player_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `overs_bowled_balls` int(11) NOT NULL DEFAULT 0,
  `overs_bowled` decimal(5,2) NOT NULL DEFAULT 0.00,
  `maidens` int(11) NOT NULL DEFAULT 0,
  `runs_conceded` int(11) NOT NULL DEFAULT 0,
  `wickets` int(11) NOT NULL DEFAULT 0,
  `wides` int(11) NOT NULL DEFAULT 0,
  `no_balls` int(11) NOT NULL DEFAULT 0,
  `economy` decimal(8,2) NOT NULL DEFAULT 0.00,
  `hat_trick_wickets` tinyint(1) NOT NULL DEFAULT 0,
  `five_wicket_haul` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bowling_scorecards_innings_id_player_id_unique` (`innings_id`,`player_id`),
  KEY `bowling_scorecards_player_id_foreign` (`player_id`),
  KEY `bowling_scorecards_team_id_foreign` (`team_id`),
  KEY `bowling_scorecards_match_id_player_id_index` (`match_id`,`player_id`),
  CONSTRAINT `bowling_scorecards_innings_id_foreign` FOREIGN KEY (`innings_id`) REFERENCES `innings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bowling_scorecards_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bowling_scorecards_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bowling_scorecards_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bowling_scorecards`
--

LOCK TABLES `bowling_scorecards` WRITE;
/*!40000 ALTER TABLE `bowling_scorecards` DISABLE KEYS */;
/*!40000 ALTER TABLE `bowling_scorecards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cricket_matches`
--

DROP TABLE IF EXISTS `cricket_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cricket_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` bigint(20) unsigned NOT NULL,
  `home_team_id` bigint(20) unsigned NOT NULL,
  `away_team_id` bigint(20) unsigned NOT NULL,
  `match_number` varchar(191) NOT NULL,
  `match_type` enum('group','quarter_final','semi_final','final') NOT NULL DEFAULT 'group',
  `venue` varchar(191) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('upcoming','live','completed','abandoned','postponed') NOT NULL DEFAULT 'upcoming',
  `overs_per_side` int(11) NOT NULL DEFAULT 10,
  `toss_winner_id` bigint(20) unsigned DEFAULT NULL,
  `toss_decision` enum('bat','field') DEFAULT NULL,
  `winner_id` bigint(20) unsigned DEFAULT NULL,
  `result_type` enum('runs','wickets','tie','no_result','super_over') DEFAULT NULL,
  `result_margin` int(11) DEFAULT NULL,
  `result_description` text DEFAULT NULL,
  `umpire1_id` bigint(20) unsigned DEFAULT NULL,
  `umpire2_id` bigint(20) unsigned DEFAULT NULL,
  `scorer_id` bigint(20) unsigned DEFAULT NULL,
  `man_of_match_player_id` varchar(191) DEFAULT NULL,
  `is_super_over` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `finalized_by` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cricket_matches_away_team_id_foreign` (`away_team_id`),
  KEY `cricket_matches_toss_winner_id_foreign` (`toss_winner_id`),
  KEY `cricket_matches_winner_id_foreign` (`winner_id`),
  KEY `cricket_matches_umpire1_id_foreign` (`umpire1_id`),
  KEY `cricket_matches_umpire2_id_foreign` (`umpire2_id`),
  KEY `cricket_matches_scorer_id_foreign` (`scorer_id`),
  KEY `cricket_matches_edition_id_status_index` (`edition_id`,`status`),
  KEY `cricket_matches_home_team_id_away_team_id_index` (`home_team_id`,`away_team_id`),
  KEY `cricket_matches_scheduled_at_index` (`scheduled_at`),
  CONSTRAINT `cricket_matches_away_team_id_foreign` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cricket_matches_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cricket_matches_home_team_id_foreign` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cricket_matches_scorer_id_foreign` FOREIGN KEY (`scorer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cricket_matches_toss_winner_id_foreign` FOREIGN KEY (`toss_winner_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cricket_matches_umpire1_id_foreign` FOREIGN KEY (`umpire1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cricket_matches_umpire2_id_foreign` FOREIGN KEY (`umpire2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cricket_matches_winner_id_foreign` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cricket_matches`
--

LOCK TABLES `cricket_matches` WRITE;
/*!40000 ALTER TABLE `cricket_matches` DISABLE KEYS */;
INSERT INTO `cricket_matches` VALUES (1,2,3,6,'1','group','Chak No 183','2026-10-02 08:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:45 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Momin, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(2,2,16,8,'2','group','Chak No 183','2026-10-02 09:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 09:35 AM | Umpires: AWAIS & ASAD | Scorer: AMAN | Referee: MOMIN | Day Duty: Momin, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(3,2,10,12,'3','group','Chak No 183','2026-10-02 11:40:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 11:25 AM | Umpires: AZHAR & RAMZAN | Scorer: AFZAAL | Referee: MOMIN | Day Duty: Momin, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(4,2,5,2,'4','group','Chak No 183','2026-10-02 13:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:15 PM | Umpires: ARSHAD & JAMSHIAD | Scorer: NOMAN | Referee: ABID | Day Duty: Momin, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(5,2,1,10,'5','group','Chak No 183','2026-10-02 15:20:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:05 PM | Umpires: DASTAGEER & SAQLAIN | Scorer: SHAHZAD | Referee: ABID | Day Duty: Momin, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(6,2,9,10,'6','group','Chak No 183','2026-10-03 07:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:15 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(7,2,13,12,'7','group','Chak No 183','2026-10-03 09:10:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 08:55 AM | Umpires: ADIL & ARSHAD | Scorer: NOMAN | Referee: AFRAHEEM | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(8,2,3,8,'8','group','Chak No 183','2026-10-03 10:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 10:35 AM | Umpires: BABAR & JAMSHAID | Scorer: WAQAS | Referee: AFRAHEEM | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(9,2,11,14,'9','group','Chak No 183','2026-10-03 12:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 12:15 PM | Umpires: AWAIS & RAMZAN | Scorer: AFZAAL | Referee: AFRAHEEM | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(10,2,1,12,'10','group','Chak No 183','2026-10-03 14:10:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:55 PM | Umpires: ASGHAR & SHOBAN | Scorer: RAZZAQ | Referee: HASNAIN | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(11,2,15,11,'11','group','Chak No 183','2026-10-03 15:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:35 PM | Umpires: WASEEM & JAMSHAID | Scorer: HAIDER | Referee: SARFRAZ | Day Duty: Afrahim, Hasnain, Sarfraz',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(12,2,5,17,'12','group','Chak No 183','2026-10-04 07:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:15 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(13,2,7,14,'13','group','Chak No 183','2026-10-04 09:10:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 08:55 AM | Umpires: DASTAGEER & YASEEN | Scorer: SHAHZAD | Referee: AWAIS | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(14,2,13,1,'14','group','Chak No 183','2026-10-04 10:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 10:35 AM | Umpires: NAVEED & SHOBAN | Scorer: RAZZAQ | Referee: IQBAL | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(15,2,7,11,'15','group','Chak No 183','2026-10-04 12:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 12:15 PM | Umpires: BABAR & WASEEM | Scorer: HAIDER | Referee: AWAIS | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(16,2,1,9,'16','group','Chak No 183','2026-10-04 14:10:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:55 PM | Umpires: NAVEED & ASGHAR | Scorer: NAJAM | Referee: SAJJAD | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(17,2,4,11,'17','group','Chak No 183','2026-10-04 15:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:35 PM | Umpires: WASEEM & ADIL | Scorer: TALHA | Referee: SAJJAD | Day Duty: Awais, Iqbal, Sajjad',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(18,2,13,10,'18','group','Chak No 183','2026-10-05 08:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:45 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Arshad, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(19,2,16,3,'19','group','Chak No 183','2026-10-05 09:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 09:35 AM | Umpires: BABAR & AFRAHIM | Scorer: NOMAN | Referee: ARSHAD | Day Duty: Arshad, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(20,2,18,17,'20','group','Chak No 183','2026-10-05 11:40:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 11:25 AM | Umpires: AZHAR & AWAIS | Scorer: FAISAL | Referee: ARSHAD | Day Duty: Arshad, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(21,2,6,8,'21','group','Chak No 183','2026-10-05 13:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:15 PM | Umpires: ABUBAKAR & YASEEN | Scorer: AWAIS | Referee: ARSHAD | Day Duty: Arshad, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(22,2,15,14,'22','group','Chak No 183','2026-10-05 15:20:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:05 PM | Umpires: ASAD & RAMZAN | Scorer: AMAN | Referee: ABID | Day Duty: Arshad, Abid',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(23,2,4,14,'23','group','Chak No 183','2026-10-06 08:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:45 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Farooq, Awais',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(24,2,13,9,'24','group','Chak No 183','2026-10-06 09:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 09:35 AM | Umpires: TALHA & SHOBAN | Scorer: SHAHID | Referee: FAROOQ | Day Duty: Farooq, Awais',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(25,2,2,17,'25','group','Chak No 183','2026-10-06 11:40:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 11:25 AM | Umpires: BABAR & ADIL | Scorer: TALHA | Referee: FAROOQ | Day Duty: Farooq, Awais',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(26,2,9,12,'26','group','Chak No 183','2026-10-06 13:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:15 PM | Umpires: SAQLAIN & YASEEN | Scorer: NAVEED | Referee: AWAIS | Day Duty: Farooq, Awais',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(27,2,15,4,'27','group','Chak No 183','2026-10-06 15:20:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:05 PM | Umpires: ADIL & JAMSHAID | Scorer: UMERBILLA | Referee: AWAIS | Day Duty: Farooq, Awais',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(28,2,16,6,'28','group','Chak No 183','2026-10-07 08:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 07:45 AM | Umpires: NABIA & MUJAHID | Scorer: QAZAFI | Referee: RAFAQAT | Day Duty: Waqas, Iqbal, Farooq',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(29,2,18,2,'29','group','Chak No 183','2026-10-07 09:50:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 09:35 AM | Umpires: AZHAR & ASAD | Scorer: FAISAL | Referee: WAQAS | Day Duty: Waqas, Iqbal, Farooq',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(30,2,4,7,'30','group','Chak No 183','2026-10-07 11:40:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 11:25 AM | Umpires: ABUBAKAR & SAQLAIN | Scorer: NAVEED | Referee: WAQAS | Day Duty: Waqas, Iqbal, Farooq',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(31,2,18,5,'31','group','Chak No 183','2026-10-07 13:30:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 01:15 PM | Umpires: TAHLA & NAVEED | Scorer: SAAD | Referee: IQBAL | Day Duty: Waqas, Iqbal, Farooq',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(32,2,15,7,'32','group','Chak No 183','2026-10-07 15:20:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 03:05 PM | Umpires: ABUBAKAR & DASTAGEER | Scorer: SHAHZAD | Referee: FAROOQ | Day Duty: Waqas, Iqbal, Farooq',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(33,2,19,20,'33','quarter_final','Chak No 183','2026-10-08 09:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 08:45 AM | Quarter Final 1: Pool A Winner (A1) vs Pool D Runner-up (D2) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(34,2,21,22,'34','quarter_final','Chak No 183','2026-10-08 11:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 10:45 AM | Quarter Final 2: Pool A Runner-up (A2) vs Pool D Winner (D1) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(35,2,23,24,'35','quarter_final','Chak No 183','2026-10-08 13:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 12:45 PM | Quarter Final 3: Pool B Winner (B1) vs Pool C Runner-up (C2) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(36,2,25,26,'36','quarter_final','Chak No 183','2026-10-08 15:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 02:45 PM | Quarter Final 4: Pool B Runner-up (B2) vs Pool C Winner (C1) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(37,2,27,28,'37','semi_final','Chak No 183','2026-10-09 09:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 08:45 AM | 1st Semifinal: Winner (A1 vs D2) vs Winner (B1 vs C2) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(38,2,29,30,'38','semi_final','Chak No 183','2026-10-09 11:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'Toss: 10:45 AM | 2nd Semifinal: Winner (A2 vs D1) vs Winner (B2 vs C1) | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(39,2,31,32,'39','final','Chak No 183','2026-10-09 15:00:00','upcoming',10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'FINAL: Winner 1st Semifinal vs Winner 2nd Semifinal | Toss: 02:45 PM | Day Duty: All Cabinet',NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL);
/*!40000 ALTER TABLE `cricket_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `demerit_points`
--

DROP TABLE IF EXISTS `demerit_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demerit_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` bigint(20) unsigned DEFAULT NULL,
  `target_type` varchar(191) NOT NULL DEFAULT 'player',
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `player_id` bigint(20) unsigned DEFAULT NULL,
  `target_name` varchar(191) DEFAULT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `points` int(10) unsigned NOT NULL DEFAULT 1,
  `reason` text NOT NULL,
  `incident_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `demerit_points_match_id_foreign` (`match_id`),
  KEY `demerit_points_issued_by_foreign` (`issued_by`),
  KEY `demerit_points_target_type_is_active_index` (`target_type`,`is_active`),
  KEY `demerit_points_team_id_is_active_index` (`team_id`,`is_active`),
  KEY `demerit_points_player_id_is_active_index` (`player_id`,`is_active`),
  KEY `demerit_points_edition_id_is_active_index` (`edition_id`,`is_active`),
  CONSTRAINT `demerit_points_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demerit_points_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demerit_points_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demerit_points_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demerit_points_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demerit_points`
--

LOCK TABLES `demerit_points` WRITE;
/*!40000 ALTER TABLE `demerit_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `demerit_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edition_teams`
--

DROP TABLE IF EXISTS `edition_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edition_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `group_number` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `edition_teams_edition_id_team_id_unique` (`edition_id`,`team_id`),
  KEY `edition_teams_team_id_foreign` (`team_id`),
  KEY `edition_teams_edition_id_index` (`edition_id`),
  CONSTRAINT `edition_teams_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `edition_teams_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edition_teams`
--

LOCK TABLES `edition_teams` WRITE;
/*!40000 ALTER TABLE `edition_teams` DISABLE KEYS */;
INSERT INTO `edition_teams` VALUES (1,2,1,4,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(2,2,2,2,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(3,2,3,3,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(4,2,4,1,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(5,2,5,2,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(6,2,6,3,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(7,2,7,1,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(8,2,8,3,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(9,2,9,4,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(10,2,10,4,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(11,2,11,1,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(12,2,12,4,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(13,2,13,4,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(14,2,14,1,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(15,2,15,1,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(16,2,16,3,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(17,2,17,2,'2026-09-29 12:10:29','2026-09-29 12:10:29'),(18,2,18,2,'2026-09-29 12:10:29','2026-09-29 12:10:29');
/*!40000 ALTER TABLE `edition_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `editions`
--

DROP TABLE IF EXISTS `editions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `editions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `edition_number` int(11) NOT NULL,
  `host_village` varchar(191) NOT NULL,
  `thumbnail` varchar(191) DEFAULT NULL,
  `banner` varchar(191) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('upcoming','active','completed','archived') NOT NULL DEFAULT 'upcoming',
  `description` text DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `editions_status_is_current_index` (`status`,`is_current`),
  KEY `editions_edition_number_index` (`edition_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `editions`
--

LOCK TABLES `editions` WRITE;
/*!40000 ALTER TABLE `editions` DISABLE KEYS */;
INSERT INTO `editions` VALUES (1,'36th Edition',36,'N/A',NULL,NULL,NULL,NULL,'completed','36th Edition of Royal Champions League.',0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(2,'37th Edition 2026',37,'Chak No 183',NULL,NULL,'2026-10-02','2026-10-09','upcoming','Royal Champions League 37th Edition 2026. Chairman: Husnain Khan Sial 421. Host: Chak No 183 Shohla Cricket Club. Venue: Chak No 183.',1,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL);
/*!40000 ALTER TABLE `editions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fielding_scorecards`
--

DROP TABLE IF EXISTS `fielding_scorecards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fielding_scorecards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `player_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `catches` int(11) NOT NULL DEFAULT 0,
  `run_outs` int(11) NOT NULL DEFAULT 0,
  `stumpings` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fielding_scorecards_match_id_player_id_unique` (`match_id`,`player_id`),
  KEY `fielding_scorecards_player_id_foreign` (`player_id`),
  KEY `fielding_scorecards_team_id_foreign` (`team_id`),
  CONSTRAINT `fielding_scorecards_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fielding_scorecards_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fielding_scorecards_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fielding_scorecards`
--

LOCK TABLES `fielding_scorecards` WRITE;
/*!40000 ALTER TABLE `fielding_scorecards` DISABLE KEYS */;
/*!40000 ALTER TABLE `fielding_scorecards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_transactions`
--

DROP TABLE IF EXISTS `finance_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finance_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` bigint(20) unsigned NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `category` enum('entry_fee','sponsorship','umpire_fee','scorer_fee','groundsman_fee','equipment','prize_money','fine_collection','other') NOT NULL,
  `description` varchar(191) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `reference_number` varchar(191) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `recorded_by` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finance_transactions_team_id_foreign` (`team_id`),
  KEY `finance_transactions_match_id_foreign` (`match_id`),
  KEY `finance_transactions_recorded_by_foreign` (`recorded_by`),
  KEY `finance_transactions_edition_id_type_category_index` (`edition_id`,`type`,`category`),
  CONSTRAINT `finance_transactions_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `finance_transactions_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transactions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `finance_transactions_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_transactions`
--

LOCK TABLES `finance_transactions` WRITE;
/*!40000 ALTER TABLE `finance_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fines`
--

DROP TABLE IF EXISTS `fines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned DEFAULT NULL,
  `edition_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `violation_type` enum('chucking','code_of_conduct','disciplinary_card','misconduct','other') NOT NULL,
  `card_type` enum('yellow','red','none') NOT NULL DEFAULT 'none',
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','paid','waived') NOT NULL DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `issued_by` bigint(20) unsigned NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fines_match_id_foreign` (`match_id`),
  KEY `fines_issued_by_foreign` (`issued_by`),
  KEY `fines_player_id_status_index` (`player_id`,`status`),
  KEY `fines_edition_id_status_index` (`edition_id`,`status`),
  KEY `fines_team_id_foreign` (`team_id`),
  CONSTRAINT `fines_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fines_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fines_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fines_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fines_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fines`
--

LOCK TABLES `fines` WRITE;
/*!40000 ALTER TABLE `fines` DISABLE KEYS */;
/*!40000 ALTER TABLE `fines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `innings`
--

DROP TABLE IF EXISTS `innings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `innings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `batting_team_id` bigint(20) unsigned NOT NULL,
  `bowling_team_id` bigint(20) unsigned NOT NULL,
  `innings_number` int(11) NOT NULL,
  `total_runs` int(11) NOT NULL DEFAULT 0,
  `total_wickets` int(11) NOT NULL DEFAULT 0,
  `total_balls` int(11) NOT NULL DEFAULT 0,
  `overs_faced` decimal(5,2) NOT NULL DEFAULT 0.00,
  `run_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `extras_wides` int(11) NOT NULL DEFAULT 0,
  `extras_no_balls` int(11) NOT NULL DEFAULT 0,
  `extras_byes` int(11) NOT NULL DEFAULT 0,
  `extras_leg_byes` int(11) NOT NULL DEFAULT 0,
  `extras_penalty` int(11) NOT NULL DEFAULT 0,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `target` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `innings_match_id_innings_number_unique` (`match_id`,`innings_number`),
  KEY `innings_batting_team_id_foreign` (`batting_team_id`),
  KEY `innings_bowling_team_id_foreign` (`bowling_team_id`),
  KEY `innings_match_id_index` (`match_id`),
  CONSTRAINT `innings_batting_team_id_foreign` FOREIGN KEY (`batting_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `innings_bowling_team_id_foreign` FOREIGN KEY (`bowling_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `innings_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `innings`
--

LOCK TABLES `innings` WRITE;
/*!40000 ALTER TABLE `innings` DISABLE KEYS */;
/*!40000 ALTER TABLE `innings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_squads`
--

DROP TABLE IF EXISTS `match_squads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `match_squads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `player_id` bigint(20) unsigned NOT NULL,
  `batting_order` int(11) DEFAULT NULL,
  `is_captain` tinyint(1) NOT NULL DEFAULT 0,
  `is_wicket_keeper` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_squads_match_id_player_id_unique` (`match_id`,`player_id`),
  KEY `match_squads_team_id_foreign` (`team_id`),
  KEY `match_squads_player_id_foreign` (`player_id`),
  KEY `match_squads_match_id_team_id_index` (`match_id`,`team_id`),
  CONSTRAINT `match_squads_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_squads_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_squads_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_squads`
--

LOCK TABLES `match_squads` WRITE;
/*!40000 ALTER TABLE `match_squads` DISABLE KEYS */;
/*!40000 ALTER TABLE `match_squads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2024_01_01_000001_create_roles_table',1),(2,'2024_01_01_000002_create_users_table',1),(3,'2024_01_01_000003_create_vcc_cabinets_table',1),(4,'2024_01_01_000004_create_editions_table',1),(5,'2024_01_01_000005_create_teams_table',1),(6,'2024_01_01_000006_create_players_table',1),(7,'2024_01_01_000007_create_matches_table',1),(8,'2024_01_01_000008_create_ball_by_ball_logs_table',1),(9,'2024_01_01_000009_create_player_stats_table',1),(10,'2024_01_01_000010_create_fines_table',1),(11,'2024_01_01_000011_create_finance_table',1),(12,'2024_01_01_000012_create_polls_table',1),(13,'2024_01_01_000013_create_notifications_table',1),(14,'2024_01_01_000014_create_notifications_and_roster_tables',1),(15,'2024_01_01_000015_add_slug_to_roles_and_label_to_permissions',1),(16,'2024_01_01_000016_add_mvp_count_to_player_edition_stats',1),(17,'2024_01_01_000017_create_banners_table',1),(18,'2026_05_19_031352_create_banned_bowlers_table',1),(19,'2026_05_19_145607_create_site_settings_table',1),(20,'2026_05_19_162513_create_sponsors_table',1),(21,'2026_05_19_164654_add_father_name_to_players',1),(22,'2026_05_19_164654_add_team_to_fines_and_player_nullable',1),(23,'2026_05_20_163429_add_cover_photo_to_teams_table',1),(24,'2026_05_20_165249_make_image_path_nullable_on_banners',1),(25,'2026_08_20_100000_extend_player_edition_stats',1),(26,'2026_08_20_100100_add_scoring_columns_to_matches',1),(27,'2026_08_20_100200_add_scoring_fidelity_to_ball_logs',1),(28,'2026_09_30_000001_create_demerit_points_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `type` enum('info','live_update','suspension','fine','announcement') NOT NULL DEFAULT 'info',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_ticker` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_is_active_is_ticker_index` (`is_active`,`is_ticker`),
  KEY `notifications_created_by_foreign` (`created_by`),
  CONSTRAINT `notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `display_name` varchar(191) NOT NULL,
  `module` varchar(191) NOT NULL,
  `label` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_matches','View Matches','matches','View Matches','2026-09-29 12:10:27','2026-09-29 12:10:27'),(2,'create_matches','Create Matches','matches','Create Matches','2026-09-29 12:10:27','2026-09-29 12:10:27'),(3,'edit_matches','Edit Matches','matches','Edit Matches','2026-09-29 12:10:27','2026-09-29 12:10:27'),(4,'delete_matches','Delete Matches','matches','Delete Matches','2026-09-29 12:10:27','2026-09-29 12:10:27'),(5,'score_matches','Score Matches','matches','Score Matches','2026-09-29 12:10:27','2026-09-29 12:10:27'),(6,'manage_results','Manage Results','matches','Manage Results','2026-09-29 12:10:27','2026-09-29 12:10:27'),(7,'view_editions','View Editions','editions','View Editions','2026-09-29 12:10:27','2026-09-29 12:10:27'),(8,'create_editions','Create Editions','editions','Create Editions','2026-09-29 12:10:27','2026-09-29 12:10:27'),(9,'edit_editions','Edit Editions','editions','Edit Editions','2026-09-29 12:10:27','2026-09-29 12:10:27'),(10,'delete_editions','Delete Editions','editions','Delete Editions','2026-09-29 12:10:27','2026-09-29 12:10:27'),(11,'view_teams','View Teams','teams','View Teams','2026-09-29 12:10:27','2026-09-29 12:10:27'),(12,'create_teams','Create Teams','teams','Create Teams','2026-09-29 12:10:27','2026-09-29 12:10:27'),(13,'edit_teams','Edit Teams','teams','Edit Teams','2026-09-29 12:10:27','2026-09-29 12:10:27'),(14,'delete_teams','Delete Teams','teams','Delete Teams','2026-09-29 12:10:27','2026-09-29 12:10:27'),(15,'view_players','View Players','players','View Players','2026-09-29 12:10:27','2026-09-29 12:10:27'),(16,'create_players','Create Players','players','Create Players','2026-09-29 12:10:27','2026-09-29 12:10:27'),(17,'edit_players','Edit Players','players','Edit Players','2026-09-29 12:10:27','2026-09-29 12:10:27'),(18,'delete_players','Delete Players','players','Delete Players','2026-09-29 12:10:27','2026-09-29 12:10:27'),(19,'manage_suspensions','Manage Suspensions','players','Manage Suspensions','2026-09-29 12:10:27','2026-09-29 12:10:27'),(20,'view_fines','View Fines','fines','View Fines','2026-09-29 12:10:27','2026-09-29 12:10:27'),(21,'create_fines','Create Fines','fines','Create Fines','2026-09-29 12:10:27','2026-09-29 12:10:27'),(22,'edit_fines','Edit Fines','fines','Edit Fines','2026-09-29 12:10:27','2026-09-29 12:10:27'),(23,'delete_fines','Delete Fines','fines','Delete Fines','2026-09-29 12:10:27','2026-09-29 12:10:27'),(24,'manage_fine_status','Manage Fine Status','fines','Manage Fine Status','2026-09-29 12:10:27','2026-09-29 12:10:27'),(25,'view_finance','View Finance','finance','View Finance','2026-09-29 12:10:27','2026-09-29 12:10:27'),(26,'create_finance','Create Finance','finance','Create Finance','2026-09-29 12:10:27','2026-09-29 12:10:27'),(27,'delete_finance','Delete Finance','finance','Delete Finance','2026-09-29 12:10:27','2026-09-29 12:10:27'),(28,'view_vcc','View Vcc','vcc','View Vcc','2026-09-29 12:10:28','2026-09-29 12:10:28'),(29,'manage_vcc','Manage Vcc','vcc','Manage Vcc','2026-09-29 12:10:28','2026-09-29 12:10:28'),(30,'view_polls','View Polls','polls','View Polls','2026-09-29 12:10:28','2026-09-29 12:10:28'),(31,'manage_polls','Manage Polls','polls','Manage Polls','2026-09-29 12:10:28','2026-09-29 12:10:28'),(32,'view_roles','View Roles','roles','View Roles','2026-09-29 12:10:28','2026-09-29 12:10:28'),(33,'manage_roles','Manage Roles','roles','Manage Roles','2026-09-29 12:10:28','2026-09-29 12:10:28'),(34,'manage_users','Manage Users','roles','Manage Users','2026-09-29 12:10:28','2026-09-29 12:10:28'),(35,'manage_notifications','Manage Notifications','notifications','Manage Notifications','2026-09-29 12:10:28','2026-09-29 12:10:28'),(36,'view_reports','View Reports','reports','View Reports','2026-09-29 12:10:28','2026-09-29 12:10:28'),(37,'export_reports','Export Reports','reports','Export Reports','2026-09-29 12:10:28','2026-09-29 12:10:28');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_edition_stats`
--

DROP TABLE IF EXISTS `player_edition_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_edition_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `edition_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `matches_played` int(11) NOT NULL DEFAULT 0,
  `innings_batted` int(11) NOT NULL DEFAULT 0,
  `total_runs` int(11) NOT NULL DEFAULT 0,
  `highest_score` int(11) NOT NULL DEFAULT 0,
  `balls_faced` int(11) NOT NULL DEFAULT 0,
  `not_outs` int(11) NOT NULL DEFAULT 0,
  `batting_average` decimal(8,2) NOT NULL DEFAULT 0.00,
  `batting_strike_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fifties` int(11) NOT NULL DEFAULT 0,
  `centuries` int(11) NOT NULL DEFAULT 0,
  `total_fours` int(11) NOT NULL DEFAULT 0,
  `total_sixes` int(11) NOT NULL DEFAULT 0,
  `hat_trick_sixes_count` int(11) NOT NULL DEFAULT 0,
  `five_sixes_in_over_count` int(11) NOT NULL DEFAULT 0,
  `innings_bowled` int(11) NOT NULL DEFAULT 0,
  `overs_bowled` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_wickets` int(11) NOT NULL DEFAULT 0,
  `total_maidens` int(11) NOT NULL DEFAULT 0,
  `runs_conceded` int(11) NOT NULL DEFAULT 0,
  `balls_bowled` int(11) NOT NULL DEFAULT 0,
  `best_bowling_wickets` int(11) NOT NULL DEFAULT 0,
  `best_bowling_runs` int(11) NOT NULL DEFAULT 0,
  `bowling_average` decimal(8,2) NOT NULL DEFAULT 0.00,
  `bowling_economy` decimal(8,2) NOT NULL DEFAULT 0.00,
  `hat_trick_wickets_count` int(11) NOT NULL DEFAULT 0,
  `five_wicket_hauls` int(11) NOT NULL DEFAULT 0,
  `total_catches` int(11) NOT NULL DEFAULT 0,
  `total_run_outs` int(11) NOT NULL DEFAULT 0,
  `total_stumpings` int(11) NOT NULL DEFAULT 0,
  `mvp_count` int(11) NOT NULL DEFAULT 0,
  `mvp_points` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_edition_stats_player_id_edition_id_unique` (`player_id`,`edition_id`),
  KEY `player_edition_stats_team_id_foreign` (`team_id`),
  KEY `player_edition_stats_edition_id_total_runs_index` (`edition_id`,`total_runs`),
  KEY `player_edition_stats_edition_id_total_wickets_index` (`edition_id`,`total_wickets`),
  KEY `player_edition_stats_edition_id_mvp_points_index` (`edition_id`,`mvp_points`),
  CONSTRAINT `player_edition_stats_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_edition_stats_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_edition_stats_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_edition_stats`
--

LOCK TABLES `player_edition_stats` WRITE;
/*!40000 ALTER TABLE `player_edition_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_edition_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_edition_teams`
--

DROP TABLE IF EXISTS `player_edition_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_edition_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `edition_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `jersey_number` varchar(10) DEFAULT NULL,
  `transfer_from_team_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_edition_teams_player_id_edition_id_unique` (`player_id`,`edition_id`),
  KEY `player_edition_teams_edition_id_foreign` (`edition_id`),
  KEY `player_edition_teams_team_id_edition_id_index` (`team_id`,`edition_id`),
  CONSTRAINT `player_edition_teams_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_edition_teams_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_edition_teams_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_edition_teams`
--

LOCK TABLES `player_edition_teams` WRITE;
/*!40000 ALTER TABLE `player_edition_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_edition_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_suspensions`
--

DROP TABLE IF EXISTS `player_suspensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_suspensions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `fine_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('suspension','ban') NOT NULL,
  `reason` varchar(191) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `matches_suspended` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `issued_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `player_suspensions_fine_id_foreign` (`fine_id`),
  KEY `player_suspensions_issued_by_foreign` (`issued_by`),
  KEY `player_suspensions_player_id_is_active_index` (`player_id`,`is_active`),
  CONSTRAINT `player_suspensions_fine_id_foreign` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `player_suspensions_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_suspensions_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_suspensions`
--

LOCK TABLES `player_suspensions` WRITE;
/*!40000 ALTER TABLE `player_suspensions` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_suspensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_team_editions`
--

DROP TABLE IF EXISTS `player_team_editions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_team_editions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `edition_id` bigint(20) unsigned NOT NULL,
  `is_captain` tinyint(1) NOT NULL DEFAULT 0,
  `is_vice_captain` tinyint(1) NOT NULL DEFAULT 0,
  `transfer_from_team` varchar(191) DEFAULT NULL,
  `transfer_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_team_editions_player_id_edition_id_unique` (`player_id`,`edition_id`),
  KEY `player_team_editions_edition_id_foreign` (`edition_id`),
  KEY `player_team_editions_team_id_edition_id_index` (`team_id`,`edition_id`),
  CONSTRAINT `player_team_editions_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_team_editions_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_team_editions_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=362 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_team_editions`
--

LOCK TABLES `player_team_editions` WRITE;
/*!40000 ALTER TABLE `player_team_editions` DISABLE KEYS */;
INSERT INTO `player_team_editions` VALUES (1,1,1,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(2,2,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(3,3,1,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(4,4,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(5,5,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(6,6,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(7,7,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(8,8,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(9,9,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(10,10,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(11,11,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(12,12,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(13,13,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(14,14,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(15,15,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(16,16,1,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(17,17,3,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(18,18,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(19,19,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(20,20,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(21,21,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(22,22,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(23,23,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(24,24,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(25,25,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(26,26,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(27,27,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(28,28,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(29,29,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(30,30,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(31,31,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(32,32,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(33,33,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(34,34,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(35,35,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(36,36,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(37,37,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(38,38,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(39,39,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(40,40,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(41,41,3,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(42,42,17,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(43,43,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(44,44,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(45,45,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(46,46,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(47,47,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(48,48,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(49,49,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(50,50,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(51,51,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(52,52,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(53,53,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(54,54,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(55,55,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(56,56,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(57,57,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(58,58,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(59,59,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(60,60,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(61,61,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(62,62,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(63,63,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(64,64,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(65,65,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(66,66,17,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(67,67,6,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(68,68,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(69,69,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(70,70,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(71,71,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(72,72,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(73,73,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(74,74,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(75,75,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(76,76,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(77,77,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(78,78,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(79,79,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(80,80,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(81,81,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(82,82,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(83,83,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(84,84,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(85,85,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(86,86,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(87,87,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(88,88,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(89,89,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(90,90,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(91,91,6,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(92,92,8,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(93,93,8,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(94,94,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(95,95,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(96,96,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(97,97,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(98,98,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(99,99,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(100,100,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(101,101,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(102,102,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(103,103,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(104,104,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(105,105,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(106,106,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(107,107,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(108,108,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(109,109,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(110,110,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(111,111,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(112,112,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(113,113,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(114,114,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(115,115,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(116,116,8,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(117,117,9,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(118,118,9,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(119,119,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(120,120,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(121,121,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(122,122,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(123,123,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(124,124,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(125,125,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(126,126,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(127,127,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(128,128,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(129,129,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(130,130,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(131,131,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(132,132,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(133,133,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(134,134,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(135,135,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(136,136,9,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(137,137,12,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(138,138,12,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(139,139,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(140,140,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(141,141,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(142,142,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(143,143,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(144,144,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(145,145,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(146,146,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(147,147,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(148,148,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(149,149,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(150,150,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(151,151,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(152,152,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(153,153,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(154,154,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(155,155,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(156,156,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(157,157,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(158,158,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(159,159,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(160,160,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(161,161,12,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(162,162,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(163,163,11,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(164,164,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(165,165,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(166,166,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(167,167,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(168,168,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(169,169,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(170,170,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(171,171,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(172,172,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(173,173,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(174,174,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(175,175,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(176,176,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(177,177,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(178,178,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(179,179,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(180,180,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(181,181,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(182,182,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(183,183,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(184,184,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(185,185,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(186,186,11,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(187,187,7,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(188,188,7,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(189,189,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(190,190,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(191,191,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(192,192,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(193,193,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(194,194,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(195,195,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(196,196,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(197,197,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(198,198,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(199,199,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(200,200,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(201,201,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(202,202,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(203,203,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(204,204,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(205,205,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(206,206,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(207,207,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(208,208,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(209,209,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(210,210,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(211,211,7,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(212,212,10,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(213,213,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(214,214,10,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(215,215,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(216,216,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(217,217,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(218,218,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(219,219,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(220,220,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(221,221,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(222,222,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(223,223,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(224,224,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(225,225,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(226,226,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(227,227,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(228,228,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(229,229,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(230,230,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(231,231,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(232,232,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(233,233,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(234,234,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(235,235,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(236,236,10,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(237,237,4,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(238,238,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(239,239,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(240,240,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(241,241,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(242,242,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(243,243,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(244,244,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(245,245,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(246,246,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(247,247,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(248,248,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(249,249,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(250,250,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(251,251,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(252,252,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(253,253,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(254,254,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(255,255,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(256,256,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(257,257,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(258,258,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(259,259,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(260,260,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(261,261,4,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(262,262,14,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(263,263,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(264,264,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(265,265,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(266,266,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(267,267,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(268,268,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(269,269,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(270,270,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(271,271,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(272,272,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(273,273,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(274,274,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(275,275,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(276,276,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(277,277,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(278,278,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(279,279,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(280,280,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(281,281,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(282,282,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(283,283,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(284,284,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(285,285,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(286,286,14,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(287,287,18,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(288,288,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(289,289,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(290,290,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(291,291,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(292,292,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(293,293,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(294,294,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(295,295,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(296,296,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(297,297,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(298,298,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(299,299,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(300,300,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(301,301,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(302,302,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(303,303,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(304,304,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(305,305,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(306,306,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(307,307,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(308,308,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(309,309,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(310,310,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(311,311,18,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(312,312,15,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(313,313,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(314,314,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(315,315,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(316,316,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(317,317,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(318,318,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(319,319,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(320,320,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(321,321,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(322,322,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(323,323,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(324,324,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(325,325,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(326,326,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(327,327,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(328,328,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(329,329,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(330,330,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(331,331,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(332,332,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(333,333,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(334,334,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(335,335,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(336,336,15,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(337,337,5,2,1,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(338,338,5,2,0,1,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(339,339,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(340,340,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(341,341,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(342,342,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(343,343,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(344,344,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(345,345,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(346,346,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(347,347,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(348,348,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(349,349,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(350,350,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(351,351,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(352,352,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(353,353,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(354,354,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(355,355,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(356,356,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(357,357,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(358,358,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(359,359,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(360,360,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(361,361,5,2,0,0,NULL,NULL,'2026-09-29 12:10:30','2026-09-29 12:10:30');
/*!40000 ALTER TABLE `player_team_editions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `father_name` varchar(191) DEFAULT NULL,
  `jersey_number` varchar(191) DEFAULT NULL,
  `photo` varchar(191) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('batsman','bowler','all_rounder','wicket_keeper') NOT NULL DEFAULT 'batsman',
  `batting_style` enum('right_hand','left_hand') NOT NULL DEFAULT 'right_hand',
  `bowling_style` enum('right_arm_fast','right_arm_medium','right_arm_spin','left_arm_fast','left_arm_medium','left_arm_spin','none') NOT NULL DEFAULT 'none',
  `bowling_action_status` enum('legal','flagged','banned') NOT NULL DEFAULT 'legal',
  `phone` varchar(191) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `players_name_is_active_index` (`name`,`is_active`),
  KEY `players_bowling_action_status_index` (`bowling_action_status`)
) ENGINE=InnoDB AUTO_INCREMENT=362 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES (1,'Sarfraz Goraya','Naaem Ullah','7',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(2,'Naseer Rajput','Bashir Ahmad','10',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(3,'Waseem Goraya','Altaf Goraya','18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(4,'Kaleem Goraya','Altaf Goraya','12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(5,'Ali Goraya','Abdul Razaq','9',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(6,'Sikandar Goraya','Akhtar Goraya','11',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(7,'Shareef Rajput','Nazar Hussain','14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(8,'Usman Qazi','Zafar','8',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(9,'Umar Goraya','Akhtar Goraya','99',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(10,'Saleem Rajput','Muneer Rajput','23',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(11,'Haider Goraya','Zafar Iqbal','17',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(12,'Qasim Rajput','Akbar','21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(13,'M Farooq','Tariq','77',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(14,'Bilal Asif','M Asif','33',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(15,'Ikram Rajput','Mansha','45',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(16,'Talha Goraya','Basharat Goraya','19',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(17,'Akhtar Abbas',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(18,'Zeeshan Manzoor',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(19,'Awais Lefty',NULL,'3',NULL,NULL,'all_rounder','left_hand','left_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(20,'Noman',NULL,'4',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(21,'Waqas J',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(22,'Waqas',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(23,'Faisal Rajpoot',NULL,'7',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(24,'Faisal',NULL,'8',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(25,'Umair',NULL,'9',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(26,'Ali Abbas',NULL,'10',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(27,'Rehman',NULL,'11',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(28,'Mujahid Manzoor',NULL,'12',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(29,'Salman Bajwa',NULL,'13',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(30,'Faizan',NULL,'14',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(31,'Sufyan',NULL,'15',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(32,'Zeeshan',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(33,'Ahmad',NULL,'17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(34,'Sohail',NULL,'18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(35,'Zain Wairrach',NULL,'19',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(36,'Zahid Manzoor',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(37,'Shahid Manzoor',NULL,'21',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(38,'Abdullah',NULL,'22',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(39,'Ali Raza',NULL,'23',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(40,'Ahsan',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(41,'Noman',NULL,'25',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(42,'Yaseen',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(43,'Muzammil',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(44,'Kashif',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(45,'Hafiz Abid',NULL,'4',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(46,'Rauf',NULL,'5',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(47,'Sajjad',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(48,'Naeem',NULL,'7',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(49,'Hafeez',NULL,'8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(50,'Zubair',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(51,'Arbab',NULL,'10',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(52,'Asad',NULL,'11',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(53,'Asif',NULL,'12',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(54,'Mazhar',NULL,'13',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(55,'Shan',NULL,'14',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(56,'Awais Sunny',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(57,'Nadeem',NULL,'16',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(58,'Mazhar S',NULL,'17',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(59,'Kafeel',NULL,'18',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(60,'Kashif',NULL,'19',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(61,'Waseem',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(62,'Niaz Ali',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(63,'Ansar',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(64,'Munawar',NULL,'23',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(65,'Malik Saleem',NULL,'24',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(66,'Khurram',NULL,'25',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(67,'Ali Zaman',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(68,'Momin Ali',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(69,'Umar Butt',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(70,'Asad',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(71,'Usama',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(72,'Tayyab Gill',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(73,'Yusaf',NULL,'7',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(74,'Ausaf',NULL,'8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(75,'Aman Gill',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(76,'Abdulrehman Jutt',NULL,'10',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(77,'Waqas Sahotra',NULL,'11',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(78,'Dilshad Mithu',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(79,'Haroon Shafique',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(80,'Sharoon Shafique',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(81,'Awais Mughal',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(82,'Saqib',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(83,'Zeeshan',NULL,'17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(84,'Sajid Bhati',NULL,'18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(85,'Abubakar Bhalo',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(86,'Salman',NULL,'20',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(87,'Bakar Mg',NULL,'21',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(88,'Abdullah',NULL,'22',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(89,'Abdullah Mg',NULL,'23',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(90,'Sufiyan Lefty',NULL,'24',NULL,NULL,'all_rounder','left_hand','left_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(91,'Jawad Butt',NULL,'25',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(92,'H. Tariq Gujjar',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(93,'Afzaal Goraya',NULL,'2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(94,'Abid Gill',NULL,'3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(95,'Asghar Raju',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(96,'Ateeq Grewal',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(97,'Ramzan Goraya',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(98,'Majid Gujjar',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(99,'Ahtisham Mirza',NULL,'8',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(100,'Adnan Mirza',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(101,'Zain Warraich',NULL,'10',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(102,'Mian Bilal',NULL,'11',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(103,'M. Umair Gill',NULL,'12',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(104,'M. Atif Gill',NULL,'13',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(105,'M. Faraz Gill',NULL,'14',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(106,'Asad Gill',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(107,'Rana Muneeb',NULL,'16',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(108,'M. Mahtab',NULL,'17',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(109,'Ali Javed',NULL,'18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(110,'Ali Azeem',NULL,'19',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(111,'Abdullah Mirza',NULL,'20',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(112,'Abdullah Wahla',NULL,'21',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(113,'M. Bilal Bhatti',NULL,'22',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(114,'M. Asif',NULL,'23',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(115,'Arham Gujjar',NULL,'24',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(116,'Usman Malanga',NULL,'25',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(117,'Dr Adil','Ghulam Murtaza','1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(118,'Touqeer Goraya','Arshad Ali','2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(119,'Atif Goraya','Ghulam Murtaza','3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(120,'Subhan Ali','M Nadeem','4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(121,'Talha Goraya','M Nadeem','5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(122,'Gufran Goraya','Asghar Ali','6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(123,'Zaman Ghuman','Abdul Kareem','7',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(124,'Mirza Bilal','Babar','8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(125,'Zeeshan Raja','Raja Salamat Ali','9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(126,'Muzammal Goraya','Anwar Ali','10',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(127,'Mirza Ismaeel','Mirza Iqbal','11',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(128,'Shoaib Iqbal','Iqbal Shaheen','12',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(129,'Malik Saleem','Latif','13',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(130,'Awais Goraya','Munawar Hussain','14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(131,'Asim Goraya','Munawar Hussain','15',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(132,'Umair Khalid','Khalid Saeed','16',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(133,'Rehman','Imran Raja','17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(134,'Mirza Sajid','Mirza Nawaz','18',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(135,'Mirza Ubaidullah','Mirza Nawaz','19',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(136,'Mirza Azeem','Mirza Iqbal','20',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(137,'H. Abdul Jabbar',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(138,'Jamshed Bombom',NULL,'2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(139,'Umar Billa',NULL,'3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(140,'Kamran Rajpoot',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(141,'Iftikhar Rajput',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(142,'Nadeem King',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(143,'Junaid Rajput',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(144,'Waqas',NULL,'8',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(145,'Shamsher',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(146,'Usman Rajpoot',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(147,'Haider Boss',NULL,'11',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(148,'Mr. Abrar',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(149,'Aslam King',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(150,'Khalid Rajpoot',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(151,'Asghar Ballu',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(152,'Yousaf Rajput',NULL,'16',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(153,'Abbas Rajpoot',NULL,'17',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(154,'Usman Junior',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(155,'Kaka',NULL,'19',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(156,'Arif Rajpoot',NULL,'20',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(157,'Shafiq Munna',NULL,'21',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(158,'Saleem Rajpoot',NULL,'22',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(159,'Waqar Rajpoot',NULL,'23',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(160,'Basit Rajpoot',NULL,'24',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(161,'Noman Rajpoot',NULL,'25',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:31','2026-09-29 12:10:31',NULL),(162,'Asghar Ali',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(163,'Najam Banto',NULL,'2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(164,'Husnain Khan',NULL,'3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(165,'Touseef',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(166,'Ali Khan',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(167,'Amjad Pathan',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(168,'Nouman',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(169,'Usman Haider',NULL,'8',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(170,'Abbas Khan',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(171,'Atif Bhuta',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(172,'Zeeshan Shani',NULL,'11',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(173,'Babar',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(174,'Hammad',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(175,'Tajmal',NULL,'14',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(176,'Mohsin',NULL,'15',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(177,'Riaz',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(178,'Shahid Imran',NULL,'17',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(179,'Amir',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(180,'Adeel',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(181,'Farman',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(182,'Fizan',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(183,'Mureed Sultan',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(184,'Ali Raza',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(185,'Ali Shahbaz',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(186,'Asif Ladi',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(187,'H. Naveed',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(188,'Sajjad',NULL,'2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(189,'Iqbal',NULL,'3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(190,'A. Hameed',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(191,'Iftikhar Q.',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(192,'Khalid',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(193,'Abid',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(194,'Mubashar',NULL,'8',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(195,'Murtaza',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(196,'Ali',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(197,'Saad',NULL,'11',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(198,'Mujeeb',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(199,'Farhan',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(200,'Shehzad',NULL,'14',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(201,'Bilal',NULL,'15',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(202,'Muzammal S.',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(203,'Hassan Ali',NULL,'17',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(204,'Umar',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(205,'Ali Hassan',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(206,'Faisal',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(207,'Umair Jutt',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(208,'Usman',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(209,'Fakhar',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(210,'Rafay Jutt',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(211,'Mujtaba',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(212,'Afrahim',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(213,'Arshid',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(214,'Nouman',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(215,'Usman',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(216,'Arfan',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(217,'Aun Lefty',NULL,'6',NULL,NULL,'all_rounder','left_hand','left_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(218,'Sagir',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(219,'Hussnain',NULL,'8',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(220,'Saleem',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(221,'Tanveer Sial',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(222,'Arif',NULL,'11',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(223,'Umer',NULL,'12',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(224,'Rehman',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(225,'Boota',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(226,'Tanveer Rajput',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(227,'Kashif',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(228,'Zeeshan J',NULL,'17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(229,'Zeeshan S',NULL,'18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(230,'Qasim',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(231,'Ch Arslan',NULL,'20',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(232,'Tahir',NULL,'21',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(233,'Shahbaz',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(234,'Kamran',NULL,'23',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(235,'Amanat Sial',NULL,'24',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(236,'Faizan',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(237,'Farooq Numberdar',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(238,'Jawad Ahmad Zia',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(239,'Talha DJ',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(240,'Zahid DJ',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(241,'Bilal Malang',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(242,'Ahmad Ali',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(243,'Asim Shah',NULL,'7',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(244,'Shan Ali Chak',NULL,'8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(245,'Shan Naseer',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(246,'Tahir Shah',NULL,'10',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(247,'Akmal',NULL,'11',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(248,'Bazail',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(249,'Shabbir Ali',NULL,'13',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(250,'Irfan Rath',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:32','2026-09-29 12:10:32',NULL),(251,'Faizan',NULL,'15',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(252,'Shahid Ali',NULL,'16',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(253,'Mohsin Ali',NULL,'17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(254,'Saqlain',NULL,'18',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(255,'Usama Munna',NULL,'19',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(256,'Ahad Ali',NULL,'20',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(257,'Akhtar Shah',NULL,'21',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(258,'Abdullah',NULL,'22',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(259,'Abdullah KC',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(260,'Usama Kaka',NULL,'24',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(261,'Shamaz',NULL,'25',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(262,'Muhammad Shohban',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(263,'Irfan Qadri',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(264,'Malik Wajid',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(265,'Shabir Chadhar',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(266,'Sharyar Khan',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(267,'Asad Jugg',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(268,'Ijaz Ahmad',NULL,'7',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(269,'Nosher Khan',NULL,'8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(270,'Razaq Khan',NULL,'9',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(271,'Sherzaman Khan',NULL,'10',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(272,'Rehman Khan',NULL,'11',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(273,'Nasir Khan',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(274,'Noor Khan',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(275,'Shabir Manj',NULL,'14',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(276,'Ali Raza',NULL,'15',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(277,'Sanaullah',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(278,'Malik Azhar',NULL,'17',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(279,'Malik Shahzaib',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(280,'Wajeh Ali',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(281,'Zainul Abedin',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(282,'Usman Daoud',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(283,'Mahr Saad',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(284,'Babu Khan',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(285,'Shahzad Khan',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(286,'Bodi Khan',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(287,'Zeeshan Sipra',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(288,'Sajjad Toor',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(289,'Abubakar Warraich',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(290,'Zaighum Cheema',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(291,'Hafiz Sadaqat',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(292,'Wajid Ali',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(293,'Shahzaib Toor',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(294,'Ali Hassan Cheema',NULL,'8',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(295,'Asim Cheema',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(296,'Aftab Sipra',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(297,'Asad Sipra',NULL,'11',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(298,'Asad Cheema',NULL,'12',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(299,'Kamran Cheema',NULL,'13',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(300,'Abdul Rehman Cheema',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(301,'Ali Hassan Cheema 2',NULL,'15',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(302,'Umair Cheema',NULL,'16',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(303,'Usman Butt',NULL,'17',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(304,'Tanzeb Haider',NULL,'18',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(305,'Hassan Bagri',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(306,'Mian Bilal',NULL,'20',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(307,'Jamshaid Sipra',NULL,'21',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(308,'Nadeem Andy',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(309,'Talal Butt',NULL,'23',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(310,'Nafy Toor',NULL,'24',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(311,'Ali Abdullah',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(312,'M. Khan Azad',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(313,'Mujahid Khan',NULL,'2',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(314,'Naeem Khan',NULL,'3',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(315,'Qaisar',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(316,'Sajid Nabia',NULL,'5',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(317,'Qazafi',NULL,'6',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(318,'Haji Qurban',NULL,'7',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(319,'Adnan',NULL,'8',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(320,'Aqib',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(321,'Salman',NULL,'10',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(322,'Asghar',NULL,'11',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(323,'Rafaqat Khan',NULL,'12',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(324,'Qaisar Junior',NULL,'13',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(325,'Salman Sallu',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(326,'Hasnain Gora',NULL,'15',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(327,'Numan',NULL,'16',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(328,'Saqlain',NULL,'17',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(329,'Safdar',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(330,'Butta',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(331,'Danish',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(332,'Affan',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(333,'Saqib',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(334,'Akash',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(335,'Zain',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(336,'Mateen',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(337,'Shahzad Kohli',NULL,'1',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(338,'Dastgeer Shah',NULL,'2',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(339,'Sajid Jutt',NULL,'3',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(340,'Hamza Malik',NULL,'4',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(341,'Akhtar Shah',NULL,'5',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(342,'Tahir Shah',NULL,'6',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(343,'Asim Shah',NULL,'7',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(344,'Shoaib Akhtar',NULL,'8',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(345,'Jabar',NULL,'9',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(346,'Ahmad',NULL,'10',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:33','2026-09-29 12:10:33',NULL),(347,'Abdul Rahman',NULL,'11',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(348,'Haider',NULL,'12',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(349,'Shamaz',NULL,'13',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(350,'Usman Ghani',NULL,'14',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(351,'Hussnain',NULL,'15',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(352,'Hamid Ali',NULL,'16',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(353,'Saqlain Shah',NULL,'17',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(354,'Asghar',NULL,'18',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(355,'Asad',NULL,'19',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(356,'Umer',NULL,'20',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(357,'Ali Haider',NULL,'21',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(358,'Umair',NULL,'22',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(359,'Zeeshan Ali',NULL,'23',NULL,NULL,'all_rounder','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(360,'Shan Naseer',NULL,'24',NULL,NULL,'batsman','right_hand','right_arm_medium','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL),(361,'Muneeb Mehar',NULL,'25',NULL,NULL,'bowler','right_hand','right_arm_fast','legal',NULL,NULL,1,'2026-09-29 12:10:34','2026-09-29 12:10:34',NULL);
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll_options`
--

DROP TABLE IF EXISTS `poll_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `option_text` varchar(191) NOT NULL,
  `option_image` varchar(191) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `poll_options_poll_id_foreign` (`poll_id`),
  CONSTRAINT `poll_options_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll_options`
--

LOCK TABLES `poll_options` WRITE;
/*!40000 ALTER TABLE `poll_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll_votes`
--

DROP TABLE IF EXISTS `poll_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `poll_option_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_token` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `poll_votes_poll_option_id_foreign` (`poll_option_id`),
  KEY `poll_votes_user_id_foreign` (`user_id`),
  KEY `poll_votes_poll_id_user_id_index` (`poll_id`,`user_id`),
  KEY `poll_votes_poll_id_ip_address_index` (`poll_id`,`ip_address`),
  CONSTRAINT `poll_votes_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_poll_option_id_foreign` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll_votes`
--

LOCK TABLES `poll_votes` WRITE;
/*!40000 ALTER TABLE `poll_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polls`
--

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `question` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `allow_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `polls_match_id_foreign` (`match_id`),
  KEY `polls_edition_id_is_active_index` (`edition_id`,`is_active`),
  CONSTRAINT `polls_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `polls_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `cricket_matches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polls`
--

LOCK TABLES `polls` WRITE;
/*!40000 ALTER TABLE `polls` DISABLE KEYS */;
/*!40000 ALTER TABLE `polls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (2,2,1),(1,2,5),(3,4,20),(4,4,21),(5,4,22),(6,4,23),(7,4,24),(8,4,25),(9,4,26),(10,4,27),(17,5,11),(18,5,12),(19,5,13),(20,5,14),(21,5,15),(22,5,16),(23,5,17),(24,5,18),(25,5,19),(16,6,15),(14,6,19),(15,6,20),(11,6,21),(12,6,22),(13,6,24);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `display_name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','super-admin','Super Admin','Full system access — all permissions granted.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28'),(2,'scorer','scorer','Match Scorer','Can access live scoring console only.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28'),(3,'umpire','umpire','Umpire','Assigned to matches as official umpire.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28'),(4,'treasurer','treasurer','Treasury Auditor','Finance & fine management access.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28'),(5,'team_manager','team-manager','Team Manager','Manage team roster and players.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28'),(6,'disciplinary_committee','disciplinary-committee','Disciplinary Committee','Manage fines and player suspensions.',1,'2026-09-29 12:10:28','2026-09-29 12:10:28');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'scoring_secret_key','&58+@@34AZ','2026-09-29 12:10:29','2026-09-29 12:10:29'),(2,'league_name','Royal Champions League','2026-09-29 12:10:29','2026-09-29 12:10:29'),(3,'governing_body','Village Cricket Council','2026-09-29 12:10:29','2026-09-29 12:10:29'),(4,'contact_email','info@rcl.local','2026-09-29 12:10:29','2026-09-29 12:10:29'),(5,'default_overs','10','2026-09-29 12:10:29','2026-09-29 12:10:29'),(6,'ball_fraction','0.17','2026-09-29 12:10:29','2026-09-29 12:10:29'),(7,'meeting_content','<h2 style=\"color:#0F5230;\">📢 RCL — Notice Board</h2>\n<p>Live scoring now runs entirely from the <strong>RCL mobile app</strong>. Match officials should:</p>\n<ol>\n  <li>Open the app and go to <strong>Score</strong>.</li>\n  <li>Enter the 10-character Scoring Passkey issued by the VCC office.</li>\n  <li>Pick the fixture, confirm the toss, and name both playing XIs.</li>\n  <li>Score ball by ball. The app keeps working with no signal and syncs when it reconnects.</li>\n  <li>Tap <strong>Finalize Match</strong> when play ends — stats and the points table update immediately.</li>\n</ol>\n<p style=\"color:#B45309;font-weight:600;\">The passkey is per-season. Do not share it outside the appointed scorers.</p>','2026-09-29 12:10:29','2026-09-29 12:10:29');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sponsors`
--

DROP TABLE IF EXISTS `sponsors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sponsors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `logo` varchar(191) DEFAULT NULL,
  `website` varchar(191) DEFAULT NULL,
  `tier` varchar(191) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sponsors`
--

LOCK TABLES `sponsors` WRITE;
/*!40000 ALTER TABLE `sponsors` DISABLE KEYS */;
INSERT INTO `sponsors` VALUES (1,'Mian Awais Sarwar Hanif','sponsors/mian_awais_sarwar_hanif.jpg',NULL,'title','Head Sponsor — Australia',1,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(2,'Master Faraz Ahmad Zia','sponsors/master_faraz_ahmad_zia.jpg',NULL,'title','Head Sponsor — Dubai, UAE',2,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(3,'Ch. Khalid Manzoor Gill','sponsors/ch_khalid_manzoor_gill.jpg',NULL,'title','Head Sponsor — Saudi Arabia (KSA)',3,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(4,'Waqar Shah Dil','sponsors/waqar_shah_dil.jpg',NULL,'title','Head Sponsor — South Africa',4,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(5,'Haji Tanveer Ahmad Rajpoot','sponsors/haji_tanveer_ahmad_rajpoot.jpg',NULL,'title','Founder & Head Sponsor — KSA',5,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(6,'Habib Akhtar Rajpoot','sponsors/habib_akhtar_rajpoot.jpg',NULL,'title','Chief Executive & Head Sponsor — KSA',6,1,'2026-09-29 12:10:30','2026-09-29 12:10:30'),(7,'Muhammad Abdullah Rajpoot','sponsors/m_abdullah_rajpoot.jpg',NULL,'title','Head Sponsor — Village 418',7,1,'2026-09-29 12:10:30','2026-09-29 12:10:30');
/*!40000 ALTER TABLE `sponsors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `village_name` varchar(191) NOT NULL,
  `short_code` varchar(5) NOT NULL,
  `logo` varchar(191) DEFAULT NULL,
  `cover_photo` varchar(191) DEFAULT NULL,
  `primary_color` varchar(7) NOT NULL DEFAULT '#1a1a2e',
  `secondary_color` varchar(7) NOT NULL DEFAULT '#16213e',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_name_village_name_index` (`name`,`village_name`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Sarja Sixer','Village 418','418',NULL,NULL,'#1e3a8a','#3b82f6','Sarja Sixer Cricket Club — Village 418',1,'2026-09-29 12:10:29','2026-09-29 12:10:30',NULL),(2,'Team 417','Village 417','417',NULL,NULL,'#831843','#ec4899',NULL,1,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(3,'Friends Cricket Club','Village 353','353',NULL,NULL,'#044728','#d4af37','Official Umpires: Awais, Faisal | Scorers: Faisal, Noman',1,'2026-09-29 12:10:29','2026-09-29 12:10:30',NULL),(4,'Azad Cricket Club','Village 348A','348A',NULL,NULL,'#701a75','#d946ef','Azad Cricket Club — Village 348A',1,'2026-09-29 12:10:29','2026-09-29 12:10:32',NULL),(5,'Maqbulpur Strikers','Village 348S','348S',NULL,NULL,'#7c2d12','#f97316','Maqbulpur Strikers — Village 348S',1,'2026-09-29 12:10:29','2026-09-29 12:10:33',NULL),(6,'Qadirabad Cricket Club','Village 354','354',NULL,NULL,'#0d9488','#14b8a6','Qadirabad Cricket Club — Village 354',1,'2026-09-29 12:10:29','2026-09-29 12:10:31',NULL),(7,'United Cricket Club','Village 355','355',NULL,NULL,'#312e81','#6366f1','United Cricket Club — Village 355',1,'2026-09-29 12:10:29','2026-09-29 12:10:32',NULL),(8,'KCC 356 JB','Village 356 JB','356',NULL,NULL,'#713f12','#eab308','KCC 356 JB Cricket Club — Village 356 JB',1,'2026-09-29 12:10:29','2026-09-29 12:10:31',NULL),(9,'Goraya Cricket Club','Village 419','419',NULL,NULL,'#064e3b','#10b981','Goraya Cricket Club — Village 419 | Umpires: Adil, Touqeer, Zaman | Scorers: Atif, Talha, Subhan',1,'2026-09-29 12:10:29','2026-09-29 12:10:31',NULL),(10,'Syed Jalal Cricket Club','Village 420','420',NULL,NULL,'#881337','#f43f5e','Syed Jalal Cricket Club — Village 420',1,'2026-09-29 12:10:29','2026-09-29 12:10:32',NULL),(11,'Al Haider Cricket Club','Village 421','421',NULL,NULL,'#1e1b4b','#4f46e5','Al Haider Cricket Club — Village 421',1,'2026-09-29 12:10:29','2026-09-29 12:10:32',NULL),(12,'Al Sadiq Cricket Club','Village 426','426',NULL,NULL,'#365314','#84cc16','Al Sadiq Cricket Club — Village 426',1,'2026-09-29 12:10:29','2026-09-29 12:10:31',NULL),(13,'Team 213','Village 213','213',NULL,NULL,'#0c4a6e','#0ea5e9',NULL,1,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(14,'786 GM Cricket Club','Village 786GM','786GM',NULL,NULL,'#4c1d95','#8b5cf6','786 GM Cricket Club — Village 786GM',1,'2026-09-29 12:10:29','2026-09-29 12:10:33',NULL),(15,'Sholah Cricket Club','Village 183','183',NULL,NULL,'#78350f','#f59e0b','Sholah Cricket Club — Village 183',1,'2026-09-29 12:10:29','2026-09-29 12:10:33',NULL),(16,'Team 214G','Village 214G','214G',NULL,NULL,'#0f766e','#2dd4bf',NULL,1,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(17,'Shaheen Cricket Club','Village 449','449',NULL,NULL,'#b91c1c','#f97316','Shaheen Cricket Club — Village 449',1,'2026-09-29 12:10:29','2026-09-29 12:10:30',NULL),(18,'Ghazi Cricket Club','Village 305','305',NULL,NULL,'#0f172a','#334155','Ghazi Cricket Club — Village 305',1,'2026-09-29 12:10:29','2026-09-29 12:10:33',NULL),(19,'Winner Pool A (A1)','Pool A','A1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(20,'Runner-up Pool D (D2)','Pool D','D2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(21,'Runner-up Pool A (A2)','Pool A','A2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(22,'Winner Pool D (D1)','Pool D','D1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(23,'Winner Pool B (B1)','Pool B','B1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(24,'Runner-up Pool C (C2)','Pool C','C2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(25,'Runner-up Pool B (B2)','Pool B','B2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(26,'Winner Pool C (C1)','Pool C','C1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(27,'Winner QF 1 (A1 vs D2)','Knockout','W_QF1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(28,'Winner QF 3 (B1 vs C2)','Knockout','W_QF3',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(29,'Winner QF 2 (A2 vs D1)','Knockout','W_QF2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(30,'Winner QF 4 (B2 vs C1)','Knockout','W_QF4',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(31,'Winner 1st Semifinal','Knockout','W_SF1',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL),(32,'Winner 2nd Semifinal','Knockout','W_SF2',NULL,NULL,'#1a1a2e','#16213e',NULL,0,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL);
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `google_id` varchar(191) DEFAULT NULL,
  `avatar` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `admin_hash` varchar(191) DEFAULT NULL COMMENT 'Unique hash for admin URL access',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_admin_hash_unique` (`admin_hash`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_email_is_active_index` (`email`,`is_active`),
  KEY `users_admin_hash_index` (`admin_hash`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'RCL Super Admin','superadmin@rcl.com','2026-09-29 12:10:29','$2y$12$lB6JolBD7m75l5gC91m2Mewgs3/rfctOWbDpkqOEQ.QFY9OVFfNPG',NULL,NULL,NULL,NULL,1,1,NULL,'2026-09-29 12:10:29','2026-09-29 12:10:29',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vcc_cabinets`
--

DROP TABLE IF EXISTS `vcc_cabinets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vcc_cabinets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `role_title` varchar(191) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(191) DEFAULT NULL,
  `village` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vcc_cabinets`
--

LOCK TABLES `vcc_cabinets` WRITE;
/*!40000 ALTER TABLE `vcc_cabinets` DISABLE KEYS */;
INSERT INTO `vcc_cabinets` VALUES (1,'Muhammad Tanveer Rajpoot','Founder',NULL,'cabinet/m_tanveer_rajpoot.jpg',NULL,NULL,1,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(2,'Naseer Ahmad Rajpoot','Patron-in-Chief',NULL,'cabinet/naseer_ahmad_rajpoot.jpg',NULL,NULL,2,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(3,'Habib Akhtar Rajpoot','Chief Executive Officer (CEO)',NULL,'cabinet/habib_akhtar_rajpoot.jpg',NULL,NULL,3,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(4,'Muhammad Iqbal Gajja','President',NULL,'cabinet/m_iqbal_gajja.jpg',NULL,NULL,4,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(5,'Muhammad Farooq Numberdar','Vice President',NULL,'cabinet/m_farooq_numberdar.jpg',NULL,NULL,5,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(6,'Husnain Khan Sial','Chairman',NULL,'cabinet/husnain_khan_sial.jpg',NULL,NULL,6,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(7,'Chaudhry Zain Warraich','Vice Chairman',NULL,'cabinet/ch_zain_warraich.jpg',NULL,NULL,7,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(8,'Chaudhry Sarfraz Goraya','Secretary General',NULL,'cabinet/ch_sarfraz_goraya.jpg',NULL,NULL,8,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(9,'Waqas Lail','Finance Secretary',NULL,'cabinet/waqas_lail.jpg',NULL,NULL,9,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(10,'Abid Gill','Chairman Supreme Council',NULL,'cabinet/abid_gill.jpg',NULL,NULL,10,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(11,'Muhammad Awais Sunny','Director General',NULL,'cabinet/m_awais_sunny.jpg',NULL,NULL,11,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(12,'Arshad Rajpoot','Supreme Body Member',NULL,'cabinet/arshad_rajpoot.jpg',NULL,NULL,12,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(13,'Muhammad Afrahim Sargana','Supreme Body Member',NULL,'cabinet/afrahim_sargana.jpg',NULL,NULL,13,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(14,'Momin Warraich','Supreme Body Member',NULL,'cabinet/momin_warraich.jpg',NULL,NULL,14,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(15,'Sajjad Gujjar','Supreme Body Member',NULL,'cabinet/sajjad_gujjar.jpg',NULL,NULL,15,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(16,'Tanveer Sial','Media Coordinator',NULL,'cabinet/tanveer_sial.jpg',NULL,NULL,16,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(17,'Zahid Baloch','Broadcaster',NULL,'cabinet/zahid_baloch.jpg',NULL,NULL,17,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(18,'Akbar Akki','Commentator',NULL,'cabinet/akbar_akki.jpg',NULL,NULL,18,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(19,'Iftikhar Thakur','Cameraman',NULL,'cabinet/iftikhar_thakur.jpg',NULL,NULL,19,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(20,'Mian Awais Sarwar Hanif','Overseas Patron & Head Sponsor',NULL,'cabinet/mian_awais_sarwar_hanif.jpg','Australia',NULL,20,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(21,'Master Faraz Ahmad Zia','Overseas Patron & Head Sponsor',NULL,'cabinet/master_faraz_ahmad_zia.jpg','Dubai, UAE',NULL,21,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(22,'Ch. Khalid Manzoor Gill','Overseas Patron & Head Sponsor',NULL,'cabinet/ch_khalid_manzoor_gill.jpg','Saudi Arabia (KSA)',NULL,22,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(23,'Waqar Shah Dil','Overseas Patron & Head Sponsor',NULL,'cabinet/waqar_shah_dil.jpg','South Africa',NULL,23,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL),(24,'Muhammad Abdullah Rajpoot','Patron & Head Sponsor',NULL,'cabinet/m_abdullah_rajpoot.jpg','Village 418',NULL,24,1,'2026-09-29 12:10:30','2026-09-29 12:10:30',NULL);
/*!40000 ALTER TABLE `vcc_cabinets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-29 23:47:39
