<?php

namespace App\Infrastructure\KnpPaginator\DTO;

enum OrderEnum: string
{
    case ASC  = 'asc';
    case DESC = 'desc';
}
