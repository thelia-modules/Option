SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE option_cart_item_order_product MODIFY product_available_option_id INT NULL;
ALTER TABLE `option_cart_item_order_product` DROP FOREIGN KEY `fk_option_cart_item_order_product_paoid`;
ALTER TABLE `option_cart_item_order_product`
    ADD CONSTRAINT `fk_option_cart_item_order_product_paoid`
        FOREIGN KEY (`product_available_option_id`)
        REFERENCES `product_available_option` (`id`)
        ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS = 1;