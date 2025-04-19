<?php

namespace Views\Core\Login;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class LoginComponent extends CoreComponent
{

    protected $config;

    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = ["components/Core/Login/login.css"];

    public function __construct( $config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {

        $this->JS_PATHS_WITH_ARG[] = [

            new ScriptCoreDTO("components/Core/Login/login.js", [ ])
    
        ];
      
       
        return <<<HTML

            <!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>LEGO Framework</title>
                <script src="https://cdn.tailwindcss.com"></script>


                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Login</title> 
                <link rel="shortcut icon" href="./assets/favicon.ico" type="image/x-icon">
             
                </head>
                <body class="min-h-screen flex items-center justify-center bg-gray-50 relative">
                <!-- Background pattern with crosses -->

                <svg class="absolute inset-0" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                    <pattern id="smallCross" width="35" height="35" patternUnits="userSpaceOnUse">
                        <path d="M13,9.5 L13,16.5 M9.5,13 L16.5,13" stroke="#60A5FA" stroke-width="2" opacity=".15"></path>
                    </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#smallCross)"></rect>
                </svg>
                <!-- Main container -->
                <div class="flex w-full max-w-5xl overflow-hidden rounded-xl shadow-lg relative z-10 main-container">
                    <!-- Left side - Blue panel -->
                    <div class="relative hidden w-1/2 blue-gradient bg-blue-500 p-10 text-white md:block">
                    <div class="grid-pattern"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold">LEGO Framework</h1>
                        <h2 class="text-xl opacity-90 mb-16">Administrative System</h2>
                        
                        <p class="my-12 text-lg opacity-90">
                        A powerful and flexible framework for building scalable administrative systems with an intuitive user interface.
                        </p>
                        
                        <div class="space-y-6 mt-12">
                        <div class="flex items-start gap-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            </div>
                            <div>
                            <h3 class="font-medium">Modular Architecture</h3>
                            <p class="text-sm opacity-80">Build and customize as you need</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            </div>
                            <div>
                            <h3 class="font-medium">Enterprise-grade Security</h3>
                            <p class="text-sm opacity-80">Advanced protection for your data</p>
                            </div>
                        </div>
                        </div>
                        
                    </div>
                    <div class="absolute bottom-10 left-10 text-sm opacity-70">
                    © 2025s LEGO Framework. All rights reserved.
                    </div>
                    </div>
                    
                    <!-- Right side - Login form -->
                    <div class="w-full p-8 bg-white md:w-1/2 md:p-10 lg:p-12">
                    <div class="max-w-md mx-auto">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome back</h2>
                        <p class="text-gray-600 mb-8">Please enter your credentials to sign in</p>
                        
                        <form>
                        <div class="space-y-6">
                            <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email address
                            </label>
                            <input
                                id="email"
                                type="email"
                                placeholder="pedro@example.com"
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            </div>
                            
                            <div>
                            <div class="flex justify-between items-center mb-1">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                Password
                                </label>
                                <a href="#" class="text-sm text-blue-500 hover:text-blue-600">
                                Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <input
                                id="password"
                                type="password"
                                placeholder="••••••"
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button
                                type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 hidden">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                                </button>
                            </div>
                            </div>
                            
                            <div class="flex items-center">
                            <input
                                id="remember-me"
                                type="checkbox"
                                class="h-4 w-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                            </div>
                            
                            <button
                            id="submit-button"
                            class="w-full flex justify-center items-center gap-2 py-2.5 px-4 bg-blue-400 hover:bg-blue-500 text-white font-medium rounded-md transition-colors" >
                            Sign in
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            </button>
                        </div>
                        </form>
                        
                        <div class="mt-6 text-center text-sm text-gray-600">
                        Don't have an account?
                        <a href="#" class="text-blue-500 hover:text-blue-600">
                            Contact administrator
                        </a>
                        </div>
                    </div>
                    </div>
                </div>

             

                <script type="module" src="./assets/js/core/base-lego-login.js" defer></script>


                </body>
                </html>

      

        HTML;


    }
}
