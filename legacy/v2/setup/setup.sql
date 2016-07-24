CREATE DATABASE IF NOT EXISTS ps_amareth_space_legacy;
USE ps_amareth_space_legacy;

CREATE TABLE IF NOT EXISTS `entries`
(
    cumulative_password_hash VARCHAR(600) NOT NULL,
    website_name_hash VARCHAR(600) NOT NULL,
    min_length INT,
    max_length INT,
    avoid_dictionary_attacks INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
