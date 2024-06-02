CREATE TABLE users (
                       id SERIAL PRIMARY KEY,
                       username VARCHAR(50) UNIQUE NOT NULL,
                       password_hash VARCHAR(255) NOT NULL,
                       first_name VARCHAR(50) NOT NULL,
                       last_name VARCHAR(50) NOT NULL,
                       date_of_birth DATE NOT NULL,
                       gender CHAR(1) CHECK (gender IN ('M', 'F', 'O')) NOT NULL,
                       interests TEXT,
                       city VARCHAR(100)
);

ALTER TABLE users ADD COLUMN roles VARCHAR(255) NOT NULL DEFAULT 'ROLE_USER';

INSERT INTO users (username, password, first_name, last_name, date_of_birth, gender, interests, city)
VALUES
    ('user1', 'password_hash_1', 'Alice', 'Johnson', '1985-05-15', 'F', 'Reading, Traveling', 'New York'),
    ('user2', 'password_hash_2', 'Bob', 'Smith', '1990-07-20', 'M', 'Sports, Music', 'Los Angeles'),
    ('user3', 'password_hash_3', 'Charlie', 'Brown', '1992-11-30', 'M', 'Cooking, Hiking', 'Chicago'),
    ('user4', 'password_hash_4', 'Daisy', 'Clark', '1988-03-25', 'F', 'Photography, Painting', 'Houston'),
    ('user5', 'password_hash_5', 'Evan', 'Davis', '1995-09-10', 'M', 'Gaming, Reading', 'Phoenix'),
    ('user6', 'password_hash_6', 'Fiona', 'Miller', '1991-12-15', 'F', 'Traveling, Music', 'Philadelphia'),
    ('user7', 'password_hash_7', 'George', 'Wilson', '1987-02-05', 'M', 'Sports, Cooking', 'San Antonio'),
    ('user8', 'password_hash_8', 'Hannah', 'Moore', '1993-06-18', 'F', 'Reading, Hiking', 'San Diego'),
    ('user9', 'password_hash_9', 'Ian', 'Taylor', '1996-08-22', 'M', 'Gaming, Photography', 'Dallas'),
    ('user10', 'password_hash_10', 'Jade', 'Anderson', '1989-10-30', 'F', 'Painting, Traveling', 'San Jose');

CREATE INDEX idx_users_first_last_name ON users (first_name, last_name);

EXPLAIN ANALYSE SELECT * FROM users WHERE first_name LIKE 'А%' AND last_name LIKE 'М%' ORDER BY id;


