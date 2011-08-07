CREATE TABLE IF NOT EXISTS `movies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `netflix_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  `year` char(4) COLLATE utf8_unicode_ci NOT NULL,
  `mpaa_rating` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `categories` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `user_rating` float NOT NULL,
  `box_art` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `ownerships` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `movie_id` int(11) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
