<?php

namespace App;

enum MonthlyLoeReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Closed = 'closed';
}
