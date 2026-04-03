/*!40101 SET NAMES utf8 */;

SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `desc` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_category_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`desc`,`created_at`) values (103,'Electronics','Electronic gadgets and devices','2026-04-02 14:49:37'),(104,'Clothing','Apparel and garments for all ages','2026-04-02 14:49:37'),(105,'Books','All kinds of books and literature','2026-04-02 14:49:37'),(106,'Furniture','Home and office furniture items','2026-04-02 14:49:37'),(107,'Toys','Toys and games for children','2026-04-02 14:49:37'),(108,'Food','Packaged and fresh food items','2026-04-02 14:49:37'),(109,'Beverages','Drinks, juices, and soft drinks','2026-04-02 14:49:37'),(110,'Health','Health and wellness products','2026-04-02 14:49:37'),(111,'Beauty','Cosmetics, skincare, and beauty items','2026-04-02 14:49:37'),(112,'Sports','Sports equipment and accessories','2026-04-02 14:49:37'),(113,'Stationery','Office and school supplies','2026-04-02 14:49:37'),(114,'Automotive','Car parts and accessories','2026-04-02 14:49:37'),(115,'Pets','Pet food and pet care products','2026-04-02 14:49:37'),(116,'Garden','Garden tools and outdoor items','2026-04-02 14:49:37'),(117,'Music','Musical instruments and accessories','2026-04-02 14:49:37'),(118,'Tools','Hardware tools and DIY equipment','2026-04-02 14:49:37'),(119,'Baby','Baby products and care items','2026-04-02 14:49:37'),(120,'Travel','Travel gear and luggage','2026-04-02 14:49:37'),(121,'Shoes','Footwear for all occasions','2026-04-02 14:49:37'),(122,'Jewelry','Jewelry and fashion accessories','2026-04-02 14:49:37'),(123,'Kitchen','Kitchen appliances and utensils','2026-04-02 14:49:37'),(124,'Art','Arts, crafts, and supplies','2026-04-02 14:49:37'),(125,'Tech Accessories','Phone, computer, and tech accessories','2026-04-02 14:49:37'),(126,'Cleaning','Cleaning products and detergents','2026-04-02 14:49:37'),(127,'Miscellaneous','Other items not categorized','2026-04-02 14:49:37');

/*Table structure for table `suppliers` */

DROP TABLE IF EXISTS `suppliers`;

CREATE TABLE `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `suppliers` */

insert  into `suppliers`(`id`,`name`,`contact_person`,`email`,`phone`,`address`) values (1,'TechSource','Alice','alice@techsource.com','1111111111','123 Tech Street'),(2,'ClothMart','Bob','bob@clothmart.com','2222222222','456 Fashion Ave'),(3,'BookWorld','Charlie','charlie@bookworld.com','3333333333','789 Book Rd'),(4,'FurniCo','Diana','diana@furnico.com','4444444444','321 Home Ln'),(5,'ToyLand','Eve','eve@toyland.com','5555555555','654 Play Blvd'),(6,'Foodies','Frank','frank@foodies.com','6666666666','987 Food St'),(7,'DrinkHub','Grace','grace@drinkhub.com','7777777777','147 Drink Rd'),(8,'HealthPlus','Hank','hank@healthplus.com','8888888888','258 Health St'),(9,'BeautyCo','Ivy','ivy@beautyco.com','9999999999','369 Beauty Ave'),(10,'SportsStore','Jack','jack@sportsstore.com','1010101010','147 Sport Ln'),(11,'StationeryHub','Karen','karen@stationeryhub.com','1112223333','258 Paper Rd'),(12,'AutoParts','Leo','leo@autoparts.com','2223334444','369 Auto Blvd'),(13,'PetMart','Mia','mia@petmart.com','3334445555','123 Pet St'),(14,'GardenShop','Nina','nina@gardenshop.com','4445556666','456 Garden Rd'),(15,'MusicWorld','Oscar','oscar@musicworld.com','5556667777','789 Music Ave'),(16,'ToolHouse','Pam','pam@toolhouse.com','6667778888','321 Tool Ln'),(17,'BabyCare','Quinn','quinn@babycare.com','7778889999','654 Baby Blvd'),(18,'TravelStore','Rick','rick@travelstore.com','8889990000','987 Travel Rd'),(19,'ShoeMart','Sara','sara@shoemart.com','9990001111','147 Shoe St'),(20,'JewelryBox','Tom','tom@jewelrybox.com','1011112222','258 Jewelry Ave'),(21,'KitchenKing','Uma','uma@kitchenking.com','2221113333','369 Kitchen Blvd'),(22,'ArtHouse','Vera','vera@arthouse.com','3332224444','123 Art Ln'),(23,'TechAccessories','Walt','walt@techaccessories.com','4443335555','456 Gadget Rd'),(24,'CleanIt','Xena','xena@cleanit.com','5554446666','789 Clean St'),(25,'MiscSupplies','Yara','yara@miscsupplies.com','6665557777','321 Misc Ave');

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `desc` text,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `cat_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `cat_id` (`cat_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`name`,`desc`,`price`,`cost`,`cat_id`,`created_at`) values (151,'Laptop','High performance laptop','1200.00','800.00',103,'2026-04-02 14:52:50'),(152,'Smartphone','Latest smartphone','900.00','600.00',103,'2026-04-02 14:52:50'),(153,'Headphones','Noise-cancelling headphones','150.00','100.00',103,'2026-04-02 14:52:50'),(154,'T-Shirt','Cotton T-shirt','20.00','10.00',104,'2026-04-02 14:52:50'),(155,'Jeans','Denim jeans','50.00','30.00',104,'2026-04-02 14:52:50'),(156,'Novel','Bestselling novel','15.00','8.00',105,'2026-04-02 14:52:50'),(157,'Desk','Wooden desk','200.00','150.00',106,'2026-04-02 14:52:50'),(158,'Chair','Office chair','100.00','60.00',106,'2026-04-02 14:52:50'),(159,'Puzzle','1000-piece puzzle','25.00','15.00',107,'2026-04-02 14:52:50'),(160,'Chocolate','Milk chocolate pack','5.00','2.50',108,'2026-04-02 14:52:50'),(161,'Soda','Carbonated drink','2.00','1.00',109,'2026-04-02 14:52:50'),(162,'Vitamins','Multivitamins','30.00','20.00',110,'2026-04-02 14:52:50'),(163,'Lipstick','Red lipstick','15.00','7.00',111,'2026-04-02 14:52:50'),(164,'Soccer Ball','Official size soccer ball','25.00','15.00',112,'2026-04-02 14:52:50'),(165,'Notebook','Lined notebook','3.00','1.50',113,'2026-04-02 14:52:50'),(166,'Car Wax','Car waxing product','12.00','6.00',114,'2026-04-02 14:52:50'),(167,'Dog Food','Premium dog food','40.00','25.00',115,'2026-04-02 14:52:50'),(168,'Plant Pot','Ceramic plant pot','10.00','5.00',116,'2026-04-02 14:52:50'),(169,'Guitar','Acoustic guitar','150.00','100.00',117,'2026-04-02 14:52:50'),(170,'Hammer','Steel hammer','20.00','10.00',118,'2026-04-02 14:52:50'),(171,'Diaper Pack','Baby diapers','25.00','15.00',119,'2026-04-02 14:52:50'),(172,'Backpack','Travel backpack','50.00','30.00',120,'2026-04-02 14:52:50'),(173,'Sneakers','Running shoes','80.00','50.00',121,'2026-04-02 14:52:50'),(174,'Necklace','Gold necklace','200.00','150.00',122,'2026-04-02 14:52:50'),(175,'Blender','Kitchen blender','70.00','40.00',123,'2026-04-02 14:52:50'),(176,'Paint Set','Watercolor paint set','35.00','20.00',124,'2026-04-02 14:52:50'),(177,'Phone Case','Protective phone case','15.00','8.00',125,'2026-04-02 14:52:50'),(178,'Broom','Cleaning broom','10.00','5.00',126,'2026-04-02 14:52:50'),(179,'USB Cable','High-speed USB cable','8.00','3.50',127,'2026-04-02 14:52:50');

/*Table structure for table `po` */

DROP TABLE IF EXISTS `po`;

CREATE TABLE `po` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `po_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `po` */

insert  into `po`(`id`,`supplier_id`,`order_date`,`status`,`total_amount`,`created_at`) values (1,1,'2026-03-01','PENDING','5000.00','2026-04-02 14:40:08'),(2,2,'2026-03-02','APPROVED','1200.00','2026-04-02 14:40:08'),(3,3,'2026-03-03','REJECTED','300.00','2026-04-02 14:40:08'),(4,4,'2026-03-04','CANCELLED','1500.00','2026-04-02 14:40:08'),(5,5,'2026-03-05','PENDING','800.00','2026-04-02 14:40:08'),(6,6,'2026-03-06','APPROVED','2000.00','2026-04-02 14:40:08'),(7,7,'2026-03-07','PENDING','350.00','2026-04-02 14:40:08'),(8,8,'2026-03-08','APPROVED','600.00','2026-04-02 14:40:08'),(9,9,'2026-03-09','REJECTED','450.00','2026-04-02 14:40:08'),(10,10,'2026-03-10','CANCELLED','700.00','2026-04-02 14:40:08'),(11,11,'2026-03-11','PENDING','1000.00','2026-04-02 14:40:08'),(12,12,'2026-03-12','APPROVED','900.00','2026-04-02 14:40:08'),(13,13,'2026-03-13','REJECTED','1200.00','2026-04-02 14:40:08'),(14,14,'2026-03-14','CANCELLED','1500.00','2026-04-02 14:40:08'),(15,15,'2026-03-15','PENDING','400.00','2026-04-02 14:40:08'),(16,16,'2026-03-16','APPROVED','600.00','2026-04-02 14:40:08'),(17,17,'2026-03-17','PENDING','700.00','2026-04-02 14:40:08'),(18,18,'2026-03-18','APPROVED','500.00','2026-04-02 14:40:08'),(19,19,'2026-03-19','REJECTED','1000.00','2026-04-02 14:40:08'),(20,20,'2026-03-20','CANCELLED','1100.00','2026-04-02 14:40:08'),(21,21,'2026-03-21','PENDING','800.00','2026-04-02 14:40:08'),(22,22,'2026-03-22','APPROVED','900.00','2026-04-02 14:40:08'),(23,23,'2026-03-23','REJECTED','1000.00','2026-04-02 14:40:08'),(24,24,'2026-03-24','CANCELLED','1200.00','2026-04-02 14:40:08'),(25,25,'2026-03-25','PENDING','1300.00','2026-04-02 14:40:08'),(26,1,'2026-03-01','PENDING','5000.00','2026-04-02 14:40:43'),(27,2,'2026-03-02','APPROVED','1200.00','2026-04-02 14:40:43'),(28,3,'2026-03-03','REJECTED','300.00','2026-04-02 14:40:43'),(29,4,'2026-03-04','CANCELLED','1500.00','2026-04-02 14:40:43'),(30,5,'2026-03-05','PENDING','800.00','2026-04-02 14:40:43'),(31,6,'2026-03-06','APPROVED','2000.00','2026-04-02 14:40:43'),(32,7,'2026-03-07','PENDING','350.00','2026-04-02 14:40:43'),(33,8,'2026-03-08','APPROVED','600.00','2026-04-02 14:40:43'),(34,9,'2026-03-09','REJECTED','450.00','2026-04-02 14:40:43'),(35,10,'2026-03-10','CANCELLED','700.00','2026-04-02 14:40:43'),(36,11,'2026-03-11','PENDING','1000.00','2026-04-02 14:40:43'),(37,12,'2026-03-12','APPROVED','900.00','2026-04-02 14:40:43'),(38,13,'2026-03-13','REJECTED','1200.00','2026-04-02 14:40:43'),(39,14,'2026-03-14','CANCELLED','1500.00','2026-04-02 14:40:43'),(40,15,'2026-03-15','PENDING','400.00','2026-04-02 14:40:43'),(41,16,'2026-03-16','APPROVED','600.00','2026-04-02 14:40:43'),(42,17,'2026-03-17','PENDING','700.00','2026-04-02 14:40:43'),(43,18,'2026-03-18','APPROVED','500.00','2026-04-02 14:40:43'),(44,19,'2026-03-19','REJECTED','1000.00','2026-04-02 14:40:43'),(45,20,'2026-03-20','CANCELLED','1100.00','2026-04-02 14:40:43'),(46,21,'2026-03-21','PENDING','800.00','2026-04-02 14:40:43'),(47,22,'2026-03-22','APPROVED','900.00','2026-04-02 14:40:43'),(48,23,'2026-03-23','REJECTED','1000.00','2026-04-02 14:40:43'),(49,24,'2026-03-24','CANCELLED','1200.00','2026-04-02 14:40:43'),(50,25,'2026-03-25','PENDING','1300.00','2026-04-02 14:40:43');

/*Table structure for table `poi` */

DROP TABLE IF EXISTS `poi`;

CREATE TABLE `poi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) STORED,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `poi_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `po` (`id`),
  CONSTRAINT `poi_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `so` */

DROP TABLE IF EXISTS `so`;

CREATE TABLE `so` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cus_name` varchar(255) NOT NULL,
  `cus_email` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `so` */

insert  into `so`(`id`,`cus_name`,`cus_email`,`order_date`,`status`,`total_amount`) values (1,'Customer1','c1@example.com','2026-03-01','PENDING','300.00'),(2,'Customer2','c2@example.com','2026-03-02','APPROVED','500.00'),(3,'Customer3','c3@example.com','2026-03-03','REJECTED','200.00'),(4,'Customer4','c4@example.com','2026-03-04','CANCELLED','400.00'),(5,'Customer5','c5@example.com','2026-03-05','PENDING','150.00'),(6,'Customer6','c6@example.com','2026-03-06','APPROVED','600.00'),(7,'Customer7','c7@example.com','2026-03-07','PENDING','700.00'),(8,'Customer8','c8@example.com','2026-03-08','APPROVED','350.00'),(9,'Customer9','c9@example.com','2026-03-09','REJECTED','450.00'),(10,'Customer10','c10@example.com','2026-03-10','CANCELLED','250.00'),(11,'Customer11','c11@example.com','2026-03-11','PENDING','300.00'),(12,'Customer12','c12@example.com','2026-03-12','APPROVED','550.00'),(13,'Customer13','c13@example.com','2026-03-13','REJECTED','500.00'),(14,'Customer14','c14@example.com','2026-03-14','CANCELLED','600.00'),(15,'Customer15','c15@example.com','2026-03-15','PENDING','200.00'),(16,'Customer16','c16@example.com','2026-03-16','APPROVED','700.00'),(17,'Customer17','c17@example.com','2026-03-17','PENDING','450.00'),(18,'Customer18','c18@example.com','2026-03-18','APPROVED','550.00'),(19,'Customer19','c19@example.com','2026-03-19','REJECTED','300.00'),(20,'Customer20','c20@example.com','2026-03-20','CANCELLED','500.00'),(21,'Customer21','c21@example.com','2026-03-21','PENDING','400.00'),(22,'Customer22','c22@example.com','2026-03-22','APPROVED','600.00'),(23,'Customer23','c23@example.com','2026-03-23','REJECTED','350.00'),(24,'Customer24','c24@example.com','2026-03-24','CANCELLED','450.00'),(25,'Customer25','c25@example.com','2026-03-25','PENDING','500.00'),(26,'Customer1','c1@example.com','2026-03-01','PENDING','300.00'),(27,'Customer2','c2@example.com','2026-03-02','APPROVED','500.00'),(28,'Customer3','c3@example.com','2026-03-03','REJECTED','200.00'),(29,'Customer4','c4@example.com','2026-03-04','CANCELLED','400.00'),(30,'Customer5','c5@example.com','2026-03-05','PENDING','150.00'),(31,'Customer6','c6@example.com','2026-03-06','APPROVED','600.00'),(32,'Customer7','c7@example.com','2026-03-07','PENDING','700.00'),(33,'Customer8','c8@example.com','2026-03-08','APPROVED','350.00'),(34,'Customer9','c9@example.com','2026-03-09','REJECTED','450.00'),(35,'Customer10','c10@example.com','2026-03-10','CANCELLED','250.00'),(36,'Customer11','c11@example.com','2026-03-11','PENDING','300.00'),(37,'Customer12','c12@example.com','2026-03-12','APPROVED','550.00'),(38,'Customer13','c13@example.com','2026-03-13','REJECTED','500.00'),(39,'Customer14','c14@example.com','2026-03-14','CANCELLED','600.00'),(40,'Customer15','c15@example.com','2026-03-15','PENDING','200.00'),(41,'Customer16','c16@example.com','2026-03-16','APPROVED','700.00'),(42,'Customer17','c17@example.com','2026-03-17','PENDING','450.00'),(43,'Customer18','c18@example.com','2026-03-18','APPROVED','550.00'),(44,'Customer19','c19@example.com','2026-03-19','REJECTED','300.00'),(45,'Customer20','c20@example.com','2026-03-20','CANCELLED','500.00'),(46,'Customer21','c21@example.com','2026-03-21','PENDING','400.00'),(47,'Customer22','c22@example.com','2026-03-22','APPROVED','600.00'),(48,'Customer23','c23@example.com','2026-03-23','REJECTED','350.00'),(49,'Customer24','c24@example.com','2026-03-24','CANCELLED','450.00'),(50,'Customer25','c25@example.com','2026-03-25','PENDING','500.00');

/*Table structure for table `soi` */

DROP TABLE IF EXISTS `soi`;

CREATE TABLE `soi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `so_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) STORED,
  PRIMARY KEY (`id`),
  KEY `so_id` (`so_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `soi_ibfk_1` FOREIGN KEY (`so_id`) REFERENCES `so` (`id`),
  CONSTRAINT `soi_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `stock` */

DROP TABLE IF EXISTS `stock`;

CREATE TABLE `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `stock` */

insert  into `stock`(`id`,`product_id`,`quantity`) values (51,151,100),(52,152,100),(53,153,100),(54,154,100),(55,155,100),(56,156,100),(57,157,100),(58,158,100),(59,159,100),(60,160,100),(61,161,100),(62,162,100),(63,163,100),(64,164,100),(65,165,100),(66,166,100),(67,167,100),(68,168,100),(69,169,100),(70,170,100),(71,171,100),(72,172,100),(73,173,100),(74,174,100),(75,175,100),(76,176,100),(77,177,100),(78,178,100),(79,179,100);

/*Table structure for table `stock_movement` */

DROP TABLE IF EXISTS `stock_movement`;

CREATE TABLE `stock_movement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `type` varchar(20) NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `stock_movement_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `stock_movement` */

insert  into `stock_movement`(`id`,`product_id`,`type`,`quantity`,`note`,`created_at`) values (51,151,'IN',100,'Initial stock','2026-04-02 14:53:51'),(52,152,'IN',100,'Initial stock','2026-04-02 14:53:51'),(53,153,'IN',100,'Initial stock','2026-04-02 14:53:51'),(54,154,'IN',100,'Initial stock','2026-04-02 14:53:51'),(55,155,'IN',100,'Initial stock','2026-04-02 14:53:51'),(56,156,'IN',100,'Initial stock','2026-04-02 14:53:51'),(57,157,'IN',100,'Initial stock','2026-04-02 14:53:51'),(58,158,'IN',100,'Initial stock','2026-04-02 14:53:51'),(59,159,'IN',100,'Initial stock','2026-04-02 14:53:51'),(60,160,'IN',100,'Initial stock','2026-04-02 14:53:51'),(61,161,'IN',100,'Initial stock','2026-04-02 14:53:51'),(62,162,'IN',100,'Initial stock','2026-04-02 14:53:51'),(63,163,'IN',100,'Initial stock','2026-04-02 14:53:51'),(64,164,'IN',100,'Initial stock','2026-04-02 14:53:51'),(65,165,'IN',100,'Initial stock','2026-04-02 14:53:51'),(66,166,'IN',100,'Initial stock','2026-04-02 14:53:51'),(67,167,'IN',100,'Initial stock','2026-04-02 14:53:51'),(68,168,'IN',100,'Initial stock','2026-04-02 14:53:51'),(69,169,'IN',100,'Initial stock','2026-04-02 14:53:51'),(70,170,'IN',100,'Initial stock','2026-04-02 14:53:51'),(71,171,'IN',100,'Initial stock','2026-04-02 14:53:51'),(72,172,'IN',100,'Initial stock','2026-04-02 14:53:51'),(73,173,'IN',100,'Initial stock','2026-04-02 14:53:51'),(74,174,'IN',100,'Initial stock','2026-04-02 14:53:51'),(75,175,'IN',100,'Initial stock','2026-04-02 14:53:51'),(76,176,'IN',100,'Initial stock','2026-04-02 14:53:51'),(77,177,'IN',100,'Initial stock','2026-04-02 14:53:51'),(78,178,'IN',100,'Initial stock','2026-04-02 14:53:51'),(79,179,'IN',100,'Initial stock','2026-04-02 14:53:51');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` char(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'STAFF',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`password`,`email`,`role`,`created_at`) values (1,'jonathan_emm','70707070','jonathan@gmail.com','MANAGER','2026-03-29 21:01:46'),(2,'zuzaki','90909090','zuzaki@gmail.com','STAFF','2026-03-29 21:01:46'),(3,'bighead','30303030','bighead@gmail.com','STAFF','2026-03-29 21:01:46'),(5,'john_doe','12345678','john.doe@example.com','ADMIN','2026-04-01 15:55:48'),(6,'jane_smith','12345678','jane.smith@example.com','MANAGER','2026-04-01 15:55:48'),(7,'mike_wilson','12345678','mike.wilson@example.com','STAFF','2026-04-01 15:55:48'),(8,'sarah_johnson','12345678','sarah.j@example.com','VIEWER','2026-04-01 15:55:48'),(9,'david_brown','12345678','david.brown@example.com','STAFF','2026-04-01 15:55:48'),(10,'emily_davis','12345678','emily.davis@example.com','MANAGER','2026-04-01 15:55:48'),(12,'lisa_anderson','12345678','lisa.anderson@example.com','VIEWER','2026-04-01 15:55:48'),(13,'kevin_taylor','12345678','kevin.taylor@example.com','STAFF','2026-04-01 15:55:48'),(14,'amanda_white','12345678','amanda.white@example.com','ADMIN','2026-04-01 15:55:48'),(15,'meow','$2y$10$oW9tjorX8qZzgAYQyOYKre1bURtWr22Ll2XuxvBVZMNrD7lr.nQQ6','admin@example.com','ADMIN','2026-04-02 12:35:34'),(18,'user1','pass1','user1@example.com','ADMIN','2026-04-02 14:40:08'),(19,'user2','pass2','user2@example.com','MANAGER','2026-04-02 14:40:08'),(20,'user3','pass3','user3@example.com','STAFF','2026-04-02 14:40:08'),(21,'user4','pass4','user4@example.com','VIEWER','2026-04-02 14:40:08'),(22,'user5','pass5','user5@example.com','STAFF','2026-04-02 14:40:08'),(23,'user6','pass6','user6@example.com','MANAGER','2026-04-02 14:40:08'),(24,'user7','pass7','user7@example.com','STAFF','2026-04-02 14:40:08'),(25,'user8','pass8','user8@example.com','VIEWER','2026-04-02 14:40:08'),(26,'user9','pass9','user9@example.com','STAFF','2026-04-02 14:40:08'),(27,'user10','pass10','user10@example.com','STAFF','2026-04-02 14:40:08'),(28,'user11','pass11','user11@example.com','MANAGER','2026-04-02 14:40:08'),(29,'user12','pass12','user12@example.com','STAFF','2026-04-02 14:40:08'),(30,'user13','pass13','user13@example.com','VIEWER','2026-04-02 14:40:08'),(31,'user14','pass14','user14@example.com','STAFF','2026-04-02 14:40:08'),(32,'user15','pass15','user15@example.com','STAFF','2026-04-02 14:40:08'),(33,'user16','pass16','user16@example.com','MANAGER','2026-04-02 14:40:08'),(34,'user17','pass17','user17@example.com','STAFF','2026-04-02 14:40:08'),(35,'user18','pass18','user18@example.com','VIEWER','2026-04-02 14:40:08'),(36,'user19','pass19','user19@example.com','STAFF','2026-04-02 14:40:08'),(37,'user20','pass20','user20@example.com','STAFF','2026-04-02 14:40:08'),(38,'user21','pass21','user21@example.com','STAFF','2026-04-02 14:40:08'),(39,'user22','pass22','user22@example.com','MANAGER','2026-04-02 14:40:08'),(40,'user23','pass23','user23@example.com','STAFF','2026-04-02 14:40:08'),(41,'user24','pass24','user24@example.com','VIEWER','2026-04-02 14:40:08'),(42,'user25','pass25','user25@example.com','STAFF','2026-04-02 14:40:08');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS=1;