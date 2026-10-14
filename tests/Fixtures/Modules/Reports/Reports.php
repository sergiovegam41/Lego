<?php

declare(strict_types=1);

namespace Tests\Fixtures\Modules\Reports;

use Lego\Module\Module;
use Lego\View\Component;

/** Test module made of a single screen, without records. */
#[Module(label: 'Informes', icon: 'bar-chart-outline', order: 20)]
final class Reports extends Component
{
    protected function render(): string
    {
        return '<section class="reports"><h1>Informes</h1></section>';
    }
}
