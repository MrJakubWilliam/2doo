<?php
    include("database.php");
    
    session_start();
    if (!isset($_SESSION['u_id']))
    {
        header('Location: ./');
    }
    $user_id = $_SESSION['u_id'];
    
    $db = new Database();


    $stmt = $db->prepare('SELECT * FROM categories WHERE user_id = :u_id');
    $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
    $categories = $stmt->execute();

    $stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id=categories.id WHERE categories.user_id = :u_id;');
    $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
    $lists = $stmt->execute();
    // $categories = $db->query("SELECT * FROM categories WHERE user_id = $user_id");
    // $lists = $db->query("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id=categories.id WHERE categories.user_id = $user_id;");


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
include("./partials/head.php");
?>

<body class="dashboard">
    <div class="container">

        <?php
        include("./partials/dashboard/navbar.php");
        ?>
    </div>
    <div class="container">
    <section>
            
            <div class="top">
                <h2>Select Categories to View</h2>
            </div>
            <div class="category-buttons">

                <?php

                while (($category = $categories->fetchArray())) {
                    ?>
                    <button class="button" for='<?= $category["id"]; ?>'><?= htmlspecialchars($category["name"]); ?></button>
                    <?php

                }

                ?>
                
                <a href="./categories_create.php" class="button" id='add-cat'><i class="fas fa-plus"></i></a>
            </div>

            <div class="list-components">
                <?php

                while (($list = $lists->fetchArray())) {
                    ?>
                    <div class="list-component hidden" category='<?= $list["category_id"]; ?>'>
                        <div class="text">
                            <?= htmlspecialchars($list["name"]); ?>
                        </div>
                        <div class="buttons">
                            <a href="./lists_show.php?list=<?=$list['id']?>">
                                <i class="fas fa-binoculars"></i>
                            </a>
                            <a class="delete" href="./delete_list.php?list=<?=$list['id']?>">
                                <i class="far fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    <?php

                }

                ?>
            </div>
            <script>
                var cat_buttons = document.querySelectorAll(".category-buttons button");
                cat_buttons.forEach((button) => {
                var list_items = document.querySelectorAll(
                    ".list-components .list-component[category= '" +
                    button.getAttribute("for") +
                    "']"
                );
                button.onclick = function () {
                    button.classList.toggle("clicked");
                    list_items.forEach((item) => {
                    item.classList.toggle("hidden");
                    });
                };
                });
            </script>
        </section>
        <?php 
            include("./partials/dashboard/status_msg.php")
        ?>
    </div>


</body>

</html>
