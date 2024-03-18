<?php
namespace Src\Controllers;

use Src\Services\DatabaseService;

class UserController
{
  private $errors = null;
  private $errorMessage = null;
  private $databaseService = null;

  public function __construct()
  {
    $this->databaseService = new DatabaseService();
  }

  public function handleLoginPost($data)
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $input = [
        'email' => $_POST['email'],
        'password' => $_POST['password']
      ];

      $result = $this->databaseService->validate_credentials($input);

      if ($result['error']) {
        $viewData = [
          'input' => $input,
          'errors' => true,
          'errorMessage' => '<br>' . $result['errorMessage']
        ];

        $viewData = array_merge($viewData, $data);

        view('login', $viewData);
        return true;
      }

      $_SESSION['username'] = $result['username'];
      $_SESSION['is_admin'] = $result['is_admin'];

      header('Location: /');
      return true;
    }

    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }

  public function handleNewUserPost($data)
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $input = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'repeat_password' => $_POST['repeat_password'],
        'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
        'sso_only' => isset($_POST['sso_only']) ? 1 : 0,
      ];

      $this->validateRegistrationForm($input);
      if ($this->errors) {
        $viewData = [
          'input' => $input,
          'errors' => $this->errors,
          'errorMessage' => $this->errorMessage
        ];
        return $viewData;
      }

      $result = $this->databaseService->register_user($input);

      if ($result['error']) {
        $viewData = [
          'input' => $input,
          'errors' => true,
          'errorMessage' => '<br>' . $result['errorMessage']
        ];
        return $viewData;
      }
      return [];
    }
    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }

  public function handleUpdateUserPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $input = [
        'id' => $_POST['userId'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'is_admin' => ($_POST['is_admin'] == '0') ? 0 : 1
      ];

      $result = $this->databaseService->update_user($input);

      if (!$result) {
        $viewData = [
          'errors' => true,
          'errorMessage' => '<br> Error updating the user !'
        ];
        return $viewData;
      }
      return [];
    }

    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }

  public function handleDeleteUserPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $inputs = [
        'id' => $_POST['userId'],
      ];

      $result = $this->databaseService->delete_user($inputs);

      if (!$result) {
        $viewData = [
          'errors' => true,
          'errorMessage' => '<br> Error deleting the user !'
        ];
        return $viewData;
      }
      return [];
    }

    header('HTTP/1.0 405 Method Not Allowed');
    die();
  }

  private function validateRegistrationForm($input)
  {
    $errorMessage = '';
    $errors = false;

    // validate field lengths
    if (strlen($input['first_name']) > 50) {
      $errorMessage .= "<br>'First Name' is too long (50 characters max)!";
      $errors = true;
    }
    if (strlen($input['last_name']) > 50) {
      $errorMessage .= "<br>'Last Name' is too long (50 characters max)!";
      $errors = true;
    }
    if (strlen($input['email']) > 100) {
      $errorMessage .= "<br>'Email' is too long (100 characters max)!";
      $errors = true;
    }

    // validate field contents
    if (empty($input['first_name'])) {
      $errorMessage .= "<br>'First Name' is required!";
      $errors = true;
    }
    if (empty($input['last_name'])) {
      $errorMessage .= "<br>'Last Name' is required!";
      $errors = true;
    }
    if (empty($input['email'])) {
      $errorMessage .= "<br>'Email' is required!";
      $errors = true;
    } else if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || !$this->databaseService->validate_email($input['email'])) {
      $errorMessage .= "<br>Invalid email!";
      $errors = true;
    }

    if ($input['sso_only'] == 0) {
      if (strlen($input['password']) > 72) {
        $errorMessage .= "<br>'Password' is too long (72 characters max)!";
        $errors = true;
      }
      if (strlen($input['password']) < 8) {
        $errorMessage .= "<br>'Password' is too short (8 characters min)!";
        $errors = true;
      }

      if (empty($input['password'])) {
        $errorMessage .= "<br>'Password' is required!";
        $errors = true;
      }

      if (empty($input['repeat_password'])) {
        $errorMessage .= "<br>'Repeat Password' is required!";
        $errors = true;
      }
      if ($input['password'] !== $input['repeat_password']) {
        $errorMessage .= "<br>Passwords do not match!";
        $errors = true;
      }
    }

    $this->errors = $errors;
    $this->errorMessage = $errorMessage;
  }

  public function getUsers()
  {
    return $this->databaseService->get_users();
  }

  public function checkUserExists($email)
  {
    return $this->databaseService->check_user_exists($email);
  }

  public function createUser($email)
  {
    return $this->databaseService->create_user_jit($email);
  }

  public function isAdmin($email)
  {
    return $this->databaseService->is_admin($email);
  }
}