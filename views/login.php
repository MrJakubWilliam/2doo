<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php 
include("./partials/head.php");
?>

<body class="auth">
<?php 
include("./partials/main/navbar.php");
?>
  <div class="container">
    <section>
        <form class="auth-form" action="./authenticate_user.php" method="post">
          <div class="buttons">
            <a href="index.php"><i class="fas fa-chevron-circle-left"></i> Back</a>
          </div>
          <div class="form-element">
            <label for="email">Email: </label>
            <input type="email" id="email" name="email" value="">
          </div>

          <div class="form-element">
            <label for="password">Password: </label>
            <input type="password" id="password" name="password" value="">
          </div>

          <input type="submit" name="" class="button blue-bg" value="Login">
        </form>
    </section>
    <?php 
        include("./partials/dashboard/status_msg.php")
      ?>
  </div>

</body>

</html>