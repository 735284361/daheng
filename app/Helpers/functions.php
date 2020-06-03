<?php

use Carbon\Carbon;

function getSubMonth()
{
    $firstOfMonth = new Carbon('first day of last month');
    return $firstOfMonth->format('Ym');
}
