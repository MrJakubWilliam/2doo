PRAGMA foreign_keys = ON;

  DROP TABLE users;
CREATE TABLE users 
(
    id                                  INTEGER        PRIMARY KEY, 
    name                                VARCHAR(100)   NOT NULL, 
    email                               VARCHAR(100)   NOT NULL, 
    password                            TEXT           NOT NULL
);



  DROP TABLE categories;
CREATE TABLE categories 
(
    id                                  INTEGER         PRIMARY KEY, 
    user_id                             INTEGER         NOT NULL, 
    name                                VARCHAR(100)    NOT NULL,

        FOREIGN KEY(user_id) 
        REFERENCES users(id) 
        -- ON DELETE CASCADE
);



  DROP TABLE lists;
CREATE TABLE lists 
(
    id                                  INTEGER         PRIMARY KEY, 
    category_id                         INTEGER         NOT NULL, 
    name                                VARCHAR(100)    NOT NULL, 
    
        FOREIGN KEY(category_id) 
        REFERENCES categories(id) 
        -- ON DELETE CASCADE
);



  DROP TABLE list_items;
CREATE TABLE list_items 
(
    id                                  INTEGER         PRIMARY KEY, 
    list_id                             INTEGER         NOT NULL, 
    content                             VARCHAR(1000)   NOT NULL, 
    checked                             BOOLEAN         NOT NULL DEFAULT 0,

        FOREIGN KEY(list_id) 
        REFERENCES lists(id) 
        -- ON DELETE CASCADE
);



  DROP TABLE households;
CREATE TABLE households 
(
    id                                  INTEGER         PRIMARY KEY, 
    name                                VARCHAR(100)    NOT NULL, 
    code                                INTEGER
);

  DROP TABLE join_requests;
CREATE TABLE join_requests 
(
    id                                  INTEGER         PRIMARY KEY, 
    household_id                        INTEGER         NOT NULL,
    user_id                             INTEGER         NOT NULL,

        FOREIGN KEY(household_id) 
        REFERENCES households(id),
        -- ON DELETE CASCADE,

        FOREIGN KEY(user_id) 
        REFERENCES users(id) 
        -- ON DELETE CASCADE
);

-- Privilages : 0 = standard 
-- | 1 = admin - can add new chores, delete chores, remove standard, not admins 
-- | 2 = super admin - can delete household, can remove admins
  DROP TABLE users_households;
CREATE TABLE users_households 
(
    id                                  INTEGER         PRIMARY KEY, 
    user_id                             INTEGER         NOT NULL, 
    household_id                        INTEGER         NOT NULL, 
    privilages                          INTEGER         NOT NULL DEFAULT 0, 
    duration_worked                     INTEGER         NOT NULL DEFAULT 0, 
    date_time_joined                    TEXT            NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    FOREIGN KEY(household_id) 
            REFERENCES households(id),
            -- ON DELETE CASCADE,

    FOREIGN KEY(user_id) 
        REFERENCES users(id) 
        -- ON DELETE CASCADE
    -- CONSTRAINT fk_households
    --     

    -- CONSTRAINT fk_users
    --     FOREIGN KEY(user_id) 
    --     REFERENCES users(id) 
    --     ON DELETE CASCADE
);


-- Frequency : 0 = one-time | 1 = every day | 2 = weekly | 3 = every fortnight 
  DROP TABLE chores;
CREATE TABLE chores 
(
    id                                  INTEGER         PRIMARY KEY, 
    users_households_id                 INTEGER,
    household_id                        INTEGER         NOT NULL,
    name                                VARCHAR(100)    NOT NULL,
    description                         VARCHAR(1000), 
    frequency                           INTEGER         NOT NULL DEFAULT 0, 
    duration                            INTEGER         NOT NULL DEFAULT 15,

    FOREIGN KEY(users_households_id)    REFERENCES users_households(id),

        FOREIGN KEY(household_id) 
        REFERENCES households(id) 
        -- ON DELETE CASCADE
);

-- when adding chore chore - allocations is created with the date
-- new chore allocations are created after compliting old ones 

-- Status 0 = upcoming | 1 = pending/overdue | 2 = complete
  DROP TABLE chore_allocations;
CREATE TABLE chore_allocations 
(
    id                                  INTEGER         PRIMARY KEY, 
    users_households_id                 INTEGER         NOT NULL, 
    chore_id                            INTEGER         NOT NULL, 
    status                              INTEGER         NOT NULL DEFAULT 0,
    date_complete_by                    TEXT            NOT NULL,

        FOREIGN KEY(users_households_id)    REFERENCES users_households(id),
    
        FOREIGN KEY(chore_id) 
        REFERENCES chores(id) 
        -- ON DELETE CASCADE
);

  DROP TABLE complete_chores_photos;
CREATE TABLE complete_chores_photos 
(
    id                                  INTEGER         PRIMARY KEY, 
    chore_allocation_id                 INTEGER         NOT NULL, 
    image                               TEXT            NOT NULL, 
    imageName                           TEXT            NOT NULL, 

        FOREIGN KEY(chore_allocation_id) 
        REFERENCES chore_allocations(id) 
        -- ON DELETE CASCADE
);

-- DROP TABLE shared_lists;
-- CREATE TABLE shared_lists (id INTEGER PRIMARY KEY, admin_id INTEGER, list_id INTEGER, FOREIGN KEY(list_id) REFERENCES lists(id), FOREIGN KEY(admin_id) REFERENCES users(id));

-- DROP TABLE privilages;
-- CREATE TABLE privilages (id INTEGER PRIMARY KEY, slist_id INTEGER, user_id INTEGER, privilage INTEGER, FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(slist_id) REFERENCES shared_lists(id));