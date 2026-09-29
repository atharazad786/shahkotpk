-- ShahkotPK v2.6.0 Advertisement Manager
ALTER TABLE advertisement_details
  ADD COLUMN description TEXT NULL AFTER notes,
  ADD COLUMN alt_text VARCHAR(255) NULL AFTER description,
  ADD COLUMN device_target ENUM('all','desktop','mobile','tablet') NOT NULL DEFAULT 'all' AFTER alt_text,
  ADD COLUMN priority INT NOT NULL DEFAULT 10 AFTER device_target,
  ADD COLUMN impression_limit BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER priority,
  ADD COLUMN click_limit BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER impression_limit,
  ADD COLUMN frequency_cap INT UNSIGNED NOT NULL DEFAULT 0 AFTER click_limit,
  ADD COLUMN audience_note VARCHAR(500) NULL AFTER frequency_cap,
  ADD COLUMN billing_status ENUM('unpaid','paid','waived','refunded') NOT NULL DEFAULT 'unpaid' AFTER audience_note,
  ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER billing_status,
  ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
  ADD INDEX idx_ad_details_device (device_target),
  ADD INDEX idx_ad_details_billing (billing_status),
  ADD CONSTRAINT fk_ad_details_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;
