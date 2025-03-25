<?php

namespace Views\Core\Login;

use Core\Components\CoreComponent\CoreComponent;


class LoginComponent extends CoreComponent
{


    protected $config;

    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = [];

    public function __construct( $config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        return <<<HTML


        <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Login</title> 
                <link rel="shortcut icon" href="./assets/favicon.ico" type="image/x-icon">


                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <meta name="description" content="Lovable Generated Project" />
                <meta name="author" content="Lovable" />

                <meta property="og:title" content="Lovable Generated Project" />
                <meta property="og:description" content="Lovable Generated Project" />
                <meta property="og:type" content="website" />
                <meta property="og:image" content="https://lovable.dev/opengraph-image-p98pqg.png" />

                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:site" content="@lovable_dev" />
                <meta name="twitter:image" content="https://lovable.dev/opengraph-image-p98pqg.png" />
                <script type="module" crossorigin src="components/Core/Login/login.js"></script>
                <link rel="stylesheet" crossorigin href="components/Core/Login/login.css">
                <!-- Solo necesitamos una versión de Babel -->


            </head>
            <body>
                <div id="root"></div>
                <!-- IMPORTANT: DO NOT REMOVE THIS SCRIPT TAG OR THIS VERY COMMENT! -->
                <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>

            
                
                <script type="module" src="./assets/js/core/base-lego-framework.js" defer></script>
                <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>


            </body>

            </html>

            

        <!DOCTYPE html>
            <html lang="en">
            <head>
                
            </head>

            <body>
                <div id="root"></div>
                <!-- IMPORTANT: DO NOT REMOVE THIS SCRIPT TAG OR THIS VERY COMMENT! -->
                <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>

            </body>
            </html>

        HTML;


    }
}
