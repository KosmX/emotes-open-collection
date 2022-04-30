<?php declare(strict_types=1);

namespace elements\pageMenu;
interface IEntry {
    function toStr(): string;
    function getName(): string;
    function setCurrent(): void;
}

