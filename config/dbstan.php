<?php

return [
    'max_columns' => 25,
    'max_varchar_length' => 190,
    'max_json_columns' => 2,
    'large_table_mb' => 100,
    'null_ratio_threshold' => 0.5,
    'enabled_checks' => [
        'structure',
        'performance',
        'integrity',
        'architecture'
    ]
];