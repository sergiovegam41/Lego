---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/InitCommand.php
loc: 75
deps: 4
dependents: 0
responsabilidad: Orquesta la inicialización del framework Lego, ejecutando migraciones, mapeo de rutas y configuración de almacenamiento, informando el éxito o errores durante el proceso.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# InitCommand

`Core\Commands\InitCommand`

📁 [Core/Commands/InitCommand.php](../../../Core/Commands/InitCommand.php)

> [!abstract] Responsabilidad
> Orquesta la inicialización del framework Lego, ejecutando migraciones, mapeo de rutas y configuración de almacenamiento, informando el éxito o errores durante el proceso.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## 🆕 Instancia

- [[init-storage-command|InitStorageCommand]]
- [[map-routes-command|MapRoutesCommand]]
- [[migrate-command|MigrateCommand]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.