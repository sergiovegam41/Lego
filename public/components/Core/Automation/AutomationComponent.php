<?php

namespace Components\Core\Automation;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class AutomationComponent extends CoreComponent
{

    protected $config;

    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = ["components/Core/Automation/automation.css"];

    public function __construct( $config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {

        $this->JS_PATHS_WITH_ARG[] = [

            new ScriptCoreDTO("components/Core/Automation/automation.js", [ ])
    
        ];
       
       
        return <<<HTML

        <iframe src="https://n8n.lego.ondeploy.space" style="width:200dvh;height:95dvh;border:none;"></iframe>

        HTML;


    }
}
