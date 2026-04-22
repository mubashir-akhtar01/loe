<?php

namespace App;

enum MonthlyLoeReportActivityAction: string
{
    case Created = 'created';
    case Prefilled = 'prefilled';
    case Updated = 'updated';
    case Submitted = 'submitted';
    case StatusChanged = 'status_changed';
    case Closed = 'closed';
    case NotificationSent = 'notification_sent';
}
