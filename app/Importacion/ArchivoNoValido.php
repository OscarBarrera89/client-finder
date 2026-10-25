<?php

namespace App\Importacion;

use RuntimeException;

/**
 * El archivo subido no se puede importar. El mensaje se enseña tal cual al admin:
 * nunca lleva detalles técnicos ni datos personales.
 */
class ArchivoNoValido extends RuntimeException {}
