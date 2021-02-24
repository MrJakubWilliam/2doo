<?php
  include("database.php");

  session_start();
  if (!isset($_SESSION['u_id']))
  {
    header('Location: ./');
  }
  $user_id = $_SESSION['u_id'];

  $id = isset($_GET['category']) ? $_GET['category'] : null;
  $db = new Database();
  
  
  // $categories = $db->query("SELECT * FROM categories WHERE user_id = $user_id;");
  $stmt = $db->prepare('SELECT * FROM categories WHERE user_id = :u_id');
  $stmt->bindValue(':u_id', $user_id, SQLITE3_INTEGER);
  $categories = $stmt->execute();

  $category = $categories->fetchArray();
  if (is_bool($category)) {
    header('Location: ./categories_index.php?status=error&message=Cannot%20find%20any%20categories!');
    exit;
  }
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
      <h2>Create a new list</h2>
      <form class="create-form" action="./create_list.php" method="post">

        <div class="form-element">
          <label for="name">List name: </label>
          <input type="text" id="name" name="name" value="">
        </div>

        <div class="form-element">
          <label for="category">Category: </label>
          <select name="category" id="category">
            <?php
            do {

            ?>
              <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php
              
              } while(($category = $categories->fetchArray()));
            ?>
          </select>
        </div>

        <input type="submit" name="" class="button blue-bg" value="Create">
      </form>
    </section>
    <?php 
      include("./partials/dashboard/status_msg.php")
    ?>
  </div>
  <script>
    document.querySelector('.form-element #category').value = "<?= $id; ?>";
  </script>
</body>

</html>