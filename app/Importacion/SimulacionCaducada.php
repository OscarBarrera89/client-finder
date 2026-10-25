<?php

namespace App\Importacion;

use RuntimeException;

/** Los datos cambiaron entre la simulación que vio el admin y la confirmación. */
class SimulacionCaducada extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Los datos han cambiado desde la simulación. Revisa la simulación nueva antes de confirmar.');
    }
}
