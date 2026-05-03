<?php

namespace Core\Controllers;

/**
 * DefaultCrudController - Implementación concreta de AbstractCrudController
 *
 * Clase concreta vacía usada por ApiCrudRouter cuando el modelo no especifica
 * un controlador custom en su atributo #[ApiCrudResource]. Toda la lógica
 * vive en AbstractCrudController; esta clase solo la hace instanciable.
 */
class DefaultCrudController extends AbstractCrudController {}
