CREATE TABLE IF NOT EXISTS ai_providers_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  provider_type VARCHAR(60) NOT NULL DEFAULT 'openai_compatible',
  base_url VARCHAR(500) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(32) NULL,
  model VARCHAR(190) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  timeout_seconds INT NOT NULL DEFAULT 30,
  config_json LONGTEXT NULL,
  last_test_status VARCHAR(30) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ai710_provider_tenant (tenant_id,enabled,is_default),
  KEY idx_ai710_provider_type (provider_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_integrations_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  integration_type VARCHAR(60) NOT NULL DEFAULT 'custom_api',
  base_url VARCHAR(500) NULL,
  auth_mode VARCHAR(40) NOT NULL DEFAULT 'bearer',
  auth_header VARCHAR(100) NULL,
  secret_cipher LONGTEXT NULL,
  secret_hint VARCHAR(32) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  ai_callable TINYINT(1) NOT NULL DEFAULT 0,
  requires_approval TINYINT(1) NOT NULL DEFAULT 1,
  config_json LONGTEXT NULL,
  last_test_status VARCHAR(30) NULL,
  last_test_message VARCHAR(500) NULL,
  last_test_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ai710_integration_tenant (tenant_id,enabled),
  KEY idx_ai710_integration_type (integration_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_knowledge_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(220) NOT NULL,
  category VARCHAR(100) NULL,
  source_type VARCHAR(40) NOT NULL DEFAULT 'manual',
  source_url VARCHAR(500) NULL,
  content LONGTEXT NOT NULL,
  tags VARCHAR(500) NULL,
  priority INT NOT NULL DEFAULT 50,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FULLTEXT KEY ft_ai710_knowledge (title,content,tags),
  KEY idx_ai710_knowledge_tenant (tenant_id,status,priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_training_examples_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(220) NULL,
  user_input TEXT NOT NULL,
  assistant_output LONGTEXT NOT NULL,
  tags VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FULLTEXT KEY ft_ai710_examples (user_input,assistant_output,tags),
  KEY idx_ai710_examples_tenant (tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_agents_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description VARCHAR(500) NULL,
  system_prompt LONGTEXT NULL,
  provider_id BIGINT UNSIGNED NULL,
  model_override VARCHAR(190) NULL,
  allowed_tools_json LONGTEXT NULL,
  temperature DECIMAL(4,2) NOT NULL DEFAULT 0.30,
  max_output_tokens INT NOT NULL DEFAULT 1200,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_ai710_agent (tenant_id,slug),
  KEY idx_ai710_agent_default (tenant_id,enabled,is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_action_queue_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  requester_user_id BIGINT UNSIGNED NULL,
  agent_id BIGINT UNSIGNED NULL,
  conversation_key VARCHAR(120) NULL,
  action_key VARCHAR(120) NOT NULL,
  action_label VARCHAR(220) NULL,
  risk_level VARCHAR(20) NOT NULL DEFAULT 'medium',
  input_json LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  result_json LONGTEXT NULL,
  error_text VARCHAR(1000) NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  executed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ai710_action_tenant (tenant_id,status,created_at),
  KEY idx_ai710_action_user (requester_user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_conversations_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  session_key VARCHAR(120) NOT NULL,
  agent_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NULL,
  channel VARCHAR(40) NOT NULL DEFAULT 'web',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_ai710_conversation (tenant_id,session_key),
  KEY idx_ai710_conversation_user (tenant_id,user_id,updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_messages_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  conversation_id BIGINT UNSIGNED NOT NULL,
  role VARCHAR(30) NOT NULL,
  content LONGTEXT NOT NULL,
  provider_id BIGINT UNSIGNED NULL,
  model VARCHAR(190) NULL,
  prompt_tokens INT NULL,
  completion_tokens INT NULL,
  latency_ms INT NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ai710_message_conversation (conversation_id,id),
  KEY idx_ai710_message_tenant (tenant_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_automation_rules_v710 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(180) NOT NULL,
  trigger_key VARCHAR(120) NOT NULL,
  agent_id BIGINT UNSIGNED NULL,
  action_key VARCHAR(120) NULL,
  conditions_json LONGTEXT NULL,
  action_input_json LONGTEXT NULL,
  requires_approval TINYINT(1) NOT NULL DEFAULT 1,
  enabled TINYINT(1) NOT NULL DEFAULT 0,
  last_run_at DATETIME NULL,
  run_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ai710_rules_tenant (tenant_id,enabled,trigger_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO ai_agents_v710 (tenant_id,name,slug,description,system_prompt,allowed_tools_json,temperature,max_output_tokens,enabled,is_default,created_at,updated_at)
VALUES (0,'ShahkotPK Command Assistant','shahkotpk-command','Default portal assistant for search, support and controlled admin actions.','You are the ShahkotPK AI assistant. Use verified portal knowledge and tools. Never claim an action was completed unless the tool result confirms it. High-impact changes require admin approval. Do not expose secrets or private user data.','["portal_search","module_status","dashboard_summary","knowledge_search"]',0.30,1200,1,1,NOW(),NOW());

CREATE TABLE IF NOT EXISTS ai_tenant_settings_v710 (
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  settings_json LONGTEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
