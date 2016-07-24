CREATE DATABASE IF NOT EXISTS ps_amareth_space;
USE ps_amareth_space;

CREATE TABLE IF NOT EXISTS `entries`
(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username_hash VARCHAR(600) NOT NULL,
    website_hash VARCHAR(600) NOT NULL,
    min_length INT NOT NULL,
    max_length INT NOT NULL,
    avoid_dictionary_attacks INT,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `successful_requests`
(
	`entryId` INT NOT NULL,
	`ip` VARCHAR(100) NOT NULL,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `failed_requests`
(
	`ip` VARCHAR(100) NOT NULL,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);