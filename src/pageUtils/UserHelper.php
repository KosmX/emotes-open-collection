<?php declare(strict_types=1);

namespace pageUtils;

# Only do 1 query per user request, and keep the important info available
use elements\IElement;
use elements\LiteralElement;
use user\RegisterUser;

class UserHelper
{
    private static UserHelper $INSTANCE;

    public string $uname;
    public string $displayName;
    public string $email;
    public bool $publicEmail;
    public bool $theCheckbox;
    private ?int $userID; //non null until registered,

    private ?string $usernameInvalid = null;

    /**
     * @param string $uname
     * @param string|null $displayName
     * @param string|null $email
     * @param bool $publicEmail
     * @param bool $theCheckbox
     * @param int|null $userID
     */
    public function __construct(string $uname, ?string $displayName, ?string $email, bool $publicEmail = false, bool $theCheckbox = false, ?int $userID = null)
    {
        $this->uname = $uname;
        $this->displayName = $displayName ?? $uname;
        $this->email = $email ?? '';
        $this->publicEmail = $publicEmail;
        $this->theCheckbox = $theCheckbox;
        $this->userID = $userID;
    }


    public function getForm(string $target = '/u/edit', string $submitTitle = 'Save'): IElement {

        $emailBox = self::checked($this->publicEmail);
        $checkbox = self::checked($this->theCheckbox);
        $unameValid = '';
        $unameFeedback = '';
        if ($this->usernameInvalid != null) {
            $unameValid = ' is-invalid';
            $unameFeedback = self::invalidFeedback('usernameHelp', $this->usernameInvalid);
        }

        return new LiteralElement(<<<FORM
<form method="post" action="$target">
  <div class="mb-3">
    <label for="username" class="form-label">User name</label>
    <span class="input-group-text" id="inputGroupPrepend3">@</span>
    <input name="username" type="text" class="form-control$unameValid" id="username" aria-describedby="usernameHelp" value="$this->uname" pattern="^[a-z0-9]+$" maxlength="128" minlength="3">
    <div id="usernameHelp" class="form-text">It will be your user URL. For example: emotes.kosmx.dev/u/kosmx<br>It must be unique.</div>
    $unameFeedback
  </div>
  <div class="mb-3">
    <label for="displayname" class="form-label">Display name</label>
    <input name="displayname" type="text" class="form-control" id="displayname" aria-describedby="displaynameHelp" value="$this->displayName" maxlength="128" minlength="1">
    <div id="displaynameHelp" class="form-text">Fancy display name, does support unicode 😉</div>
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp" value="$this->email">
    <div id="emailHelp" class="form-text">Only needed as a contact info. We don't verify it.</div>
  </div>
  <div class="mb-3 form-check">
    <input name="public-email" type="checkbox" class="form-check-input" id="public-email" value="1"$emailBox>
    <label class="form-check-label" for=public-email>Make my email public</label>
  </div>
  <div class="mb-3 form-check">
    <input name="checkbox" type="checkbox" class="form-check-input" id="exampleCheck1" aria-describedby="exampleCheckHelp" value="1"$checkbox>
    <label class="form-check-label" for="exampleCheck1">Check me out</label>
    <div id="exampleCheckHelp" class="form-text">It's purpose is completely unknown, apparently it does not do anything.</div>
  </div>
  <button type="submit" class="btn btn-primary">$submitTitle</button>
</form>
FORM);
    }

    /**
     * @return bool if registration successful
     */
    public function register(int $userID, string $authName, string $token): mixed {
        $this->usernameInvalid = null;
        if (isset($_POST['username']) && isset($_POST['email'])) {
            $this->uname = $_POST['username'];
            $this->email = $_POST['email'];

            if ($this->uname == '') {
                $this->usernameInvalid = "Please specify a username!";
                return false;
            }

            $this->displayName = $_POST['displayname'] ?? $this->uname;
            if ($this->displayName == '') {
                $this->displayName = $this->uname;
            }
            $this->publicEmail = ($_POST['public-email'] ?? '') == '1';
            $this->theCheckbox = ($_POST['checkbox'] ?? '') == '1';

            if (preg_match('~^[a-z\\d]+$~', $this->uname) == 0) {
                //$this->usernameInvalid = 'Username is verified on the server. good try!'; //The standard form does not allow invalid usernames, whoever finds it, they must have done the post manually.
                $this->usernameInvalid = "Please choose a valid username, only contains small letters and numbers";
                return false;
            }

            getDB()->begin_transaction();
            $lockAndCheckUname = getDB()->prepare("SELECT * from users where username = ? FOR UPDATE;"); //Lock the username
            $lockAndCheckUname->bind_param("s", $this->uname);
            $lockAndCheckUname->execute();
            $res = $lockAndCheckUname->get_result();

            if ($res->num_rows != 0) {
                $this->usernameInvalid = 'The username is already taken, please choose another one';
                getDB()->rollback(); //We did nothing, close the transaction
                return false;
            } else {
                $addUser = getDB()->prepare('INSERT INTO users (username, displayName, email, isEmailPublic, theCheckbox) VALUES (?, ?, ?, ?, ?)');
                $addUser->bind_param("sssbb", $this->uname, $this->displayName, $this->email, $this->publicEmail, $this->theCheckbox);
                $addUser->execute();
                //$getIndex = $addUser->insert_id; //The ID of our new user!
                $addAuth = getDB()->prepare('INSERT INTO userAccounts (userID, authID, platformUserID, token) SELECT LAST_INSERT_ID(), auths.id, ?, ? FROM auths where auths.name = ?');
                $addAuth->bind_param('iss', $userID, $token, $authName);
                $addAuth->execute();
                getDB()->commit();
                return true;
            }
        } else {
            return false; //What are you doing?!
        }
    }

    private static function checked(bool $ch): string
    {
        if($ch) {
            return ' checked';
        } else {
            return '';
        }
    }

    private static function invalidFeedback(string $describer, string $msg): string {
        return <<<END
      <div id="$describer" class="invalid-feedback">$msg</div>
END;

    }

    private static function getUser(): ?UserHelper {
        return null;
    }

    public static function getTheme(): string {
        $user = self::getUser();
        if ($user == null) {
            return '/bootstrap/css/bootstrap.css';
        } else {
            return '/bootstrap/css/bootstrap.css';
            //TODO user-specific information
        }
    }

}