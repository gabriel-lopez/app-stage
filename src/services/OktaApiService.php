<?php
namespace Src\Services;

use Src\Controllers\SettingController;

class OktaApiService
{
  private $clientId;
  private $clientSecret;
  private $redirectUri;
  private $metadataUrl;
  private $userController;
  private $settingController;
  private $apiUrlBase;
  private $apiToken;

  public function __construct($userController, $settingController)
  {
    $this->userController = $userController;
    $this->settingController = $settingController;

    $this->clientId = $settingController->getSetting("oauth_client_id");
    $this->clientSecret = $settingController->getSetting("oauth_client_secret");
    $this->redirectUri = $settingController->getSetting("oauth_redirect_uri");
    $this->metadataUrl = $settingController->getSetting("oauth_metadata_url");
  }

  public function buildAuthorizeUrl($state)
  {
    $metadata = $this->httpRequest($this->metadataUrl);
    $url = $metadata->authorization_endpoint . '?' . http_build_query([
      'response_type' => 'code',
      'client_id' => $this->clientId,
      'redirect_uri' => $this->redirectUri,
      'state' => $state,
      'scope' => 'openid',
    ]);
    return $url;
  }

  public function authorizeUser()
  {
    if ($_SESSION['state'] != $_GET['state']) {
      $result['error'] = true;
      $result['errorMessage'] = 'Authorization server returned an invalid state parameter';
      return $result;
    }

    if (isset($_GET['error'])) {
      $result['error'] = true;
      $result['errorMessage'] = 'Authorization server returned an error: ' . htmlspecialchars($_GET['error']);
      return $result;
    }

    $metadata = $this->httpRequest($this->metadataUrl);

    $response = $this->httpRequest($metadata->token_endpoint, [
      'grant_type' => 'authorization_code',
      'code' => $_GET['code'],
      'redirect_uri' => $this->redirectUri,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret
    ]);

    if (!isset($response->access_token)) {
      $result['error'] = true;
      $result['errorMessage'] = 'Error fetching access token!';
      return $result;
    }
    $_SESSION['access_token'] = $response->access_token;

    $token = $this->httpRequest($metadata->introspection_endpoint, [
      'token' => $response->access_token,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret
    ]);

    if ($token->active == 1) {
      $result['success'] = true;
      $result['username'] = $token->username;
      $result['is_admin'] = $this->userController->isAdmin($token->username);

      return $result;
    }
  }

  public function registerUser($input)
  {
    $data['profile'] = [
      'firstName' => $input['first_name'],
      'lastName' => $input['last_name'],
      'email' => $input['email'],
      'login' => $input['email']
    ];
    $data['credentials'] = [
      'password' => [
        'value' => $input['password']
      ]
    ];
    $data = json_encode($data);

    $ch = curl_init($this->apiUrlBase . 'users');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: applicgetenvation/json',
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data),
      'Authorization: SSWS ' . $this->apiToken
    ]);

    return curl_exec($ch);
  }

  public function findUser($input)
  {
    $url = $this->apiUrlBase . 'users?q=' . urlencode($input['email']) . '&limit=1';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: application/json',
      'Content-Type: application/json',
      'Authorization: SSWS ' . $this->apiToken
    ]);

    return curl_exec($ch);
  }

  public function resetPassword($userId)
  {
    $url = $this->apiUrlBase . 'users/' . $userId . '/lifecycle/reset_password';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, []);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: application/json',
      'Content-Type: application/json',
      'Authorization: SSWS ' . $this->apiToken
    ]);

    return curl_exec($ch);
  }

  private function httpRequest($url, $params = null)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($params) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    return json_decode(curl_exec($ch));
  }
}