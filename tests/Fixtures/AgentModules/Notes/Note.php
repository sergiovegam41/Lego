<?php

declare(strict_types=1);

namespace Tests\Fixtures\AgentModules\Notes;

use Lego\Data\Fields\Text;
use Lego\Data\Record;
use Lego\Module\Module;

/** Test module that lets AI agents write when LEGO_AGENT_ACCESS=write. */
#[Module(label: 'Notas', agentWrites: true)]
final class Note extends Record
{
    protected $table = 'notes';

    public static function fields(): array
    {
        return [Text::make('title', 'Título')->required()];
    }
}
