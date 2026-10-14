<?php

namespace Core\Controllers;

/**
 * DefaultGetController - Implementación concreta de AbstractGetController
 *
 * Clase concreta vacía usada por ApiGetRouter cuando el modelo no especifica
 * un controlador custom en su atributo #[ApiGetResource]. Toda la lógica
 * vive en AbstractGetController; esta clase solo la hace instanciable.
 */
class DefaultGetController extends AbstractGetController {}
