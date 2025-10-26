<?php

namespace Core\Commands;

use Core\Controller\CoreController;

/**
 * MapRoutesCommand - Map and register application routes
 */
class MapRoutesCommand extends CoreCommand
{
    protected string $name = 'map:routes';
    protected string $description = 'Map and register all application routes';
    protected string $signature = 'map:routes [--output=routeMap.json]';

    /**
     * Execute route mapping command
     */
    public function execute(): bool
    {
        $this->info("Mapping routes...\n");

        try {
            // Get routes from CoreController
            $routes = CoreController::mapControllers();

            // Determine output file
            $outputFile = $this->option('output', 'routeMap.json');
            $fullPath = __DIR__ . '/../../' . $outputFile;

            // Save routes to JSON file
            $jsonOutput = json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if (file_put_contents($fullPath, $jsonOutput) === false) {
                $this->error("Failed to write routes to: {$fullPath}");
                return false;
            }

            // Display results
            $this->displayRouteResults($routes, $fullPath);

            return true;

        } catch (\Exception $e) {
            $this->error("Route mapping failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Display route mapping results
     */
    private function displayRouteResults(array $routes, string $outputFile): void
    {
        $routeCount = count($routes);

        if ($routeCount === 0) {
            $this->warning("No routes found to map");
            $this->info("Make sure your controllers have the ROUTE constant defined");
            return;
        }

        $this->success("Found {$routeCount} route(s):");
        $this->line("");

        foreach ($routes as $route => $controller) {
            $this->line("  📍 /{$route} → {$controller}");
        }

        $this->line("");
        $this->success("Routes mapped successfully to: {$outputFile}");
    }
}