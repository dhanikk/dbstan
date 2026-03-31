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

    // Total database size threshold in MB used for database-wide storage alerts.
    'database_size_mb' => 2048,

    // Warn when a table occupies this share of total database storage.
    'unusually_large_table_ratio' => 0.20,

    // Error when a single table dominates total database storage.
    'table_dominance_ratio' => 0.40,

    // Ratio of NULL values (0 to 1) that triggers nullable overuse/performance warnings.
    'null_ratio_threshold' => 0.5,

    // Auto-increment usage ratio thresholds for warning/critical overflow risk.
    'autoincrement_warn_ratio' => 0.70,
    'autoincrement_critical_ratio' => 0.90,

    // Minimum estimated rows to treat INT auto-increment columns as high-growth risk.
    'autoincrement_growth_rows_threshold' => 5000000,

    // Minimum row-count before evaluating low-cardinality index quality.
    'index_cardinality_min_rows' => 1000,

    // Thresholds for identifying low-selectivity indexes.
    'index_cardinality_ratio_threshold' => 0.05,
    'index_cardinality_absolute_threshold' => 20,

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