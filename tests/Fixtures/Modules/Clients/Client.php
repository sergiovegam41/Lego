<?php

declare(strict_types=1);

namespace Tests\Fixtures\Modules\Clients;

use Lego\Action\Action;
use Lego\Action\Param;
use Lego\Data\Fields\Boolean;
use Lego\Data\Fields\Date;
use Lego\Data\Fields\Email;
use Lego\Data\Fields\Money;
use Lego\Data\Fields\Number;
use Lego\Data\Fields\Select;
use Lego\Data\Fields\Text;
use Lego\Data\Fields\TextArea;
use Lego\Data\Record;
use Lego\Errors\BusinessRuleViolation;
use Lego\Module\Module;

/** Test module: every field type and two custom actions. */
#[Module(label: 'Clientes', icon: 'people-outline', order: 10)]
final class Client extends Record
{
    protected $table = 'clients';

    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre')->required()->max(120)->searchable()->sortable(),
            Email::make('email', 'Correo')->required()->unique()->searchable(),
            Money::make('credit', 'Crédito')->min(0)->sortable(),
            Boolean::make('active', 'Activo'),
            Select::make('tier', 'Nivel')->options(['basic' => 'Básico', 'gold' => 'Oro']),
            Date::make('since', 'Cliente desde'),
            Number::make('visits', 'Visitas')->integer()->min(0)->default(0)->hideInTable(),
            TextArea::make('notes', 'Notas')->hideInTable(),
        ];
    }

    #[Action(label: 'Bloquear', description: 'Impide que el cliente compre.', destructive: true, confirm: true)]
    public function block(#[Param('Motivo')] string $reason): array
    {
        if (!$this->active) {
            throw new BusinessRuleViolation('client_already_blocked', 'El cliente ya está bloqueado.');
        }

        $this->forceFill(['active' => false, 'notes' => trim(($this->notes ?? '') . "\nBloqueado: {$reason}")])->save();

        return ['blocked' => true];
    }

    #[Action(label: 'Registrar visita', description: 'Suma visitas al contador del cliente.')]
    public function registerVisit(int $times = 1): int
    {
        $this->forceFill(['visits' => (int) $this->visits + $times])->save();

        return (int) $this->visits;
    }

    #[Action(label: 'Contar activos', description: 'Cuántos clientes activos hay.', readOnly: true)]
    public static function countActive(): int
    {
        return self::query()->where('active', true)->count();
    }
}
