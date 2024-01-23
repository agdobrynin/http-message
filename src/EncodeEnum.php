<?php

declare(strict_types=1);

namespace Kaspi\HttpMessage;

enum EncodeEnum
{
    case userinfo;
    case path;
    case query;
    case fragment;
}
