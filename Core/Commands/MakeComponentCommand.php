<?php

namespace Core\Commands;

/**
 * MakeComponentCommand - Generate new Lego components
 */
class MakeComponentCommand extends CoreCommand
{
    protected string $name = 'make:component';
    protected string $description = 'Create a new Lego component with PHP, CSS, and JS files';
    protected string $signature = 'make:component {name} [--type=default] [--path=App]';

    /**
     * Execute component creation command
     */
    public function execute(): bool
    {
        $componentName = $this->argument(1);

        if (!$componentName) {
            $this->error("Component name is required");
            $this->line("Usage: php lego make:component ComponentName");
            return false;
        }

        $type = $this->option('type', 'default');
        $path = $this->option('path', 'App');

        $this->info("Creating component: {$componentName}");

        try {
            $this->createComponent($componentName, $type, $path);
            $this->success("Component '{$componentName}' created successfully!");
            $this->displayComponentInfo($componentName, $path);
            return true;

        } catch (\Exception $e) {
            $this->error("Failed to create component: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create component directory structure and files
     */
    private function createComponent(string $name, string $type, string $path): void
    {

        $componentPath = __DIR__ . "/../../components/{$path}/{$name}";

        // Create directory
        if (!mkdir($componentPath, 0755, true) && !is_dir($componentPath)) {
            throw new \Exception("Failed to create component directory: {$componentPath}");
        }

        // Create PHP file
        $this->createPhpFile($componentPath, $name, $path);

        // Create CSS file
        $this->createCssFile($componentPath, $name);

        // Create JS file
        $this->createJsFile($componentPath, $name);
        
    }

    /**
     * Create PHP component file
     */
    private function createPhpFile(string $path, string $name, string $namespace): void
    {
        $phpContent = $this->getPhpTemplate($name, $namespace);
        file_put_contents("{$path}/{$name}Component.php", $phpContent);
    }

    /**
     * Create CSS component file
     */
    private function createCssFile(string $path, string $name): void
    {
        $cssName = $this->kebabCase($name);
        $cssContent = $this->getCssTemplate($cssName);
        file_put_contents("{$path}/{$cssName}.css", $cssContent);
    }

    /**
     * Create JS component file
     */
    private function createJsFile(string $path, string $name): void
    {
        $jsName = $this->kebabCase($name);
        $jsContent = $this->getJsTemplate($name);
        file_put_contents("{$path}/{$jsName}.js", $jsContent);
    }

    /**
     * Get PHP template
     */
    private function getPhpTemplate(string $name, string $namespace): string
    {
        $kebabName = $this->kebabCase($name);

        return <<<PHP
<?php

namespace Components\\{$namespace}\\{$name};

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class {$name}Component extends CoreComponent
{
    protected \$config;
    protected \$CSS_PATHS = ["./{$kebabName}.css"];

    public function __construct(\$config = [])
    {
        \$this->config = \$config;
    }

    protected function component(): string
    {
        \$this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./{$kebabName}.js", [])
        ];

        return <<<HTML
        <div class="{$kebabName}">
            <h2>{$name} Component</h2>
            <p>This is a generated Lego component.</p>
        </div>
        HTML;
    }
}
PHP;
    }

    /**
     * Get CSS template
     */
    private function getCssTemplate(string $kebabName): string
    {
        return <<<CSS
/* {$kebabName} Component Styles */

.{$kebabName} {
    /* Component-specific styles */
    padding: var(--spacing-md, 1rem);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--border-radius, 0.5rem);
    background-color: var(--bg-surface, #ffffff);
}

.{$kebabName} h2 {
    margin-top: 0;
    color: var(--text-primary, #1a202c);
    font-size: var(--font-size-lg, 1.125rem);
}

.{$kebabName} p {
    color: var(--text-secondary, #718096);
    margin-bottom: 0;
}
CSS;
    }

    /**
     * Get JS template
     */
    private function getJsTemplate(string $name): string
    {
        $kebabName = $this->kebabCase($name);

        return <<<JS
// {$name} Component JavaScript

class {$name}Component {
    constructor(element) {
        this.element = element;
        this.init();
    }

    init() {
        console.log('{$name} component initialized');
        // Add your component logic here
    }

    // Add component methods here
}

// Auto-initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.{$kebabName}');
    elements.forEach(element => {
        new {$name}Component(element);
    });
});
JS;
    }

    /**
     * Convert PascalCase to kebab-case
     */
    private function kebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }

    /**
     * Display component information after creation
     */
    private function displayComponentInfo(string $name, string $path): void
    {
        $this->line("");
        $this->info("Component created at:");
        $this->line("  📁 components/{$path}/{$name}/");
        $this->line("    ├── {$name}Component.php");
        $this->line("    ├── " . $this->kebabCase($name) . ".css");
        $this->line("    └── " . $this->kebabCase($name) . ".js");
        $this->line("");
        $this->info("Usage example:");
        $this->line("  \$component = new {$name}Component(['title' => 'My Title']);");
        $this->line("  echo \$component->render();");
        $this->line("");
    }
}