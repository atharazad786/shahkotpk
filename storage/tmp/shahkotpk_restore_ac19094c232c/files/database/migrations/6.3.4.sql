-- ShahkotPK v6.3.4 Password Recovery Hotfix
-- Additive and idempotent migration; existing data is preserved.
CREATE TABLE IF NOT EXISTS password_reset_tokens_v634 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  email_hash CHAR(64) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  requester_hash CHAR(64) NOT NULL,
  admin_context TINYINT(1) NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used_at DATETIME NULL,
  PRIMARY KEY(id),
  UNIQUE KEY uq_pr634_token(token_hash),
  KEY ix_pr634_user(user_id,used_at,expires_at),
  KEY ix_pr634_rate_email(email_hash,requested_at),
  KEY ix_pr634_rate_client(requester_hash,requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
