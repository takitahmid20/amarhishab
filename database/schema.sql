-- AmarHishab database schema (MySQL / InnoDB, utf8mb4)
-- Import:  mysql -u root < database/schema.sql
-- Money columns use DECIMAL(12,2). All money in BDT (taka).

CREATE DATABASE IF NOT EXISTS amarhishab
	CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE amarhishab;

-- Drop existing tables so the script is re-runnable during development.
-- FK checks are disabled here so drop order / stray legacy tables can't block it.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ai_messages;
DROP TABLE IF EXISTS ai_chats;
DROP TABLE IF EXISTS reminders;
DROP TABLE IF EXISTS borrow_lend;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS budget_categories;
DROP TABLE IF EXISTS cashbooks;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
	id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name          VARCHAR(120)  NOT NULL,
	email         VARCHAR(190)  NOT NULL UNIQUE,
	password_hash VARCHAR(255)  NOT NULL,
	created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cashbooks (
	id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id     INT UNSIGNED NOT NULL,
	name        VARCHAR(120) NOT NULL,
	description VARCHAR(255) NULL,
	status      ENUM('live','review') NOT NULL DEFAULT 'live',
	created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_cashbooks_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_cashbooks_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE budget_categories (
	id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id      INT UNSIGNED NOT NULL,
	cashbook_id  INT UNSIGNED NULL,
	name         VARCHAR(120) NOT NULL,
	icon         VARCHAR(60)  NULL,
	color        VARCHAR(20)  NULL,
	limit_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
	created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_budget_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE,
	CONSTRAINT fk_budget_cashbook FOREIGN KEY (cashbook_id)
		REFERENCES cashbooks(id) ON DELETE SET NULL,
	INDEX idx_budget_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
	id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id     INT UNSIGNED NOT NULL,
	cashbook_id INT UNSIGNED NOT NULL,
	category_id INT UNSIGNED NULL,
	direction   ENUM('in','out') NOT NULL,
	amount      DECIMAL(12,2) NOT NULL,
	mode        ENUM('cash','bank','mobile') NOT NULL DEFAULT 'cash',
	bill        VARCHAR(120) NULL,
	details     VARCHAR(255) NULL,
	occurred_at DATETIME     NOT NULL,
	created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_txn_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE,
	CONSTRAINT fk_txn_cashbook FOREIGN KEY (cashbook_id)
		REFERENCES cashbooks(id) ON DELETE CASCADE,
	CONSTRAINT fk_txn_category FOREIGN KEY (category_id)
		REFERENCES budget_categories(id) ON DELETE SET NULL,
	INDEX idx_txn_cashbook (cashbook_id),
	INDEX idx_txn_user_date (user_id, occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE borrow_lend (
	id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id    INT UNSIGNED NOT NULL,
	type       ENUM('borrow','lend') NOT NULL,
	person     VARCHAR(120) NOT NULL,
	amount     DECIMAL(12,2) NOT NULL,
	note       VARCHAR(255) NULL,
	due_date   DATE         NULL,
	is_settled TINYINT(1)   NOT NULL DEFAULT 0,
	created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_bl_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_bl_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reminders (
	id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id      INT UNSIGNED NOT NULL,
	title        VARCHAR(150) NOT NULL,
	category     VARCHAR(120) NULL,
	amount       DECIMAL(12,2) NULL,
	due_date     DATE         NOT NULL,
	repeat_cycle ENUM('none','weekly','monthly') NOT NULL DEFAULT 'none',
	is_done      TINYINT(1)   NOT NULL DEFAULT 0,
	created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_rem_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_rem_user_due (user_id, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
