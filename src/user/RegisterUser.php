<?php declare(strict_types=1);

namespace user;
include 'auth/IAuthMethod.php';

use elements\bootstrap\HOrdered;
use elements\ErrorTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
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

    #[ArrayShape(['id' => "int", 'user' => "\pageUtils\UserHelper"])]
    private ?array $userData;
    private ?IAuthMethod $authMethod = null;

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
            1 => $this->register(),
            default => Routes::NOT_FOUND,
        };
    }

    private function loginMainScreen(): ?object {

        $R = new Router(1);

        $R->all(Router::$EMPTY)->action(function () {return $this->welcomeNewPeople();});
        $R->get('~^auth\\/[^\\/]+$~')->action(function () {return $this->oauthCallback(); });

        return $R->run(getCurrentPage());
    }

    private function oauthCallback(): IElement
    {

        $auth = getUrlArray()[2];
        $auth = match ($auth) {
            'gh' => $this->gitHubAuth,
            default => false,
        };
        try {
            $ret = $auth->authCallback();

        } catch (IllegalStateException) {

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
                return AccountPage::getAccountPage();
            } else {
                $this->userData = $userData;
                $this->step = 1;
                header('/u');
                $this->authMethod = $auth;
                return $this->register();
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
        $hstack = new HOrdered();
        $listElement->addElement(new LiteralElement(<<<END
<h1>You are not logged in, please log in!</h1>
END
));

        $hstack->addElement(new LiteralElement(<<<END
<h4>Please select one method to log-in!</h4>
To register, first log-in with an account,<br>and follow the form.
END

));

        $hstack->addElement($this->gitHubAuth->getAuthButton(), 5);


        $listElement->addElement($hstack);
        $listElement->setClass('content');
        return $listElement;
    }

    private function register(): IElement
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->tryRegisterUser();
        } else {
            return $this->displayRegister();
        }
    }

    private function displayRegister(): IElement
    {
        $content = new SimpleList();

        $content->addElement(new LiteralElement(<<<END
<h1>Register</h1>
<h4>Please verify/check the form below, and press <bold>register</bold> to finish your account</h4>
END));

        $form = $this->userData['user']->getForm('/u/register', 'Register');

        $content->addElement($form);
        return $content;
    }

    private function tryRegisterUser(): IElement {
        if ($this->userData['user']->register($this->userData['id'], $this->authMethod->getName(), $this->authMethod->getToken())) {
            header('/u');
            $_SESSION['user'] = serialize($this->userData);
            return AccountPage::getAccountPage();
        } else {
            return $this->displayRegister();
        }
    }
}