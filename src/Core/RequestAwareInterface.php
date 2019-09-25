<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestAwareInterface должен имплементиться классами, которым нужен Request
 */
interface RequestAwareInterface
{
    /**
     * Установка объекта реквеста
     *
     * @param Request $request
     */
    public function setRequest(Request $request);
}
