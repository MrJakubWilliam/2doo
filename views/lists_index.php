<?php
  include("database.php");

  session_start();
  if (!isset($_SESSION['u_id']))
  {
    header('Location: ./');
  }
  $user_id = $_SESSION['u_id'];

  $db = new Database();
  // $lists = $db->query("SELECT lists.*, categories.name AS c_name, categories.id AS c_id FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = $user_id");

  $stmt = $db->prepare('SELECT lists.*, categories.name AS c_name, categories.id AS c_id FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE categories.user_id = :u_id');
  $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
  $lists = $stmt->execute();
      // $lists = $db->query("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id=categories.id WHERE categories.user_id = 1;");
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

    
    <section>
      <div class='top'>
        <h2>Lists</h2>
        <div class="buttons">
          <a href="./lists_create.php" class="button" id='add'><i class="fas fa-plus"></i></a>
        </div>
      </div>
      <div class="list-components">
        <?php

        while (($list = $lists->fetchArray())) {
        ?>
          <div class="list-component">
            <div class="text">
                <a class="category-link" href="./categories_show.php?category=<?= $list['c_id'] ?>"><?= $list['c_name'] ?></a>
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
    </section>
    <?php 
      include("./partials/dashboard/status_msg.php")
    ?>
  </div>
    
</body>

</html>