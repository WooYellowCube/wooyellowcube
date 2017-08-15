# WooYellowCube
WooYellowCube allow the synchronization between WooCommerce and YellowCube (Swiss Post).

Swiss Post offers an all-in logistics solution for distance selling with YellowCube. The range of services covers goods receipt, storage, picking, packaging, fast shipping, and returns management.

Requirements: WooCommerce 3.0+, PHP 5.7

## Installation
### Database (MySQL installation)
WooCommerce work with a MysQL Database. The WooYellowCube plugin use 5 tables :
* **wooyellowcube_logs** _(Stock the YellowCube transaction logs)_
* **wooyellowcube_orders** _(Stock all the YellowCube WAB (order) informations)_
* **wooyellowcube_orders_lots** _(Stock all the YellowCube WAB informations from lots)_
* **wooyellowcube_products** _(Stock all the YellowCube ART (article) informations)_
* **wooyellowcube_stock** _(Stock all the YellowCube BAR (inventory) informations)_
* **wooyellowcube_stock_lots** _(Stock all the YellowCube BAR (inventory) lots informations)_

### MySQL migration SQL Code
To implement the correct MySQL table in your database. Please execute the following SQL code in your database.
```mysql
CREATE TABLE `wooyellowcube_logs` (
`id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `type` varchar(250) NOT NULL,
  `response` int(11) DEFAULT NULL,
  `reference` int(11) DEFAULT NULL,
  `object` int(11) DEFAULT NULL,
  `message` mediumtext
) ENGINE=InnoDB AUTO_INCREMENT=1780 DEFAULT CHARSET=latin1;

CREATE TABLE `wooyellowcube_orders` (
`id` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `pdf_file` varchar(250) NOT NULL,
  `yc_response` int(11) NOT NULL,
  `yc_status_code` int(11) NOT NULL,
  `yc_status_text` mediumtext NOT NULL,
  `yc_reference` int(11) NOT NULL,
  `yc_shipping` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

CREATE TABLE `wooyellowcube_orders_lots` (
`id` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `product_no` varchar(250) NOT NULL,
  `product_lot` varchar(250) NOT NULL,
  `product_quantity` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `wooyellowcube_products` (
`id` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_variation` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `lotmanagement` tinyint(1) NOT NULL,
  `yc_response` int(11) NOT NULL,
  `yc_status_code` int(11) NOT NULL,
  `yc_status_text` mediumtext NOT NULL,
  `yc_reference` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=latin1;

CREATE TABLE `wooyellowcube_stock` (
`id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(250) NOT NULL,
  `woocommerce_stock` int(11) DEFAULT NULL,
  `yellowcube_stock` int(11) NOT NULL,
  `yellowcube_date` int(11) DEFAULT NULL,
  `yellowcube_articleno` varchar(250) DEFAULT NULL,
  `yellowcube_lot` varchar(250) DEFAULT NULL,
  `yellowcube_bestbeforedate` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3550 DEFAULT CHARSET=latin1;

CREATE TABLE `wooyellowcube_stock_lots` (
`id` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `product_lot` varchar(250) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `product_expiration` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

ALTER TABLE `wooyellowcube_logs` ADD PRIMARY KEY (`id`);
ALTER TABLE `wooyellowcube_orders` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id_order` (`id_order`);
ALTER TABLE `wooyellowcube_orders_lots` ADD PRIMARY KEY (`id`);
ALTER TABLE `wooyellowcube_products` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id_product` (`id_product`);
ALTER TABLE `wooyellowcube_stock` ADD PRIMARY KEY (`id`);
ALTER TABLE `wooyellowcube_stock_lots` ADD PRIMARY KEY (`id`);
ALTER TABLE `wooyellowcube_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `wooyellowcube_orders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `wooyellowcube_orders_lots` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `wooyellowcube_products` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `wooyellowcube_stock` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `wooyellowcube_stock_lots` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
```
## Crons
### WordPress based
There is 3 crons that are executed by used timestamp difference. Theses crons need to got a frontend or backend visit to be triggered.

Please refer to the next section to integrate crons with a server cron-job system.

**Every 60 seconds difference** :   
$yellowcube->cron_response();  
_Get article and orders results_

**Every hour (60 minutes) difference** :   
$yellowcube->cron_hourly();  
_Get WAR results_

**Every day difference** :   
$yellowcube->cron_daily();  
_Get the inventory (BAR)_

### Cron-job system
Endpoint to call cron-jobs :  
* http://yourwebsite.com/?cron_response=true
* http://yourwebsite.com/?cron_hourly=true
* http://yourwebsite.com/?cron_daily=true
