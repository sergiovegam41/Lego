<?php

declare(strict_types=1);

namespace Lego\View;

use ReflectionClass;
use Stringable;

/**
 * Base class of every Lego component: a PHP class with typed constructor
 * parameters that returns HTML.
 *
 *     final class Card extends Component
 *     {
 *         protected array $styles = ['card.css'];
 *
 *         public function __construct(private string $title) {}
 *
 *         protected function render(): string
 *         {
 *             return '<article class="card">' . self::e($this->title) . '</article>';
 *         }
 *     }
 *
 * Asset paths are relative to the component's own folder. Components can be
 * nested inside heredocs with {$child}: rendering a child registers its assets
 * in the same page.
 */
abstract class Component implements Stringable
{
    /** @var list<string> Stylesheets relative to the component folder. */
    protected array $styles = [];

    /** @var list<string> ES modules relative to the component folder. */
    protected array $scripts = [];

    abstract protected function render(): string;

    final public function toHtml(): string
    {
        $folder = dirname((string) (new ReflectionClass($this))->getFileName());
        foreach ($this->styles as $path) {
            Assets::style($folder . DIRECTORY_SEPARATOR . $path);
        }
        foreach ($this->scripts as $path) {
            Assets::script($folder . DIRECTORY_SEPARATOR . $path);
        }

        return $this->render();
    }

    final public function __toString(): string
    {
        return $this->toHtml();
    }

    /** Escapes a value for HTML text and attribute contexts. */
    final protected static function e(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
