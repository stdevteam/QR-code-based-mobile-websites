/*
SQLyog Enterprise - MySQL GUI v8.05 
MySQL - 5.5.8 : Database - vendingqr
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`vendingqr` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `vendingqr`;

/*Table structure for table `analytics` */

DROP TABLE IF EXISTS `analytics`;

CREATE TABLE `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `projectId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `pageId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `analytics` */

/*Table structure for table `codeqrs` */

DROP TABLE IF EXISTS `codeqrs`;

CREATE TABLE `codeqrs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `serialNo` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `imagePath` varchar(255) NOT NULL,
  `shortUrl` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `codeqrs` */

insert  into `codeqrs`(`id`,`projectId`,`userId`,`serialNo`,`location`,`imagePath`,`shortUrl`) values (6,1,295,'68678678678678','xcvxcvxcvxcvx','6','5a0966f3ce'),(7,1,295,'353453','fsdfsdsd','7','97ed444187'),(8,1,295,'789456','asddfg','8.png','dcbc1b8b85'),(9,1,295,'1111111111111111111111111','california los angeles','9.png','ed91776575'),(10,1,295,'1223344565667','armenia erevan','10.png','120b3263d9'),(11,1,295,'555555555555555555555555','aaaaaaaaaaaaaaaaaaaaaaa','11.png','201ac457ed');

/*Table structure for table `mobile_sites` */

DROP TABLE IF EXISTS `mobile_sites`;

CREATE TABLE `mobile_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fbPage` varchar(255) DEFAULT NULL,
  `twitPage` varchar(255) DEFAULT NULL,
  `phNumber` varchar(30) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `projectId` int(11) DEFAULT NULL,
  `active` int(3) unsigned DEFAULT NULL,
  `template` varchar(50) DEFAULT NULL,
  `showNumber` int(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `mobile_sites` */

insert  into `mobile_sites`(`id`,`fbPage`,`twitPage`,`phNumber`,`userId`,`projectId`,`active`,`template`,`showNumber`) values (1,'111111111111111111111111','44444','3333',295,1,NULL,NULL,NULL),(7,'ffghfhfh456456','fghfghff454','fhfhfghf45645',295,4,NULL,NULL,NULL),(8,'7242424','4545454532','4545454',295,8,NULL,NULL,NULL);

/*Table structure for table `projects` */

DROP TABLE IF EXISTS `projects`;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `projects` */

insert  into `projects`(`id`,`userID`,`name`,`description`) values (1,295,'my project','blaablablablalablabablaa'),(4,295,'old','newewewwewewewewew5434545464646'),(8,295,'test','teteeteteteette');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) DEFAULT NULL,
  `intrested_in` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `verification` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=315 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`id`,`first_name`,`last_name`,`email`,`password`,`intrested_in`,`created`,`active`,`verification`,`is_admin`) values (295,'karo','stdev','karo@stdev.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-02-29 06:08:57',1,'d61459b41a8c48513ac0d9b83618669d',1),(296,'Test','StDev','test@stdev.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-02-29 06:13:46',1,'5c974906671d3d9320d503cb59b045cb',0),(297,'ooo','ooo','ooo@masil.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-02-29 06:23:03',1,'b70cb153a92ba34ced5485362d64fbcf',0),(309,'sdfdssdf','sdfsdf','sdfsdfsd@dsdfsds.rt','643886443edf0c4b41623af2fc83fb52',NULL,'2012-03-02 04:49:18',1,'b61ef2c2d40761eacab3919726379f25',0),(312,'asdasdas','jdfjgkdljgkdjlfg`','asadsdssda@adadaaaasdsaas`daa.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-03-02 04:56:48',1,'d07638f5976cb4b39b93441938448c43',0),(311,'asdasdas','jdfjgkdljgkdjlfg`','asadsdssda@adadaaaasdsaas`daa.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-03-02 04:56:07',1,'d07638f5976cb4b39b93441938448c43',0),(310,'asdasdas','jdfjgkdljgkdjlfg`','asadsdssda@adadaaaasdsaas`daa.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-03-02 04:54:09',1,'d07638f5976cb4b39b93441938448c43',0),(313,'asdasdas','jdfjgkdljgkdjlfg`','asadsdssda@adadaaaasdsaas`daa.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-03-02 04:57:12',1,'d07638f5976cb4b39b93441938448c43',0),(314,'asdasdas','jdfjgkdljgkdjlfg`','asadsdssda@adadaaaasdsaas`daa.com','6c83635c41584b0959a9f5ad31488977',NULL,'2012-03-02 04:57:51',1,'d07638f5976cb4b39b93441938448c43',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
