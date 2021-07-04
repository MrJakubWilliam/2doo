<div class="hamburger">
    <i class="fas fa-bars"></i>
</div>
<nav class="mobile-dash closed">
    <div class="nav-links">
        <div class="nav-link">
            <a href="../dashboard/index.php">Dashboard</a>
        </div>
        <div class="nav-link">
            <a href="../category/index.php">Categories</a>
        </div>
        <div class="nav-link">
            <a href="../list/index.php">Lists</a>
        </div>
        <div class="nav-link">
            <a href="../household/index.php">Households</a>
        </div>
        <div class="nav-link">
            <a href="../chore/index.php">Chores</a>
        </div>
        <div class="nav-link" id="logout">
            <a href="../routes/route.php?controller=auth&resource=logout">Logout</a>
        </div>
    </div>
    <div class="close">
        <i class="fas fa-times"></i>
    </div>
</nav>

<script type="application/javascript">
    var hamburger = document.querySelector(".hamburger");
    var dashboard = document.querySelector(".mobile-dash");
    var dashClose = dashboard.querySelector(".close");
    hamburger.onclick = function() {
        dashboard.classList.remove("closed");
    }
    dashClose.onclick = function() {
        dashboard.classList.add("closed");
    }
</script>

<nav class="dash">
    <div class="logo">
        <img src="/~u2015138/cs139/assets/svg/logo_white.svg" alt="" srcset="">
    </div>
    <div class="hamburger">
    </div>
    <div class="nav-links">
        <div class="nav-link">
            <a href="../dashboard/index.php">Dashboard</a>
        </div>
        <div class="nav-link">
            <a href="../category/index.php">Categories</a>
        </div>
        <div class="nav-link">
            <a href="../list/index.php">Lists</a>
        </div>
        <div class="nav-link">
            <a href="../household/index.php">Households</a>
        </div>
        <div class="nav-link">
            <a href="../chore/index.php">Chores</a>
        </div>
        <div class="nav-link" id="logout">
            <a href="../routes/route.php?controller=auth&resource=logout">Logout</a>
        </div>
    </div>
</nav>