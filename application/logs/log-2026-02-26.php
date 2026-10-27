<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-02-26 06:23:40 --> 404 Page Not Found: /index
ERROR - 2026-02-26 10:54:08 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: SELECT `BidStatus`
FROM `BidCreation`
WHERE `Bcid` = '7'
ERROR - 2026-02-26 09:09:18 --> 404 Page Not Found: /index
ERROR - 2026-02-26 14:26:45 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: SELECT `BidStatus`
FROM `BidCreation`
WHERE `Bcid` = '8'
ERROR - 2026-02-26 14:32:00 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: SELECT `BidStatus`
FROM `BidCreation`
WHERE `Bcid` = '9'
ERROR - 2026-02-26 14:34:04 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: UPDATE `BidCreation` SET `BidStatus` = '3', `ActualDate` = '2026-02-12'
WHERE `Bcid` = '9'
ERROR - 2026-02-26 14:34:49 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: UPDATE `BidCreation` SET `BidStatus` = '3', `ActualDate` = '2026-02-18'
WHERE `Bcid` = '9'
ERROR - 2026-02-26 14:35:29 --> Query error: Unknown column 'ActualDate' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `BidStatus` = '3', `ActualDate` = '2026-02-19'
WHERE `Bcid` = '9'
ERROR - 2026-02-26 14:36:40 --> Query error: Unknown column 'OldStatus' in 'field list' - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatus`, `NewStatus`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `Remarks`, `UpdatedBy`) VALUES ('9', '1', '3', '2026-02-27', 0, NULL, 'jkbgkjhvujh', NULL)
ERROR - 2026-02-26 10:29:28 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:29:34 --> 404 Page Not Found: ../modules/bid/controllers/Bid/ApprovedByManagement
ERROR - 2026-02-26 10:31:12 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:31:32 --> 404 Page Not Found: ../modules/bid/controllers/Bid/OEMBids
ERROR - 2026-02-26 10:36:42 --> 404 Page Not Found: ../modules/bid/controllers/Bid/OEMBids
ERROR - 2026-02-26 10:38:43 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:38:51 --> 404 Page Not Found: ../modules/bid/controllers/Bid/UploadChecklist
ERROR - 2026-02-26 10:39:42 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:44:42 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:59:33 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 10:59:44 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-26 15:32:35 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: SELECT `b`.*, `s`.`DisplayName`, `s`.`BadgeClass`
FROM `BidCreation` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`BidStatus`
WHERE `b`.`BidStatus` = '3'
ORDER BY `b`.`CreationTs` DESC
ERROR - 2026-02-26 15:33:08 --> 404 Page Not Found: 
ERROR - 2026-02-26 15:33:15 --> 404 Page Not Found: 
ERROR - 2026-02-26 15:36:00 --> 404 Page Not Found: 
ERROR - 2026-02-26 15:40:39 --> Query error: Table 'bid_core_db.bidcreation' doesn't exist - Invalid query: SELECT COUNT(*) AS `numrows`
FROM `BidCreation`
WHERE `BidStatus` = '1'
ERROR - 2026-02-26 17:28:09 --> 404 Page Not Found: 
ERROR - 2026-02-26 17:35:34 --> Query error: Table 'bid_core_db.bids' doesn't exist - Invalid query: SELECT `b`.`BidId`, `b`.`BidTitle`, `b`.`BidType`, `b`.`Department`, `b`.`PublishedDate`, `s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `bids` `b`
LEFT JOIN `bidstatus` `s` ON `s`.`Sid` = `b`.`StatusId`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
ERROR - 2026-02-26 17:37:32 --> Query error: Table 'bid_core_db.departmentmaster' doesn't exist - Invalid query: SELECT `b`.`BidId`, `b`.`BidTitle`, `b`.`BidType`, `d`.`DepartmentName`, `b`.`PublishedDate`, `s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `BidCreationDetails` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`StatusId`
LEFT JOIN `DepartmentMaster` `d` ON `d`.`Did` = `b`.`DepartmentId`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
ERROR - 2026-02-26 17:37:53 --> Query error: Unknown column 'b.BidId' in 'field list' - Invalid query: SELECT `b`.`BidId`, `b`.`BidTitle`, `b`.`BidType`, `b`.`Department`, `b`.`PublishedDate`, `s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `BidCreationDetails` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`StatusId`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
ERROR - 2026-02-26 17:38:07 --> Query error: Unknown column 'b.Department' in 'field list' - Invalid query: SELECT `b`.`Bcid`, `b`.`BidTitle`, `b`.`BidType`, `b`.`Department`, `b`.`PublishedDate`, `s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `BidCreationDetails` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`StatusId`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
ERROR - 2026-02-26 17:38:19 --> Query error: Unknown column 'b.StatusId' in 'on clause' - Invalid query: SELECT `b`.`Bcid`, `b`.`BidTitle`, `b`.`BidType`, `b`.`BidDepartment`, `b`.`PublishedDate`, `s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `BidCreationDetails` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`StatusId`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
ERROR - 2026-02-26 17:41:50 --> Query error: Unknown column 'b.CreationTs s.DisplayName' in 'field list' - Invalid query: SELECT `b`.`Bcid`, `b`.`BidTitle`, `b`.`BidType`, `b`.`BidDepartment`, `b`.`PublishedDate`, `b`.`BidStatus`, `b`.`CreationTs s`.`DisplayName` as `StatusName`, `s`.`BadgeClass`
FROM `BidCreationDetails` `b`
LEFT JOIN `BidStatusMaster` `s` ON `s`.`Sid` = `b`.`BidStatus`
ORDER BY `b`.`CreationTs` DESC
 LIMIT 10
