<?php declare(strict_types=1);
namespace routing;
use elements\IElement;

class Router
{
    private array $map = array();

    public function get(string $regex): Route
    {
        return $this->addElement($regex, Method::GET);
    }
    public function post(string $regex): Route
    {
        return $this->addElement($regex, Method::POST);
    }
    public function all(string $regex): Route
    {
        return $this->addElement($regex, Method::ALL);
    }

    private function addElement(string $regex, Method $method): Route {

        $route = new Route($regex, $method);
        $this->map[] = $route;

        return $route;
    }

    public function run(string $path): ?object {
        foreach ($this->map as $action) {
            if ($action->test($path)) {
                return $action->exec();
            }
        }
        return Routes::NOT_FOUND;
    }

}

class Route {
    private Method $method;
    private string $regex;
    private object $execute;

    public function __construct(string $regex, Method $method) {
        $this->regex = $regex;
        $this->method = $method;
        $this->execute = function () {
            throw new \http\Exception\InvalidArgumentException("WAT?");
        };
    }

    public function action($func): void {
        $this->execute = $func;
    }

    public function exec(): ?object {
        $execute = $this->execute;
        return $execute();
    }

    public function test(string $path): bool
    {
        return $this->method->isActive() && preg_match($this->regex, $path);
    }
}

enum Method {
    case GET;
    case POST;
    case ALL;

    function isActive(): bool {
        if ($this === Method::POST) {
            return $_SERVER['REQUEST_METHOD'] === 'POST';
        }
        else if ($this === Method::GET) {
            return $_SERVER['REQUEST_METHOD'] === 'GET';
        } else {
            return true;
        }
    }
}
