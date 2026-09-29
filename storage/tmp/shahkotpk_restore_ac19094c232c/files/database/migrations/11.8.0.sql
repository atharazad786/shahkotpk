-- ShahkotPK v11.8.0 — Business Customer CRM + Chat + Quote Pipeline

CREATE TABLE IF NOT EXISTS business_crm_contacts_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL DEFAULT 0, business_id BIGINT NOT NULL, user_id BIGINT NULL,
 customer_name VARCHAR(160) NOT NULL DEFAULT '', phone VARCHAR(60) NOT NULL DEFAULT '', email VARCHAR(190) NOT NULL DEFAULT '', source VARCHAR(50) NOT NULL DEFAULT 'website', status VARCHAR(30) NOT NULL DEFAULT 'lead', tags_json LONGTEXT NULL, last_contact_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_crm_business (tenant_id,business_id,last_contact_at), KEY idx_crm_user (user_id), KEY idx_crm_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS business_crm_conversations_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL DEFAULT 0, business_id BIGINT NOT NULL, contact_id BIGINT NOT NULL, public_token CHAR(48) NOT NULL, subject VARCHAR(190) NOT NULL DEFAULT '', status VARCHAR(30) NOT NULL DEFAULT 'open', last_message_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uniq_public_token(public_token), KEY idx_conv_business(tenant_id,business_id,status,last_message_at), KEY idx_conv_contact(contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS business_crm_messages_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, conversation_id BIGINT NOT NULL, sender_type VARCHAR(20) NOT NULL DEFAULT 'customer', sender_user_id BIGINT NULL, message TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_messages_conversation(conversation_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS business_quotes_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL DEFAULT 0, business_id BIGINT NOT NULL, contact_id BIGINT NOT NULL, conversation_id BIGINT NULL, quote_no VARCHAR(40) NOT NULL, title VARCHAR(190) NOT NULL DEFAULT 'Quotation', subtotal DECIMAL(14,2) NOT NULL DEFAULT 0, discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0, total_amount DECIMAL(14,2) NOT NULL DEFAULT 0, valid_until DATE NULL, status VARCHAR(30) NOT NULL DEFAULT 'draft', note TEXT NULL, created_by BIGINT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uniq_quote_no(tenant_id,quote_no), KEY idx_quote_business(tenant_id,business_id,status,created_at), KEY idx_quote_contact(contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS business_quote_items_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, quote_id BIGINT NOT NULL, item_name VARCHAR(190) NOT NULL, description VARCHAR(500) NOT NULL DEFAULT '', quantity DECIMAL(12,2) NOT NULL DEFAULT 1, unit_price DECIMAL(14,2) NOT NULL DEFAULT 0, line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
 PRIMARY KEY(id), KEY idx_quote_items(quote_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS business_followups_v1180 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT NOT NULL DEFAULT 0, business_id BIGINT NOT NULL, contact_id BIGINT NOT NULL, due_at DATETIME NOT NULL, note VARCHAR(1000) NOT NULL DEFAULT '', status VARCHAR(30) NOT NULL DEFAULT 'pending', assigned_user_id BIGINT NULL, completed_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_followup_due(tenant_id,business_id,status,due_at), KEY idx_followup_contact(contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings(setting_key,setting_value) VALUES ('installed_app_version','11.8.0'),('business_crm_version','11.8.0') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
