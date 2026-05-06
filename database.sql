CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    arrival VARCHAR(100),
    departure VARCHAR(100),
    pays TINYINT(1),
    notes TEXT
);