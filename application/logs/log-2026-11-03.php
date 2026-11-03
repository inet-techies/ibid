<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<<<<<<< HEAD
ERROR - 2026-11-03 07:18:33 --> 404 Page Not Found: /index
=======
ERROR - 2026-11-03 05:50:31 --> 404 Page Not Found: /index
ERROR - 2026-11-03 07:11:17 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): No connection could be made because the target machine actively refused it.
 D:\xampp\htdocs\ibid\system\database\drivers\mysqli\mysqli_driver.php 203
ERROR - 2026-11-03 07:11:17 --> Unable to connect to the database
ERROR - 2026-11-03 07:19:51 --> Severity: Warning --> mysqli::real_connect(): (HY000/1049): Unknown database 'inet_ibids_db' D:\xampp\htdocs\ibid\system\database\drivers\mysqli\mysqli_driver.php 203
ERROR - 2026-11-03 07:19:51 --> Unable to connect to the database
ERROR - 2026-11-03 11:55:35 --> Query error: Unknown column 'ActualDate' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `BidStatus` = '9', `Remarks` = 'yes', `ActualDate` = '2026-11-24', `ApprovedByManagement` = 0, `ApprovedByWhom` = NULL
WHERE `Bcid` = '1'
ERROR - 2026-11-03 11:56:01 --> Query error: Unknown column 'ActualDate' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `BidStatus` = '9', `Remarks` = 'yes', `ActualDate` = '2026-11-12', `ApprovedByManagement` = 0, `ApprovedByWhom` = NULL
WHERE `Bcid` = '1'
ERROR - 2026-11-03 12:11:17 --> Query error: Unknown column 'ApprovedByManagement' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`) VALUES ('1', '1', '9', '2026-11-25', 0, NULL, 'yes', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0')
ERROR - 2026-11-03 12:11:59 --> Query error: Unknown column 'ApprovedByManagement' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`) VALUES ('1', '1', '9', '2026-11-12', 0, NULL, 'yes', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0')
ERROR - 2026-11-03 12:15:41 --> Query error: Unknown column 'ApprovedByManagement' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`) VALUES ('1', '1', '9', '2026-11-18', 0, NULL, 'yes', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0')
ERROR - 2026-11-03 12:18:43 --> Query error: Unknown column 'ApprovedByManagement' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`) VALUES ('1', '1', '9', '2026-11-19', 0, NULL, 'yws', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0')
ERROR - 2026-11-03 12:23:53 --> Query error: Table 'inet_ibids_db.bidnotificationlog' doesn't exist - Invalid query: INSERT INTO `BidNotificationLog` (`Bcid`, `NotificationType`, `RecipientEmail`, `Status`, `SentAt`, `CurrentStatusId`, `OpeningDate`, `Subject`, `ErrorMessage`) VALUES (1, 'STATUS_CHANGE', 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)', 'SENT', '2026-11-03 12:23:53', 9, '2026-09-24', 'Bid Status Update - WEBEL TECHNOLOGY LIMITED', NULL)
ERROR - 2026-11-03 12:27:26 --> Query error: Table 'inet_ibids_db.bidnotificationlog' doesn't exist - Invalid query: INSERT INTO `BidNotificationLog` (`Bcid`, `NotificationType`, `RecipientEmail`, `Status`, `SentAt`, `CurrentStatusId`, `OpeningDate`, `Subject`, `ErrorMessage`) VALUES (1, 'STATUS_CHANGE', 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)', 'SENT', '2026-11-03 12:27:26', 25, '2026-09-24', 'Bid Status Update - WEBEL TECHNOLOGY LIMITED', NULL)
ERROR - 2026-11-03 12:30:24 --> Query error: Table 'inet_ibids_db.bidnotificationlog' doesn't exist - Invalid query: INSERT INTO `BidNotificationLog` (`Bcid`, `NotificationType`, `RecipientEmail`, `Status`, `SentAt`, `CurrentStatusId`, `OpeningDate`, `Subject`, `ErrorMessage`) VALUES (1, 'STATUS_CHANGE', 'prasannakumari@inetcsc.com (CC: reka@inetcsc.com, Mohamedfazil@inetcsc.com)', 'SENT', '2026-11-03 12:30:24', 27, '2026-09-24', 'Bid Status Update - WEBEL TECHNOLOGY LIMITED', NULL)
ERROR - 2026-11-03 12:35:44 --> Query error: Unknown column 'TechnicalSelected' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`, `TechnicalSelected`) VALUES ('1', '27', '29', '2026-11-27', 'yes', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 1)
ERROR - 2026-11-03 12:37:10 --> Query error: Unknown column 'TechnicalSelected' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `Remarks`, `UpdatedBy`, `Ipaddress`, `UserAgent`, `TechnicalSelected`) VALUES ('1', '27', '29', '2026-11-19', 'yes', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 1)
ERROR - 2026-11-03 12:39:46 --> Skipped unsupported BidStatusTracking fields: TechnicalSelected
ERROR - 2026-11-03 12:39:48 --> BidNotificationLog table is unavailable; notification log was not recorded.
ERROR - 2026-11-03 08:38:24 --> 404 Page Not Found: /index
ERROR - 2026-11-03 09:53:55 --> 404 Page Not Found: /index
>>>>>>> 733f4e3cbc39a724c8e9c0cb907d28c71ec2c9bf
ERROR - 2026-11-03 15:15:05 --> Query error: Unknown column 'PTId' in 'order clause' - Invalid query: SELECT *
FROM `ProjectTable`
ORDER BY `PTId` DESC
ERROR - 2026-11-03 12:15:39 --> 404 Page Not Found: /index
ERROR - 2026-11-03 13:20:49 --> 404 Page Not Found: /index
