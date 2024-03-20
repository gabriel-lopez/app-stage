<?php
namespace Src\Services;

class DatabaseService
{
  private $link;
  private $dbServer;
  private $dbName;
  private $dbUsername;
  private $dbPassword;

  public function __construct()
  {
    $this->dbServer = $_ENV['AZURE_MYSQL_HOST'];
    $this->dbName = $_ENV['AZURE_MYSQL_DBNAME'];
    $this->dbUsername = $_ENV['AZURE_MYSQL_USERNAME'];
    $this->dbPassword = $_ENV['AZURE_MYSQL_PASSWORD'];

    $this->connect();
  }

  private function connect()
  {
    # DEV
    //$this->link = mysqli_connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->dbName);
    // PROD
    $mysqli = mysqli_init();
    $mysqli->real_connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->dbName, 3306, NULL, MYSQLI_CLIENT_SSL );

    $this->link = $mysqli;

    # Check connection
    if (!$this->link) {
      die("Connection failed: " . mysqli_connect_error());
    }

    # Check if table settings exists
    $this->importSqlFileIfTableNotExists('settings', './db.sql');
  }

  public function register_user($inputs)
  {
    $sql = "INSERT INTO users(first_name, last_name, email, password, sso_only, is_admin) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "ssssss", $param_firstname, $param_lastname, $param_email, $param_password, $param_sso_only, $param_is_admin);

      $password = trim($inputs['password']);

      $param_firstname = $inputs['first_name'];
      $param_lastname = $inputs['last_name'];
      $param_email = $inputs['email'];
      $param_password = ($password == '') ? null : password_hash($password, PASSWORD_DEFAULT);
      $param_sso_only = $inputs['sso_only'];
      $param_is_admin = $inputs['is_admin'];

      $result = null;
      if (mysqli_stmt_execute($stmt)) {
        $result['error'] = false;
      } else {
        $result['error'] = true;
        $result['errorMessage'] = 'Oops! Something went wrong. Please try again later.';
      }

      mysqli_stmt_close($stmt);

      return $result;
    }
  }

  public function validate_credentials($inputs)
  {
    $sql = "SELECT email, password, sso_only, is_admin FROM users WHERE email = ?";

    $password = trim($inputs['password']);

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_email);

      $param_email = $inputs['email'];

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);

        # Check if user exists, If yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          mysqli_stmt_bind_result($stmt, $email, $hashed_password, $sso_only, $is_admin);

          if (mysqli_stmt_fetch($stmt)) {
            # Check if password is correct
            $result['error'] = true;
            if (!$sso_only && password_verify($password, $hashed_password)) {
              $result['error'] = false;
              $result['success'] = true;
              $result['username'] = $email;
              $result['is_admin'] = $is_admin;
              #return $result;
            } else {
              $result['error'] = true;
              $result['errorMessage'] = "The email or password you entered is incorrect.";
              #return $result;
            }
          }
        } else {
          $result['error'] = true;
          $result['errorMessage'] = "Invalid username or password.";
        }
      } else {
        $result['error'] = true;
        $result['errorMessage'] = "Oops! Something went wrong. Please try again later.";
      }

      # Close statement
      mysqli_stmt_close($stmt);
    }

    return $result;
  }

  public function validate_email($email)
  {
    $sql = "SELECT id FROM users WHERE email = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_email);

      $param_email = $email;

      # Execute the prepared statement 
      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);

        # Check if email is already registered
        if (mysqli_stmt_num_rows($stmt) == 1) {
          return false;
        }
      } else {
        return false;
      }

      mysqli_stmt_close($stmt);

      return true;
    }
  }

  public function get_setting($name)
  {
    $sql = "SELECT value FROM settings WHERE name = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_name);

      $param_name = $name;

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $value);

        if (mysqli_stmt_fetch($stmt)) {
          return $value;
        }
      }
    }

    return null;
  }

  public function update_setting($name, $value)
  {
    $sql = "UPDATE settings SET value = ? WHERE name = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "ss", $value, $name);

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
      }
    }

    return false;
  }

  public function get_users()
  {
    $sql = "SELECT * FROM users";

    if ($result = mysqli_query($this->link, $sql)) {
      $users = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
      }
      mysqli_free_result($result);
      return $users;
    } else {
      return null;
    }
  }

  public function check_user_exists($email)
  {
    $sql = "SELECT COUNT(*) FROM users WHERE email = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $email);

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $count);

        if (mysqli_stmt_fetch($stmt)) {
          if ($count > 0) {
            return true;
          } else {
            return false;
          }
        }
      }
    }

    return false;
  }

  public function create_user_jit($email)
  {
    $sql = "INSERT INTO users(email, jit_created, sso_only) VALUES (?, 1, 1)";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_email);

      $param_email = $email;

      $result = null;
      if (mysqli_stmt_execute($stmt)) {
        $result['error'] = false;
      } else {
        $result['error'] = true;
        $result['errorMessage'] = 'Oops! Something went wrong. Please try again later.';
      }

      mysqli_stmt_close($stmt);

      return $result;
    }
  }

  public function update_user($inputs)
  {
    $sql = "UPDATE users SET first_name = ?, last_name = ?, is_admin = ? WHERE id = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "sssi", $inputs['first_name'], $inputs['last_name'], $inputs['is_admin'], $inputs['id']);

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
      }
    }

    return false;
  }

  public function delete_user($inputs)
  {
    $sql = "DELETE FROM users WHERE id = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "i", $inputs['id']);

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
      }
    }

    return false;
  }

  public function is_admin($email)
  {
    $sql = "SELECT is_admin FROM users WHERE email = ?";

    if ($stmt = mysqli_prepare($this->link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $email);

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $isAdmin);

        if (mysqli_stmt_fetch($stmt)) {
          return $isAdmin;
        }
      }
    }

    return false;
  }

  private function tableExists($tableName) {
    $query = "SHOW TABLES LIKE '$tableName'";
    $result = mysqli_query($this->link, $query);

    return mysqli_num_rows($result) > 0;
  }

  private function importSqlFileIfTableNotExists($tableName, $sqlFilePath) {
    // Check if table exists
    if (!$this->tableExists($tableName)) {
        // Read SQL file
        $query = file_get_contents($sqlFilePath, true);

        // Execute SQL file
        $result = mysqli_multi_query($this->link, $query);

        if ($result) {
            echo "SQL file imported successfully.";
        } else {
            echo "Error importing SQL file: " . mysqli_error($this->link);
        }

        die();
    }
  }
}