<?php

declare(strict_types=1);

namespace Lego\Http;

/** Who may reach a route. Routes are Authenticated unless marked Public. */
enum Access
{
    case Public;
    case Authenticated;
}
