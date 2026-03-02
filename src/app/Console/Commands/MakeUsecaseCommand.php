<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class MakeUsecaseCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:usecase
                            {name : The use case class name or path (e.g. User\Auth\LoginUser or Catalog\GetItems)}';

    /**
     * @var string
     */
    protected $description = 'Create a new use case class in the Application layer';

    public function handle(): int
    {
        $name = $this->argument('name');
        $name = str_replace('/', '\\', $name);

        if (!preg_match('#^[a-zA-Z0-9_\\\\]+$#', $name)) {
            $this->error('Invalid use case name. Use only letters, numbers, backslashes and underscores.');

            return self::FAILURE;
        }

        $segments = explode('\\', trim($name, '\\'));
        $className = array_pop($segments);
        $subPath = implode(DIRECTORY_SEPARATOR, $segments);

        $applicationPath = app_path('Application');
        $targetDir = $subPath !== ''
            ? $applicationPath . DIRECTORY_SEPARATOR . $subPath
            : $applicationPath;
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            $this->error("Use case already exists: {$filePath}");

            return self::FAILURE;
        }

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $this->error("Could not create directory: {$targetDir}");

                return self::FAILURE;
            }
        }

        $namespace = $subPath !== ''
            ? 'App\\Application\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $subPath)
            : 'App\\Application';

        $stub = $this->getStub($namespace, $className);
        file_put_contents($filePath, $stub);

        $this->info("Use case created: {$filePath}");

        return self::SUCCESS;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

final readonly class {$className}
{
    public function run(): void
    {
        //
    }
}

PHP;
    }
}
