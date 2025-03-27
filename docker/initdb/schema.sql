CREATE DATABASE IF NOT EXISTS `pokejob` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pokejob`;

DROP TABLE IF EXISTS `company_counter`;
CREATE TABLE `company_counter` (
  `count` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
INSERT INTO `company_counter` (`count`) VALUES (2);

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(50) DEFAULT 'Non lu',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `company_name` VARCHAR(100) NOT NULL,
  `contact_name` VARCHAR(100) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `contact_mail` VARCHAR(100) DEFAULT NULL,
  `link_annonce` VARCHAR(255) DEFAULT NULL,
  `link_linkedin` VARCHAR(255) DEFAULT NULL,
  `date_applied` DATE DEFAULT NULL,
  `date_relance` DATE DEFAULT NULL,
  `notes_perso` TEXT,
  `company_website` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('JE_POSTULE','POSTULE','RELANCE','PREPARATION_ENTRETIEN','ENTRETIEN_REALISE','REFUSE') DEFAULT 'POSTULE',
  `type_candidature` ENUM('SPONTANEE','ANNONCE') DEFAULT 'SPONTANEE',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `jobs` (`id`, `user_id`, `company_name`, `link_annonce`, `status`, `type_candidature`, `created_at`, `updated_at`) VALUES
(177,20,'O2 Care Services','https://fr.indeed.com/jobs?q=O2+Care+Services&l=Carpentras','POSTULE','SPONTANEE','2025-03-13 21:33:55','2025-03-14 23:41:52'),
(176,20,'Lacoste','https://fr.indeed.com/jobs?q=lacoste&l=Carpentras','POSTULE','SPONTANEE','2025-03-13 21:31:59','2025-03-14 23:20:22'),
(175,20,'Relais Vert','https://www.relais-vert.com/carriere','POSTULE','SPONTANEE','2025-03-13 21:30:21','2025-03-15 02:10:40'),
(178,20,'HT TRANSPORT','https://fr.indeed.com/jobs?q=HT+TRANSPORT','POSTULE','SPONTANEE','2025-03-13 21:35:36','2025-03-15 01:42:51'),
(179,20,'Azaé Carpentras','https://fr.indeed.com/jobs?q=Aza%C3%A9+Carpentras','POSTULE','SPONTANEE','2025-03-13 21:36:18','2025-03-17 22:01:23'),
(180,20,'JD Sports','https://fr.indeed.com/jobs?q=JD+Sports','POSTULE','SPONTANEE','2025-03-13 21:36:38','2025-03-19 00:25:08'),
(186,20,'Algovital',NULL,'REFUSE','SPONTANEE','2025-03-17 15:09:14','2025-03-17 22:01:19'),
(183,20,'Ressource','https://fr.indeed.com/jobs?q=ressources84','POSTULE','SPONTANEE','2025-03-14 01:41:40','2025-03-14 23:23:01'),
(184,20,'La Parenthèse','https://fr.indeed.com/jobs?q=veilleur+de+nuit','POSTULE','SPONTANEE','2025-03-14 19:48:16','2025-03-17 22:30:17'),
(185,20,'RGIS',NULL,'POSTULE','SPONTANEE','2025-03-15 01:32:30','2025-03-18 10:12:56'),
(194,25,'Microsoft',NULL,'POSTULE','SPONTANEE','2025-03-19 21:05:41','2025-03-19 21:05:43');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('USER','ADMIN') DEFAULT 'USER',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `photo` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`,`first_name`,`last_name`,`email`,`password`,`role`,`created_at`,`photo`) VALUES
(2,'Super','Admin','admin@pokejob.com','$2y$10$pXhwyWM5eJZM1FfA49IZbeWxoebcbMoPMidE4W1O0H7nGTMdpDWXK','ADMIN','2025-03-02 17:22:10',NULL),
(20,'Hamza','Kachmir','hamza.kachmir@icloud.com','$2y$10$7mfAi1cvoFCGVRlbpzcQtOidx0ONGsywHRABDsS0GX9pdfL2rSRWO','USER','2025-03-04 03:16:26',NULL),
(25,'Hamid','Jackson','hamid.jackson@icloud.com','$2y$10$KC1iZ08Jr8/BqHn.d60NruV1w/nmP4pdmSk3VrmugYYwVQuYE9W3K','USER','2025-03-19 21:05:11',NULL);
