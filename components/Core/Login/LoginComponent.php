<?php

namespace Components\Core\Login;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class LoginComponent extends CoreComponent
{


    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = ["./login.css"];

    public function __construct() {}

    protected function component(): string
    {

        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./login.js", [ ])
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
                <link rel="stylesheet" href="./assets/css/core/base.css">
                
                <!-- Inline script to prevent flash of wrong theme -->
                <script>
                (function() {
                    // Get theme immediately from localStorage
                    const STORAGE_KEY = 'lego_theme';
                    let savedTheme = localStorage.getItem(STORAGE_KEY);
                    
                    // If no saved theme, check system preference and default to dark
                    if (!savedTheme) {
                        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                        savedTheme = prefersDark ? 'dark' : 'dark'; // Default to dark always
                    }
                    
                    // Apply theme immediately to prevent flash
                    if (savedTheme === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme = 'dark';
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.style.colorScheme = 'light';
                    }
                })();
                </script>
             
                </head>
                <body class="min-h-screen flex items-center justify-center relative transition-colors durationtheme-toggle-300">
                <!-- Background pattern with crosses -->

                <svg class="absolute inset-0" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                    <pattern id="smallCross" width="35" height="35" patternUnits="userSpaceOnUse">
                        <path d="M13,9.5 L13,16.5 M9.5,13 L16.5,13" stroke="var(--color-gray-500)" stroke-width="2" class="opacity-[.15] "></path>
                    </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#smallCross)"></rect>
                </svg>
                <!-- Main container -->
                <div class="flex w-full max-w-4xl overflow-hidden rounded-xl shadow-lg relative z-10 main-container">
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

                    </div>
                    
                    <!-- Right side - Login form -->
                    <div class="w-full p-6 md:w-1/2 md:p-8 lg:p-10 relative transition-colors duration-300" style="background-color: var(--bg-sidebar);">
                        <!-- Theme Toggle Button -->
                        <button id="theme-toggle" class="absolute top-4 right-4 p-2 rounded-full bg-transparent hover:bg-opacity-10 transition-colors duration-200" style="z-index: 9999; pointer-events: auto; cursor: pointer;">
                            <!-- Sun icon (visible in dark mode) -->
                            <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 hidden dark:block text-gray-300">
                                <circle cx="12" cy="12" r="5"/>
                                <line x1="12" y1="1" x2="12" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="23"/>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                <line x1="1" y1="12" x2="3" y2="12"/>
                                <line x1="21" y1="12" x2="23" y2="12"/>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                            <!-- Moon icon (visible in light mode) -->
                            <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 block dark:hidden text-gray-700">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>
                        
                    <div class="max-w-sm mx-auto">
                        <h2 class="text-xl font-bold mb-2" style="color: var(--text-primary);">Welcome back</h2>
                        <p class="text-sm mb-6" style="color: var(--text-secondary);">Please enter your credentials to sign in</p>
                        
                        <form>
                        <div class="space-y-4">
                            <div>
                            <label for="email" class="block text-sm font-medium mb-1" style="color: var(--text-primary);">
                                Email address
                            </label>
                            <input
                                id="email"
                                type="email"
                                placeholder="user@example.com"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors duration-200" style="border-color: var(--border-light); background-color: var(--bg-surface); color: var(--text-primary); --tw-ring-color: var(--accent-primary);"
                            />
                            </div>
                            
                            <div>
                            <div class="flex justify-between items-center mb-1">
                                <label for="password" class="block text-sm font-medium" style="color: var(--text-primary);">
                                Password
                                </label>
                                <a href="#" class="text-sm hover:opacity-80" style="color: var(--accent-primary);">
                                Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <input
                                id="password"
                                type="password"
                                placeholder="••••••"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors duration-200" style="border-color: var(--border-light); background-color: var(--bg-surface); color: var(--text-primary); --tw-ring-color: var(--accent-primary);"
                                />
                                <button
                                type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" style="color: var(--text-secondary);">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 hidden" style="color: var(--text-secondary);">
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
                            <label for="remember-me" class="ml-2 block text-sm" style="color: var(--text-primary);">
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
                        
                        <div class="mt-6 text-center text-sm" style="color: var(--text-secondary);">
                        Don't have an account?
                        <a href="#" class="hover:opacity-80" style="color: var(--accent-primary);">
                            Contact administrator
                        </a>
                        </div>
                    </div>
                    </div>
                </div>

             

                <script type="module" src="./assets/js/core/base-lego-login.js" defer></script>
                <script type="module" src="./assets/js/core/modules/theme/theme-manager.js"></script>
                


                </body>
                </html>

      

        HTML;


    }
}
