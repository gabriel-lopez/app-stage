<?php
namespace Src\Controllers;

use Src\Services\DatabaseService;
use Src\Services\OktaApiService;

class OktaController
{
  private $oktaApi;
  private $errors = null;
  private $errorMessage = null;
  private $databaseService = null;

  public function __construct(OktaApiService $oktaApi)
  {
    $this->oktaApi = $oktaApi;
  }

  public function handleForgotPasswordPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $input = [
        'email' => $_POST['email']
      ];

      // validate the email address
      if (
        empty($input['email']) ||
        strlen($input['email']) > 100 ||
        (!filter_var($input['email'], FILTER_VALIDATE_EMAIL))
      ) {
        $viewData = [
          'input' => $input,
          'errors' => true,
          'errorMessage' => '<br>Invalid email!'
        ];
        view('forgot', $viewData);
        return true;
      }

      // search for this user via the OktaApi
      $result = $this->oktaApi->findUser($input);
      $result = json_decode($result, true);
      if (!isset($result[0]['id'])) {
        $viewData = [
          'input' => $input,
          'errors' => true,
          'errorMessage' => '<br>User not found!'
        ];
        view('forgot', $viewData);
        return true;
      }

      // attempt to send a reset link to this user
      $userId = $result[0]['id'];
      $result = $this->oktaApi->resetPassword($userId);

      header('Location: /?password_reset');
      return true;
    }

    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }
}