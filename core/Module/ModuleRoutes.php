<?php

declare(strict_types=1);

namespace Lego\Module;

use Lego\Action\ActionDefinition;
use Lego\Action\ActionExecutor;
use Lego\Action\ActionRegistry;
use Lego\Action\ActionResult;
use Lego\Audit\ActionLog;
use Lego\Auth\Gate;
use Lego\Data\Record;
use Lego\Errors\NotFound;
use Lego\Errors\ValidationFailed;
use Lego\Http\Request;
use Lego\Http\Response;
use Lego\Http\Router;
use Lego\Http\Layer;
use Lego\Ui\Shell\Screen;
use Lego\Ui\Shell\Shell;
use Lego\Ui\Records\ActionMessage;
use Lego\Ui\Records\ConfirmAction;
use Lego\Ui\Records\RecordForm;
use Lego\Ui\Records\RecordList;
use Lego\View\Component;
use Lego\View\Document;

/**
 * Registers every HTTP surface of the modules. Nothing here decides what an
 * action does: each request becomes one ActionExecutor::execute() call.
 *
 * JSON API
 *   GET  /api/actions             catalog of actions the user may run
 *   POST /api/actions/{name}      run an action (JSON body, confirmation_token for step 2)
 *
 * Screens of a Record module (also under /component/... as fragments)
 *   GET  /{slug}                         list
 *   GET  /{slug}/new      POST same      create form
 *   GET  /{slug}/{id}     POST same      edit form
 *   POST /{slug}/{id}/delete             delete, two steps
 *   POST /{slug}/{id}/actions/{op}       custom record action
 *   POST /{slug}/actions/{op}            custom module action
 *
 * A Component module gets GET /{slug} only.
 */
final class ModuleRoutes
{
    public function __construct(
        private readonly ModuleRegistry $modules,
        private readonly ActionRegistry $actions,
        private readonly ActionExecutor $executor,
        private readonly Gate $gate,
        private readonly Menu $menu,
    ) {
    }

    public function register(Router $router): void
    {
        $router->get('/api/actions', $this->catalog(...));
        $router->post('/api/actions/{name}', $this->run(...));

        foreach ($this->modules->all() as $module) {
            $module->isRecord() ? $this->registerRecord($router, $module) : $this->registerScreen($router, $module);
        }
    }

    /**
     * Wraps content in its Screen root and returns it as a fragment on the
     * component layer, or inside the shell as a full page otherwise.
     */
    public function respond(
        Request $request,
        string $title,
        Component $content,
        int $status = 200,
        ?ModuleDefinition $module = null,
        ?int $record = null,
    ): Response {
        $key = Screen::keyFor($request->path);
        $path = $key === 'home' ? '/' : '/' . $key;
        $url = $request->query === [] ? $path : $path . '?' . http_build_query($request->query);
        $screen = new Screen($key, $url, $title, $content, $module?->name, $record);

        if ($request->layer() === Layer::Component) {
            return Response::html(Document::fragment($screen), $status);
        }

        $shell = new Shell($request->user, $this->menu->for($request->user, $path), $request->csrfToken, $screen);

        return Response::html(
            Document::page($title . ' · Lego', $shell, csrfToken: $request->csrfToken, head: Shell::importMap()),
            $status,
        );
    }

    private function catalog(Request $request): Response
    {
        $module = $request->query('module');
        $actions = $this->actions->allowedFor($request->user, $this->gate, is_string($module) ? $module : null);

        return Response::json(['status' => 'ok', 'data' => array_map(static fn (ActionDefinition $a): array => $a->toArray(), $actions)]);
    }

    private function run(Request $request): Response
    {
        $result = $this->executor->execute(
            (string) $request->param('name'),
            $request->body,
            $request->user,
            ActionLog::VIA_API,
            $request->traceId,
            $request->ip,
        );

        return Response::json($result->toArray(), $result->httpStatus());
    }

    private function registerScreen(Router $router, ModuleDefinition $module): void
    {
        $handler = function (Request $request) use ($module): Response {
            $this->gate->authorize($request->user, $module->permission('view'));
            /** @var Component $screen */
            $screen = new ($module->class)();

            return $this->respond($request, $module->label, $screen, module: $module);
        };

        $router->get($module->url(), $handler);
        $router->get('/component' . $module->url(), $handler);
    }

    private function registerRecord(Router $router, ModuleDefinition $module): void
    {
        $url = $module->url();
        $both = static function (string $path, callable $handler) use ($router): void {
            $router->get($path, $handler);
            $router->get('/component' . $path, $handler);
        };

        if ($module->allows('list')) {
            $both($url, fn (Request $r): Response => $this->list($r, $module));
        }
        if ($module->allows('create')) {
            $both($url . '/new', fn (Request $r): Response => $this->createForm($r, $module));
            $router->post($url . '/new', fn (Request $r): Response => $this->create($r, $module));
        }
        foreach ($this->actions->forModule($module->name) as $action) {
            if ($action->scope === ActionDefinition::COLLECTION && !in_array($action->operation, Module::OPERATIONS, true)) {
                $router->post($url . '/actions/' . $action->operation, fn (Request $r): Response => $this->custom($r, $module, $action));
            }
        }
        if ($module->allows('get')) {
            $both($url . '/{id}', fn (Request $r): Response => $this->editForm($r, $module));
        }
        if ($module->allows('update')) {
            $router->post($url . '/{id}', fn (Request $r): Response => $this->update($r, $module));
        }
        if ($module->allows('delete')) {
            $router->post($url . '/{id}/delete', fn (Request $r): Response => $this->delete($r, $module));
        }
        foreach ($this->actions->forModule($module->name) as $action) {
            if ($action->scope === ActionDefinition::RECORD && !in_array($action->operation, Module::OPERATIONS, true)) {
                $router->post($url . '/{id}/actions/' . $action->operation, fn (Request $r): Response => $this->custom($r, $module, $action));
            }
        }
    }

    private function list(Request $request, ModuleDefinition $module): Response
    {
        $input = array_intersect_key($request->query, array_flip(['q', 'sort', 'dir', 'page']));
        $result = $this->execute($request, $module->name . '_list', $input);

        return $this->respond($request, $module->label, new RecordList($module, $this->fields($module), $result->data, $this->allowed($request, $module)), module: $module);
    }

    private function createForm(Request $request, ModuleDefinition $module, array $values = [], array $errors = [], int $status = 200): Response
    {
        $this->gate->authorize($request->user, $module->permission('create'));
        $form = new RecordForm($module, $this->fields($module), RecordForm::CREATE, $request->csrfToken, $values, $errors, $this->allowed($request, $module), domPrefix: Screen::domPrefix(Screen::keyFor($request->path)));

        return $this->respond($request, 'Nuevo · ' . $module->label, $form, $status, $module);
    }

    private function create(Request $request, ModuleDefinition $module): Response
    {
        try {
            $this->execute($request, $module->name . '_create', $request->body);
        } catch (ValidationFailed $e) {
            return $this->createForm($request, $module, self::submitted($request), $e->fields, 422);
        }

        return Response::redirect($module->url(), 303);
    }

    private function editForm(Request $request, ModuleDefinition $module, ?array $values = null, array $errors = [], int $status = 200, array $actionErrors = []): Response
    {
        $id = self::id($request);
        $data = $this->execute($request, $module->name . '_get', ['id' => $id])->data;
        if ($values === null) {
            $values = [];
            foreach ($this->fields($module) as $field) {
                $values[$field->name] = $field->formValue($data[$field->name] ?? null);
            }
        }

        $form = new RecordForm($module, $this->fields($module), RecordForm::UPDATE, $request->csrfToken, $values, $errors, $this->allowed($request, $module), $id, $actionErrors, Screen::domPrefix(Screen::keyFor($request->path)));

        return $this->respond($request, self::recordTitle($module, $this->fields($module), $data, $id), $form, $status, $module, $id);
    }

    private function update(Request $request, ModuleDefinition $module): Response
    {
        try {
            $this->execute($request, $module->name . '_update', ['id' => self::id($request), ...$request->body]);
        } catch (ValidationFailed $e) {
            return $this->editForm($request, $module, self::submitted($request), $e->fields, 422);
        }

        return Response::redirect($module->url(), 303);
    }

    private function delete(Request $request, ModuleDefinition $module): Response
    {
        $result = $this->execute($request, $module->name . '_delete', ['id' => self::id($request), ...$request->body]);
        if ($result->needsConfirmation()) {
            return $this->confirm($request, $module, $result);
        }

        return Response::redirect($module->url(), 303);
    }

    private function custom(Request $request, ModuleDefinition $module, ActionDefinition $action): Response
    {
        $isRecord = $action->scope === ActionDefinition::RECORD;
        $input = $isRecord ? ['id' => self::id($request), ...$request->body] : $request->body;

        try {
            $result = $this->execute($request, $action->name, $input);
        } catch (ValidationFailed $e) {
            if ($isRecord) {
                return $this->editForm($request, $module, status: 422, actionErrors: [$action->name => $e->fields]);
            }
            throw $e;
        }

        if ($result->needsConfirmation()) {
            return $this->confirm($request, $module, $result);
        }

        $back = $isRecord ? $module->url() . '/' . self::id($request) : $module->url();
        if (is_array($result->data) && is_string($result->data['message'] ?? null)) {
            return $this->respond($request, $action->label, new ActionMessage($result->data['message'], $back), module: $module);
        }

        return Response::redirect($back, 303);
    }

    private function confirm(Request $request, ModuleDefinition $module, ActionResult $result): Response
    {
        $resend = array_map('strval', array_filter(
            array_diff_key(self::submitted($request), ['confirmation_token' => true]),
            'is_scalar',
        ));
        $screen = new ConfirmAction(
            $result->confirmation['message'],
            $request->path,
            $result->confirmation['token'],
            $request->csrfToken,
            $module->url(),
            $resend,
        );

        return $this->respond($request, 'Confirmar', $screen, module: $module);
    }

    /** @param array<string, mixed> $input */
    private function execute(Request $request, string $action, array $input): ActionResult
    {
        return $this->executor->execute($action, $input, $request->user, ActionLog::VIA_UI, $request->traceId, $request->ip);
    }

    /** @return list<ActionDefinition> */
    private function allowed(Request $request, ModuleDefinition $module): array
    {
        return $this->actions->allowedFor($request->user, $this->gate, $module->name);
    }

    /** @return list<\Lego\Data\Fields\Field> */
    private function fields(ModuleDefinition $module): array
    {
        /** @var class-string<Record> $class */
        $class = $module->class;

        return $class::fields();
    }

    /**
     * "Florería Lima · Clientes": the first non-empty text field names the record.
     *
     * @param list<\Lego\Data\Fields\Field> $fields
     * @param array<string, mixed>          $data
     */
    private static function recordTitle(ModuleDefinition $module, array $fields, array $data, int $id): string
    {
        foreach ($fields as $field) {
            if (in_array($field->type(), ['text', 'email'], true) && ($label = $field->display($data[$field->name] ?? null)) !== '') {
                return $label . ' · ' . $module->label;
            }
        }

        return $module->label . ' #' . $id;
    }

    private static function id(Request $request): int
    {
        $id = filter_var($request->param('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? throw new NotFound() : $id;
    }

    /** @return array<string, string|list<string>> Submitted form values, for re-rendering. */
    private static function submitted(Request $request): array
    {
        $values = [];
        foreach ($request->body as $name => $value) {
            if ($name === '_csrf') {
                continue;
            }
            if (is_scalar($value)) {
                $values[(string) $name] = (string) $value;
            } elseif (is_array($value) && array_is_list($value)) {
                $values[(string) $name] = array_map('strval', array_filter($value, 'is_scalar'));
            }
        }

        return $values;
    }
}
