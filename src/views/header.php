<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="PHP Login App bd-index-custom-example">
  <title>Okta Login Example </title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.2/css/bulma.min.css">
</head>

<body class="layout-default">
  <nav id="navbar" class="navbar has-shadow is-spaced">
    <div class="container">
      <div class="content">
        <h1>Okta Login Example</h1>
        <?php
        if (isset($_SESSION['username'])) {
          ?>
          <p>
            Logged in as
            <?php echo $_SESSION['username'] ?>
          </p>
          <p>
            <a href="/">Home</a> <span aria-hidden="true">|</span>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
              echo '<a href="/?settings">Settings</a> <span aria-hidden="true">|</span>';
            } ?>
            <a href="/?logout">Log Out</a></p>
        <?php
        } else {
          ?>
          <p>Not logged in</p>
          <p>
            <?php
            if($data && isset($data['settings'])) {
              $sso_only = $data['settings']['sso_only'];

              if($sso_only == '0') {
                echo '<a href="/?login">Log In</a> <span aria-hidden="true">|</span>';
              }
            }
            ?>
            <?php 
            if($data && isset($data['settings'])) {
              $sso_enabled = $data['settings']['oauth_enabled'];

              if($sso_enabled == 'on') {
                echo '<a href="/?sso">Log In SSO</a>';
              }
            }
            ?>
            <!-- <a href="/?forgot">Forgot Password</a> <span aria-hidden="true">|</span> -->
          </p>
          <?php
        }
        ?>
      </div>
    </div>
  </nav>