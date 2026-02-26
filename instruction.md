# Livewire CRUD Enterprise — Developer Guide

> **Stack:** Laravel 10–12 · Livewire 3 · Bootstrap 5 · Alpine.js 3  
> **Package version:** 4.1 · PHP ^8.2

---

## Table of Contents

1. [Quick Start](#1-quick-start)
2. [Framework Integration](#2-framework-integration)
3. [Developer Productivity](#3-developer-productivity)
4. [UI Consistency](#4-ui-consistency)
5. [Community Standards & Patterns](#5-community-standards--patterns)
6. [Enterprise Feature Reference](#6-enterprise-feature-reference)
7. [Generator Command Reference](#7-generator-command-reference)
8. [Upgrade Path (v3 → v4)](#8-upgrade-path-v3--v4)
9. [Configuration Reference](#9-configuration-reference)
10. [Architecture Decisions](#10-architecture-decisions)

---

## 1. Quick Start

### Install the package

```bash
composer require xslainadmin/livewire-crud
```

### Run the install wizard

```bash
# Traditional mode — scaffolds inside app/ + resources/ (backward-compatible)
php artisan crud:install

# Modular mode — scaffolds inside Modules/{Module}/ (nWidart/laravel-modules)
php artisan crud:install --modular
php artisan crud:install --module=Backend   # --module implies --modular
```

**Modular mode** performs the following steps:
1. Checks for / creates the nWidart module via `module:make`
2. Creates all required sub-directories inside `Modules/{Module}/`
3. Scaffolds `routes/web.php` (with `//Route Hooks - Do not delete//`) and `module.json`
4. Generates a `{Module}ServiceProvider` and registers it in `bootstrap/providers.php`
5. Optionally runs `crud:layout {Module}` and `crud:auth {Module}` in the same session
6. Optionally runs `npm install && npm run build`

**Traditional mode** wizard will:
- Create required directories (`Http/Livewire`, `Models`, `views/livewire`, `views/layouts`)
- Optionally scaffold `ui:auth`
- Inject `//Route Hooks - Do not delete//` into `routes/web.php`
- Publish `config/livewire-crud.php`
- Run `npm install && npm run build`

### Generate your first CRUD module (interactive)

```bash
php artisan crud:make
```

Or non-interactively:

```bash
php artisan crud:generate invoices modern admin Accounting
#                          {table} {theme} {menu} {module}
```

### New: Generate a CRUD without an existing database table

```bash
php artisan crud:new invoices modern admin Accounting
```

The interactive wizard collects column definitions (name, type, nullable, default, enum values) and generates the **migration**, **model**, and all **views** in one step — no database table required beforehand.

### New: Scaffold authentication for a module

```bash
php artisan crud:auth Accounting
php artisan crud:auth Accounting --force   # overwrite existing files
```

Generates login, register, password reset, email verification, and confirm-password flows as Livewire 3 components — scoped to the specified module.

### New: Scaffold layout files for a module

```bash
php artisan crud:layout Accounting
php artisan crud:layout Accounting --type=backend   # admin layout + partials only
php artisan crud:layout Accounting --force
```

Generates app, backend (Metronic-style), and frontend layouts plus sidebar, topbar, print partials, and a starter dashboard view.

#### Available themes

| Key | Description |
|-----|-------------|
| `none` | Bare Livewire component — no layout wrapper |
| `nonedefault` | Bare component using the package's Bootstrap layout |
| `default` | Default Bootstrap 5 Admin theme |
| `modern` | Modern card-based Bootstrap 5 + ApexCharts theme |

---

## 2. Framework Integration

### Bootstrap 5 + Livewire

Livewire's `wire:model` and `wire:click` are paired with Bootstrap 5 utility classes throughout all generated views. Bootstrap JS is loaded via CDN or Vite; **do not** mix Bootstrap 4 and 5.

**Key conventions used by this package:**

```blade
{{-- Pagination — Bootstrap theme set in every Livewire component --}}
protected $paginationTheme = 'bootstrap';

{{-- Offcanvas / modals use aliqasemzadeh/livewire-bootstrap-modal --}}
<livewire:modal-component />
```

**Vite integration** (`resources/js/app.js`):

```js
import './bootstrap';
import Alpine from 'alpinejs';
import 'apexcharts';           // charts
import '@fullcalendar/core';   // calendar

window.Alpine = Alpine;
Alpine.start();
```

### Alpine.js 3

Alpine is the glue between Livewire re-renders and vanilla JS interactions (dropdown toggles, inline confirmations, chart initialisation). Follow these rules to avoid conflicts:

| Rule | Reason |
|------|--------|
| Use `x-data` on the **wrapper div**, not on Livewire's root element | Livewire owns the root; Alpine owns child components |
| Prefer `$wire.call()` from Alpine to trigger Livewire methods | Avoids full page-reload risk |
| Use `@js()` helper to pass PHP data to Alpine | Safer than raw Blade `{{ }}` inside `x-data` |

```blade
<div x-data="{ open: false, record: @js($selectedRecord) }">
    <button @click="$wire.edit(record.id); open = true">Edit</button>
</div>
```

### Livewire 3 Patterns

All generated components use **Livewire 3** conventions:

```php
use Livewire\Attributes\On;       // replaces protected $listeners
use Livewire\Attributes\Validate; // inline rules
use Livewire\Attributes\Url;      // query-string binding

#[Url(as: 'q')]
public string $search = '';

#[On('refresh-the-component')]
public function refresh(): void { /* ... */ }
```

**Lazy loading** for heavy components (enable in config):

```blade
<livewire:accounting::invoices lazy />
```

---

## 3. Developer Productivity

### Command Overview

| Command | Input | Best for |
|---------|-------|----------|
| `crud:make` | Interactive prompts | First-time setup, exploratory scaffolding |
| `crud:generate` | CLI arguments | CI pipelines, scripted generation |
| `crud:new` | Interactive (column builder) | New models with no existing DB table |
| `crud:auth` | `{module} [--force]` | Full auth scaffolding per module |
| `crud:layout` | `{module} [--type] [--force]` | Layout + partials + dashboard per module |
| `crud:install` | Options flags | Initial package setup (traditional or modular) |

### The `crud:make` Wizard vs `crud:generate`

| | `crud:make` | `crud:generate` |
|--|--|--|
| Input method | Interactive prompts | CLI arguments |
| Feature selection | Checkbox multi-select | All features always generated |
| Dry-run | `--dry-run` flag | — |
| Best for | First-time setup, exploratory scaffolding | CI pipelines, scripted generation |

### `crud:new` — Code-first CRUD without a database table

When the database table does not yet exist, `crud:new` collects column definitions interactively and generates the full stack:

```bash
php artisan crud:new invoices modern admin Accounting
```

For each column the wizard prompts:
- **Name** — column name
- **Type** — choice from 19 types (string, integer, text, boolean, date, datetime, decimal, float, json, enum, uuid, foreignId, …)
- **Nullable** — yes/no
- **Default** — optional default value
- **Enum values** — comma-separated list (only shown when type = `enum`)

A summary table is displayed before writing any files. The command then calls `buildMigration()` → `buildModel()` → `buildViews()` with the collected columns.

### Generated Output per Module

One `crud:generate` (or `crud:new`) call produces:

```
Modules/{Module}/
├── Livewire/
│   └── {ModelPlural}.php          # Main Livewire component
├── Models/
│   └── {Model}.php                # Eloquent model + traits
├── Exports/
│   ├── {ModelPlural}Export.php    # Excel/CSV export (Maatwebsite)
│   ├── {Model}PdfExport.php       # PDF export (DomPDF)
│   └── {Model}Print.php           # Print-friendly export
├── Imports/
│   └── {ModelPlural}Import.php    # Excel/CSV import (Maatwebsite)
├── Notifications/
│   └── {Model}Notification.php    # Queued notification (DB + Mail)
├── Emails/
│   └── {Model}Email.php           # Mailable
├── Charts/
│   └── {Model}Chart.php           # ApexCharts data class
├── Fullcalendar/
│   └── {Model}Calendar.php        # FullCalendar event source
├── Database/
│   ├── factories/
│   │   └── {Model}Factory.php     # Typed factory with states
│   └── migrations/
│       └── xxxx_create_{table}_table.php  # Migration (crud:new only)
├── resources/views/livewire/{model}/
│   ├── view.blade.php             # Main datatable view
│   ├── index.blade.php            # Page layout wrapper
│   ├── create.blade.php           # Create modal
│   ├── update.blade.php           # Edit modal
│   ├── show.blade.php             # Detail view modal
│   ├── delete.blade.php           # Delete confirmation modal
│   ├── import.blade.php           # Import modal
│   ├── pdf-export.blade.php       # PDF export modal
│   └── print.blade.php            # Print dialog
└── routes/web.php                 # Route auto-appended via hook
```

`crud:auth` adds to the module:

```
Modules/{Module}/
├── Livewire/Auth/
│   ├── Login.php
│   ├── Register.php
│   ├── ForgotPassword.php
│   ├── ResetPassword.php
│   ├── VerifyEmail.php
│   ├── ConfirmPassword.php
│   └── Logout.php
├── resources/views/auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   ├── reset-password.blade.php
│   ├── verify-email.blade.php
│   ├── confirm-password.blade.php
│   └── layouts/auth.blade.php
└── routes/auth.php                # auto-required from web.php
```

`crud:layout` adds to the module:

```
Modules/{Module}/resources/views/
├── layouts/
│   ├── app.blade.php              # Bootstrap 5 simple layout
│   ├── backend.blade.php          # Metronic-style admin layout
│   ├── frontend.blade.php         # Centered split-panel layout
│   ├── print.blade.php            # Print media CSS partial
│   ├── sidebar.blade.php          # Admin sidebar + nav hook
│   └── topbar.blade.php           # Admin header + user dropdown
└── dashboard.blade.php            # Starter dashboard view
```

### PHPStan / Static Analysis

Run at level 8:

```bash
php artisan vendor:publish --tag=livewire-crud-config
# then add the package path to phpstan.neon:
```

```neon
# phpstan.neon
includes:
    - vendor/larastan/larastan/extension.neon
parameters:
    level: 8
    paths:
        - src
        - Modules
```

### Code Quality Scripts

```bash
composer quality          # security audit + pint check + phpstan + coverage
composer ci               # full CI pipeline (security → analyse → rector-dry → parallel tests)
composer test-parallel    # run tests across cores using ParaTest
composer rector-dry       # preview auto-refactoring without writing files
```

---

## 4. UI Consistency

### Bootstrap 5 Component Map

| UI Element | Bootstrap 5 Component | Notes |
|---|---|---|
| Data table | `.table.table-hover.table-striped` | Responsive via `table-responsive` wrapper |
| Action buttons | `.btn.btn-sm` + icon | Grouped in `.btn-group` |
| Create / Edit modal | `livewire-bootstrap-modal` | `aliqasemzadeh/livewire-bootstrap-modal` |
| Flash messages | `php-flasher/flasher-laravel` | Toast-based, auto-dismiss |
| Pagination | Livewire `WithPagination` | Bootstrap theme automatically applied |
| Status badges | `.badge.text-bg-{color}` | Generated via `$model->status_badge` accessor |
| Cards (stats) | `.card.border-0.shadow-sm` | Four summary cards per index page |
| Breadcrumbs | `.breadcrumb` | Auto-generated from module/model names |
| Sidebar nav | Hook-based auto-injection | `<!--Nav Bar Hooks - Do not delete!!-->` |

### Consistent Status Colours

The `status_badge` and `status_class` accessors on every generated model follow this palette:

| Status | Bootstrap colour token |
|--------|----------------------|
| active / approved | `success` |
| inactive / rejected | `danger` |
| pending / warning | `warning` |
| draft | `secondary` |
| approved (primary flow) | `primary` |

### Typography & Spacing

- Use `fs-` utilities for font sizes, never hard-coded `font-size` styles.
- Spacing follows the Bootstrap 5 `gap-`, `g-`, `p-`, `m-` scale.
- Icons: use **Bootstrap Icons** (`bi bi-*`) — included with the install scaffold.

---

## 5. Community Standards & Patterns

### Laravel Module Architecture (`nWidart/laravel-modules`)

This package targets a modular application structure. Each generated CRUD lives in its own module:

```
Modules/
├── Accounting/
├── HR/
├── Inventory/
└── Backend/          # Shared layouts + nav
```

Install the module manager:

```bash
composer require nwidart/laravel-modules
php artisan module:install
```

### Spatie Packages (pre-wired)

| Package | Role in generated code |
|---------|----------------------|
| `spatie/laravel-permission` | `HasRoles` trait on every model; permission gates in Livewire methods |
| `spatie/laravel-activitylog` | `LogsActivity` trait; logs every create/update/delete with dirty-only diffing |
| `spatie/laravel-data` | `GeneratorConfig` DTO in the generator pipeline |
| `spatie/laravel-query-builder` | API controller scaffold alongside the Livewire component |

### Notification Pattern

**Do not** place notification dispatch logic inside Eloquent model methods. The correct pattern:

```php
// ✅ In the Livewire component — after a store/update/delete
$record->notify(new InvoiceNotification($record, InvoiceNotification::TYPE_CREATED, auth()->user()));

// ✅ Queue via Horizon for heavy payloads
dispatch(new SendInvoiceEmail($record))->onQueue(config('livewire-crud.horizon.notification_queue'));

// ❌ Never — models are not notification dispatchers
Invoice::notify('created', $data, $recipients);
```

### Queued Imports / Exports

```php
// Use ShouldQueue on Maatwebsite export for large datasets
class InvoicesExport implements FromQuery, ShouldQueue { ... }

// Route through Horizon's dedicated export queue
Excel::queue(new InvoicesExport($query), 'invoices.xlsx', 'local')
     ->onQueue(config('livewire-crud.horizon.export_queue'));
```

### Feature Flags

Control generated features globally via `config/livewire-crud.php`:

```php
'features' => [
    'audit_logging'       => true,   // Spatie activitylog hooks
    'analytics_dashboard' => true,   // stat cards + ApexCharts
    'calendar_integration'=> true,   // FullCalendar
    'horizon_monitoring'  => false,  // set true when Horizon is installed
    'pulse_monitoring'    => false,  // set true when Pulse is installed
    'scout_search'        => false,  // set true + configure driver
],
```

---

## 6. Enterprise Feature Reference

### ApexCharts Analytics

Every generated index page includes four summary stat cards and an interactive ApexCharts line/bar chart. The Livewire component exposes:

```php
public string $chartPeriod = 'month';   // today|week|month|year
public string $chartType   = 'line';    // line|bar|area|donut
public array  $chartData   = [];
```

Override `getChartData()` in the Livewire component to supply custom series.

### FullCalendar Integration

Enable via the `calendar_integration` feature flag. Each calendar event maps to a model row via:

```php
// In {Model}Calendar.php
public function getEvents(Carbon $start, Carbon $end): array;
```

### Spatie Activity Log

Every generated model includes:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logFillable()          // only log fillable columns
        ->logOnlyDirty()         // only log what actually changed
        ->dontSubmitEmptyLogs()  // skip if nothing changed
        ->useLogName('crud');
}
```

View logs in Telescope (`/telescope/requests`) or query directly:

```php
activity('crud')->get();
$invoice->activities;
```

### Laravel Pulse

When `pulse_monitoring` is enabled, slow CRUD queries (threshold configurable) are automatically recorded.

Add Pulse's dashboard card:

```php
// In your Pulse dashboard view
<livewire:pulse.slow-queries />
```

### Horizon Queue Monitoring

```bash
php artisan horizon
```

Heavy operations (bulk export, import, email batches) are dispatched to dedicated queues:

```
exports        → InvoicesExport, InvoicesPdfExport
notifications  → InvoiceNotification, InvoiceEmail
```

Configure in `config/livewire-crud.php` → `horizon` section.

---

## 7. Generator Command Reference

### `crud:install` — Installation wizard

```bash
php artisan crud:install [options]
```

| Option | Description |
|--------|-------------|
| `--modular` | Enable modular mode (nWidart) |
| `--module=Name` | Module name — implies `--modular` |
| `--skip-auth` | Skip `crud:auth` prompt |
| `--skip-layout` | Skip `crud:layout` prompt (modular only) |
| `--skip-npm` | Skip npm install and build |
| `--force` | Overwrite existing files |

**Modular mode scaffolds:**

```
Modules/{Module}/
├── Providers/{Module}ServiceProvider.php
├── routes/web.php               # with //Route Hooks - Do not delete//
├── module.json
├── Livewire/  Http/  Models/  Exports/  Imports/
├── Notifications/  Emails/  Charts/  Database/
└── resources/views/layouts/     # optionally via crud:layout
    resources/views/auth/        # optionally via crud:auth
```

The ServiceProvider is automatically appended to `bootstrap/providers.php` (Laravel 11+). For Laravel 10, a hint is printed to add it to `config/app.php`.

---

### `crud:new` — Interactive column builder

```bash
php artisan crud:new {name} {theme} {menu} {module}
```

| Argument | Description |
|----------|-------------|
| `name` | Table / model name (e.g. `invoices`) |
| `theme` | Layout theme: `none` \| `nonedefault` \| `default` \| `modern` |
| `menu` | Menu group: `admin` \| `super_admin` |
| `module` | nWidart module name (e.g. `Accounting`) |

The wizard loops until you enter an empty column name. Supported column types:

`string` · `integer` · `bigInteger` · `unsignedBigInteger` · `text` · `longText` · `boolean` · `date` · `dateTime` · `timestamp` · `decimal` · `float` · `double` · `json` · `enum` · `uuid` · `foreignId` · `tinyInteger` · `smallInteger`

The migration token `{{migrationColumns}}` is replaced with one `$table->type('column')` call per defined column. The migration file is written to `Modules/{Module}/Database/migrations/`.

---

### `crud:auth` — Authentication scaffolding

```bash
php artisan crud:auth {module} [--force]
```

**What is generated:**

| File | Location |
|------|----------|
| `Login.php` … `Logout.php` | `Modules/{Module}/Livewire/Auth/` |
| `login.blade.php` … `confirm-password.blade.php` | `Modules/{Module}/resources/views/auth/` |
| `auth.blade.php` (Bootstrap 5 auth layout) | `…/auth/layouts/` |
| `auth.php` (routes file) | `Modules/{Module}/routes/` |

The routes file is auto-required from `routes/web.php` if the file contains the `//Auth Routes Hook` comment, otherwise the `require` is appended to the end of the file.

**Auth component features:**

| Component | Key feature |
|-----------|-------------|
| `Login` | Rate limiting (5 attempts / minute), remember-me |
| `Register` | `Hash::make`, fires `Registered` event |
| `ForgotPassword` | `Password::sendResetLink` |
| `ResetPassword` | Token-based, fires `PasswordReset` event |
| `VerifyEmail` | `hasVerifiedEmail()` guard, resend link |
| `ConfirmPassword` | `auth.password_confirmed_at` session |
| `Logout` | Session invalidate + token regeneration |

All components use `#[Validate]` attributes (Livewire 3), typed properties, and `declare(strict_types=1)`.

---

### `crud:layout` — Layout scaffolding

```bash
php artisan crud:layout {module} [--type=all] [--force]
```

| `--type` value | Files written |
|----------------|---------------|
| `all` (default) | All layouts + partials + dashboard |
| `app` | Bootstrap 5 simple navbar layout |
| `backend` | Metronic admin layout + sidebar + topbar + print partials |
| `frontend` | Centered split-panel public layout |
| `partials` | `print.blade.php`, `sidebar.blade.php`, `topbar.blade.php` only |
| `dashboard` | Starter `dashboard.blade.php` view only |

**Token replacements applied in all layout stubs:**

| Token | Replaced with |
|-------|---------------|
| `{{getModuleInputModule}}` | Module name as given (e.g. `Accounting`) |
| `{{getModuleInput}}` | Lowercase module name (e.g. `accounting`) |

**Using generated layouts in child views:**

```blade
@extends('accounting::layouts.backend')

@section('page_title', 'Invoices')

@section('toolbar_actions')
    <a href="{{ route('accounting.invoices.create') }}" class="btn btn-sm btn-primary">
        New Invoice
    </a>
@endsection

@section('content')
    <livewire:accounting::invoices />
@endsection
```

The `sidebar.blade.php` partial preserves the `<!--Nav Bar Hooks - Do not delete!!-->` comment so `crud:generate` can continue injecting nav links automatically.

---

## 8. Upgrade Path (v3 → v4)

### What changed

| Area | v3 | v4 |
|------|----|----|
| PHP minimum | 8.1 | **8.2** |
| Laravel minimum | 9.x | **10.x** |
| Main class | Missing (ServiceProvider crashed) | `LivewireCrud` class created |
| Namespace double-semicolon bug | Present | Fixed |
| ServiceProvider | `boot()` no return type | `boot(): void`, `register(): void` |
| Generator commands | Raw `int\|bool` returns | `self::SUCCESS / self::FAILURE` |
| Type safety | Untyped properties | Fully typed properties + return types |
| Model.stub | 453 lines incl. static notification bus | **202 lines**, clean SOLID model |
| Factory.stub namespace | `App\Models` (wrong) | `Modules\{Module}\Database\Factories` |
| Config sections | 12 sections | **19 sections** — adds audit, horizon, pulse, scout, permissions, query_builder, livewire |
| Enums | None | `ThemeType`, `ExportFormat` |
| DTO | None | `GeneratorConfig` (spatie/laravel-data) |
| Contracts | None | `CrudGeneratorInterface` |
| Install command | Destructive (deleted public/css) | Non-destructive, options-based, task-based output |

### Step-by-step migration

1. **Bump PHP to 8.2** in your server config and `composer.json`.

2. **Bump Laravel** to `^10.0` if on v9.

3. **Update composer.json** minimum versions:

   ```bash
   composer require xslainadmin/livewire-crud:^4.0
   ```

4. **Re-publish config** (new sections will be merged):

   ```bash
   php artisan vendor:publish --tag=livewire-crud-config --force
   ```

5. **Audit existing models**: If your models contain static notification helper methods (from v3's `Model.stub`), extract them into dedicated `Notification` classes and dispatch from Livewire components.

6. **Check Factory namespaces**: v3 generated factories under `Database\Factories` pointing to `App\Models`. v4 generates them under `Modules\{Module}\Database\Factories`. Regenerate or manually update the namespace + import.

7. **Update Artisan references**: The `crud:install` command no longer deletes `public/css`, `public/js`, or `node_modules`. Run it with `--force` to overwrite existing scaffold:

   ```bash
   php artisan crud:install --force --skip-auth
   ```

8. **Replace bare `crud:generate` calls with `crud:make`** in development workflows. `crud:generate` remains available for CI/scripted use.

9. **Run quality gate**:

   ```bash
   composer quality
   ```

---

## 9. Configuration Reference

Full file: `config/livewire-crud.php`

| Key path | Default | Notes |
|----------|---------|-------|
| `stub_path` | `'default'` | Path to custom stubs directory |
| `layout` | `'layouts.app'` | Blade layout for generated views |
| `model.namespace` | `'App\Models'` | Override for module-based apps |
| `model.unwantedColumns` | `[id, password, ...]` | Excluded from `$fillable` and views |
| `features.*` | varies | Master on/off per enterprise feature |
| `audit.log_name` | `'crud'` | Activity log group name |
| `audit.retention_days` | `90` | Set 0 for forever |
| `horizon.export_queue` | `'exports'` | Queue for bulk export jobs |
| `horizon.notification_queue` | `'notifications'` | Queue for email/notification jobs |
| `pulse.slow_query_threshold_ms` | `500` | Pulse slow-query threshold |
| `scout.driver` | `'meilisearch'` | Scout search driver |
| `scout.debounce_ms` | `300` | Livewire search debounce |
| `permissions.auto_create` | `[view-X, create-X, ...]` | Permission names seeded per model |
| `query_builder.max_results` | `500` | Max records per API page |
| `livewire.lazy` | `false` | Lazy-load all generated components |
| `livewire.poll_interval` | `0` | Polling interval (0 = event-driven) |
| `export.limits.max_records` | `10000` | Hard cap on single export |
| `ui.table.default_per_page` | `10` | Initial pagination page size |

---

## 10. Architecture Decisions

### Why Spatie Data instead of plain arrays?

`GeneratorConfig` is a `\Spatie\LaravelData\Data` subclass. This gives:
- Compile-time type checking via PHPStan/Psalm
- Auto-casting from request arrays
- Serialisation for queued generator jobs
- IDE autocomplete on every config key

### Why enums instead of string constants?

`ThemeType` and `ExportFormat` are PHP 8.1+ backed enums. They enable exhaustive `match` expressions in generators and prevent invalid theme/format strings from ever reaching stub processing.

### Why the `CrudGeneratorInterface` contract?

Binding `LivewireCrudGenerator` to `CrudGeneratorInterface` means you can swap the generator implementation (e.g. for a custom in-house generator) without modifying the service provider — just bind your class in a local service provider.

### Why move notification dispatch out of the Model?

Eloquent models should be **persistence concerns only**. Static notification helper methods violated:
- Single Responsibility Principle
- Testability (impossible to mock)
- The Observer / Listener pattern Laravel ships with

The correct flow is: **Livewire component → dispatch Notification/Mailable → queue worker delivers**.

### Stub customisation

Publish stubs to your application:

```bash
php artisan vendor:publish --tag=livewire-crud-stubs
```

Stubs land in `stubs/livewire-crud/`. Set `stub_path` in config:

```php
'stub_path' => base_path('stubs/livewire-crud'),
```

All tokens use `{{doubleBraces}}` and are listed in `LivewireGeneratorCommand::buildReplacements()`.
