-- Impact MEAL Database Schema
CREATE DATABASE IF NOT EXISTS impact_meal;
USE impact_meal;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    startDate DATE,
    endDate DATE,
    status ENUM('active', 'completed', 'on-hold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indicators table
CREATE TABLE IF NOT EXISTS indicators (
    id VARCHAR(50) PRIMARY KEY,
    projectId VARCHAR(50),
    name VARCHAR(255) NOT NULL,
    target DECIMAL(15, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    category VARCHAR(100) NOT NULL,
    actual DECIMAL(15, 2) DEFAULT 0,
    achievedPercentage INT DEFAULT 0,
    gap DECIMAL(15, 2) DEFAULT 0,
    status ENUM('on-track', 'at-risk', 'behind') DEFAULT 'behind',
    lastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    geojson TEXT,
    FOREIGN KEY (projectId) REFERENCES projects(id) ON DELETE SET NULL
);

-- Monitoring entries table
CREATE TABLE IF NOT EXISTS monitoring_entries (
    id VARCHAR(50) PRIMARY KEY,
    indicatorId VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    value DECIMAL(15, 2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    notes TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    disaggregation JSON,
    FOREIGN KEY (indicatorId) REFERENCES indicators(id) ON DELETE CASCADE
);

-- Qualitative feedback table
CREATE TABLE IF NOT EXISTS qualitative_feedback (
    id VARCHAR(50) PRIMARY KEY,
    source VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    date DATE NOT NULL,
    sentiment ENUM('positive', 'neutral', 'negative'),
    themes TEXT,
    respondentType VARCHAR(100)
);

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'meal_officer', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
