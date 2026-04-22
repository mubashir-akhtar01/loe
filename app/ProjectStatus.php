<?php

namespace App;

enum ProjectStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Closed = 'closed';
}
