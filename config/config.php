<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stubs Path
    |--------------------------------------------------------------------------
    | The stubs path directory to generate crud. You may configure your
    | stubs paths here, allowing you to customize the own stubs of the
    | model,controller or view. Or, you may simply stick with the CrudGenerator defaults!
    | Example: 'stub_path' => resource_path('path/to/views/stubs/')
    | Default: "default"
    */

    'stub_path' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Application Layout
    |--------------------------------------------------------------------------
    | This value is the name of your application layout. This value is used when creating
    | views for crud. Default will be the "layouts.app".
    */

    'layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    | Configure model generation settings including namespace, unwanted columns,
    | and additional model features.
    */
    'model' => [
        'namespace' => 'App\Models',

        /*
         * Do not make these columns $fillable in Model or views
         */
        'unwantedColumns' => [
            'id',
            'password',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
        ],

        /*
         * Additional model features
         */
        'features' => [
            'soft_deletes' => true,
            'timestamps' => true,
            'uuid' => false,
            'sluggable' => false,
            'searchable' => true,
            'sortable' => true,
            'cacheable' => false,
        ],

        /*
         * Model traits to include
         */
        'traits' => [
            'HasFactory',
            'SoftDeletes',
            'Searchable',
            'Sortable',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Configuration
    |--------------------------------------------------------------------------
    | Configure controller generation settings including namespace and features.
    */
    'controller' => [
        'namespace' => 'App\Http\Controllers',
        
        /*
         * Controller features
         */
        'features' => [
            'api_resource' => true,
            'validation' => true,
            'authorization' => false,
            'caching' => false,
            'rate_limiting' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    | Configure export functionality including formats, templates, and limits.
    */
    'export' => [
        /*
         * Enabled export formats
         */
        'formats' => [
            'pdf' => true,
            'excel' => true,
            'csv' => true,
            'json' => false,
            'xml' => false,
        ],

        /*
         * PDF Export Settings
         */
        'pdf' => [
            'default_template' => 'default',
            'available_templates' => [
                'default' => 'Default Template',
                'detailed' => 'Detailed Report',
                'summary' => 'Summary Report',
                'minimal' => 'Minimal Template',
            ],
            'default_orientation' => 'portrait',
            'default_paper_size' => 'a4',
            'enable_watermark' => true,
            'enable_password_protection' => true,
            'enable_compression' => true,
            'max_records_per_page' => 50,
        ],

        /*
         * Excel Export Settings
         */
        'excel' => [
            'default_format' => 'xlsx',
            'enable_styling' => true,
            'enable_charts' => true,
            'enable_auto_filter' => true,
            'enable_formulas' => false,
        ],

        /*
         * CSV Export Settings
         */
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'include_bom' => false,
        ],

        /*
         * Export Limits
         */
        'limits' => [
            'max_records' => 10000,
            'batch_size' => 1000,
            'memory_limit' => '512M',
            'execution_time_limit' => 300, // 5 minutes
        ],

        /*
         * Storage Configuration
         */
        'storage' => [
            'disk' => 'local',
            'path' => 'exports',
            'cleanup_after_hours' => 24,
            'enable_compression' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart & Analytics Configuration
    |--------------------------------------------------------------------------
    | Configure analytics dashboard and chart settings.
    */
    'charts' => [
        /*
         * Chart Library
         */
        'default_library' => 'apexcharts', // 'apexcharts' or 'chartjs'

        /*
         * Available Chart Types
         */
        'available_types' => [
            'line' => 'Line Chart',
            'bar' => 'Bar Chart',
            'column' => 'Column Chart',
            'area' => 'Area Chart',
            'pie' => 'Pie Chart',
            'donut' => 'Donut Chart',
            'radar' => 'Radar Chart',
            'scatter' => 'Scatter Plot',
            'heatmap' => 'Heatmap',
            'treemap' => 'Treemap',
            'candlestick' => 'Candlestick',
            'gauge' => 'Gauge Chart',
            'radialbar' => 'Radial Bar',
            'sparkline' => 'Sparkline',
        ],

        /*
         * Default Chart Settings
         */
        'defaults' => [
            'height' => 400,
            'animations' => true,
            'toolbar' => true,
            'export_enabled' => true,
            'real_time_updates' => false,
            'cache_duration' => 3600, // 1 hour
        ],

        /*
         * Color Schemes
         */
        'color_schemes' => [
            'default' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'],
            'pastel' => ['#FFB6C1', '#98FB98', '#F0E68C', '#FFA07A', '#87CEEB', '#DDA0DD'],
            'dark' => ['#2C3E50', '#34495E', '#7F8C8D', '#95A5A6', '#BDC3C7', '#ECF0F1'],
            'vibrant' => ['#FF5733', '#33FF57', '#3357FF', '#FF33F5', '#F5FF33', '#33FFF5'],
        ],

        /*
         * Analytics Features
         */
        'analytics' => [
            'enable_real_time' => false,
            'enable_drill_down' => true,
            'enable_export' => true,
            'enable_sharing' => false,
            'cache_results' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Calendar Configuration
    |--------------------------------------------------------------------------
    | Configure calendar functionality and event management.
    */
    'calendar' => [
        /*
         * Calendar Settings
         */
        'default_view' => 'month',
        'available_views' => [
            'month' => 'Month View',
            'week' => 'Week View',
            'day' => 'Day View',
            'list' => 'List View',
        ],

        /*
         * Event Settings
         */
        'events' => [
            'enable_drag_drop' => true,
            'enable_resize' => true,
            'enable_recurring' => true,
            'default_duration' => 60, // minutes
            'max_title_length' => 255,
        ],

        /*
         * Export Options
         */
        'export' => [
            'formats' => ['ical', 'csv', 'json', 'pdf'],
            'default_format' => 'ical',
        ],

        /*
         * Integration Settings
         */
        'integration' => [
            'google_calendar' => false,
            'outlook_calendar' => false,
            'apple_calendar' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    | Configure notification channels and settings.
    */
    'notifications' => [
        /*
         * Available Channels
         */
        'channels' => [
            'database' => true,
            'mail' => true,
            'broadcast' => false,
            'slack' => false,
            'telegram' => false,
            'sms' => false,
        ],

        /*
         * Default Settings
         */
        'defaults' => [
            'channels' => ['database', 'mail'],
            'priority' => 'normal',
            'template' => 'default',
            'queue' => true,
        ],

        /*
         * Templates
         */
        'templates' => [
            'default' => 'Default Notification',
            'alert' => 'Alert Notification',
            'reminder' => 'Reminder Notification',
            'update' => 'Update Notification',
            'welcome' => 'Welcome Notification',
        ],

        /*
         * Priority Levels
         */
        'priorities' => [
            'low' => 'Low Priority',
            'normal' => 'Normal Priority',
            'high' => 'High Priority',
            'urgent' => 'Urgent Priority',
            'critical' => 'Critical Priority',
        ],

        /*
         * Auto-notifications
         */
        'auto_notify' => [
            'on_create' => true,
            'on_update' => true,
            'on_delete' => true,
            'on_status_change' => true,
            'on_bulk_operations' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    | Configure email functionality and templates.
    */
    'email' => [
        /*
         * Email Types
         */
        'types' => [
            'notification' => 'Notification Email',
            'report' => 'Report Email',
            'single_record' => 'Single Record Email',
            'multiple_records' => 'Multiple Records Email',
            'bulk_operation' => 'Bulk Operation Email',
            'import_results' => 'Import Results Email',
            'export_ready' => 'Export Ready Email',
        ],

        /*
         * Default Settings
         */
        'defaults' => [
            'queue' => true,
            'include_statistics' => true,
            'attach_data' => false,
            'template' => 'default',
        ],

        /*
         * Attachments
         */
        'attachments' => [
            'max_size' => '10MB',
            'allowed_types' => ['pdf', 'xlsx', 'csv', 'zip'],
            'auto_cleanup_hours' => 24,
        ],

        /*
         * Templates
         */
        'templates' => [
            'default' => 'Default Template',
            'professional' => 'Professional Template',
            'minimal' => 'Minimal Template',
            'branded' => 'Branded Template',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Print Configuration
    |--------------------------------------------------------------------------
    | Configure print functionality and templates.
    */
    'print' => [
        /*
         * Print Settings
         */
        'default_format' => 'pdf',
        'default_orientation' => 'portrait',
        'default_paper_size' => 'a4',

        /*
         * Available Templates
         */
        'templates' => [
            'default' => 'Default Print Template',
            'detailed' => 'Detailed Print Template',
            'summary' => 'Summary Print Template',
            'minimal' => 'Minimal Print Template',
        ],

        /*
         * Print Options
         */
        'options' => [
            'include_header' => true,
            'include_footer' => true,
            'include_logo' => true,
            'include_page_numbers' => true,
            'include_timestamp' => true,
        ],

        /*
         * Paper Sizes
         */
        'paper_sizes' => [
            'a4' => 'A4',
            'letter' => 'Letter',
            'legal' => 'Legal',
            'a3' => 'A3',
            'tabloid' => 'Tabloid',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    | Configure import functionality and validation.
    */
    'import' => [
        /*
         * Supported Formats
         */
        'formats' => ['xlsx', 'xls', 'csv', 'json'],

        /*
         * Import Settings
         */
        'settings' => [
            'max_file_size' => '10MB',
            'batch_size' => 1000,
            'validate_headers' => true,
            'skip_empty_rows' => true,
            'trim_whitespace' => true,
        ],

        /*
         * Error Handling
         */
        'error_handling' => [
            'continue_on_error' => true,
            'max_errors' => 100,
            'log_errors' => true,
            'email_errors' => true,
        ],

        /*
         * Validation Rules
         */
        'validation' => [
            'required_columns' => [],
            'date_format' => 'Y-m-d',
            'number_format' => 'decimal',
            'boolean_values' => ['true', 'false', '1', '0', 'yes', 'no'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    | Configure user interface elements and themes.
    */
    'ui' => [
        /*
         * Theme Settings
         */
        'default_theme' => 'default',
        'available_themes' => [
            'default' => 'Default Theme',
            'dark' => 'Dark Theme',
            'modern' => 'Modern Theme',
            'minimal' => 'Minimal Theme',
        ],

        /*
         * Layout Settings
         */
        'layout' => [
            'sidebar_collapsed' => false,
            'show_breadcrumbs' => true,
            'show_page_title' => true,
            'show_statistics_cards' => true,
        ],

        /*
         * Table Settings
         */
        'table' => [
            'default_per_page' => 10,
            'per_page_options' => [10, 25, 50, 100],
            'enable_sorting' => true,
            'enable_filtering' => true,
            'enable_search' => true,
            'enable_bulk_operations' => true,
        ],

        /*
         * Modal Settings
         */
        'modals' => [
            'backdrop' => 'static',
            'keyboard' => true,
            'show_close_button' => true,
            'auto_focus' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    | Configure performance optimization settings.
    */
    'performance' => [
        /*
         * Caching
         */
        'cache' => [
            'enable_query_cache' => true,
            'cache_duration' => 3600, // 1 hour
            'cache_tags' => true,
        ],

        /*
         * Pagination
         */
        'pagination' => [
            'default_per_page' => 10,
            'max_per_page' => 100,
            'enable_cursor_pagination' => false,
        ],

        /*
         * Database
         */
        'database' => [
            'enable_query_log' => false,
            'slow_query_threshold' => 1000, // milliseconds
            'enable_connection_pooling' => false,
        ],

        /*
         * Asset Optimization
         */
        'assets' => [
            'enable_minification' => true,
            'enable_compression' => true,
            'enable_cdn' => false,
            'cache_bust' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    | Configure security features and restrictions.
    */
    'security' => [
        /*
         * File Upload Security
         */
        'file_upload' => [
            'max_file_size' => '10MB',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'scan_for_viruses' => false,
            'quarantine_suspicious_files' => false,
        ],

        /*
         * Data Export Security
         */
        'export_security' => [
            'require_authentication' => true,
            'log_export_activities' => true,
            'encrypt_sensitive_exports' => false,
            'watermark_exports' => false,
        ],

        /*
         * API Security
         */
        'api' => [
            'enable_rate_limiting' => true,
            'rate_limit_attempts' => 60,
            'rate_limit_period' => 60, // seconds
            'require_api_key' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    | Enable or disable specific features globally.
    */
    'features' => [
        'analytics_dashboard' => true,
        'calendar_integration' => true,
        'advanced_export' => true,
        'email_functionality' => true,
        'notification_system' => true,
        'print_functionality' => true,
        'import_functionality' => true,
        'bulk_operations' => true,
        'real_time_updates' => false,
        'audit_logging' => false,
        'multi_language' => false,
        'api_endpoints' => true,
        'webhook_support' => false,
    ],

];
