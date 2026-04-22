<?php

namespace App;

enum MonthlyLoeReportLineType: string
{
    case Project = 'project';
    case TimeOff = 'time_off';
    case OpenToNewProjects = 'open_to_new_projects';
}
