<?php

namespace App\Infrastructure\KnpPaginator\Model;

enum OrderEnum: string
{
    case ASC  = 'asc';
    case DESC = 'desc';
}
