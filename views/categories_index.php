<?php
  include("database.php");

  session_start();
  if (!isset($_SESSION['u_id']))
  {
    header('Location: ./');
  }
  $user_id = $_SESSION['u_id'];
  
  $db = new Database();
  // $categories = $db->query("SELECT * FROM categories WHERE user_id = $user_id");

  $stmt = $db->prepare('SELECT * FROM categories WHERE user_id = :u_id');
    $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
    $categories = $stmt->execute();

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
      <h2>Categories</h2>
      <div class="buttons">
        <a href="./categories_create.php" class="button" id='add'><i class="fas fa-plus"></i></a>
      </div>
    </div>
    
    <div class="list-components">
      <?php
      while (($category = $categories->fetchArray())) {
      ?>
        <div class="list-component">
          <div class="text">
              <?= htmlspecialchars($category["name"]); ?>
          </div>
          <div class="buttons">
            <a href="./categories_show.php?category=<?=$category['id']?>">
              <i class="fas fa-binoculars"></i>
            </a>
            <a class="delete" href="./delete_category.php?category=<?=$category['id']?>">
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