# Sequel Pro dump
# Version 254
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.0.67)
# Database: reznap_development
# Generation Time: 2009-01-02 19:18:43 +0200
# ************************************************************

# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

INSERT INTO `categories` (`id`,`name`,`description`)
VALUES
	(1,'info',''),
	(2,'stuff','');



# Dump of table categories_pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories_pages`;

CREATE TABLE `categories_pages` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

INSERT INTO `categories_pages` (`id`,`category_id`,`page_id`)
VALUES
	(1,1,2),
	(2,2,2),
	(3,1,1);



# Dump of table comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `comments` (`id`,`name`,`email`,`url`,`body`,`page_id`)
VALUES
	(1,'jim','','','fuck this is cool',2),
	(2,'peter','ithinkicanfly@petrelli.com','','you suck! you really suck!!',2);



# Dump of table pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `body` text NOT NULL,
  `created_at` int(11) default NULL,
  `modified_at` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

INSERT INTO `pages` (`id`,`title`,`body`,`created_at`,`modified_at`)
VALUES
	(1,'home page','wiiee, this is the HOME page, thingy... kinda... lol!',NULL,NULL),
	(2,'another page :P','this is yet another page-thingy, this is cool... hehe :D :P',NULL,NULL),
	(3,'my page','my page is awesome, so awesome :$',NULL,NULL),
	(4,'iphone','omg omg omg omg O M G ! ! ! !',NULL,NULL),
	(10,'title','body',NULL,NULL),
	(11,'anything else','...maybe not...',NULL,NULL);



