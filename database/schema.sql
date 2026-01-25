-- Smart Expense Tracking System - Database Schema (PostgreSQL)
-- Student ID: 20034038
-- Assessment 4 - ICT726 Web Development
-- 
-- This SQL file creates the database structure for the expense tracking application
-- Run this file in pgAdmin or psql command line to set up the database

-- Create database (run this separately in psql if needed)
-- CREATE DATABASE expense_tracker WITH ENCODING 'UTF8';

-- Connect to the database
-- \c expense_tracker

-- =====================================================
-- Create ENUM types for PostgreSQL
-- =====================================================
DO $$ BEGIN
    CREATE TYPE user_role AS ENUM ('admin', 'member', 'user');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE user_status AS ENUM ('active', 'inactive', 'suspended');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_method_type AS ENUM ('cash', 'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'other');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE recurring_frequency_type AS ENUM ('daily', 'weekly', 'monthly', 'yearly');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE message_status AS ENUM ('new', 'read', 'replied', 'archived');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

-- =====================================================
-- Function to auto-update updated_at timestamp
-- =====================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- Users Table
-- Stores user account information with secure password hashing
-- Implements role-based access control (admin, member, user)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role user_role NOT NULL DEFAULT 'user',
    status user_status NOT NULL DEFAULT 'active',
    profile_image VARCHAR(255) DEFAULT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP DEFAULT NULL,
    login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for users table
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);

-- Add comment for password_hash column
COMMENT ON COLUMN users.password_hash IS 'Bcrypt hashed password';

-- Create trigger for updated_at
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- Expense Categories Table
-- Predefined and custom expense categories
-- =====================================================
CREATE TABLE IF NOT EXISTS expense_categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    category_icon VARCHAR(10) DEFAULT '📦',
    category_color VARCHAR(7) DEFAULT '#2c7a7b',
    description VARCHAR(255) DEFAULT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    user_id INTEGER DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create index for expense_categories
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON expense_categories(user_id);

-- Add comment for user_id column
COMMENT ON COLUMN expense_categories.user_id IS 'NULL for default categories, user_id for custom';

-- =====================================================
-- Expenses Table
-- Main table for storing expense records
-- =====================================================
CREATE TABLE IF NOT EXISTS expenses (
    expense_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'AUD',
    description VARCHAR(255) NOT NULL,
    expense_date DATE NOT NULL,
    receipt_image VARCHAR(255) DEFAULT NULL,
    payment_method payment_method_type DEFAULT 'cash',
    notes TEXT DEFAULT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_frequency recurring_frequency_type DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE RESTRICT
);

-- Create indexes for expenses table
CREATE INDEX IF NOT EXISTS idx_expenses_user_id ON expenses(user_id);
CREATE INDEX IF NOT EXISTS idx_expenses_category_id ON expenses(category_id);
CREATE INDEX IF NOT EXISTS idx_expenses_expense_date ON expenses(expense_date);
CREATE INDEX IF NOT EXISTS idx_expenses_amount ON expenses(amount);

-- Create trigger for updated_at
DROP TRIGGER IF EXISTS update_expenses_updated_at ON expenses;
CREATE TRIGGER update_expenses_updated_at
    BEFORE UPDATE ON expenses
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- Budgets Table
-- Monthly budget limits per category
-- =====================================================
CREATE TABLE IF NOT EXISTS budgets (
    budget_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    budget_amount DECIMAL(10, 2) NOT NULL,
    month INTEGER NOT NULL CHECK (month >= 1 AND month <= 12),
    year INTEGER NOT NULL,
    alert_threshold INTEGER DEFAULT 80,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE CASCADE,
    UNIQUE (user_id, category_id, month, year)
);

-- Create index for budgets
CREATE INDEX IF NOT EXISTS idx_budgets_user_month_year ON budgets(user_id, month, year);

-- Add comments
COMMENT ON COLUMN budgets.month IS '1-12';
COMMENT ON COLUMN budgets.alert_threshold IS 'Percentage to trigger alert';

-- Create trigger for updated_at
DROP TRIGGER IF EXISTS update_budgets_updated_at ON budgets;
CREATE TRIGGER update_budgets_updated_at
    BEFORE UPDATE ON budgets
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- User Sessions Table
-- For secure session management
-- =====================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INTEGER NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create indexes for user_sessions
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON user_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON user_sessions(expires_at);

-- =====================================================
-- Activity Log Table
-- Tracks user actions for security and auditing
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_log (
    log_id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create indexes for activity_log
CREATE INDEX IF NOT EXISTS idx_activity_user_id ON activity_log(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_action ON activity_log(action);
CREATE INDEX IF NOT EXISTS idx_activity_created_at ON activity_log(created_at);

-- =====================================================
-- Contact Messages Table
-- Stores contact form submissions
-- =====================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status message_status DEFAULT 'new',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create indexes for contact_messages
CREATE INDEX IF NOT EXISTS idx_messages_status ON contact_messages(status);
CREATE INDEX IF NOT EXISTS idx_messages_created_at ON contact_messages(created_at);

-- Create trigger for updated_at
DROP TRIGGER IF EXISTS update_messages_updated_at ON contact_messages;
CREATE TRIGGER update_messages_updated_at
    BEFORE UPDATE ON contact_messages
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- SEEDING THE DATABASE - DEFAULT CATEGORIES, ADMIN USER, MEMBER USER, AND REGULAR USER

-- =====================================================
-- Insert Default Categories
-- =====================================================
INSERT INTO expense_categories (category_name, category_icon, category_color, description, is_default) VALUES
('Food & Dining', '🍔', '#e74c3c', 'Restaurants, groceries, and food delivery', TRUE),
('Transportation', '🚗', '#3498db', 'Fuel, public transport, and vehicle maintenance', TRUE),
('Shopping', '🛍️', '#9b59b6', 'Clothing, electronics, and general purchases', TRUE),
('Entertainment', '🎬', '#f39c12', 'Movies, games, and recreational activities', TRUE),
('Bills & Utilities', '💡', '#1abc9c', 'Electricity, water, internet, and phone', TRUE),
('Health & Medical', '🏥', '#e91e63', 'Doctor visits, medications, and insurance', TRUE),
('Education', '📚', '#2196f3', 'Courses, books, and learning materials', TRUE),
('Travel', '✈️', '#00bcd4', 'Flights, hotels, and vacation expenses', TRUE),
('Personal Care', '💆', '#ff9800', 'Grooming, spa, and self-care', TRUE),
('Subscriptions', '📱', '#795548', 'Streaming services and recurring subscriptions', TRUE),
('Rent & Housing', '🏠', '#607d8b', 'Rent, mortgage, and home maintenance', TRUE),
('Other', '📦', '#9e9e9e', 'Miscellaneous expenses', TRUE)
ON CONFLICT DO NOTHING;

-- =====================================================
-- Insert Default Admin User
-- Password: password (hashed with bcrypt)
-- =====================================================
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) VALUES
('admin', 'admin@expensetracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', TRUE)
ON CONFLICT (username) DO NOTHING;

-- =====================================================
-- Insert Sample Member User
-- Password: password (hashed with bcrypt)
-- =====================================================
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) VALUES
('member', 'member@expensetracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'Member', 'member', 'active', TRUE)
ON CONFLICT (username) DO NOTHING;

-- =====================================================
-- Insert Sample Regular User
-- Password: password (hashed with bcrypt)
-- =====================================================
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified) VALUES
('user', 'user@expensetracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User', 'user', 'active', TRUE)
ON CONFLICT (username) DO NOTHING;

-- =====================================================
-- Insert Sample Expenses for Demo
-- =====================================================
INSERT INTO expenses (user_id, category_id, amount, description, expense_date, payment_method, notes) VALUES
(3, 1, 45.50, 'Weekly grocery shopping', CURRENT_DATE - INTERVAL '2 days', 'debit_card', 'At Woolworths'),
(3, 2, 35.00, 'Uber rides this week', CURRENT_DATE - INTERVAL '3 days', 'credit_card', NULL),
(3, 5, 120.00, 'Monthly electricity bill', CURRENT_DATE - INTERVAL '5 days', 'bank_transfer', 'AGL Energy'),
(3, 4, 25.00, 'Netflix subscription', CURRENT_DATE - INTERVAL '7 days', 'credit_card', 'Monthly subscription'),
(3, 1, 32.00, 'Dinner with friends', CURRENT_DATE - INTERVAL '1 day', 'cash', 'Italian restaurant'),
(3, 3, 89.99, 'New headphones', CURRENT_DATE - INTERVAL '10 days', 'credit_card', 'JB Hi-Fi'),
(3, 6, 65.00, 'Doctor consultation', CURRENT_DATE - INTERVAL '14 days', 'debit_card', 'Regular checkup'),
(3, 7, 199.00, 'Online course', CURRENT_DATE - INTERVAL '20 days', 'paypal', 'Web development course')
ON CONFLICT DO NOTHING;

-- =====================================================
-- Insert Sample Budgets
-- =====================================================
INSERT INTO budgets (user_id, category_id, budget_amount, month, year, alert_threshold) VALUES
(3, 1, 500.00, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 80),
(3, 2, 200.00, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 75),
(3, 3, 300.00, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 80),
(3, 4, 100.00, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 90),
(3, 5, 250.00, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 80)
ON CONFLICT DO NOTHING;
