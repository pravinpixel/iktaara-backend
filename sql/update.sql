ALTER TABLE `mm_order_products` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `updated_at`, ADD INDEX (`status`);

ALTER TABLE `mm_order_histories` ADD `product_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `order_id`, ADD INDEX (`product_id`);

/* 06/09/2023 */

ALTER TABLE `mm_reviews` CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE `start` `star` DECIMAL(2,2) NOT NULL;

ALTER TABLE `mm_reviews` ADD `order_id` BIGINT(20) UNSIGNED NOT NULL AFTER `updated_at`, ADD `ip` VARCHAR(16) NULL DEFAULT NULL AFTER `order_id`, ADD `status` TINYINT(1) NOT NULL DEFAULT '1' AFTER `ip`, ADD `approved_by` BIGINT(20) UNSIGNED NOT NULL AFTER `status`;

ALTER TABLE `mm_reviews` CHANGE `star` `star` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `mm_orders` CHANGE `status` `status` ENUM('pending','placed','shipped','delivered','cancelled','payment_pending','cancel_requested','accepted','rejected','partial_cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `mm_merchant_orders` CHANGE `order_status` `order_status` ENUM('pending','accept','ship','reject','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending';

ALTER TABLE `mm_merchant_order_statuses` CHANGE `order_status` `order_status` ENUM('pending','accept','ship','reject','deliver','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';

INSERT INTO `mm_merchant_order_statuses` (`id`, `order_status`, `order_status_name`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, 'cancel', 'Cancelled', NULL, NULL, NULL);

/*  'pending','placed','shipped','delivered','cancelled','payment_pending','cancel_requested','accepted','rejected','partial_cancel', 'exchange_requested', 'exchanged' */
/*13-09-2023*/

INSERT INTO `mm_order_statuses` (`id`, `status_name`, `description`, `order`, `added_by`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, 'Exchange Request', 'Exchange Request', '10', '1', 'published', NULL, NULL, NULL), (NULL, 'Exchanged', 'Exchanged', '11', '1', 'published', NULL, NULL, NULL);


ALTER TABLE `mm_orders` CHANGE `status` `status` ENUM('pending','placed','shipped','delivered','cancelled','payment_pending','cancel_requested','accepted','rejected','partial_cancel', 'exchange_requested', 'exchanged') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

/* 14-09-2023 */


CREATE TABLE `mm_order_exchange` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `order_item_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `seller_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `reason_id` tinyint(3) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delivered_at` datetime DEFAULT NULL,
  `apporved_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ordered Product Exchange Request';


ALTER TABLE `mm_order_exchange`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`);


ALTER TABLE `mm_order_exchange`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;


CREATE TABLE `mm_order_exchange_reasons` (
  `id` int(3) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `order_by` int(3) DEFAULT NULL,
  `status` enum('published','unpublished') NOT NULL DEFAULT 'published',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `mm_order_exchange_reasons`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `mm_order_exchange_reasons`
  MODIFY `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;


UPDATE `mm_order_statuses` SET `description` = 'Exchange Accepted' WHERE `mm_order_statuses`.`id` = 11;

INSERT INTO `mm_order_statuses` (`id`, `status_name`, `description`, `order`, `added_by`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, 'Exchange Rejected', 'Exchange Rejected', '12', '1', 'published', NULL, NULL, NULL), (NULL, 'Exchanged', 'Exchanged', '13', '1', 'published', NULL, NULL, NULL);

ALTER TABLE `mm_merchant_orders` CHANGE `order_status` `order_status` ENUM('pending','accept','ship','reject','deliver','cancel','exchange') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';


08-11-2023

ALTER TABLE `mm_brands` ADD `profit_margin_percent` INT NULL AFTER `status`;
ALTER TABLE `mm_product_categories` ADD `profit_margin_percent` INT NULL AFTER `updated_by`;
