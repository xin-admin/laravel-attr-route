<?php

namespace Xin\AttrRoute;

use Illuminate\Support\Facades\Log;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Xin\AttrRoute\Contracts\AttrRoute;

class AttrRouteService implements AttrRoute
{

    /**
     * Scan controllers from the given paths and register attribute routes
     *
     * @param string|array $path Controller directories (multiple allowed)
     * @return void
     */
    public function register(string|array $path): void
    {
        $paths = is_array($path) ? $path : [$path];

        foreach ($paths as $p) {
            $this->registerFromPath($p);
        }
    }

    /**
     * Register routes from the given path
     */
    private function registerFromPath(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $finder = new Finder();
        $finder->files()
            ->in($path)
            ->name('*Controller.php');

        foreach ($finder as $controller) {
            $className = $this->getClassNameFromFile(
                $controller->getRealPath(),
                $controller->getPath()
            );
            if ($className && class_exists($className)) {
                try {
                    RouteRegisterService::register($className);
                } catch (ReflectionException $e) {
                    Log::warning("AttrRoute: failed to register routes [{$className}]: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Resolve the class name from the file path and its directory
     */
    private function getClassNameFromFile(string $filePath, string $fileDir): ?string
    {
        // Read the file content to get the namespace
        $content = file_get_contents($filePath);

        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            // No namespace, try guessing from the path
            return $this->guessClassNameFromPath($filePath, $fileDir);
        }

        $namespace = trim($namespaceMatch[1]);
        $className = basename($filePath, '.php');

        return $namespace . '\\' . $className;
    }

    /**
     * Guess the class name from the path when the file has no namespace
     */
    private function guessClassNameFromPath(string $filePath, string $fileDir): ?string
    {
        // Get the path relative to the project root
        $basePath = base_path();
        $relativePath = str_replace($basePath, '', $fileDir);

        // Convert the path into a namespace
        $namespace = str_replace('/', '\\', ltrim($relativePath, '/'));

        // Remove leading backslashes and normalize path separators
        $namespace = trim($namespace, '\\');

        $className = basename($filePath, '.php');

        // If the namespace is empty, return the class name directly
        if (empty($namespace)) {
            return $className;
        }

        return $namespace . '\\' . $className;
    }
}
