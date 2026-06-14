-- AmarHishab sample data for development.
-- Import after schema:  mysql -u root < database/seed.sql
-- Demo login:  demo@amarhishab.test  /  password123

USE amarhishab;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE reminders;
TRUNCATE TABLE borrow_lend;
TRUNCATE TABLE transactions;
TRUNCATE TABLE budget_categories;
TRUNCATE TABLE cashbooks;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Demo user (password: password123)
INSERT INTO users (id, name, email, password_hash) VALUES
	(1, 'Demo Student', 'demo@amarhishab.test', '$2y$12$NDRN2H78dE.ScJZyygHM3es0VgzRiHMFJ9i0b7krHSD49KjuvNZxO');

-- Cashbooks
INSERT INTO cashbooks (id, user_id, name, description, status) VALUES
	(1, 1, 'Personal',  'Daily spending, snacks, bus fare, personal tracking.', 'live'),
	(2, 1, 'Tuition',   'Semester fees, academic expenses, education costs.',  'live'),
	(3, 1, 'Freelance', 'Project income, client payments, business costs.',    'review');

-- Budget categories
INSERT INTO budget_categories (id, user_id, cashbook_id, name, icon, color, limit_amount) VALUES
	(1, 1, 1, 'Food & Dining',     'utensils-crossed', '#8257e5', 1500),
	(2, 1, 1, 'Transportation',    'car',              '#3b82f6', 1000),
	(3, 1, 1, 'Bills & Utilities', 'plug',             '#dc2626', 1000),
	(4, 1, 1, 'Shopping',          'shopping-bag',     '#f59e0b', 800),
	(5, 1, 1, 'Entertainment',     'clapperboard',     '#10b981', 500),
	(6, 1, 1, 'Healthcare',        'heart-pulse',      '#8b5cf6', 500);

-- Transactions
INSERT INTO transactions (user_id, cashbook_id, category_id, direction, amount, mode, bill, details, occurred_at) VALUES
	(1, 1, NULL, 'in',  2400, 'cash',   NULL,        'Pocket money',        '2026-06-01 09:00:00'),
	(1, 1, 1,    'out',  320, 'cash',   NULL,        'Lunch with friends',  '2026-06-02 13:30:00'),
	(1, 1, 2,    'out',   80, 'cash',   NULL,        'Bus fare',            '2026-06-02 18:10:00'),
	(1, 1, 3,    'out',  950, 'mobile', 'Internet',  'Monthly broadband',   '2026-06-03 11:00:00'),
	(1, 1, 4,    'out',  600, 'bank',   NULL,        'New headphones',      '2026-06-05 16:45:00'),
	(1, 2, NULL, 'in', 14000, 'bank',   NULL,        'Semester deposit',    '2026-06-01 10:00:00'),
	(1, 2, NULL, 'out', 2000, 'bank',   'Tuition',   'Course material',     '2026-06-04 12:00:00'),
	(1, 3, NULL, 'in',  9500, 'bank',   NULL,        'Client project',      '2026-06-06 15:00:00'),
	(1, 3, NULL, 'out', 1300, 'mobile', NULL,        'Software subscription','2026-06-07 09:30:00');

-- Borrow / Lend
INSERT INTO borrow_lend (user_id, type, person, amount, note, due_date, is_settled) VALUES
	(1, 'lend',   'Rahim',  500, 'Lent for lunch',     '2026-06-20', 0),
	(1, 'borrow', 'Karim', 1000, 'Borrowed for books', '2026-06-25', 0),
	(1, 'lend',   'Sadia',  300, 'Rickshaw share',     '2026-06-15', 1);

-- Reminders
INSERT INTO reminders (user_id, title, category, amount, due_date, repeat_cycle, is_done) VALUES
	(1, 'Internet bill',       'Bills & Utilities', 950,  '2026-06-18', 'monthly', 0),
	(1, 'Tuition installment', 'Education',        5000,  '2026-06-30', 'monthly', 0),
	(1, 'Gym membership',      'Health',           1200,  '2026-06-16', 'monthly', 0);
