<?php

declare(strict_types=1);

namespace Domain\Auth\Exceptions;

use DomainException;

class ExpiredTokenException extends DomainException
{
}
