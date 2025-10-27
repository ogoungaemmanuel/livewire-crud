# Livewire CRUD Generator - Enterprise Edition

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ogoungaemmanuel/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/ogoungaemmanuel/livewire-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/ogoungaemmanuel/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/ogoungaemmanuel/livewire-crud)
[![License](https://img.shields.io/packagist/l/ogoungaemmanuel/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/ogoungaemmanuel/livewire-crud)
[![PHP Version](https://img.shields.io/packagist/php-v/ogoungaemmanuel/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/ogoungaemmanuel/livewire-crud)

A comprehensive Laravel Livewire CRUD generator package with enterprise-level features including interactive charts, calendar management, advanced export/import capabilities, notification systems, and modern Bootstrap 5 UI. Perfect for rapid application development with production-ready components.

## 🚀 Features

### Core CRUD Operations

- **Advanced CRUD Generation**: Complete Create, Read, Update, Delete operations with modern UI
- **Real-time Updates**: Powered by Livewire for seamless user experience
- **Bulk Operations**: Mass delete, bulk edit, and batch processing capabilities
- **Advanced Search & Filtering**: Multi-column search with real-time filtering
- **Pagination**: Efficient data pagination with customizable page sizes

### 📊 Analytics & Visualization

- **Interactive Charts**: 17+ chart types powered by ApexCharts
  - Line, Area, Bar, Column, Pie, Donut, Radial, Scatter
  - Heatmaps, Treemaps, Candlestick, Boxplot
  - Gauges, Sparklines, Mixed charts
- **Real-time Data Updates**: Live chart updates with WebSocket support
- **Export Charts**: PNG, JPG, PDF, SVG export capabilities
- **Responsive Design**: Mobile-optimized chart rendering

### 📅 Calendar Management

- **Full Calendar Integration**: Powered by FullCalendar
- **Multiple Views**: Month, week, day, list, and timeline views
- **Drag & Drop**: Interactive event management
- **Recurring Events**: Support for repeating events
- **Event Categories**: Color-coded event organization
- **Export Options**: Calendar export to ICS, PDF formats

### 📄 Export & Import System

- **Multi-format Export**: PDF, Excel, CSV, Word documents
- **Template System**: Customizable export templates
- **Batch Processing**: Handle large datasets efficiently
- **Print Optimization**: Professional print layouts
- **Security Features**: Password protection and watermarks
- **Bulk Import**: CSV/Excel import with validation

### 🔔 Notification System

- **Multi-channel Delivery**: Database, email, broadcast, SMS
- **Real-time Notifications**: Instant updates via WebSockets
- **Email Templates**: Beautiful, responsive email designs
- **Notification Center**: Centralized notification management
- **Scheduling**: Delayed and scheduled notifications

### 🎨 Modern UI/UX

- **Bootstrap 5**: Latest Bootstrap framework with custom theming
- **Dark Mode**: Complete dark/light theme support
- **Responsive Design**: Mobile-first approach for all devices
- **Alpine.js Integration**: Reactive components and interactions
- **FontAwesome Icons**: Comprehensive icon library
- **Accessibility**: WCAG 2.1 compliant interfaces

### 🔧 Advanced Features

- **Theme System**: Multiple pre-built themes and customization
- **Multi-language Support**: Internationalization ready
- **Role-based Access**: Permission management integration
- **API Generation**: RESTful API endpoints with documentation
- **Testing Suite**: Automated tests for generated components
- **Performance Optimization**: Query optimization and caching

## 📋 Requirements

- PHP ^8.0
- Laravel ^9.0|^10.0|^11.0
- Livewire ^3.0
- Node.js ^16.0 (for asset compilation)
- Composer ^2.0

## 🛠 Installation

### Step 1: Install via Composer

```bash
composer require nwidart/laravel-modules
composer require xslainadmin/livewire-crud
```

### Step 2: Install Package Dependencies

```bash
php artisan crud:install
```

This command will:

- Install and configure Bootstrap 5, ApexCharts, FullCalendar
- Set up Alpine.js and FontAwesome
- Configure Webpack/Vite for asset compilation
- Install all JavaScript dependencies
- Compile CSS/JS assets
- Publish configuration files

### Step 3: Configure Environment

Add to your `.env` file:

```env
# Chart Configuration
CHARTS_ENABLED=true
CHARTS_DEFAULT_TYPE=line
CHARTS_CACHE_DURATION=3600

# Calendar Configuration
CALENDAR_ENABLED=true
CALENDAR_DEFAULT_VIEW=dayGridMonth
CALENDAR_TIME_ZONE=UTC

# Export Configuration
EXPORT_ENABLED=true
EXPORT_MAX_RECORDS=10000
EXPORT_QUEUE_ENABLED=true

# Notification Configuration
NOTIFICATIONS_ENABLED=true
NOTIFICATIONS_CHANNELS=database,mail
NOTIFICATIONS_QUEUE=default
```

### Step 4: Run Migrations (Optional)

If you want to use the built-in notification system:

```bash
php artisan migrate
```

## 🎯 Usage

### Basic CRUD Generation

Generate a complete CRUD interface for any model:

```bash
php artisan crud:generate {table_name} {theme?} {module?}
```

**Example:**

```bash
php artisan crud:generate users modern
php artisan crud:generate products default admin
```

### Available Themes

- **default**: Clean, professional design
- **modern**: Contemporary with advanced animations
- **minimal**: Simplified, focused interface
- **dark**: Dark-first design approach

### Generated Components

Each CRUD generation creates:

#### 📁 Livewire Components

- `{Model}Component.php` - Main CRUD component
- `{Model}Chart.php` - Analytics component
- `{Model}Calendar.php` - Calendar component
- `{Model}Export.php` - Export component
- `{Model}Import.php` - Import component

#### 🎨 Views

- `index.blade.php` - Data listing with advanced features
- `create.blade.php` - Creation form with validation
- `edit.blade.php` - Edit form with live updates
- `show.blade.php` - Detailed view with related data
- `modals/` - Modal components for quick actions

#### 🗃 Models & Factories

- `{Model}.php` - Eloquent model with relationships
- `{Model}Factory.php` - Database factory for testing
- Migration files with proper indexing

#### 📧 Notifications

- `{Model}Created.php` - Creation notification
- `{Model}Updated.php` - Update notification
- `{Model}Deleted.php` - Deletion notification

### Advanced Usage Examples

#### 1. Chart Integration

```php
// In your Livewire component
public function loadChartData()
{
    return [
        'series' => [
            [
                'name' => 'Sales',
                'data' => $this->getSalesData()
            ]
        ],
        'options' => [
            'chart' => ['type' => 'line'],
            'xaxis' => ['categories' => $this->getMonths()]
        ]
    ];
}
```

#### 2. Calendar Events

```php
// Define calendar events
public function getCalendarEvents()
{
    return $this->model::query()
        ->select('id', 'title', 'start_date as start', 'end_date as end')
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'backgroundColor' => $this->getEventColor($event)
            ];
        });
}
```

#### 3. Custom Export Templates

```php
// Create custom PDF export
public function exportToPdf()
{
    $data = $this->getFilteredData();

    return $this->export()
        ->template('custom.pdf-template')
        ->data($data)
        ->filename('report-' . now()->format('Y-m-d'))
        ->download();
}
```

#### 4. Real-time Notifications

```php
// Send real-time notification
public function notifyUsers($message, $type = 'info')
{
    $this->dispatch('notification', [
        'message' => $message,
        'type' => $type,
        'timeout' => 5000
    ]);
}
```

## 🎛 Configuration

### Publishing Configuration Files

```bash
php artisan vendor:publish --provider="LivewireCrud\LivewireCrudServiceProvider" --tag=config
```

### Main Configuration (`config/livewire-crud.php`)

```php
return [
    'export' => [
        'enabled' => true,
        'formats' => ['pdf', 'excel', 'csv'],
        'templates' => [
            'pdf' => 'exports.pdf.default',
            'excel' => 'exports.excel.default',
        ],
        'security' => [
            'password_protect' => false,
            'watermark' => false,
        ],
    ],

    'charts' => [
        'enabled' => true,
        'default_type' => 'line',
        'color_scheme' => 'default',
        'animations' => true,
        'toolbar' => true,
    ],

    'calendar' => [
        'enabled' => true,
        'default_view' => 'dayGridMonth',
        'time_format' => 'H:mm',
        'date_format' => 'YYYY-MM-DD',
    ],

    'notifications' => [
        'enabled' => true,
        'channels' => ['database', 'mail'],
        'templates' => [
            'mail' => 'notifications.mail.default',
        ],
    ],
];
```

## 🎨 Customization

### Custom Themes

Create your own theme by extending the base theme:

```bash
php artisan crud:theme MyCustomTheme
```

### Custom Templates

Override default templates:

```bash
php artisan vendor:publish --provider="LivewireCrud\LivewireCrudServiceProvider" --tag=views
```

### Custom Styling

The package uses CSS custom properties for easy theming:

```css
:root {
  --primary-color: #your-color;
  --secondary-color: #your-color;
  --success-color: #your-color;
  /* ... */
}
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Generate test coverage:

```bash
composer test-coverage
```

## 📚 API Reference

### Livewire Methods

| Method             | Description             | Parameters                  |
| ------------------ | ----------------------- | --------------------------- |
| `loadData()`       | Load paginated data     | `$page`, `$perPage`         |
| `search($query)`   | Search records          | `$query` string             |
| `sort($field)`     | Sort by field           | `$field`, `$direction`      |
| `export($format)`  | Export data             | `$format` (pdf\|excel\|csv) |
| `bulkDelete($ids)` | Delete multiple records | `$ids` array                |

### JavaScript API

```javascript
// Chart management
App.charts.create("#chart", options);
App.charts.updateData("#chart", newData);

// Calendar management
App.calendar.init("#calendar", options);
App.calendar.addEvent(eventData);

// Notifications
App.notifications.show(message, type, options);
```

## 🔧 Troubleshooting

### Common Issues

1. **Assets not loading**: Run `php artisan crud:install` and ensure Node.js dependencies are installed
2. **Charts not rendering**: Verify ApexCharts is loaded and check browser console for errors
3. **Export failing**: Ensure proper file permissions and storage configuration
4. **Calendar not showing**: Check FullCalendar dependencies and configuration

### Debug Mode

Enable debug mode in configuration:

```php
'debug' => env('CRUD_DEBUG', false),
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Clone your fork
3. Install dependencies: `composer install && npm install`
4. Run tests: `composer test`
5. Create feature branch
6. Submit pull request

## 🔒 Security

If you discover any security-related issues, please email info@xslain.com instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- [Ogounga Emmanuel](https://github.com/ogoungaemmanuel) - Creator & Maintainer
- [All Contributors](../../contributors) - Community contributors
- [ApexCharts](https://apexcharts.com/) - Chart library
- [FullCalendar](https://fullcalendar.io/) - Calendar component
- [Bootstrap](https://getbootstrap.com/) - UI framework
- [Livewire](https://laravel-livewire.com/) - Frontend framework

## 🌟 Support

- ⭐ Star this repository if it helped you!
- 🐛 [Report bugs](https://github.com/ogoungaemmanuel/livewire-crud/issues)
- 💡 [Request features](https://github.com/ogoungaemmanuel/livewire-crud/issues)
- 📖 [Documentation](https://ogoungaemmanuel.github.io/livewire-crud)
- 💬 [Discussions](https://github.com/ogoungaemmanuel/livewire-crud/discussions)

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@xslain.com instead of using the issue tracker.

## Credits

- [Ogounga Emmanuel](https://github.com/xslainadmin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
