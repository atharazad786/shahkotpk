-- ShahkotPK v2.7.0 Subscription Revenue & Accounts Ledger

CREATE TABLE IF NOT EXISTS accounting_accounts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(160) NOT NULL,
  account_type ENUM('asset','liability','equity','revenue','expense') NOT NULL,
  system_key VARCHAR(80) NULL UNIQUE,
  parent_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_accounting_type (account_type),
  INDEX idx_accounting_parent (parent_id),
  CONSTRAINT fk_accounting_parent FOREIGN KEY (parent_id) REFERENCES accounting_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounting_journals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(80) NOT NULL UNIQUE,
  journal_date DATE NOT NULL,
  source_type VARCHAR(60) NOT NULL DEFAULT 'manual',
  source_id BIGINT UNSIGNED NULL,
  description VARCHAR(700) NOT NULL,
  status ENUM('posted','void') NOT NULL DEFAULT 'posted',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  voided_at DATETIME NULL,
  INDEX idx_journal_date (journal_date),
  INDEX idx_journal_source (source_type,source_id),
  CONSTRAINT fk_journal_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounting_journal_lines (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  journal_id BIGINT UNSIGNED NOT NULL,
  account_id BIGINT UNSIGNED NOT NULL,
  line_no INT UNSIGNED NOT NULL DEFAULT 1,
  debit DECIMAL(14,2) NOT NULL DEFAULT 0,
  credit DECIMAL(14,2) NOT NULL DEFAULT 0,
  memo VARCHAR(500) NULL,
  user_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NULL,
  payment_order_id BIGINT UNSIGNED NULL,
  subscription_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_journal_line_journal (journal_id),
  INDEX idx_journal_line_account (account_id),
  INDEX idx_journal_line_business (business_id),
  INDEX idx_journal_line_payment (payment_order_id),
  INDEX idx_journal_line_subscription (subscription_id),
  CONSTRAINT fk_journal_line_journal FOREIGN KEY (journal_id) REFERENCES accounting_journals(id) ON DELETE CASCADE,
  CONSTRAINT fk_journal_line_account FOREIGN KEY (account_id) REFERENCES accounting_accounts(id),
  CONSTRAINT fk_journal_line_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_journal_line_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  CONSTRAINT fk_journal_line_payment FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE SET NULL,
  CONSTRAINT fk_journal_line_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(80) NOT NULL UNIQUE,
  subscription_id BIGINT UNSIGNED NULL,
  business_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  payment_order_id BIGINT UNSIGNED NULL UNIQUE,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('unpaid','partial','paid','overdue','void','waived') NOT NULL DEFAULT 'unpaid',
  notes VARCHAR(700) NULL,
  created_by BIGINT UNSIGNED NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sub_invoice_business (business_id),
  INDEX idx_sub_invoice_status (status),
  INDEX idx_sub_invoice_due (due_date),
  CONSTRAINT fk_sub_invoice_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
  CONSTRAINT fk_sub_invoice_business FOREIGN KEY (business_id) REFERENCES businesses(id),
  CONSTRAINT fk_sub_invoice_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
  CONSTRAINT fk_sub_invoice_order FOREIGN KEY (payment_order_id) REFERENCES payment_orders(id) ON DELETE SET NULL,
  CONSTRAINT fk_sub_invoice_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO accounting_accounts(code,name,account_type,system_key,description,status) VALUES
('1000','Cash on Hand','asset','cash_on_hand','Cash received directly by administration.',1),
('1010','Bank / Raast','asset','bank','Bank transfer and Raast collections.',1),
('1020','JazzCash Wallet','asset','jazzcash','JazzCash collections.',1),
('1030','Easypaisa Wallet','asset','easypaisa','Easypaisa collections.',1),
('1040','PayFast Clearing','asset','payfast','PayFast online checkout collections.',1),
('1100','Accounts Receivable','asset','accounts_receivable','Amounts billed but not yet collected.',1),
('2000','Customer Advances','liability','customer_advances','Advance amounts received before billing.',1),
('3000','Owner Equity','equity','owner_equity','Opening equity / balancing account.',1),
('4000','Subscription Revenue','revenue','subscription_revenue','Revenue from business plans and packages.',1),
('4010','Advertisement Revenue','revenue','advertisement_revenue','Revenue from advertisement campaigns.',1),
('4090','Other Revenue','revenue','other_revenue','Other platform income.',1),
('5000','Refunds & Adjustments','expense','refunds_adjustments','Refund and adjustment expenses.',1),
('5100','Operating Expenses','expense','operating_expense','General operating expenses.',1);

INSERT INTO settings(setting_key,setting_value) VALUES
('subscription_invoice_due_days','7'),
('subscription_tax_percent','0')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
