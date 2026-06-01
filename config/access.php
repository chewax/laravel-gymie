<?php

return [
    /*
    | Maximum number of members allowed in the gym at the same time.
    | 0 = unlimited (capacity checks are skipped).
    */
    'capacity' => (int) env('GYM_CAPACITY', 0),
];
