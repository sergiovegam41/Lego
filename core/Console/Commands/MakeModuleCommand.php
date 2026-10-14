<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Illuminate\Support\Str;
use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a working module: a Record with one field and its migration. After
 * `php lego migrate` it already has list, create, edit and delete screens,
 * JSON actions and CLI access. Add fields in fields() and in the migration.
 */
#[AsCommand(name: 'make:module', description: 'Crea un módulo nuevo con su modelo y su migración')]
final class MakeModuleCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Nombre en PascalCase y plural, p. ej. Clients')
            ->addOption('label', 'l', InputOption::VALUE_REQUIRED, 'Nombre visible en el menú, p. ej. Clientes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) !== 1) {
            $output->writeln('<error>El nombre debe estar en PascalCase, p. ej. Clients o SalesOrders.</error>');

            return Command::INVALID;
        }

        $folder = $this->app->modulesPath . DIRECTORY_SEPARATOR . $name;
        if (file_exists($folder)) {
            $output->writeln("<error>Ya existe la carpeta {$folder}.</error>");

            return Command::FAILURE;
        }

        $class = Str::singular($name);
        $class = $class === $name ? $name . 'Record' : $class;
        $table = Str::snake($name);
        $label = (string) ($input->getOption('label') ?? Str::headline($name));
        $migration = date('Y_m_d_His') . "_create_{$table}_table.php";

        mkdir($folder . DIRECTORY_SEPARATOR . 'migrations', 0775, true);
        file_put_contents("{$folder}/{$class}.php", self::recordTemplate($name, $class, $table, $label));
        file_put_contents("{$folder}/migrations/{$migration}", self::migrationTemplate($table));

        $output->writeln("<info>Módulo creado:</info> modules/{$name}/{$class}.php");
        $output->writeln("<info>Migración:</info>     modules/{$name}/migrations/{$migration}");
        $output->writeln('Siguiente paso: php lego migrate');

        return Command::SUCCESS;
    }

    private static function recordTemplate(string $folder, string $class, string $table, string $label): string
    {
        $label = addslashes($label);

        return <<<PHP
            <?php

            declare(strict_types=1);

            namespace Modules\\{$folder};

            use Lego\\Data\\Fields\\Text;
            use Lego\\Data\\Record;
            use Lego\\Module\\Module;

            #[Module(label: '{$label}')]
            final class {$class} extends Record
            {
                protected \$table = '{$table}';

                public static function fields(): array
                {
                    return [
                        Text::make('name', 'Nombre')->required()->max(120)->searchable()->sortable(),
                    ];
                }
            }

            PHP;
    }

    private static function migrationTemplate(string $table): string
    {
        return <<<PHP
            <?php

            declare(strict_types=1);

            use Illuminate\\Database\\Connection;
            use Illuminate\\Database\\Schema\\Blueprint;
            use Illuminate\\Database\\Schema\\Builder;
            use Lego\\Database\\Migration;

            return new class extends Migration {
                public function up(Builder \$schema, Connection \$db): void
                {
                    \$schema->create('{$table}', static function (Blueprint \$table): void {
                        \$table->id();
                        \$table->string('name', 120);
                        \$table->timestampsTz();
                    });
                }

                public function down(Builder \$schema, Connection \$db): void
                {
                    \$schema->drop('{$table}');
                }
            };

            PHP;
    }
}
