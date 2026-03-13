<?php

return [
    // Maximum number of columns allowed in a table before flagging it as wide.
    'max_columns' => 25,

    // Maximum VARCHAR length considered acceptable for general schema design.
    'max_varchar_length' => 190,

    // Maximum number of JSON columns allowed per table before warning about overuse.
    'max_json_columns' => 2,

    // Table size threshold in MB used to identify potentially large tables.
    'large_table_mb' => 100,

    // Ratio of NULL values (0 to 1) that triggers nullable overuse/performance warnings.
    'null_ratio_threshold' => 0.5,

    // Analysis groups to run. Remove a group to skip those checks.
    'enabled_checks' => [
        // Structural schema design checks (columns, data types, naming/layout patterns).
        'structure',

        // Performance-focused checks (indexes, growth risks, and heavy table patterns).
        'performance',

        // Data integrity checks (constraints, relationships, duplicates, and orphan risks).
        'integrity',

        // Architecture-level checks (audit trails, JSON/polymorphic usage patterns).
        'architecture'
    ]
];