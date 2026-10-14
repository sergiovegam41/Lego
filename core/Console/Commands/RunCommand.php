<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Lego\Audit\ActionLog;
use Lego\Auth\User;
use Lego\Errors\HttpError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs an action from the command line as a real, active user: same
 * permissions, validation, confirmation and audit as the UI and the API.
 *
 *     php lego run clients_create --as=ana@example.com --set name="Florería Lima" --set email=ventas@lima.pe
 *     php lego run clients_delete --as=ana@example.com --set id=12 --yes
 */
#[AsCommand(name: 'run', description: 'Ejecuta una acción como un usuario')]
final class RunCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'Nombre de la acción, p. ej. clients_create')
            ->addOption('as', null, InputOption::VALUE_REQUIRED, 'Correo del usuario que ejecuta la acción')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'Entrada como objeto JSON')
            ->addOption('set', 's', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Campo de entrada clave=valor (repetible)')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Confirma las acciones que piden confirmación');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getOption('as');
        if (!is_string($email) || $email === '') {
            $output->writeln('<error>Indica el usuario con --as=correo.</error>');

            return Command::INVALID;
        }

        $user = User::query()->with('role')->where('email', User::normalizeEmail($email))->where('is_active', true)->first();
        if ($user === null) {
            $output->writeln('<error>No existe un usuario activo con ese correo.</error>');

            return Command::FAILURE;
        }

        $data = $this->input($input);
        if ($data === null) {
            $output->writeln('<error>--input debe ser un objeto JSON.</error>');

            return Command::INVALID;
        }

        $action = (string) $input->getArgument('action');
        $identity = $user->toIdentity();
        $traceId = bin2hex(random_bytes(8));

        try {
            $result = $this->app->executor->execute($action, $data, $identity, ActionLog::VIA_CLI, $traceId);
            if ($result->needsConfirmation()) {
                if (!$input->getOption('yes')) {
                    $output->writeln('<comment>' . $result->confirmation['message'] . '</comment>');
                    $output->writeln('Repite el comando con --yes para confirmar.');

                    return 2;
                }
                $result = $this->app->executor->execute(
                    $action,
                    [...$data, 'confirmation_token' => $result->confirmation['token']],
                    $identity,
                    ActionLog::VIA_CLI,
                    $traceId,
                );
            }
        } catch (HttpError $e) {
            $output->writeln((string) json_encode(
                ['status' => 'error', 'error' => ['code' => $e->errorCode(), 'message' => $e->getMessage(), ...$e->details()], 'trace_id' => $traceId],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return Command::FAILURE;
        }

        $output->writeln((string) json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }

    /** @return array<string, mixed>|null */
    private function input(InputInterface $input): ?array
    {
        $data = [];
        $json = $input->getOption('input');
        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);
            if (!is_array($decoded) || array_is_list($decoded) && $decoded !== []) {
                return null;
            }
            $data = $decoded;
        }

        foreach ((array) $input->getOption('set') as $pair) {
            [$key, $value] = array_pad(explode('=', (string) $pair, 2), 2, '');
            $data[trim($key)] = $value;
        }

        return $data;
    }
}
