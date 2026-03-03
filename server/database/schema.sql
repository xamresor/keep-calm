-- Run this to initialize SQLite: sqlite3 database/database.sqlite < database/schema.sql

CREATE TABLE IF NOT EXISTS snapshots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    last_updated DATE NOT NULL UNIQUE,
    global_chaos TEXT,
    key_indicators TEXT,
    shipping_chokepoint TEXT,
    created_at DATETIME,
    updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS countries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    snapshot_id INTEGER NOT NULL,
    name VARCHAR(255),
    liquidity TINYINT,
    logistics TINYINT,
    legitimacy TINYINT,
    overall DECIMAL(5,1),
    liquidity_history TEXT,
    logistics_history TEXT,
    legitimacy_history TEXT,
    overall_history TEXT,
    family_safety_note TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scenarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    snapshot_id INTEGER NOT NULL,
    name VARCHAR(255),
    description TEXT,
    when_visible VARCHAR(50),
    earliest_date VARCHAR(50),
    probability_percent TINYINT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);
