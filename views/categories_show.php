<?php
    include("database.php");
    
    session_start();
    
    if (!isset($_SESSION['u_id']))
    {
      header('Location: ./');
    }
    
    $user_id = $_SESSION['u_id'];
    
    if (!isset($_GET['category'])) {
      header('Location: ./categories_index.php?status=error&message=Cannot%20find%20the%20category!');
    }

    $id = $_GET['category'];
    $db = new Database();


    // $category = $db->querySingle("SELECT * FROM categories WHERE id = $id AND user_id = $user_id;");

    $stmt = $db->prepare('SELECT * FROM categories WHERE id = :c_id AND user_id = :u_id');
    $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':c_id', $id, SQLITE3_INTEGER);
    $category = $stmt->execute();
    $category = $category->fetchArray();




    // $lists = $db->query("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.category_id = $id AND categories.user_id = $user_id;");


    $stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.category_id = :c_id AND categories.user_id = :u_id');
    $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':c_id', $id, SQLITE3_INTEGER);
    $lists = $stmt->execute();


    if (empty($category)) {
      header('Location: ./categories_index.php?status=error&message=Category%20does%20not%20exist!');
    }
    // $lists = $db->query("SELECT * FROM lists WHERE category_id = $id;");
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
      <h2>Category: <?= htmlspecialchars($category['name']) ?></h2>
      <div class="buttons">
        <a href="./lists_create.php?category=<?= $id ?>" class="button" id='add'><i class="fas fa-plus"></i></a>
        <a href="./delete_category.php?category=<?= $id ?>" class="button" id='delete'><i class="far fa-trash-alt"></i></a>

      </div>
    </div>
  <div class="list-components">
    <?php
      while (($list = $lists->fetchArray())) {
    ?>
        <div class="list-component">
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
  </section>

</div>
</body>

</html>