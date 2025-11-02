<?php

namespace Core\Providers;

trait TimeSet {

    public function setTimezone() {
        date_default_timezone_set('America/Bogota');
    }
}