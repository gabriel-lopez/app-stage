<?php view('header', $data); ?>
<section class="hero">
  <div class="hero-body">
    <div class="container">

    </div>
  </div>
</section>
<section>
  <div class="container">
    <?php
    if (isset($_SESSION['username'])) {
      ?>
      <h1 class="title">Settings</h1>
      <h2 class="subtitle">SSO</h2>

      <form action="/?settings" method="POST">
        <div class="columns">
          <div class="column">
            <div class="field">
              <label class="label" for="oauth_enabled">OAuth Enabled</label>
              <div class="control">
                <input type='hidden' value='0' name='oauth_enabled'>
                <input type="checkbox" name="oauth_enabled" id="oauth_enabled" <?php if ($data['settings']['oauth_enabled'] != '0') {
                  echo 'checked';
                } ?>>
              </div>
            </div>
          </div>
          <div class="column">
            <div class="field">
              <label class="label" for="jit_enabled">JIT Enabled</label>
              <div class="control">
                <input type='hidden' value='0' name='jit_enabled'>
                <input type="checkbox" name="jit_enabled" id="jit_enabled" <?php if ($data['settings']['jit_enabled'] != '0') {
                  echo 'checked';
                } ?>>
              </div>
            </div>
          </div>
          <div class="column">
            <div class="field">
              <label class="label" for="sso_only">SSO Only</label>
              <div class="control">
                <input type='hidden' value='0' name='sso_only'>
                <input type="checkbox" name="sso_only" id="sso_only" <?php if ($data['settings']['sso_only'] != '0') {
                  echo 'checked';
                } ?>>
              </div>
            </div>
          </div>
        </div>
        <div class="columns">
          <div class="column">
            <div class="field">
              <label class="label" for="oauth_client_id">OAuth Client ID</label>
              <div class="control">
                <input class="input" type="text" name="oauth_client_id" id="oauth_client_id" placeholder=""
                  value="<?php echo $data['settings']['oauth_client_id']; ?>">
              </div>
            </div>
          </div>
          <div class="column">
            <div class="field">
              <label class="label" for="oauth_client_secret">OAuth Client Secret</label>
              <div class="control">
                <input class="input" type="password" name="oauth_client_secret" id="oauth_client_secret" placeholder=""
                  value="<?php echo $data['settings']['oauth_client_secret']; ?>">
              </div>
            </div>
          </div>
        </div>
        <div class="columns">
          <div class="column">
            <div class="field">
              <label class="label" for="oauth_redirect_uri">OAuth Redirect Uri</label>
              <div class="control">
                <input class="input" type="text" name="oauth_redirect_uri" id="oauth_redirect_uri" placeholder=""
                  value="<?php echo $data['settings']['oauth_redirect_uri']; ?>">
              </div>
            </div>
          </div>
          <div class="column">
            <div class="field">
              <label class="label" for="oauth_metadata_url">OAuth Metadata Url</label>
              <div class="control">
                <input class="input" type="text" name="oauth_metadata_url" id="oauth_metadata_url" placeholder=""
                  value="<?php echo $data['settings']['oauth_metadata_url']; ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="columns">
          <div class="column">
            <input type="hidden" name="command" value="settings">
            <button class="button is-primary">Update Settings</button>
          </div>
        </div>
      </form>

      <h1 class="title">Users management</h1>
      <h2 class="subtitle">User list</h2>

      <table class="table">
        <thead aria-hidden="true">
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Has Password</th>
            <th>JIT Created</th>
            <th>SSO Only</th>
            <th>Is Administrator</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tfoot aria-hidden="true">
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Has Password</th>
            <th>JIT Created</th>
            <th>SSO Only</th>
            <th>Is Administrator</th>
            <th>Actions</th>
          </tr>
        </tfoot>
        <tbody>

          <?php
          if (isset($data['users'])) {
            $users = $data['users'];

            foreach ($users as $user) {
              echo '<form action="/?settings" method="POST">';
              echo '<input type="hidden" name="command" value="updateUser">';
              echo '<input type="hidden" name="userId" value="' . $user['id'] . '">';
              echo "<tr>";
              echo "<td>" . $user['id'] . "</td>";
              echo '<td><input class="input" type="text" name="first_name" id="first_name" placeholder="" value="' . $user['first_name'] . '"></td>';
              echo '<td><input class="input" type="text" name="last_name" id="last_name" placeholder="" value="' . $user['last_name'] . '"></td>';
              echo '<td>' . $user['email'] . '</td>';
              echo '<td><label for="has_password">Has Password</label><input type="checkbox" name="has_password" id="has_password" disabled';
              if ($user['password']) {
                echo ' checked';
              }
              echo '></td>';
              echo '<td><label for="jit_created">JIT Created</label><input type="checkbox" name="jit_created" id="jit_created" disabled';
              if ($user['jit_created']) {
                echo ' checked';
              }
              echo '></td>';
              echo '<td><label for="sso_only">SSO Only</label><input type="checkbox" name="sso_only" id="sso_only" disabled';
              if ($user['sso_only']) {
                echo ' checked';
              }
              echo '></td>';
              echo '<td><input type="hidden" value="0" name="is_admin"><label for="is_admin">Is Administrator</label><input type="checkbox" name="is_admin" id="is_admin"';
              if ($user['is_admin']) {
                echo ' checked';
              }
              echo '></td>';
              echo '<td><button class="button is-primary">Update User</button>';
              echo "</form>";
              echo '<form action="/?settings" method="POST">';
              echo '<input type="hidden" name="command" value="deleteUser">';
              echo '<input type="hidden" name="userId" value="' . $user['id'] . '">';
              echo '<button class="button is-danger">Delete User</button>';
              echo '</form></td>';
              echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>

      <h2 class="subtitle">User creation</h2>

      <form method="post" action="/?settings" method="POST">
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
          <label class="label">Is Administrator</label>
          <div class="control">
            <input type="checkbox" name="is_admin">
          </div>
        </div>

        <div class="field">
          <label class="label">SSO Only</label>
          <div class="control">
            <input type="checkbox" name="sso_only">
          </div>
        </div>

        <div class="field">
          <label class="label">First Name</label>
          <div class="control">
            <input class="input" name="first_name" type="text" value="<?php if ($data && isset($data['input'])) {
              echo $data['input']['first_name'];
            } ?>">
          </div>
        </div>

        <div class="field">
          <label class="label">Last Name</label>
          <div class="control">
            <input class="input" name="last_name" type="text" value="<?php if ($data && isset($data['input'])) {
              echo $data['input']['last_name'];
            } ?>">
          </div>
        </div>

        <div class="field">
          <label class="label">Email</label>
          <div class="control">
            <input class="input" name="email" type="email" value="<?php if ($data && isset($data['input'])) {
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

        <div class="field">
          <label class="label">Repeat Password</label>
          <div class="control">
            <input class="input" name="repeat_password" type="password" value="">
          </div>
        </div>

        <input type="hidden" name="command" value="newUser">

        <div class="control">
          <button class="button is-link">Create User</button>
        </div>
      </form>
      <?php
    } else {
      ?>
      <p class="subtitle is-4">
        You need to login to access the content!
      </p>
      <?php
    }
    ?>
  </div>
</section>
<?php view('footer'); ?>