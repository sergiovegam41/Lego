<?php

declare(strict_types=1);

namespace Lego\Http;

use Closure;
use Lego\Auth\Permissions;

/**
 * One registered route. Closed by default: it requires a signed-in user until
 * it is explicitly marked public(), and can additionally require a permission.
 *
 *     $router->get('/login', $show)->public();
 *     $router->post('/api/clients', $create)->can('clients.create');
 */
final class Route
{
    private Access $access = Access::Authenticated;

    private ?string $permission = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $regex,
        public readonly Closure $handler,
    ) {
    }

    public function public(): self
    {
        $this->access = Access::Public;
        $this->permission = null;

        return $this;
    }

    public function can(string $permission): self
    {
        Permissions::assertValid($permission);
        $this->access = Access::Authenticated;
        $this->permission = $permission;

        return $this;
    }

    public function access(): Access
    {
        return $this->access;
    }

    public function permission(): ?string
    {
        return $this->permission;
    }
}
