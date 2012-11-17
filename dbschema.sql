-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Palvelin: localhost
-- Luontiaika: 03.05.2012 klo 15:22
-- Palvelimen versio: 5.5.23
-- PHP:n versio: 5.4.1-1~dotdeb.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Tietokanta: `northpole`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `admin_messages`
--

CREATE TABLE IF NOT EXISTS `admin_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `sent` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `receiver` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `is_read` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`,`is_read`),
  KEY `receiver_2` (`receiver`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `admin_users`
--

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` text COLLATE utf8_unicode_ci NOT NULL,
  `user_class` int(1) NOT NULL,
  `last_ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `password` (`password`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `ads`
--

CREATE TABLE IF NOT EXISTS `ads` (
  `category` int(11) NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `start_time` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  `reason` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `staff_note` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `can_read` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `banned_by` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `is_old` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `session` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL,
  `international` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `pages` int(11) NOT NULL DEFAULT '15',
  `locked` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `worksafe` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ad_category` int(1) NOT NULL DEFAULT '1' COMMENT '0 = no ads, 1-n = show ads',
  `namefield` enum('2','1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '2',
  `default_name` text COLLATE utf8_unicode_ci NOT NULL,
  `show_empty_names` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `default_style` text COLLATE utf8_unicode_ci NOT NULL,
  `hide_sidebar` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `osoite` (`url`),
  KEY `category` (`category`),
  KEY `worksafe` (`worksafe`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `cache_dns`
--

CREATE TABLE IF NOT EXISTS `cache_dns` (
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `host` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ip`),
  KEY `time` (`time`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `cache_other`
--

CREATE TABLE IF NOT EXISTS `cache_other` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `content` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`),
  KEY `time` (`time`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `cache_proxydetection`
--

CREATE TABLE IF NOT EXISTS `cache_proxydetection` (
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `port` int(5) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`),
  KEY `time` (`time`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `embed_sources`
--

CREATE TABLE IF NOT EXISTS `embed_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `video_url` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `code` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `help` text COLLATE utf8_unicode_ci NOT NULL,
  `sfw` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Safe for work',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `errorlog`
--

CREATE TABLE IF NOT EXISTS `errorlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `headers` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `ip` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orig_name` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `extension` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `thumb_ext` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `thumb_width` int(11) NOT NULL,
  `thumb_height` int(11) NOT NULL,
  `mime` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `information` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `md5` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `duplicate_of` int(11) NOT NULL DEFAULT '0',
  `id3_name` text COLLATE utf8_unicode_ci NOT NULL,
  `id3_artist` text COLLATE utf8_unicode_ci NOT NULL,
  `id3_length` text COLLATE utf8_unicode_ci NOT NULL,
  `id3_bitrate` text COLLATE utf8_unicode_ci NOT NULL,
  `id3_image` tinyint(1) NOT NULL DEFAULT '0',
  `folder` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5` (`md5`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `filetypes`
--

CREATE TABLE IF NOT EXISTS `filetypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `mime` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `image` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `extension` (`extension`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `follow`
--

CREATE TABLE IF NOT EXISTS `follow` (
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `thread` int(11) NOT NULL,
  KEY `uid` (`uid`),
  KEY `thread` (`thread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `fp_categories`
--

CREATE TABLE IF NOT EXISTS `fp_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `fp_posts`
--

CREATE TABLE IF NOT EXISTS `fp_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `added_by` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Posts in front page (rules, faq etc)' ;

-- --------------------------------------------------------

--
-- Rakenne taululle `hide`
--

CREATE TABLE IF NOT EXISTS `hide` (
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `thread` int(11) NOT NULL,
  KEY `uid` (`uid`),
  KEY `thread` (`thread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `hide_boards`
--

CREATE TABLE IF NOT EXISTS `hide_boards` (
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `board` int(11) NOT NULL,
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `modlog`
--

CREATE TABLE IF NOT EXISTS `modlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mod_id` int(11) NOT NULL,
  `action` int(11) NOT NULL,
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `mod_announcements`
--

CREATE TABLE IF NOT EXISTS `mod_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `added_by` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board` int(11) NOT NULL,
  `thread` int(11) NOT NULL DEFAULT '0',
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip_plain` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `proxy` int(1) NOT NULL DEFAULT '0' COMMENT '1 = proxy, 2 = tor',
  `geoip_country_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `geoip_country_name` text COLLATE utf8_unicode_ci NOT NULL,
  `geoip_region_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `geoip_region_name` text COLLATE utf8_unicode_ci NOT NULL,
  `geoip_city` text COLLATE utf8_unicode_ci NOT NULL,
  `geoip_lat` text COLLATE utf8_unicode_ci NOT NULL,
  `geoip_lon` text COLLATE utf8_unicode_ci NOT NULL,
  `name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `tripcode` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `posted_by_op` int(1) NOT NULL DEFAULT '0',
  `modpost` int(1) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `subject` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `embed_source` int(11) NOT NULL,
  `embed_code` text COLLATE utf8_unicode_ci NOT NULL,
  `sage` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `rage` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `love` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `bump_time` int(11) NOT NULL DEFAULT '0',
  `locked` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `sticky` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `deleted_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `locked` (`locked`),
  KEY `sticky` (`sticky`),
  KEY `thread` (`thread`),
  KEY `board` (`board`),
  KEY `ip` (`ip`),
  KEY `time` (`time`),
  KEY `bump_time` (`bump_time`),
  KEY `deleted_time` (`deleted_time`),
  FULLTEXT KEY `subject` (`subject`,`message`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `post_files`
--

CREATE TABLE IF NOT EXISTS `post_files` (
  `postid` int(11) NOT NULL,
  `fileid` int(11) NOT NULL,
  `order` int(1) NOT NULL DEFAULT '0',
  KEY `postid` (`postid`),
  KEY `fileid` (`fileid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `reason` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `reported_by` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Rakenne taululle `sidebar_links`
--

CREATE TABLE IF NOT EXISTS `sidebar_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Rakenne taululle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `online` int(1) NOT NULL DEFAULT '1',
  `last_load` int(11) NOT NULL,
  `last_page` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `site_style` text COLLATE utf8_unicode_ci NOT NULL,
  `show_sidebar` enum('2','1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '2',
  `show_postform` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `save_scroll` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `sfw` int(1) NOT NULL DEFAULT '1' COMMENT 'Safe For Work',
  `locale` text COLLATE utf8_unicode_ci NOT NULL,
  `timezone` text COLLATE utf8_unicode_ci NOT NULL,
  `post_password` text COLLATE utf8_unicode_ci NOT NULL,
  `post_name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `post_noko` int(1) NOT NULL DEFAULT '0',
  `hide_names` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `autoload_media` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `autoplay_gifs` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `hide_region` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `hide_ads` int(1) NOT NULL DEFAULT '0',
  `hide_browserwarning` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `uname` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `uname` (`uname`),
  KEY `online` (`online`),
  KEY `last_load` (`last_load`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
