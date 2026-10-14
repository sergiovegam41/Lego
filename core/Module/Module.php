<?php

declare(strict_types=1);

namespace Lego\Module;

use Attribute;

/**
 * Marks the entry class of a module: one per folder under modules/.
 *
 * On a Record subclass the module gets CRUD actions and screens for the
 * operations listed. On a Component subclass it is a single screen.
 * Name, URL and permissions come from the folder: modules/SalesOrders gives
 * module "sales_orders", URL /sales-orders and permissions "sales_orders.*".
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Module
{
    public const OPERATIONS = ['list', 'get', 'create', 'update', 'delete'];

    /**
     * @param list<string> $operations  Subset of OPERATIONS for Record modules.
     * @param bool         $agentWrites Let AI agents run this module's write actions
     *                                  when LEGO_AGENT_ACCESS=write (see AgentPolicy).
     */
    public function __construct(
        public string $label,
        public string $icon = 'square',
        public int $order = 100,
        public string $description = '',
        public array $operations = self::OPERATIONS,
        public bool $agentWrites = false,
    ) {
    }
}
