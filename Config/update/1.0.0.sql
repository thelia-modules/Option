# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `pse_available_option`;

ALTER TABLE option_product DROP configuration;


# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;