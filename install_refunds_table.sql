-- Create refund tracking table for Perfex CRM
-- Run this SQL in your database to add refund functionality

CREATE TABLE IF NOT EXISTS `tblrefunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoiceid` int(11) NOT NULL,
  `paymentid` int(11) DEFAULT NULL COMMENT 'Reference to original payment if applicable',
  `refund_amount` decimal(15,2) NOT NULL,
  `refund_type` enum('amount','percentage') NOT NULL DEFAULT 'amount',
  `refund_value` decimal(15,2) NOT NULL COMMENT 'Original value entered (amount or percentage)',
  `refund_mode` int(11) DEFAULT NULL COMMENT 'Payment mode used for refund',
  `transaction_id` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `note` text,
  `staffid` int(11) NOT NULL,
  `datecreated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoiceid` (`invoiceid`),
  KEY `paymentid` (`paymentid`),
  KEY `staffid` (`staffid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add foreign key constraints
ALTER TABLE `tblrefunds`
  ADD CONSTRAINT `fk_refunds_invoices` FOREIGN KEY (`invoiceid`) REFERENCES `tblinvoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_refunds_staff` FOREIGN KEY (`staffid`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE;
