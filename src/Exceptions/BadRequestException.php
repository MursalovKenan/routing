<?php

namespace Mursalov\Routing\Exceptions;

class BadRequestException extends HttpException
{
    protected $code = 400;
}