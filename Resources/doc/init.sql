/* 
 * Execute after command doctrine:schema:update
 */
/**
 * Author:  vitaliy
 * Created: 27 февр. 2019 г.
 */

/* Reservation payment status*/
INSERT INTO `vtereshenkov_reservation_payment_status`(`id`, `title`) VALUES (1,'Payed');
INSERT INTO `vtereshenkov_reservation_payment_status`(`id`, `title`) VALUES (2,'Not payed');
INSERT INTO `vtereshenkov_reservation_payment_status`(`id`, `title`) VALUES (3,'Partially payed');

/* Reservation resident status*/
INSERT INTO `vtereshenkov_reservation_resident_status`(`id`, `title`) VALUES (1,'Settled');
INSERT INTO `vtereshenkov_reservation_resident_status`(`id`, `title`) VALUES (2,'Not settled');

/* Reservation status*/
INSERT INTO `vtereshenkov_reservation_status`(`id`, `title`) VALUES (1,'Active');
INSERT INTO `vtereshenkov_reservation_status`(`id`, `title`) VALUES (2,'Cancelled by client');
INSERT INTO `vtereshenkov_reservation_status`(`id`, `title`) VALUES (3,'Cancelled for non-payment');
INSERT INTO `vtereshenkov_reservation_status`(`id`, `title`) VALUES (4,'Denied by manager');