<?php

declare(strict_types=1);

namespace Lego\Action;

use Lego\Audit\ActionLog;
use Lego\Auth\Gate;
use Lego\Auth\Identity;
use Lego\Data\Record;
use Lego\Database\Database;
use Lego\Errors\BusinessRuleViolation;
use Lego\Errors\Forbidden;
use Lego\Errors\HttpError;
use Lego\Errors\NotFound;
use Lego\Errors\ValidationFailed;
use Lego\Module\ModuleRegistry;
use Lego\Support\Clock;
use Throwable;

/**
 * The only way an action runs, whatever the surface (UI, API, CLI, agent):
 *
 *   1. find the action                         -> 404
 *      calls from AI agents pass the AgentPolicy -> 403 agent_not_allowed
 *   2. load the record for record actions      -> 422 without id, 404 if missing
 *   3. authorize through the Gate              -> 403, audited as denied
 *   4. validate the input (prepare)            -> 422 with per-field errors
 *   5. confirm destructive actions in two steps -> 428 with a single-use token
 *   6. run inside a database transaction
 *   7. audit every change in action_log (read-only actions are not logged)
 */
final class ActionExecutor
{
    public const CONFIRMATION_TTL_SECONDS = 300;

    /** Keys that steer the executor and never reach the action. */
    public const CONTROL_KEYS = ['confirmation_token', '_csrf'];

    public function __construct(
        private readonly ActionRegistry $actions,
        private readonly ModuleRegistry $modules,
        private readonly Gate $gate,
        private readonly ActionLog $log,
        private readonly Database $db,
        private readonly Clock $clock,
        private readonly ?AgentPolicy $agents = null,
    ) {
    }

    /** @param array<string, mixed> $input */
    public function execute(
        string $name,
        array $input,
        Identity $user,
        string $via,
        string $traceId = '',
        ?string $ip = null,
    ): ActionResult {
        $action = $this->actions->get($name) ?? throw new NotFound('La acción no existe.');
        $token = $input['confirmation_token'] ?? null;
        $input = array_diff_key($input, array_flip(self::CONTROL_KEYS));

        if ($via === ActionLog::VIA_AGENT && !($this->agents?->allows($action) ?? false)) {
            $this->log->record($action->name, $via, ActionLog::DENIED, $user->id, $action->module, $input, $traceId, $ip);
            throw new Forbidden('Esta acción no está disponible para asistentes de IA.', 'agent_not_allowed');
        }

        $record = $action->needsRecord() ? $this->loadRecord($action, $input) : null;
        $subject = $record === null ? $action->module : $action->module . ':' . $record->getKey();

        try {
            $this->gate->authorize($user, $action->permission, $input, $record);
        } catch (Forbidden $e) {
            $this->log->record($action->name, $via, ActionLog::DENIED, $user->id, $subject, $input, $traceId, $ip);
            throw $e;
        }

        $clean = ($action->prepare)($input, $record);

        if ($action->confirm) {
            if (!is_string($token) || $token === '') {
                return ActionResult::confirmationRequired($this->requestConfirmation($action, $user, $record, $subject, $clean, $via, $traceId, $ip));
            }
            if (!$this->log->consumeConfirmation($token, $action->name, $user->id, self::binding($clean, $record))) {
                throw new BusinessRuleViolation('confirmation_invalid', 'La confirmación caducó o ya se usó. Vuelve a intentarlo.');
            }
        }

        try {
            $data = $this->db->connection()->transaction(static fn (): mixed => ($action->run)($clean, $record, $user));
        } catch (Throwable $e) {
            if (!$action->readOnly && !$e instanceof HttpError) {
                $this->log->record($action->name, $via, ActionLog::ERROR, $user->id, $subject, $clean, $traceId, $ip);
            }
            throw $e;
        }

        $data = $data instanceof Record ? $data->toData() : $data;
        if (!$action->readOnly) {
            $id = is_array($data) && isset($data['id']) ? $data['id'] : $record?->getKey();
            $this->log->record($action->name, $via, ActionLog::OK, $user->id, $id === null ? $subject : $action->module . ':' . $id, $clean, $traceId, $ip);
        }

        return ActionResult::ok($data, $this->uiEffect($action, $data), created: $action->operation === 'create');
    }

    /** @param array<string, mixed> $input */
    private function loadRecord(ActionDefinition $action, array $input): Record
    {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ValidationFailed(['id' => [['rule' => 'required', 'message' => 'Falta el id del registro.']]]);
        }

        /** @var class-string<Record> $class */
        $class = $this->modules->get($action->module)?->class ?? throw new NotFound();

        return $class::query()->find($id) ?? throw new NotFound('El registro no existe.');
    }

    /**
     * @param array<string, mixed> $clean
     * @return array{token: string, expires_at: string, message: string, preview: array<string, mixed>}
     */
    private function requestConfirmation(
        ActionDefinition $action,
        Identity $user,
        ?Record $record,
        string $subject,
        array $clean,
        string $via,
        string $traceId,
        ?string $ip,
    ): array {
        $token = $this->log->requestConfirmation($action->name, $via, $user->id, $subject, self::binding($clean, $record), self::CONFIRMATION_TTL_SECONDS, $traceId, $ip);
        $label = $record?->label();

        return [
            'token' => $token,
            'expires_at' => $this->clock->now()->modify('+' . self::CONFIRMATION_TTL_SECONDS . ' seconds')->format(DATE_ATOM),
            'message' => $action->operation === 'delete' && $label !== null
                ? "Se eliminará «{$label}». Esta acción no se puede deshacer."
                : $action->label . ($label !== null ? ": «{$label}»" : '') . '. ¿Confirmas?',
            'preview' => array_filter(['action' => $action->name, 'id' => $record?->getKey(), 'label' => $label, 'input' => $clean === [] ? null : $clean]),
        ];
    }

    /**
     * What a confirmation token is bound to: the validated input and the
     * record, so a token for one record can never confirm another.
     *
     * @param array<string, mixed> $clean
     * @return array{input: array<string, mixed>, record: int|string|null}
     */
    private static function binding(array $clean, ?Record $record): array
    {
        return ['input' => $clean, 'record' => $record?->getKey()];
    }

    /** @return array<string, mixed>|null */
    private function uiEffect(ActionDefinition $action, mixed $data): ?array
    {
        $module = $this->modules->get($action->module);
        if ($module === null) {
            return null;
        }

        return match ($action->operation) {
            'create', 'update' => is_array($data) && isset($data['id']) ? ['focus' => ['module' => $module->name, 'id' => $data['id']]] : null,
            'delete' => ['navigate' => $module->url()],
            default => null,
        };
    }
}
