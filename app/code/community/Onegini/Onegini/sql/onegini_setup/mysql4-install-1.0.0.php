<?php

$installer = $this;

$installer->startSetup();
$installer->run("
CREATE TABLE `{$installer->getTable('onegini_identifiers')}` (
  `onegini_identifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`onegini_identifier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
$installer->endSetup();
