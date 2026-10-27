<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-02-25 06:13:23 --> 404 Page Not Found: /index
ERROR - 2026-02-25 06:13:51 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-25 06:16:26 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-25 09:38:12 --> 404 Page Not Found: ../modules/bid/controllers/Bid/BidEvaluated
ERROR - 2026-02-25 14:17:23 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: SELECT `StatusId`
FROM `BidCreationDetails`
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:17:24 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: SELECT `StatusId`
FROM `BidCreationDetails`
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:17:24 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: SELECT `StatusId`
FROM `BidCreationDetails`
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:18:15 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: SELECT `StatusId`
FROM `BidCreationDetails`
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:20:30 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `StatusId` = NULL, `ActualDate` = NULL, `ApprovedByManagement` = NULL, `ApprovedByWhom` = NULL, `UpdatedTs` = '2026-02-25 14:20:30'
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:21:00 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `StatusId` = NULL, `ActualDate` = '2026-02-24', `ApprovedByManagement` = '1', `ApprovedByWhom` = '3', `UpdatedTs` = '2026-02-25 14:21:00'
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:21:32 --> Query error: Unknown column 'StatusId' in 'field list' - Invalid query: UPDATE `BidCreationDetails` SET `StatusId` = NULL, `ActualDate` = NULL, `ApprovedByManagement` = NULL, `ApprovedByWhom` = NULL, `UpdatedTs` = '2026-02-25 14:21:32'
WHERE `Bcid` IS NULL
ERROR - 2026-02-25 14:59:16 --> Query error: Column 'UpdatedBy' cannot be null - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '1', '3', '2026-02-25', '1', '1', NULL, 'sfsfds sdfsdf sdf sdff sdf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 14:59:16')
ERROR - 2026-02-25 14:59:50 --> Query error: Column 'UpdatedBy' cannot be null - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '1', '3', '2026-02-24', '1', '1', NULL, 'dfgsfsdfsdfsf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 14:59:50')
ERROR - 2026-02-25 15:11:29 --> Query error: Column 'UpdatedBy' cannot be null - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '1', '3', '2026-02-24', '1', '1', NULL, 'dfgfdgdfgdfg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 15:11:29')
ERROR - 2026-02-25 15:21:38 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`bid_core_db`.`bidstatustracking`, CONSTRAINT `fk_BidTracking_ApprovedUser` FOREIGN KEY (`ApprovedByWhom`) REFERENCES `bidapproverdetails` (`Baid`)) - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '3', '2', '2026-02-25', '2', '', '1', 'sdfsdfsdfsdf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 15:21:38')
ERROR - 2026-02-25 15:22:34 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`bid_core_db`.`bidstatustracking`, CONSTRAINT `fk_BidTracking_ApprovedUser` FOREIGN KEY (`ApprovedByWhom`) REFERENCES `bidapproverdetails` (`Baid`)) - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '2', '4', '2026-02-25', '2', '', '1', 'sdfsdfsdfsdf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 15:22:34')
ERROR - 2026-02-25 15:23:57 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`bid_core_db`.`bidstatustracking`, CONSTRAINT `fk_BidTracking_ApprovedUser` FOREIGN KEY (`ApprovedByWhom`) REFERENCES `bidapproverdetails` (`Baid`)) - Invalid query: INSERT INTO `BidStatusTracking` (`Bcid`, `OldStatusId`, `CurrentStatusId`, `ActualDate`, `ApprovedByManagement`, `ApprovedByWhom`, `UpdatedBy`, `Remarks`, `Ipaddress`, `UserAgent`, `IsSystemGenerated`, `CreationTs`) VALUES ('7', '4', '5', '2026-02-25', '2', '', '1', 'sdfsdfsdfsdf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 15:23:57')
