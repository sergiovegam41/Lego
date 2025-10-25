<?php

namespace Components\App\TestButton;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class TestButtonComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./test-button.css"];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./test-button.js", [])
        ];

        return <<<HTML
        <div class="test-button">
            <h2>TestButton Component</h2>
            <p>This is a generated Lego component.</p>
        </div>
        HTML;
    }
}