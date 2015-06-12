CREATE DATABASE  IF NOT EXISTS `blog` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `blog`;
-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: localhost    Database: blog
-- ------------------------------------------------------
-- Server version	5.5.41-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `uuid` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `text` text,
  `date_added` datetime DEFAULT NULL,
  `blnDeleted` tinyint(4) DEFAULT NULL,
  `uuidPost` char(36) DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `uuid` char(36) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `blnPublished` tinyint(4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `uuidUser` char(36) DEFAULT NULL,
  `blnDeleted` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES ('7406a346-9c76-4d7b-8ffe-797c82c67595','blog1','This is the title','<h2>Opening</h2><p><strong>This is the opening paragraph. Lorem ipsum Laborum in in veniam in cupidatat dolore commodo incididunt officia et esse dolore non.</strong></p><p>Lorem ipsum Deserunt Duis laboris ex in commodo commodo ad commodo in ullamco enim sint occaecat nulla cupidatat incididunt in consequat tempor aliquip sed officia tempor Excepteur sint ea occaecat veniam irure labore Duis proident in sunt et enim labore sit voluptate aliqua ad occaecat magna aute ullamco ut voluptate nostrud sint nulla eu Excepteur consequat incididunt consectetur dolor minim aute ad incididunt laborum amet cillum elit dolore commodo proident nostrud amet anim id ut Excepteur adipisicing nisi dolor reprehenderit Excepteur esse pariatur minim proident labore anim pariatur elit sunt aliquip labore in consectetur et minim veniam in in id eiusmod proident pariatur fugiat eiusmod dolore in dolore Duis reprehenderit ut aliquip et consectetur sit incididunt commodo aute qui sed esse consequat elit aliquip cillum aliqua elit sunt magna ea esse amet deserunt consequat occaecat eiusmod nisi veniam in eu ullamco aute in minim in commodo nulla exercitation eu nisi dolor cupidatat in reprehenderit qui occaecat enim eu sit nisi ullamco enim sed deserunt labore qui cillum id veniam reprehenderit qui aliquip adipisicing eu aliquip mollit enim minim.</p><p>Lorem ipsum Voluptate dolor do Duis ut consectetur sint dolore non consectetur magna proident ad occaecat ullamco nulla deserunt ad exercitation nostrud laborum sed ut amet velit eu nisi ex commodo consequat ut nulla quis Excepteur ex dolor aliqua irure eu dolor sint aliquip nisi culpa cillum anim cupidatat incididunt pariatur qui laboris dolor aute et Excepteur culpa fugiat labore Duis officia ex nostrud aliqua nisi non occaecat eu nulla ut esse nostrud dolore Duis eiusmod sed eiusmod occaecat ullamco enim labore sed reprehenderit esse tempor non reprehenderit minim ad laboris minim mollit sit in et cupidatat dolor quis eu aute officia irure anim sit sunt nisi ut enim nisi ullamco aliqua dolor aliqua velit Duis enim cupidatat ut consectetur ut qui in aute nisi nisi aute laboris laboris exercitation ad mollit sunt commodo ad id ullamco veniam elit aute eu Duis reprehenderit tempor voluptate elit ut commodo Duis proident dolore tempor enim ut qui laborum occaecat dolore pariatur in proident sed aliquip enim culpa id culpa dolore culpa velit est sit ex tempor incididunt in elit aliquip sint enim in nostrud in nostrud pariatur cupidatat aliqua Excepteur quis ut voluptate non proident veniam occaecat ut sit adipisicing magna in culpa sed amet mollit exercitation voluptate enim anim esse ut dolore consequat irure adipisicing consequat do in commodo eiusmod in anim est consectetur incididunt ut in tempor et velit deserunt eiusmod culpa aliqua magna et eu aliquip velit consectetur enim irure nostrud ullamco est.</p>',0,'0000-00-00 00:00:00','2015-06-12 16:18:42',0),('cd34c032-19e9-45ec-bbf5-477439676b85','blog-hell-world','Hello World','<p>This is a <strong>Hello World</strong> Blog!!!</p> <p>This is the first test of the blog we have done</p>',1,'2015-06-12 11:25:55','a0b48f2a-1bb9-47fc-8c69-66100bab78dc',0),('cd34c032-19e9-45ec-bbf5-477439676b86','test-blog','This Blog Post Is Awesome','<h2>This is a really cool blog post</h2><p>It is here to show you how you can view the blog posts</p><ul><li>Bullet one</li><li>bullet Two</li><li>bullet three</li></ul><p>This is the end of theblog post</p><p>One final paragraph</p>',1,'2015-06-12 11:19:36','a0b48f2a-1bb9-47fc-8c69-66100bab78dc',0),('e062d203-e74b-4efe-9c96-b40d7de5161a','blog2','This is the title','<h2>Opening</h2><p><strong>This is the opening paragraph. Lorem ipsum Laborum in in veniam in cupidatat dolore commodo incididunt officia et esse dolore non.</strong></p><p>Lorem ipsum Deserunt Duis laboris ex in commodo commodo ad commodo in ullamco enim sint occaecat nulla cupidatat incididunt in consequat tempor aliquip sed officia tempor Excepteur sint ea occaecat veniam irure labore Duis proident in sunt et enim labore sit voluptate aliqua ad occaecat magna aute ullamco ut voluptate nostrud sint nulla eu Excepteur consequat incididunt consectetur dolor minim aute ad incididunt laborum amet cillum elit dolore commodo proident nostrud amet anim id ut Excepteur adipisicing nisi dolor reprehenderit Excepteur esse pariatur minim proident labore anim pariatur elit sunt aliquip labore in consectetur et minim veniam in in id eiusmod proident pariatur fugiat eiusmod dolore in dolore Duis reprehenderit ut aliquip et consectetur sit incididunt commodo aute qui sed esse consequat elit aliquip cillum aliqua elit sunt magna ea esse amet deserunt consequat occaecat eiusmod nisi veniam in eu ullamco aute in minim in commodo nulla exercitation eu nisi dolor cupidatat in reprehenderit qui occaecat enim eu sit nisi ullamco enim sed deserunt labore qui cillum id veniam reprehenderit qui aliquip adipisicing eu aliquip mollit enim minim.</p><p>Lorem ipsum Voluptate dolor do Duis ut consectetur sint dolore non consectetur magna proident ad occaecat ullamco nulla deserunt ad exercitation nostrud laborum sed ut amet velit eu nisi ex commodo consequat ut nulla quis Excepteur ex dolor aliqua irure eu dolor sint aliquip nisi culpa cillum anim cupidatat incididunt pariatur qui laboris dolor aute et Excepteur culpa fugiat labore Duis officia ex nostrud aliqua nisi non occaecat eu nulla ut esse nostrud dolore Duis eiusmod sed eiusmod occaecat ullamco enim labore sed reprehenderit esse tempor non reprehenderit minim ad laboris minim mollit sit in et cupidatat dolor quis eu aute officia irure anim sit sunt nisi ut enim nisi ullamco aliqua dolor aliqua velit Duis enim cupidatat ut consectetur ut qui in aute nisi nisi aute laboris laboris exercitation ad mollit sunt commodo ad id ullamco veniam elit aute eu Duis reprehenderit tempor voluptate elit ut commodo Duis proident dolore tempor enim ut qui laborum occaecat dolore pariatur in proident sed aliquip enim culpa id culpa dolore culpa velit est sit ex tempor incididunt in elit aliquip sint enim in nostrud in nostrud pariatur cupidatat aliqua Excepteur quis ut voluptate non proident veniam occaecat ut sit adipisicing magna in culpa sed amet mollit exercitation voluptate enim anim esse ut dolore consequat irure adipisicing consequat do in commodo eiusmod in anim est consectetur incididunt ut in tempor et velit deserunt eiusmod culpa aliqua magna et eu aliquip velit consectetur enim irure nostrud ullamco est.</p>',0,'0000-00-00 00:00:00','2015-06-12 16:22:10',0);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uuid` char(36) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` text,
  `token` char(36) DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('a0b48f2a-1bb9-47fc-8c69-66100bab78dc','adminUser','$2y$10$3M1FcmLCPsKIUPnK.c3RhOuZd78M1CCbAcDKduCo5WILFVVJ.QZi2','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'blog'
--

--
-- Dumping routines for database 'blog'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-06-12 16:40:33
