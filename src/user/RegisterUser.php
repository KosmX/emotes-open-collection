<?php declare(strict_types=1);

namespace user;
include 'auth/IAuthMethod.php';

use elements\ErrorTag;
use elements\form\Input;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleForm;
use elements\SimpleList;
use elements\SimpleTable;
use elements\TableRow;
use JetBrains\PhpStorm\ArrayShape;
use routing\Router;
use routing\Routes;
use user\auth\GitHub;
use user\auth\IAuthMethod;
use user\auth\IllegalStateException;

class RegisterUser
{

    private int $step = 0;
    private GitHub $gitHubAuth;

    #[ArrayShape(['id' => "int", 'name' => "string", 'displayname' => "string", 'email' => "string"])]
    private ?array $userData;

    /*
    public function __serialize(): array
    {
        $a = array();
        $a['step'] = $this->step;
        $a['state'] = $this->state;
        return $a;
    }

    public function __unserialize(array $data): void
    {
        $this->state = $data['state'];
        $this->step= $data['step'];
    }*/

    public function __construct() {
        $this->gitHubAuth = new GitHub();
    }

    public function continue(): ?object
    {

        return match ($this->step) {
            0 => $this->loginMainScreen(),
            1 => $this->step1(),
            default => Routes::NOT_FOUND,
        };
    }

    private function loginMainScreen(): ?object {

        $R = new Router(1);

        $R->all(Router::$EMPTY)->action(function () {return $this->welcomeNewPeople();});
        $R->get('~^auth\\/[^\\/]+$~')->action(function () {return $this->oauthCallback(); });

        return $R->run(getCurrentPage());
    }

    private function oauthCallback() {

        $auth = getUrlArray()[2];
        $auth = match ($auth) {
            'gh' => $this->gitHubAuth,
            default => false,
        };
        try {
            $ret = $auth->authCallback();

        } catch (IllegalStateException $e) {

            //state mismatch, timeout or third-party
            http_response_code(498); //Note somehow the mismatched token
            $listElement = new SimpleList();
            $listElement->addElement(new ErrorTag(new LiteralElement(<<<END
Mismatched oauth state, probably you waited for too long, please try again
END
            )));
            return $this->welcomeNewPeople($listElement);
        }
        if ($ret === true) {
            $userData = $auth->getVerifiedUserData();
            $isUserQuery = getDB()->prepare("SELECT uA.userID from auths join userAccounts uA on auths.id = uA.authID where auths.name = ? and uA.platformUserID = ?");
            $method = $auth->getName();
            $isUserQuery->bind_param('si', $method, $userData['id']);
            $isUserQuery->execute();

            $res = $isUserQuery->get_result();
            $isUserQuery->close();


            if($res->num_rows == 1) {
                //User exists, we are good
            } else {
                $this->userData = $userData;
                $this->step = 1;
                header('/u');
                return $this->step1();
            }

        } else {
            $list = new SimpleList();
            if ($ret instanceof IElement) {
                $list->addElement($ret);
            }
            return $this->welcomeNewPeople($list);
        }
    }

    private function welcomeNewPeople(SimpleList $listElement = new SimpleList()): IElement
    {
        #$listElement = new SimpleList();
        $tableRow = new TableRow();
        $listElement->addElement(new LiteralElement(<<<END
<h1>You are not logged in, please log in!</h1>
END
));

        $tableRow->addElement(new LiteralElement(<<<END
<h2>Please select one to log-in method!</h2>
<h3>To register, first log-in with an account,<br>and follow the form.S</h3>
END

));

        $tableRow->addElement($this->gitHubAuth->getAuthButton());

        $table = new SimpleTable();
        $table->addRow($tableRow);
        $table->class = 'loginTable';
        $listElement->addElement($table);
        $listElement->setClass('content');
        return $listElement;
    }

    private function step1(): IElement
    {
        $content = new SimpleList();

        $content->addElement(new LiteralElement(<<<END
<h1>You are not registered.</h1>
<h2>Please verify/check the form below, and press <bold>register</bold> to finish your account</h2>
<h2>Public field has <span style="color: darkred">red</span> frame</h2>
END));

        $registerForm = new SimpleForm("POST", "/u");
        $registerForm->addElement(new Input(''));



        return $content;
    }
}