/*
MySQL Data Transfer
Source Host: localhost
Source Database: silex
Target Host: localhost
Target Database: silex
Date: 7/10/2012 3:54:39 PM
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for slx_administrator
-- ----------------------------
CREATE TABLE `slx_administrator` (
  `administrator_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `fb_uid` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`administrator_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_campaigns
-- ----------------------------
CREATE TABLE `slx_campaigns` (
  `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `startdate` datetime NOT NULL,
  `enddate` datetime NOT NULL,
  `upload_enddate` datetime NOT NULL,
  `selectiondate` datetime NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`campaign_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_customer_campaigns
-- ----------------------------
CREATE TABLE `slx_customer_campaigns` (
  `customer_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  PRIMARY KEY (`customer_id`,`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_customer_fbrel
-- ----------------------------
CREATE TABLE `slx_customer_fbrel` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `fb_uid` bigint(20) NOT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `fb_uid` (`fb_uid`),
  CONSTRAINT `slx_customer_fbrel_ibfk_2` FOREIGN KEY (`fb_uid`) REFERENCES `slx_facebook_auth` (`fb_uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_customer_items
-- ----------------------------
CREATE TABLE `slx_customer_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `summary` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`customer_id`,`campaign_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `slx_customer_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `slx_customer_fbrel` (`customer_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_customer_profile
-- ----------------------------
CREATE TABLE `slx_customer_profile` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_facebook_auth
-- ----------------------------
CREATE TABLE `slx_facebook_auth` (
  `fb_uid` bigint(20) NOT NULL DEFAULT '0',
  `access_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`fb_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_facebook_feed
-- ----------------------------
CREATE TABLE `slx_facebook_feed` (
  `feedID` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `feed_tag` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `description` text,
  `picture` varchar(200) DEFAULT NULL,
  `link` varchar(200) DEFAULT NULL,
  `json_value` text,
  PRIMARY KEY (`feedID`),
  UNIQUE KEY `feed_tag` (`feed_tag`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `slx_facebook_feed_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `slx_campaigns` (`campaign_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for slx_pages
-- ----------------------------
CREATE TABLE `slx_pages` (
  `page_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `summary` text,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `slx_campaigns` VALUES ('1', 'Campaign uji coba', '2012-07-10 00:00:00', '2012-08-16 00:00:00', '2012-10-01 00:00:00', '2012-12-04 08:00:00', 'active');
INSERT INTO `slx_campaigns` VALUES ('2', 'testset', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', 'pending');
INSERT INTO `slx_campaigns` VALUES ('3', 'asdfasdaf', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', 'pending');
INSERT INTO `slx_campaigns` VALUES ('4', 'test lagi deh ed', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', '2007-01-01 00:00:00', 'pending');
INSERT INTO `slx_customer_fbrel` VALUES ('1', '730189516');
INSERT INTO `slx_facebook_auth` VALUES ('730189516', 'aldhflaksdjflskjdfhklasdbjf');
