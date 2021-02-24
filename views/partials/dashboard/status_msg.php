<?php
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $message = isset($_GET['message']) ? $_GET['message'] : null;
    
    if (isset($status)) {
      ?>

        <div class="status-msg <?= $status?>">
            <div class="msg">
                <?= htmlspecialchars($message)?>
            </div>
            <div class="close">
                <i class="fas fa-times"></i>
            </div>
        </div>
        

      <?php
    }
  ?>

  <script>


    var stat = document.querySelector('.status-msg');
    var statusClose = stat.querySelector('.close');

    statusClose.onclick = function() {
        stat.classList.add("hidden");
    }
  </script>
      