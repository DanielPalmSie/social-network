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
                          CONSTRAINT fk_dialog FOREIGN KEY(dialog_id) REFERENCES dialogs(id),
                          CONSTRAINT fk_sender FOREIGN KEY(sender_id) REFERENCES users(id)
);



CREATE INDEX idx_users_first_last_name ON users (first_name, last_name);

--------------------Sharding--------------------------------------------

SELECT create_reference_table('users');

CREATE TABLE dialogs (
                         id BIGSERIAL NOT NULL,
                         user1_id INT NOT NULL,
                         user2_id INT NOT NULL,
                         PRIMARY KEY (user1_id, id),
                         CONSTRAINT fk_user1 FOREIGN KEY(user1_id) REFERENCES users(id),
                         CONSTRAINT fk_user2 FOREIGN KEY(user2_id) REFERENCES users(id),
                         UNIQUE (id)
);

SELECT create_reference_table('dialogs');

CREATE TABLE messages (
                          id BIGSERIAL NOT NULL,
                          dialog_id INT NOT NULL,
                          sender_id INT NOT NULL,
                          content TEXT NOT NULL,
                          timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (dialog_id, id),
                          CONSTRAINT fk_dialog FOREIGN KEY(dialog_id) REFERENCES dialogs(id),
                          CONSTRAINT fk_sender FOREIGN KEY(sender_id) REFERENCES users(id)
);

SELECT create_distributed_table('messages', 'dialog_id');

TRUNCATE TABLE messages, dialogs RESTART IDENTITY CASCADE;

DO
$$
    BEGIN
        FOR i IN 1..500000 LOOP
                INSERT INTO dialogs (user1_id, user2_id)
                VALUES (
                           (RANDOM() * (1170502 - 585252) + 585252)::INT,
                           (RANDOM() * (1170502 - 585252) + 585252)::INT
                       );
            END LOOP;
    END
$$;

DROP TABLE IF EXISTS temp_messages_gen;

CREATE TEMPORARY TABLE temp_messages_gen (
                                             id BIGSERIAL PRIMARY KEY,
                                             dialog_id INT,
                                             sender_id INT,
                                             content TEXT,
                                             timestamp TIMESTAMP
);

INSERT INTO temp_messages_gen (dialog_id, sender_id, content, timestamp)
SELECT
    (RANDOM() * 500000)::INT + 1 AS dialog_id,
    (RANDOM() * (1170502 - 585252) + 585252)::INT AS sender_id,
    'Message content' AS content,
    NOW() - INTERVAL '1 DAY' * (RANDOM() * 365)::INT AS timestamp
FROM generate_series(1, 1000000);

WITH existing_dialogs AS (
    SELECT id AS dialog_id FROM dialogs
)
INSERT INTO messages (dialog_id, sender_id, content, timestamp)
SELECT t.dialog_id, t.sender_id, t.content, t.timestamp
FROM temp_messages_gen t
         JOIN existing_dialogs d ON d.dialog_id = t.dialog_id;

select count(*) from users;

DO $$
    DECLARE
        message_count INT := 50000;
        i INT;
        random_user_id1 INT;
        random_user_id2 INT;
        random_dialog_id INT;
        random_sender_id INT;
        random_content TEXT;
        random_timestamp TIMESTAMP;
    BEGIN
        FOR i IN 1..message_count LOOP
                -- Выбираем случайного пользователя 1
                SELECT id INTO random_user_id1 FROM users ORDER BY random() LIMIT 1;

                -- Выбираем случайного пользователя 2 (чтобы отличался от пользователя 1)
                SELECT id INTO random_user_id2 FROM users WHERE id != random_user_id1 ORDER BY random() LIMIT 1;

                -- Находим или создаем диалог между этими пользователями
                SELECT id INTO random_dialog_id FROM dialogs
                WHERE (user1_id = random_user_id1 AND user2_id = random_user_id2)
                   OR (user1_id = random_user_id2 AND user2_id = random_user_id1)
                LIMIT 1;

                -- Если диалога нет, создаем его
                IF NOT FOUND THEN
                    INSERT INTO dialogs (user1_id, user2_id) VALUES (random_user_id1, random_user_id2) RETURNING id INTO random_dialog_id;
                END IF;

                -- Выбираем случайного отправителя из двух пользователей
                IF random() < 0.5 THEN
                    random_sender_id := random_user_id1;
                ELSE
                    random_sender_id := random_user_id2;
                END IF;

                -- Генерируем случайный текст сообщения
                random_content := 'Random message content ' || i;

                -- Генерируем случайную временную метку в пределах последнего года
                random_timestamp := NOW() - INTERVAL '1 year' * random();

                -- Вставляем сообщение в таблицу messages
                INSERT INTO messages (dialog_id, sender_id, content, timestamp)
                VALUES (random_dialog_id, random_sender_id, random_content, random_timestamp);
            END LOOP;
    END $$;
