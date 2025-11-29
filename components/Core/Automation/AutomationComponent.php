<?php

namespace Components\Core\Automation;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Attributes\ApiComponent;

#[ApiComponent('/automation', methods: ['GET'])]
class AutomationComponent extends CoreComponent
{


    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = ["components/Core/Automation/automation.css"];

    /**
     * Constructor vacío intencional.
     * 
     * RAZÓN ARQUITECTÓNICA:
     * AutomationComponent es un wrapper para iframe externo (n8n).
     * Es un entry point del router que no requiere configuración.
     * TODO: Considerar hacer la URL del iframe configurable via constructor.
     */
    public function __construct() {}

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
