<?php
namespace Src\Controllers;

use Src\Services\DatabaseService;

class SettingController
{
  private $oktaApi;
  private $errors = null;
  private $errorMessage = null;
  private $databaseService = null;

  public function __construct()
  {
    $this->databaseService = new DatabaseService();
  }

  public function getSetting($name)
  {
    return $this->databaseService->get_setting($name);
  }

  public function getUsers()
  {
    $users = $this->databaseService->get_users();

    return $users;
  }

  public function handleSettingsPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $inputs = [
        'oauth_enabled' => $_POST['oauth_enabled'],
        'jit_enabled' => $_POST['jit_enabled'],
        'sso_only' => $_POST['sso_only'],
        'oauth_client_id' => $_POST['oauth_client_id'],
        'oauth_client_secret' => $_POST['oauth_client_secret'],
        'oauth_redirect_uri' => $_POST['oauth_redirect_uri'],
        'oauth_metadata_url' => $_POST['oauth_metadata_url'],
      ];

      foreach ($inputs as $key => $value) {
        $this->databaseService->update_setting($key, $value);
      }

      return [];
    }

    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }
}