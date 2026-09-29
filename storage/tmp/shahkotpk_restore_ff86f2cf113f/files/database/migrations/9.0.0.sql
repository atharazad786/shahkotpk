CREATE TABLE IF NOT EXISTS enterprise_settings_v900 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  settings_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_branding_v900 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  app_name VARCHAR(160) NULL,
  bundle_android VARCHAR(190) NULL,
  bundle_ios VARCHAR(190) NULL,
  primary_color VARCHAR(30) NULL,
  secondary_color VARCHAR(30) NULL,
  logo_url VARCHAR(500) NULL,
  splash_url VARCHAR(500) NULL,
  support_url VARCHAR(500) NULL,
  privacy_url VARCHAR(500) NULL,
  terms_url VARCHAR(500) NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_app_versions_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  platform VARCHAR(20) NOT NULL,
  version_name VARCHAR(40) NOT NULL,
  build_number INT NOT NULL DEFAULT 1,
  minimum_build INT NOT NULL DEFAULT 1,
  update_mode VARCHAR(20) NOT NULL DEFAULT 'optional',
  store_url VARCHAR(500) NULL,
  release_notes TEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  released_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by BIGINT UNSIGNED NULL,
  PRIMARY KEY(id),
  KEY ix_v900_appver (tenant_id,platform,enabled,build_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_devices_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  device_uuid VARCHAR(190) NOT NULL,
  platform VARCHAR(20) NOT NULL DEFAULT 'android',
  push_provider VARCHAR(30) NULL,
  push_token TEXT NULL,
  app_version VARCHAR(40) NULL,
  build_number INT NULL,
  locale VARCHAR(20) NULL,
  timezone VARCHAR(80) NULL,
  last_ip_hash CHAR(64) NULL,
  last_seen_at DATETIME NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_device (tenant_id,device_uuid),
  KEY ix_v900_device_user (tenant_id,user_id,enabled),
  KEY ix_v900_device_seen (tenant_id,last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_sessions_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  device_id BIGINT UNSIGNED NULL,
  token_hash CHAR(64) NOT NULL,
  token_hint VARCHAR(40) NULL,
  scopes_json LONGTEXT NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_mobile_token (token_hash),
  KEY ix_v900_mobile_user (tenant_id,user_id,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_push_profiles_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  provider_type VARCHAR(40) NOT NULL DEFAULT 'onesignal',
  endpoint_url VARCHAR(500) NULL,
  app_id VARCHAR(255) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(80) NULL,
  config_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  last_test_status VARCHAR(30) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_push_profile (tenant_id,enabled,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_push_queue_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  profile_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  device_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  body TEXT NOT NULL,
  deep_link VARCHAR(500) NULL,
  data_json LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  attempts INT NOT NULL DEFAULT 0,
  available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  provider_message_id VARCHAR(190) NULL,
  last_error VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_push_claim (tenant_id,status,available_at,id),
  KEY ix_v900_push_user (tenant_id,user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_realtime_events_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  channel_key VARCHAR(120) NOT NULL DEFAULT 'public',
  event_key VARCHAR(120) NOT NULL,
  payload_json LONGTEXT NULL,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_realtime_poll (tenant_id,channel_key,id),
  KEY ix_v900_realtime_user (tenant_id,user_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_sync_cursors_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  device_id BIGINT UNSIGNED NOT NULL,
  dataset_key VARCHAR(80) NOT NULL,
  cursor_value VARCHAR(190) NULL,
  synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_sync_cursor (tenant_id,device_id,dataset_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mobile_deep_links_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  code VARCHAR(120) NOT NULL,
  web_url VARCHAR(500) NULL,
  app_path VARCHAR(500) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_deeplink (tenant_id,code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_sender_profiles_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  channel VARCHAR(30) NOT NULL,
  provider_type VARCHAR(50) NOT NULL DEFAULT 'custom',
  sender_identity VARCHAR(190) NULL,
  endpoint_url VARCHAR(500) NULL,
  account_ref VARCHAR(255) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(80) NULL,
  webhook_token_hash CHAR(64) NULL,
  webhook_token_hint VARCHAR(80) NULL,
  config_json LONGTEXT NULL,
  ai_auto_reply TINYINT(1) NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  last_test_status VARCHAR(30) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_sender (tenant_id,channel,enabled,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_templates_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  channel VARCHAR(30) NOT NULL DEFAULT 'in_app',
  name VARCHAR(160) NOT NULL,
  template_key VARCHAR(120) NOT NULL,
  subject VARCHAR(220) NULL,
  body LONGTEXT NOT NULL,
  variables_json LONGTEXT NULL,
  provider_template_ref VARCHAR(190) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_template (tenant_id,channel,template_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_conversations_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  channel VARCHAR(30) NOT NULL DEFAULT 'in_app',
  sender_profile_id BIGINT UNSIGNED NULL,
  external_thread_id VARCHAR(190) NULL,
  user_id BIGINT UNSIGNED NULL,
  contact_name VARCHAR(190) NULL,
  contact_address VARCHAR(255) NULL,
  subject VARCHAR(255) NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  unread_count INT NOT NULL DEFAULT 0,
  last_message_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_conv_inbox (tenant_id,status,last_message_at),
  KEY ix_v900_conv_external (tenant_id,channel,external_thread_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_messages_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  conversation_id BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NULL,
  direction VARCHAR(20) NOT NULL DEFAULT 'outbound',
  sender_profile_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  external_message_id VARCHAR(190) NULL,
  message_type VARCHAR(30) NOT NULL DEFAULT 'text',
  subject VARCHAR(220) NULL,
  body LONGTEXT NOT NULL,
  media_url VARCHAR(500) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'queued',
  ai_generated TINYINT(1) NOT NULL DEFAULT 0,
  meta_json LONGTEXT NULL,
  sent_at DATETIME NULL,
  delivered_at DATETIME NULL,
  read_at DATETIME NULL,
  failed_at DATETIME NULL,
  last_error VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_message_conv (tenant_id,conversation_id,id),
  KEY ix_v900_message_campaign (tenant_id,campaign_id,status),
  KEY ix_v900_message_delivery (tenant_id,status,created_at),
  KEY ix_v900_message_external (tenant_id,external_message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_campaigns_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  channel VARCHAR(30) NOT NULL,
  sender_profile_id BIGINT UNSIGNED NULL,
  template_id BIGINT UNSIGNED NULL,
  audience_json LONGTEXT NULL,
  subject VARCHAR(220) NULL,
  body LONGTEXT NULL,
  schedule_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  total_count INT NOT NULL DEFAULT 0,
  sent_count INT NOT NULL DEFAULT 0,
  delivered_count INT NOT NULL DEFAULT 0,
  read_count INT NOT NULL DEFAULT 0,
  failed_count INT NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_campaign (tenant_id,status,schedule_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comm_delivery_events_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  message_id BIGINT UNSIGNED NULL,
  external_message_id VARCHAR(190) NULL,
  event_type VARCHAR(40) NOT NULL,
  provider_status VARCHAR(80) NULL,
  payload_json LONGTEXT NULL,
  occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_delivery_ext (tenant_id,external_message_id,occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_gateways_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  gateway_type VARCHAR(50) NOT NULL DEFAULT 'custom',
  environment VARCHAR(20) NOT NULL DEFAULT 'sandbox',
  endpoint_url VARCHAR(500) NULL,
  merchant_id VARCHAR(190) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(80) NULL,
  webhook_secret_cipher LONGTEXT NULL,
  config_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_gateway (tenant_id,enabled,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  gateway_id BIGINT UNSIGNED NULL,
  transaction_no VARCHAR(80) NOT NULL,
  external_ref VARCHAR(190) NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  direction VARCHAR(20) NOT NULL DEFAULT 'in',
  amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  meta_json LONGTEXT NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_tx_no (tenant_id,transaction_no),
  KEY ix_v900_tx_entity (tenant_id,entity_type,entity_id,status),
  KEY ix_v900_tx_external (tenant_id,gateway_id,external_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settlement_accounts_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  payee_type VARCHAR(50) NOT NULL,
  payee_id BIGINT UNSIGNED NOT NULL,
  display_name VARCHAR(190) NOT NULL,
  method VARCHAR(40) NOT NULL DEFAULT 'bank',
  account_mask VARCHAR(120) NULL,
  account_ref_cipher LONGTEXT NULL,
  bank_name VARCHAR(160) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_settle_account (tenant_id,payee_type,payee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settlement_batches_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  batch_no VARCHAR(80) NOT NULL,
  settlement_type VARCHAR(50) NOT NULL,
  period_start DATE NULL,
  period_end DATE NULL,
  gross_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  commission_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  net_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  paid_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_batch (tenant_id,batch_no),
  KEY ix_v900_batch_status (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settlement_items_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  batch_id BIGINT UNSIGNED NOT NULL,
  payee_type VARCHAR(50) NOT NULL,
  payee_id BIGINT UNSIGNED NOT NULL,
  source_type VARCHAR(60) NULL,
  source_id BIGINT UNSIGNED NULL,
  gross_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  commission_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  net_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_batch_item (tenant_id,batch_id,status),
  KEY ix_v900_payee_item (tenant_id,payee_type,payee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS billing_invoices_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  invoice_no VARCHAR(80) NOT NULL,
  bill_to_type VARCHAR(50) NOT NULL,
  bill_to_id BIGINT UNSIGNED NULL,
  bill_to_name VARCHAR(190) NOT NULL,
  subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  due_date DATE NULL,
  items_json LONGTEXT NULL,
  notes TEXT NULL,
  issued_at DATETIME NULL,
  paid_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_invoice (tenant_id,invoice_no),
  KEY ix_v900_invoice_due (tenant_id,status,due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS billing_subscriptions_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  subscriber_type VARCHAR(50) NOT NULL,
  subscriber_id BIGINT UNSIGNED NOT NULL,
  plan_key VARCHAR(120) NOT NULL,
  amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  interval_key VARCHAR(30) NOT NULL DEFAULT 'monthly',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  starts_at DATETIME NULL,
  renews_at DATETIME NULL,
  ends_at DATETIME NULL,
  auto_renew TINYINT(1) NOT NULL DEFAULT 1,
  gateway_id BIGINT UNSIGNED NULL,
  external_ref VARCHAR(190) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_subscription (tenant_id,status,renews_at),
  KEY ix_v900_subscriber (tenant_id,subscriber_type,subscriber_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS billing_refunds_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  transaction_id BIGINT UNSIGNED NULL,
  refund_no VARCHAR(80) NOT NULL,
  amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  reason VARCHAR(500) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'requested',
  gateway_ref VARCHAR(190) NULL,
  requested_by BIGINT UNSIGNED NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_refund (tenant_id,refund_no),
  KEY ix_v900_refund_status (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commission_rules_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  module_key VARCHAR(60) NOT NULL,
  payee_type VARCHAR(50) NULL,
  rule_type VARCHAR(30) NOT NULL DEFAULT 'percent',
  rate_value DECIMAL(18,4) NOT NULL DEFAULT 0,
  minimum_amount DECIMAL(18,2) NULL,
  maximum_amount DECIMAL(18,2) NULL,
  priority INT NOT NULL DEFAULT 100,
  conditions_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_commission_rule (tenant_id,module_key,enabled,priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_reconciliations_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  reconciliation_no VARCHAR(80) NOT NULL,
  account_key VARCHAR(120) NOT NULL,
  period_start DATETIME NULL,
  period_end DATETIME NULL,
  opening_balance DECIMAL(18,2) NOT NULL DEFAULT 0,
  system_total DECIMAL(18,2) NOT NULL DEFAULT 0,
  gateway_total DECIMAL(18,2) NOT NULL DEFAULT 0,
  difference_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  notes TEXT NULL,
  reconciled_by BIGINT UNSIGNED NULL,
  reconciled_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_reconciliation (tenant_id,reconciliation_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_profiles_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  entity_type VARCHAR(50) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  display_name VARCHAR(190) NOT NULL,
  verification_level VARCHAR(30) NOT NULL DEFAULT 'basic',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  risk_level VARCHAR(20) NOT NULL DEFAULT 'normal',
  identity_hash CHAR(64) NULL,
  identity_mask VARCHAR(40) NULL,
  expires_at DATE NULL,
  assigned_team_id BIGINT UNSIGNED NULL,
  submitted_at DATETIME NULL,
  approved_at DATETIME NULL,
  approved_by BIGINT UNSIGNED NULL,
  rejection_reason VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_kyc_entity (tenant_id,entity_type,entity_id),
  KEY ix_v900_kyc_status (tenant_id,status,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_documents_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  profile_id BIGINT UNSIGNED NOT NULL,
  document_type VARCHAR(60) NOT NULL,
  document_label VARCHAR(190) NULL,
  storage_path VARCHAR(500) NOT NULL,
  original_name VARCHAR(255) NULL,
  mime_type VARCHAR(120) NULL,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  sha256_hash CHAR(64) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  expires_at DATE NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  review_notes VARCHAR(1000) NULL,
  PRIMARY KEY(id),
  KEY ix_v900_kyc_doc (tenant_id,profile_id,status,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_review_history_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  profile_id BIGINT UNSIGNED NOT NULL,
  document_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(50) NOT NULL,
  from_status VARCHAR(30) NULL,
  to_status VARCHAR(30) NULL,
  notes VARCHAR(1000) NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_kyc_history (tenant_id,profile_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_teams_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  member_user_ids_json LONGTEXT NULL,
  entity_types_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_kyc_team (tenant_id,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_workflows_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  description VARCHAR(500) NULL,
  trigger_type VARCHAR(40) NOT NULL DEFAULT 'manual',
  trigger_key VARCHAR(120) NULL,
  schedule_expr VARCHAR(120) NULL,
  graph_json LONGTEXT NOT NULL,
  approval_mode VARCHAR(30) NOT NULL DEFAULT 'safe_writes',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  next_run_at DATETIME NULL,
  last_run_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_workflow_due (tenant_id,enabled,next_run_at),
  KEY ix_v900_workflow_event (tenant_id,enabled,trigger_type,trigger_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_runs_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  workflow_id BIGINT UNSIGNED NOT NULL,
  trigger_type VARCHAR(40) NOT NULL,
  trigger_payload_json LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'queued',
  current_node VARCHAR(120) NULL,
  output_json LONGTEXT NULL,
  error_message VARCHAR(1000) NULL,
  started_at DATETIME NULL,
  finished_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_run (tenant_id,workflow_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_events_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  event_key VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  payload_json LONGTEXT NULL,
  processed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_auto_event (tenant_id,event_key,processed_at,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_content_jobs_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  agent_id BIGINT UNSIGNED NULL,
  content_type VARCHAR(80) NOT NULL,
  source_type VARCHAR(80) NULL,
  source_id BIGINT UNSIGNED NULL,
  prompt_text LONGTEXT NOT NULL,
  output_text LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'queued',
  approval_required TINYINT(1) NOT NULL DEFAULT 1,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  published_at DATETIME NULL,
  error_message VARCHAR(1000) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_content_job (tenant_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_ai_policies_v900 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  monthly_request_limit INT NOT NULL DEFAULT 20000,
  monthly_cost_limit DECIMAL(18,4) NOT NULL DEFAULT 100,
  auto_approve_read TINYINT(1) NOT NULL DEFAULT 1,
  auto_approve_low_risk TINYINT(1) NOT NULL DEFAULT 0,
  require_approval_external_write TINYINT(1) NOT NULL DEFAULT 1,
  require_approval_publish TINYINT(1) NOT NULL DEFAULT 1,
  policy_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_ai_usage_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  usage_date DATE NOT NULL,
  agent_id BIGINT UNSIGNED NULL,
  provider_id BIGINT UNSIGNED NULL,
  workflow_id BIGINT UNSIGNED NULL,
  requests INT NOT NULL DEFAULT 0,
  input_tokens BIGINT NOT NULL DEFAULT 0,
  output_tokens BIGINT NOT NULL DEFAULT 0,
  estimated_cost DECIMAL(18,6) NOT NULL DEFAULT 0,
  success_count INT NOT NULL DEFAULT 0,
  failure_count INT NOT NULL DEFAULT 0,
  latency_total_ms BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_ai_usage (tenant_id,usage_date,agent_id,provider_id,workflow_id),
  KEY ix_v900_ai_usage_date (tenant_id,usage_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_contacts_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  contact_type VARCHAR(40) NOT NULL DEFAULT 'lead',
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  company VARCHAR(190) NULL,
  source VARCHAR(80) NULL,
  owner_user_id BIGINT UNSIGNED NULL,
  stage VARCHAR(60) NOT NULL DEFAULT 'new',
  score INT NOT NULL DEFAULT 0,
  tags_json LONGTEXT NULL,
  consent_json LONGTEXT NULL,
  notes TEXT NULL,
  last_activity_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_crm_stage (tenant_id,stage,owner_user_id),
  KEY ix_v900_crm_email (tenant_id,email),
  KEY ix_v900_crm_phone (tenant_id,phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_activities_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  contact_id BIGINT UNSIGNED NOT NULL,
  activity_type VARCHAR(40) NOT NULL,
  subject VARCHAR(220) NULL,
  body TEXT NULL,
  due_at DATETIME NULL,
  completed_at DATETIME NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_crm_activity (tenant_id,contact_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS helpdesk_tickets_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  ticket_no VARCHAR(80) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  contact_id BIGINT UNSIGNED NULL,
  channel VARCHAR(30) NOT NULL DEFAULT 'portal',
  category VARCHAR(80) NULL,
  subject VARCHAR(255) NOT NULL,
  description LONGTEXT NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  assigned_user_id BIGINT UNSIGNED NULL,
  sla_policy_id BIGINT UNSIGNED NULL,
  first_response_due_at DATETIME NULL,
  resolution_due_at DATETIME NULL,
  first_responded_at DATETIME NULL,
  resolved_at DATETIME NULL,
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_ticket (tenant_id,ticket_no),
  KEY ix_v900_ticket_queue (tenant_id,status,priority,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS helpdesk_replies_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  ticket_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  author_type VARCHAR(30) NOT NULL DEFAULT 'staff',
  body LONGTEXT NOT NULL,
  internal_note TINYINT(1) NOT NULL DEFAULT 0,
  ai_generated TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_ticket_reply (tenant_id,ticket_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sla_policies_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  first_response_minutes INT NOT NULL DEFAULT 60,
  resolution_minutes INT NOT NULL DEFAULT 1440,
  business_hours_json LONGTEXT NULL,
  escalation_user_ids_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_sla (tenant_id,enabled,priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hr_employees_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  employee_no VARCHAR(80) NOT NULL,
  name VARCHAR(190) NOT NULL,
  department VARCHAR(120) NULL,
  designation VARCHAR(120) NULL,
  manager_employee_id BIGINT UNSIGNED NULL,
  employment_type VARCHAR(40) NOT NULL DEFAULT 'full_time',
  join_date DATE NULL,
  shift_json LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_employee (tenant_id,employee_no),
  KEY ix_v900_employee_user (tenant_id,user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hr_attendance_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  employee_id BIGINT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  check_in_at DATETIME NULL,
  check_out_at DATETIME NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'present',
  source VARCHAR(40) NOT NULL DEFAULT 'admin',
  location_label VARCHAR(190) NULL,
  notes VARCHAR(500) NULL,
  approved_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_attendance (tenant_id,employee_id,attendance_date),
  KEY ix_v900_attendance_date (tenant_id,attendance_date,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounting_accounts_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  account_code VARCHAR(60) NOT NULL,
  name VARCHAR(190) NOT NULL,
  account_type VARCHAR(40) NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_account (tenant_id,account_code),
  KEY ix_v900_account_type (tenant_id,account_type,enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounting_journals_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  journal_no VARCHAR(80) NOT NULL,
  journal_date DATE NOT NULL,
  reference_type VARCHAR(60) NULL,
  reference_id BIGINT UNSIGNED NULL,
  memo VARCHAR(500) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'posted',
  total_debit DECIMAL(18,2) NOT NULL DEFAULT 0,
  total_credit DECIMAL(18,2) NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_journal (tenant_id,journal_no),
  KEY ix_v900_journal_date (tenant_id,journal_date,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounting_journal_lines_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  journal_id BIGINT UNSIGNED NOT NULL,
  account_id BIGINT UNSIGNED NOT NULL,
  description VARCHAR(500) NULL,
  debit DECIMAL(18,2) NOT NULL DEFAULT 0,
  credit DECIMAL(18,2) NOT NULL DEFAULT 0,
  entity_type VARCHAR(60) NULL,
  entity_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_journal_line (tenant_id,journal_id,id),
  KEY ix_v900_account_line (tenant_id,account_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS procurement_vendors_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  vendor_code VARCHAR(80) NOT NULL,
  name VARCHAR(190) NOT NULL,
  contact_name VARCHAR(190) NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  tax_ref_mask VARCHAR(80) NULL,
  payment_terms VARCHAR(120) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  rating DECIMAL(5,2) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_vendor (tenant_id,vendor_code),
  KEY ix_v900_vendor_status (tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS procurement_orders_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  po_no VARCHAR(80) NOT NULL,
  vendor_id BIGINT UNSIGNED NOT NULL,
  order_date DATE NOT NULL,
  expected_date DATE NULL,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  approval_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_po (tenant_id,po_no),
  KEY ix_v900_po_status (tenant_id,status,approval_status,order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS procurement_order_items_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  order_id BIGINT UNSIGNED NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  sku VARCHAR(120) NULL,
  quantity DECIMAL(18,4) NOT NULL DEFAULT 1,
  unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(18,2) NOT NULL DEFAULT 0,
  received_quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_po_item (tenant_id,order_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS franchise_kpis_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  snapshot_date DATE NOT NULL,
  metric_key VARCHAR(120) NOT NULL,
  metric_value DECIMAL(20,4) NOT NULL DEFAULT 0,
  target_value DECIMAL(20,4) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_franchise_kpi (tenant_id,snapshot_date,metric_key),
  KEY ix_v900_franchise_metric (metric_key,snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS compliance_controls_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  control_code VARCHAR(80) NOT NULL,
  category VARCHAR(100) NOT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NULL,
  owner_user_id BIGINT UNSIGNED NULL,
  frequency VARCHAR(40) NOT NULL DEFAULT 'quarterly',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  next_review_at DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_control (tenant_id,control_code),
  KEY ix_v900_control_review (tenant_id,status,next_review_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS compliance_reviews_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  control_id BIGINT UNSIGNED NOT NULL,
  result VARCHAR(30) NOT NULL DEFAULT 'pending',
  evidence_note TEXT NULL,
  evidence_path VARCHAR(500) NULL,
  reviewer_user_id BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  remediation_due_at DATE NULL,
  remediation_status VARCHAR(30) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_review (tenant_id,control_id,created_at),
  KEY ix_v900_remediation (tenant_id,remediation_status,remediation_due_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  actor_user_id BIGINT UNSIGNED NULL,
  module_key VARCHAR(80) NOT NULL,
  action_key VARCHAR(100) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  risk_level VARCHAR(20) NOT NULL DEFAULT 'low',
  summary VARCHAR(1000) NOT NULL,
  before_hash CHAR(64) NULL,
  after_hash CHAR(64) NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  KEY ix_v900_audit_scope (tenant_id,module_key,created_at),
  KEY ix_v900_audit_actor (tenant_id,actor_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_approvals_v900 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  run_id BIGINT UNSIGNED NOT NULL,
  workflow_id BIGINT UNSIGNED NOT NULL,
  node_index INT NOT NULL,
  node_label VARCHAR(190) NULL,
  risk_level VARCHAR(20) NOT NULL DEFAULT 'medium',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_by BIGINT UNSIGNED NULL,
  decided_at DATETIME NULL,
  decision_notes VARCHAR(1000) NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_v900_run_approval (tenant_id,run_id,node_index),
  KEY ix_v900_approval_queue (tenant_id,status,requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_rate_limits_v900 (
  bucket_key CHAR(64) NOT NULL,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  hit_count INT NOT NULL DEFAULT 0,
  window_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(bucket_key),
  KEY ix_v900_rate_tenant (tenant_id,updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
