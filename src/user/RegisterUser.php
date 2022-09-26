<?php declare(strict_types=1);

namespace user;
include 'auth/IAuthMethod.php';

use elements\bootstrap\HOrdered;
use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use i18n\Translatable;
use JetBrains\PhpStorm\ArrayShape;
use pageUtils\UserHelper;
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
        $this->gitHubAuth = new GitHub('/register/auth/gh');
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

    /**
     * @return IElement|Routes stuff
     */
    private function oauthCallback(): IElement|Routes
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
            $listElement->addElement(new AlertTag(new Translatable("oauth_mismatch")));
            return $this->welcomeNewPeople($listElement);
        }
        if ($ret === true) {
            $userData = $auth->getVerifiedUserData();
            $isUserQuery = getDB()->prepare("SELECT uA.userID from auths join userAccounts uA on auths.id = uA.authID where auths.name = ? and uA.platformUserID = ?");
            $authName = $auth->getName();
            $isUserQuery->bind_param('si', $authName, $userData['id']);
            $isUserQuery->execute();

            $res = $isUserQuery->get_result();
            $isUserQuery->close();


            if($res->num_rows == 1) { //User is already connected to this OAuth
                session_regenerate_id();
                $_SESSION['user'] = serialize($res->fetch_array()['userID']);
                $_SESSION['authMode'] = $authName;

                getDB()->begin_transaction();
                $q = getDB()->prepare('UPDATE userAccounts join auths a on a.id = userAccounts.authID SET userAccounts.token = ? where userID = ? && a.name = ?;');
                $token = $auth->getToken();
                $q->bind_param('sis', $token, UserHelper::getCurrentUser()->userID, $authName);
                $q->execute();
                getDB()->commit();

                redirect('/u');
                return AccountPage::getAccountPage(UserHelper::getCurrentUser());
            } else {
                $this->userData = $userData;
                $this->step = 1;
                header('/register');
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
        $listElement->addElement(new Translatable("not_logged_in"));

        $hstack->addElement(new Translatable("select_method"));

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

        $content->addElement(new Translatable("check_form"));

        $form = $this->userData['user']->getForm('/register', 'Register');

        $content->addElement($form);
        return $content;
    }

    private function tryRegisterUser(): IElement {
        if ($this->userData['user']->register($this->userData['id'], $this->authMethod->getName(), $this->authMethod->getToken())) {
            header('/u');
            return AccountPage::getAccountPage();
        } else {
            return $this->displayRegister();
        }
    }
}