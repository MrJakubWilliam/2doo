SELECT lists.*  FROM categories, lists WHERE categories.user_id = 1 AND lists.category_id = categories.id AND lists.checked = 0;
SELECT lists.*  FROM categories, lists WHERE categories.user_id = 1 AND lists.category_id = categories.id AND lists.checked = 1;
SELECT list_items.*  FROM list_items, categories, lists WHERE categories.user_id = 1 AND lists.category_id = categories.id AND list_items.list_id = lists.id;;

SELECT lists.*
FROM lists
INNER JOIN categories ON lists.category_id=categories.id
WHERE categories.user_id = 1;

SELECT lists.name
FROM lists
INNER JOIN categories ON lists.category_id=categories.id
WHERE categories.user_id = 1;

SELECT list_items.*
FROM list_items
INNER JOIN lists, categories ON list_items.list_id=lists.id AND lists.category_id = categories.id
WHERE categories.user_id = 1 AND list_items.checked = 0;

SELECT list_items.*
FROM list_items
INNER JOIN lists, categories ON list_items.list_id=lists.id AND lists.category_id = categories.id
WHERE categories.user_id = 1 AND list_items.checked = 1;

SELECT COUNT(*)
FROM list_items
WHERE list_items.list_id = 1;

SELECT COUNT(*)
FROM list_items
INNER JOIN lists, categories ON list_items.list_id=lists.id AND lists.category_id = categories.id
WHERE categories.user_id = 1;