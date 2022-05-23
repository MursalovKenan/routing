<?php

namespace Mursalov\Routing\Exceptions;

class NotFoundException extends HttpException
{
    protected $code = 404;
}