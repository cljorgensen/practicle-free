<div class='modal fade' id='viewshortcuts' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'>&times;</button>
        <div id='ModalViewShortcuts'><h4><span class=''></span> <?php echo _('Shortcuts');?> </div>
      </div>
      <div class='modal-body'>
        <form role='form' method='POST'>
        <div class='form-group'>
          <small>
            Alt+L - Lock<br>
            Alt+O - Logout<br>
            Alt+H - Home<br>
            Alt+I - New Incident<br>
            Alt+R - New Request<br>
            Alt+C - New Change<br>
            Alt+P - Password Manager<br>
          </small>
        </div>
      </div>
      <div class='modal-footer'>
        </form>
      </div>
    </div>
  </div>
</div>

<div class='modal fade' id='viewguihelp' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'>&times;</button>
        <div id='ModalViewGUIHelp'><h4><span class=''></span> <?php echo _('Hjælp til GUI shortcuts');?> </div>
      </div>
      <div class='modal-body'>
        <form role='form' method='POST'>
        <div class='form-group'>
        <small>
          Gør alting større:<br>
          Windows og Linux: Tryk på Ctrl og +<br>
          Mac: Tryk på ⌘ og +.<br>
          Chrome OS: Tryk på Ctrl og +<br><br>
          Gør alting mindre:<br>
          Windows og Linux: Tryk på Ctrl og -<br>
          Mac: Tryk på ⌘ og -<br>
          Chrome OS: Tryk på Ctrl og -<br><br>
          Se i fuld skærm:<br>
          Windows og Linux: Tryk på F11<br>
          Mac: Tryk på ⌘+Ctrl+f<br>
          Chrome OS: Tryk på tasten for fuld skærm Fuld skærm øverst på tastaturet. Denne tast kaldes også F4<br>
        </small>
        </div>
      </div>
      <div class='modal-footer'>
        </form>
      </div>
    </div>
  </div>
</div>

<div class='modal fade' id='viewgoogleauthenticator' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'>&times;</button>
        <div id='ModalGoogleAuthenticator'><h4><span class=''></span> <?php echo _('Google authenticator');?> </div>
      </div>
      <div class='modal-body'>
        <form role='form' method='POST'>
        <div class='form-group'>
        <small>
          Du aktiverer Google authenticator via <a href='user_settings.php'>brugerindstillingerne</a>.<br><br>
          1. Download Google Authenticater app til din mobil.<br>
          2. Klik på dit navn i toppen til venstre og tryk på indstillinger.<br>
          3. Kontroller at din Google authenticator app starter op på din mobil.<br>
          4. Hvis appen kører fint skal du nu aktivere Google Authenticator i practicle under <a href='user_settings.php'>brugerindstillingerne</a>.<br>
          5. Scan via Google Authenticator appen den QR kode der nu er kommet frem under dine brugerindstillinger.<br>
          6. Du kan til enhver tid deaktivere Google authenticator.<br>
        </small>
        </div>
      </div>
      <div class='modal-footer'>
        </form>
      </div>
    </div>
  </div>
</div>