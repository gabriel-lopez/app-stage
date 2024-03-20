<?php
require('./bootstrap.php');

use Src\Services\OktaApiService;
use Src\Controllers\UserController;
use Src\Controllers\SettingController;

$userController = new UserController();
$settingController = new SettingController();

$oktaApi = new OktaApiService($userController, $settingController);

// view data
$data = null;

$data['settings']['oauth_enabled'] = $settingController->getSetting('oauth_enabled');
$data['settings']['sso_only'] = $settingController->getSetting('sso_only');

// build login URL and redirect the user
if (isset($_REQUEST['sso']) && (!isset($_SESSION['username']))) {
  $_SESSION['state'] = bin2hex(random_bytes(5));
  $authorizeUrl = $oktaApi->buildAuthorizeUrl($_SESSION['state']);
  header('Location: ' . $authorizeUrl);
  die();
}

// build login URL and redirect the user
if (isset($_REQUEST['login']) && (!isset($_SESSION['username']))) {
  view('login', $data);
  die();
}

if (isset($_REQUEST['command']) && ($_REQUEST['command'] == 'login')) {
  $userController->handleLoginPost($data);
  die();
}

// handle the redirect back
if (isset($_GET['code'])) {
  $result = $oktaApi->authorizeUser();
  if (isset($result['error'])) {
    $data['loginError'] = $result['errorMessage'];
  }

  $username = '';
  $is_admin = 0;

  if (array_key_exists('username', $result)) {
    $username = $result['username'];
  }

  if (array_key_exists('is_admin', $result)) {
    $is_admin = $result['is_admin'];
  }

  // Check if user exists in the database
  $userExists = $userController->checkUserExists($username);
  if ($userExists) {
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = $is_admin;
  } else {
    $jitEnabled = $settingController->getSetting('jit_enabled');
    if ($jitEnabled) {
      // JIT is enabled, create user
      $created = $userController->createUser($username);

      if($created) {
        $_SESSION['username'] = $username;
      }
    } else  {
      $_SESSION['username'] = $username;
      $_SESSION['is_admin'] = $is_admin;
    }
  }
}

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['username']);
  header('Location: /');
  die();
}

/* Password Reset */

// if (isset($_REQUEST['forgot'])) {
//   view('forgot', $data);
//   die();
// }

// if (isset($_REQUEST['command']) && ($_REQUEST['command'] == 'forgot_password')) {
//   $userController->handleForgotPasswordPost();
//   die();
// }

// if (isset($_REQUEST['password_reset'])) {
//   $data['thank_you'] = 'You should receive an email with password reset instructions';
// }

/* Settings */

if (isset($_REQUEST['settings'])) {

  $viewData = [];
  if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'settings') {
    $viewData = $settingController->handleSettingsPost();
  } else if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'newUser') {
    $viewData = $userController->handleNewUserPost($data);
  } else if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'updateUser') {
    $viewData = $userController->handleUpdateUserPost();
  } else if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'deleteUser') {
    $viewData = $userController->handleDeleteUserPost();
  }

  $users = $userController->getUsers();

  $data['users'] = $users;
  $data['settings']['oauth_enabled'] = $settingController->getSetting('oauth_enabled');
  $data['settings']['jit_enabled'] = $settingController->getSetting('jit_enabled');
  $data['settings']['sso_only'] = $settingController->getSetting('sso_only');
  $data['settings']['oauth_client_id'] = $settingController->getSetting('oauth_client_id');
  $data['settings']['oauth_client_secret'] = $settingController->getSetting('oauth_client_secret');
  $data['settings']['oauth_redirect_uri'] = $settingController->getSetting('oauth_redirect_uri');
  $data['settings']['oauth_metadata_url'] = $settingController->getSetting('oauth_metadata_url');

  $data = array_merge($viewData, $data);

  view('admin/settings', $data);
  die();
}

/* Home */

view('home', $data);
