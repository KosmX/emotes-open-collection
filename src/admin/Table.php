<?php

namespace admin;

use elements\IElement;
use elements\LiteralElement;
use routing\Routes;

/**
 * mysqli dynamic table helper
 */
class Table implements IElement
{
    private string $table;
    private array $cols;

    public function __construct(string $table)
    {
        $this->table = $table;
        $q = getDB()->query("SHOW COLUMNS FROM $table;");
        $cols = array();
        foreach ($q as $item) {
            $type = $item['Type'];
            if (str_contains($type, 'int')) {
                $t = 'i';
            } else if (str_contains($type, 'char')) {
                $t = 's';
            } else {
                $t = 'b';
            }
            //var_dump($item);

            $cols[] = array(
                'name' => $item['Field'],
                'type' => $t,
                'null' => $item['Null'] != 'NO',
                'key' => $item['Key'] == 'PRI'
            );
        }
        $this->cols = $cols;
    }

    function post(): bool|Routes {
        if (isset($_POST['keys'])) {
            $keys = json_decode($_POST['keys'], true);
            getDB()->begin_transaction();

            //Somewhere the params must refer.
            $tmp = array();

            $select = 'TRUE';
            $selectParamList = array();
            $selectParamTypes = '';
            foreach ($keys as $key=>$value) {
                $select .= " && $this->table.$key = ?";
                $tmp[] = $value;
                $selectParamList[] = &$tmp[sizeof($tmp) - 1];

                $selectParamTypes .= 'i';
            }

            if (isset($_POST['delete'])) {
                $q = getDB()->prepare("DELETE FROM $this->table where $select;");

                $paramArray = array_merge([$selectParamTypes], $selectParamList);
            }
            else {
                $sets = '';
                $paramTypes = '';
                $params = array();

                foreach ($this->cols as $col) {
                    if ($col['type'] == 'b') continue;
                    $set = "$col[name] = ?";
                    $paramTypes .= $col['type'];

                    $value = $_POST[$col['name']]??0; //sometimes I hate PHP
                    //var_dump($value);
                    if ($col['type'] == 'i') {
                        $value = (int)$value;
                    }
                    $tmp[] = $value;

                    $params[] = &$tmp[sizeof($tmp) - 1];

                    if ($sets == '') {
                        $sets = $set;
                    } else {
                        $sets .= ", $set";
                    }
                }

                //var_dump($params);
                //var_dump($sets);

                $q = getDB()->prepare("UPDATE $this->table SET $sets where $select;");

                $paramArray = array_merge([$paramTypes.$selectParamTypes], $params, $selectParamList);
            }
            //var_dump($paramArray);
            call_user_func_array([$q, 'bind_param'], $paramArray);
            $q->execute();
            getDB()->commit();
        }
        return Routes::INTERNAL_ERROR;
    }

    function build(): string
    {
        $page = (int)($_GET['p']?? 0);
        $page *= 64;

        $q = getDB()->prepare("SELECT * FROM $this->table LIMIT 64 OFFSET ?;");
        $q->bind_param('i', $page); //Make it usable
        $q->execute();
        $r = $q->get_result();

        $row = '';
        foreach ($this->cols as $col) {
            $row .= "<th>{$col['name']}</th>";
        }
        $rows = $row;

        foreach ($r as $row) {
            $rowStr = '';
            $keys = array();

            foreach ($this->cols as $item) {
                if ($item['type'] == 'b') continue; //We can not edit blobs
                $name = $item['name'];
                $val = $row[$name];
                $rowStr .= <<<END
<td><input type="text" value="$val" name="$name" class="form-control"></td>
END;

                if ($item['key']) {
                    $keys[$name] = $val;
                }
            }
            $keys = htmlspecialchars(json_encode($keys));
            $rows .= <<<END
<tr>
    <form method="post">
        <input type="hidden" value="$keys" name="keys">
        $rowStr
        <td><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i></button></td>
    </form>
    <td>
        <form method="post">
            <input type="hidden" name = "keys" value="$keys">
            <input type="hidden" name = "delete" value="1">
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button>
        </form>
    </td>
</tr>
END;

        }

        return <<<END
<table class="table">
$rows
</table>
END;}
}