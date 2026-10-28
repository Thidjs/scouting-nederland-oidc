CREATE TABLE IF NOT EXISTS `#__oidc_identities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `issuer` varchar(191)
    CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `subject` varchar(191)
    CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `member_id` varchar(150) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_oidc_issuer_subject` (`issuer`,`subject`),
  UNIQUE KEY `uq_oidc_server_member` (`server_id`,`member_id`),
  UNIQUE KEY `uq_oidc_user` (`user_id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
