<?php declare(strict_types=1);

namespace user;
include 'auth/IAuthMethod.php';

use elements\ErrorTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use elements\SimpleTable;
use elements\SubmitConstantButton;
use elements\TableRow;
use routing\Routes;
use function user\auth\randomStr;

class RegisterUser
{

    private int $step = 0;
    private string $state;

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
        $this->state = randomStr();
    }

    public function continue(): ?object
    {

        return match ($this->step) {
            0 => $this->loginMainScreen(),
            1 => $this->step1(),
            default => Routes::NOT_FOUND,
        };
    }

    private function loginMainScreen(): IElement {
        if (isset($_GET['code'])) {
            $state = $_GET['state'] ?? '';
            if ($state == $this->state) {
                //State matches, oauth can continue
            } else {
                //state mismatch, timeout or third-party
                http_response_code(498); //Note somehow the mismatched token
                $listElement = new SimpleList();
                $listElement->addElement(new ErrorTag(new LiteralElement(<<<END
Mismatched oauth state, probably you waited for too long, please try again
END)));
                return $this->welcomeNewPeople($listElement);
            }

        } else {
            return $this->welcomeNewPeople();
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

        $ghLogin = new LiteralElement(<<<END
<div style="width: 224px;">
    <img alt="GH logo" src="/assets/GitHub-Mark-64px.png" width="64" height="64">
    <img src="/assets/GitHub_Logo.png" alt="GitHub" width="150">
</div>
END
);
        $params = array();
        $params['client_id'] = "154d6d89f8cf32b9275e"; //GH OAuth Client ID
        $params['redirect_uri'] = "https://emotes.kosmx.dev/u";
        $params['state'] = $this->state;
        $params['scope'] = "read:user";#, user:email";
        $tableRow->addElement(new SubmitConstantButton($ghLogin, $params, "get", "https://github.com/login/oauth/authorize"));

        $table = new SimpleTable();
        $table->addRow($tableRow);
        $table->class = 'loginTable';
        $listElement->addElement($table);
        $listElement->setClass('content');
        return $listElement;
    }

    private function step1(): IElement
    {
        return new LiteralElement("hehe");
    }
}