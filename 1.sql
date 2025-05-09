CREATE TABLE users (
    id INTEGER  PRIMARY KEY   AUTOINCREMENT,
    username CHAR(64) NOT NULL,
    password CHAR(64) NOT NULL,
    email CHAR(128),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    group_id INT NOT NULL
);
CREATE UNIQUE INDEX  username_index ON users(username);
CREATE INDEX group_id_index ON users(group_id);

CREATE TABLE user_session (
    id INTEGER  PRIMARY KEY   AUTOINCREMENT,
	title TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL,
    update_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX user_id_index ON user_session(user_id);
CREATE INDEX update_time_index ON user_session(update_time);

CREATE TABLE chat_content (
    id INTEGER  PRIMARY KEY   AUTOINCREMENT,
    user_content TEXT NOT NULL,
    ai_content TEXT,
    session_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	model TEXT NOT NULL,
	temperature REAL NOT NULL,
    history_count INT
);
CREATE INDEX session_id_index ON chat_content(session_id);
--CREATE INDEX created_at_index ON chat_content(created_at);


CREATE TABLE setting (
    key CHAR(32) NOT NULL,
	value TEXT NOT NULL
);
CREATE INDEX key_index ON setting(key);
