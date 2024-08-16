SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE product_available_option ADD `option_price` DECIMAL(16,6) DEFAULT 0.000000;
ALTER TABLE product_available_option ADD `option_promo_price` DECIMAL(16,6) DEFAULT 0.000000;
SET FOREIGN_KEY_CHECKS = 1;