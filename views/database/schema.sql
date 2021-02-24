PRAGMA foreign_keys = ON;

DROP TABLE users;
CREATE TABLE users (id INTEGER PRIMARY KEY, name VARCHAR(100), email VARCHAR(100), password TEXT);

DROP TABLE categories;
CREATE TABLE categories (id INTEGER PRIMARY KEY, user_id INTEGER, name VARCHAR(100), FOREIGN KEY(user_id) REFERENCES users(id));

DROP TABLE lists;
CREATE TABLE lists (id INTEGER PRIMARY KEY, category_id INTEGER, name VARCHAR(100), FOREIGN KEY(category_id) REFERENCES categories(id));

DROP TABLE list_items;
CREATE TABLE list_items (id INTEGER PRIMARY KEY, list_id INTEGER, content VARCHAR(1000), checked BOOLEAN, FOREIGN KEY(list_id) REFERENCES lists(id));

DROP TABLE shared_lists;
CREATE TABLE shared_lists (id INTEGER PRIMARY KEY, admin_id INTEGER, list_id INTEGER, FOREIGN KEY(list_id) REFERENCES lists(id), FOREIGN KEY(admin_id) REFERENCES users(id));

DROP TABLE privilages;
CREATE TABLE privilages (id INTEGER PRIMARY KEY, slist_id INTEGER, user_id INTEGER, privilage INTEGER, FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(slist_id) REFERENCES shared_lists(id));