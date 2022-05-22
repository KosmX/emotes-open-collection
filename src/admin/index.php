<?php declare(strict_types=1);

namespace admin;

use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use pageUtils\UserHelper;
use routing\Method;
use routing\Router;
use routing\Routes;

/**
 * If user is not logged in OR has no privileges, it will return 403.
 * No more verification is required in sub-methods
 */
class index
{
    public static function index(): ?object
    {
        if (UserHelper::getCurrentUser() != null && UserHelper::getCurrentUser()->privileges >= 2) {

            $R = new Router(1);

            $R->all('~^t\\/[^\\/]+(\\/)?$~')->action(function () {
                return self::editTable(getUrlArray()[2]);
            });

            $R->get(Router::$EMPTY)->action(function () {return self::adminMenu();});

            return $R->run(getCurrentPage());

        }
        return Routes::FORBIDDEN;
    }

    private static function editTable(string $table): IElement|Router {

        $list = new SimpleList();
        $list->addElement(new LiteralElement("<h1>Edit: $table</h1>"));

        $table = new Table($table);


        if (Method::POST->isActive()) {
            $table->post();
        }

        $list->addElement($table);

        return $list;


    }

    private static function adminMenu(): IElement
    {

        $list = new SimpleList();
        $q = getDB()->prepare("SHOW TABLES");
        $q->execute();

        $tables = '';
        foreach ($q->get_result() as $item) {
            foreach ($item as $table) {
                $tables .= <<<END
<a class="btn btn-primary" href="/admin/t/$table" role="button">$table</a>

END;
            }
        }

        $list->addElement(new LiteralElement(<<<END
<h1>Tables:</h1>
<hr>
$tables
END));

        return $list;
    }
}