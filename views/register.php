<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php 
include("./partials/head.php");
?>

<body class="auth">
  <nav class="main">
    <div class="container">
      <div class="logo">
        <img src="../assets/svg/logo.svg" alt="" srcset="">
      </div>
      <div>
      </div>
    </div>
  </nav>
    <div class="container">
  <section>
      <form class="auth-form" action="./create_user.php" method="post">
        <div class="buttons">
          <a href="./"><i class="fas fa-chevron-circle-left"></i> Back</a>
        </div>
        <div class="form-element">
          <label for="name">Name: </label>
          <input type="text" id="name" name="name" value="">
        </div>


        <div class="form-element">
          <label for="email">Email: </label>
          <input type="email" id="email" name="email" value="">
        </div>

        <div class="form-element">
          <label for="password">Password: </label>
          <input type="password" id="password" name="password" value="">
        </div>

        <input type="submit" name="" class="button blue-bg" value="Register">
      </form>
  </section>
  <?php 
        include("./partials/dashboard/status_msg.php")
      ?>
    </div>

</body>

</html>