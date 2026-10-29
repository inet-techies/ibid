-- Migration: Create tender_factsheet_evidence table
-- Supports: Multiple evidence passages per field, page numbers, verification status, confidence score, text coordinates, document hash

CREATE TABLE IF NOT EXISTS `tender_factsheet_evidence` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tfsid` INT NOT NULL,
  `bcid` INT NULL,
  `document_path` VARCHAR(255) NULL,
  `document_hash` VARCHAR(64) NULL,
  `field_key` VARCHAR(100) NOT NULL,
  `field_section` VARCHAR(50) NULL,
  `extracted_value` TEXT NULL,
  `page_number` INT NULL,
  `supporting_quote` TEXT NULL,
  `verification_status` ENUM('verified', 'needs_review', 'not_found') DEFAULT 'needs_review',
  `confidence_score` DECIMAL(4,2) NULL,
  `confidence_level` VARCHAR(20) DEFAULT 'medium',
  `extraction_method` VARCHAR(50) DEFAULT 'text',
  `coordinates_json` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_tfsid` (`tfsid`),
  KEY `idx_bcid` (`bcid`),
  KEY `idx_field_key` (`field_key`),
  KEY `idx_doc_hash` (`document_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
