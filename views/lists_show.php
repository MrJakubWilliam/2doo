<?php
  include("database.php");
  
  session_start();
  if (!isset($_SESSION['u_id']))
  {
    header('Location: ./');
    exit;
  }
  $user_id = $_SESSION['u_id'];

  if (!isset($_GET['list'])) {
    header('Location: ./lists_index.php?status=error&message=Cannot%20find%20the%20list!');
    exit;
  }

  $id = $_GET['list'];

  $db = new Database();
  // $list = $db->querySingle("SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = $id AND categories.user_id = $user_id;");

  $stmt = $db->prepare('SELECT lists.* FROM lists INNER JOIN categories ON lists.category_id = categories.id WHERE lists.id = :l_id AND categories.user_id = :u_id');
  $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
  $stmt->bindValue(':l_id', $id, SQLITE3_INTEGER);
  $list = $stmt->execute();
  $list = $list->fetchArray();

  // $list_items = $db->query("SELECT * FROM list_items WHERE list_id = $id;");
  if (empty($list)) {
    header('Location: ./lists_index.php?status=error&message=List%20does%20not%20exist!');
    exit;
  }
  $list_items = $db->query("SELECT * FROM list_items WHERE list_id = $id");
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
      <h2>List: <?= htmlspecialchars($list['name']) ?></h2>
      <div class="buttons">
        <!-- <a href="./list_item_create.php?list=<?= $id ?>" class="button" id='add'><i class="fas fa-plus"></i></a> -->
        <a href="./delete_list.php?list=<?= $id ?>" class="button" id='delete'><i class="far fa-trash-alt"></i></a>

      </div>
    </div>

    <form action="./create_list_item.php" id="list-item-create" class="create-form" method="post">
        <div class="form-element">
          <label for="todo">New 2doo: </label>
          <input type="text" id="todo" name="todo" value="">
        </div>
        <input type="hidden" name="list" value="<?= $id?>">
        <input type="submit" name="" class="button blue-bg" value="Add">
      </form>
    <div class="list-components">
      <?php
        while (($list_item = $list_items->fetchArray())) {
      ?>
        <div class="list-component <?php 
          if ($list_item['checked']) {
            echo 'checked';
          }
        ?>">
          <div class="text">
              <?= htmlspecialchars($list_item["content"]); ?>
          </div>
          <div class="buttons">
            <a href="./list_item_check.php?list_item=<?=$list_item['id']?>">
              <i class="far fa-check-square"></i>
            </a>
            <a class="delete" href="./delete_list_item.php?list_item=<?=$list_item['id']?>">
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