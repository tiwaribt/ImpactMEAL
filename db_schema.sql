-- ImpactMEAL Database Schema (MySQL Compatible)

CREATE DATABASE IF NOT EXISTS impact_meal;
USE impact_meal;

-- Indicators Table
CREATE TABLE IF NOT EXISTS indicators (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    target DOUBLE NOT NULL,
    actual DOUBLE DEFAULT 0,
    unit VARCHAR(50),
    category VARCHAR(100),
    trend ENUM('up', 'down', 'stable') DEFAULT 'stable',
    status ENUM('on-track', 'at-risk', 'behind') DEFAULT 'on-track',
    gap DOUBLE DEFAULT 0,
    achieved_percentage DOUBLE DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    geojson JSON
);

-- Monitoring Entries Table
CREATE TABLE IF NOT EXISTS monitoring_entries (
    id VARCHAR(50) PRIMARY KEY,
    indicator_id VARCHAR(50),
    date DATE NOT NULL,
    value DOUBLE NOT NULL,
    location VARCHAR(255),
    notes TEXT,
    latitude DOUBLE,
    longitude DOUBLE,
    FOREIGN KEY (indicator_id) REFERENCES indicators(id) ON DELETE CASCADE
);

-- Qualitative Feedback Table
CREATE TABLE IF NOT EXISTS qualitative_feedback (
    id VARCHAR(50) PRIMARY KEY,
    date DATE NOT NULL,
    source VARCHAR(255),
    content TEXT NOT NULL,
    sentiment ENUM('positive', 'neutral', 'negative'),
    themes JSON,
    summary TEXT
);

-- Insert Initial Data
INSERT INTO indicators (id, name, target, actual, unit, category, trend, status, gap, achieved_percentage) VALUES
('1', 'Number of beneficiaries reached', 5000, 4250, 'people', 'Outreach', 'up', 'on-track', 750, 85),
('2', 'Training completion rate', 95, 88, '%', 'Capacity Building', 'stable', 'at-risk', 7, 92.6),
('3', 'Community satisfaction index', 4.5, 4.2, '/5', 'Accountability', 'up', 'on-track', 0.3, 93.3),
('4', 'Average response time to feedback', 48, 36, 'hours', 'Accountability', 'down', 'on-track', -12, 125);
