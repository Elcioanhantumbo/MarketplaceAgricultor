<?php

namespace App\Exceptions;

/**
 * Violação de uma regra de negócio do ciclo de vida do pedido
 * (RN05, RN06, RN09, RN11, RN15) — mensagem segura para mostrar ao utilizador.
 */
class OrderWorkflowException extends \RuntimeException {}