<?php view('header', $data); ?>

<?php 

// # Check if user is already logged in, If yes then redirect him to index page
// if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == TRUE) {
//   echo "<script>" . "window.location.href='./'" . "</script>";
//   exit;
// }

?>

<section class="hero">
  <div class="hero-body">
    <div class="container">
      <form method="post" action="/">

        <?php
        if ($data && isset($data['errors'])) {
          ?>
          <div class="notification is-danger">
            <?php
            echo "Errors:";
            echo $data['errorMessage'];
            ?>
          </div>
          <?php
        }
        ?>

        <div class="field">
          <label class="label">Email</label>
          <div class="control">
            <input class="input" name="email" type="email"
              value="<?php if ($data && isset($data['input'])) {
                echo $data['input']['email'];
              } ?>">
          </div>
        </div>

        <div class="field">
          <label class="label">Password</label>
          <div class="control">
            <input class="input" name="password" type="password" value="">
          </div>
        </div>

        <input type="hidden" name="command" value="login">

        <div class="control">
          <button class="button is-link">Login</button>
        </div>

      </form>
    </div>
  </div>
</section>
<?php view('footer'); ?>