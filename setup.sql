CREATE DATABASE IF NOT EXISTS ps_amareth_space;
use ps_amareth_space;

CREATE TABLE IF NOT EXISTS websiteLog
(
    cumulative_password_hash VARCHAR(256) NOT NULL,
    website_name_hash VARCHAR(256) NOT NULL,
    min_length INT,
    max_length INT,
    forbidden_characters TEXT,
    avoid_dictionary_attacks INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);
