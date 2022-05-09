<?php declare(strict_types=1);

namespace elements\bootstrap\navbar;
interface IEntry {
    function toStr(): string;
    function getName(): string;
    function setCurrent(): void;
}

