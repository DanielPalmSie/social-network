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

CREATE TABLE dialogs (
                         id SERIAL PRIMARY KEY,
                         user1_id INT NOT NULL,
                         user2_id INT NOT NULL,
                         CONSTRAINT fk_user1 FOREIGN KEY(user1_id) REFERENCES users(id),
                         CONSTRAINT fk_user2 FOREIGN KEY(user2_id) REFERENCES users(id)
);

-- Таблица сообщений
CREATE TABLE messages (
                          id SERIAL PRIMARY KEY,
                          dialog_id INT NOT NULL,
                          sender_id INT NOT NULL,
                          content TEXT NOT NULL,
                          timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          shard_key INT NOT NULL,
                          CONSTRAINT fk_dialog FOREIGN KEY(dialog_id) REFERENCES dialogs(id),
                          CONSTRAINT fk_sender FOREIGN KEY(sender_id) REFERENCES users(id)
);

CREATE INDEX idx_users_first_last_name ON users (first_name, last_name);

EXPLAIN ANALYSE SELECT * FROM users WHERE first_name LIKE 'А%' AND last_name LIKE 'М%' ORDER BY id;

