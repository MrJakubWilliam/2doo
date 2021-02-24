<?php 
  session_start();

  if (!isset($_SESSION['u_id']))
  {
    header('Location: ./');
    exit;
  }

  $user_id = $_SESSION['u_id'];
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
  </nav>
  <div class="container">

    <section>
      <h2>Create a new category</h2>
      <form class="create-form" action="./create_category.php" method="post">

        <div class="form-element">
          <label for="name">Category name: </label>
          <input type="text" id="name" name="name" value="">
        </div>

        <input type="submit" name="" class="button blue-bg" value="Create">
      </form>
    </section>
    <?php 
      include("./partials/dashboard/status_msg.php")
    ?>
  </div>

  


</body>

</html>