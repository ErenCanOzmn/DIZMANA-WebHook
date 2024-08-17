You need to create a MySQL database and a table to store the request logs. Run the following SQL query in your MySQL environment:

CREATE DATABASE hook_database CHARACTER SET utf8 COLLATE utf8_general_ci;

USE hook_database;

CREATE TABLE visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method VARCHAR(10) NOT NULL,
    path TEXT NOT NULL,
    query_string TEXT,
    referrer TEXT,
    headers TEXT,
    body TEXT,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
