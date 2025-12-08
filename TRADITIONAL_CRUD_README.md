# Traditional Laravel MVC CRUD Generator

## Quick Start

This package now supports **both** Livewire-based CRUD and traditional Laravel MVC CRUD generation.

### Generate Traditional MVC CRUD

```bash
php artisan crud:traditional {table_name} {theme} {menu} {module}
```

**Example:**
```bash
php artisan crud:traditional users default admin backend
```

This will generate:
- ✅ **Controller** with all CRUD methods (`UserController.php`)
- ✅ **Form Request** validation class (`UserRequest.php`)
- ✅ **Views**: index, create, edit, show, pdf
- ✅ **Routes**: RESTful resource routes + custom routes
- ✅ **Navigation** menu link

---

## What Gets Generated

### 1. Controller (`Modules/{Module}/Http/Controllers/{Model}Controller.php`)

```php
class UserController extends Controller
{
    public function index(Request $request)        // List with search/filter
    public function create()                        // Show create form
    public function store(UserRequest $request)     // Save new record
    public function show($id)                       // Show single record
    public function edit($id)                       // Show edit form
    public function update(UserRequest $request)    // Update record
    public function destroy($id)                    // Delete record
    
    // Additional methods
    public function bulkDelete(Request $request)    // Delete multiple
    public function exportExcel(Request $request)   // Export to Excel
    public function exportPdf(Request $request)     // Export to PDF
    public function import(Request $request)        // Import from Excel/CSV
}
```

**Features:**
- Transaction support for data integrity
- Image upload handling with Intervention Image
- Search and filtering
- Sorting
- Pagination with query string preservation
- Email notifications
- Error handling and logging
- Flash messages for user feedback

---

### 2. Form Request (`Modules/{Module}/Http/Requests/{Model}Request.php`)

```php
class UserRequest extends FormRequest
{
    public function authorize()    // Authorization logic
    public function rules()        // Validation rules
    public function messages()     // Custom error messages
    public function attributes()   // Field name mapping
}
```

**Benefits:**
- ✅ Centralized validation logic
- ✅ Reusable across multiple controllers
- ✅ Automatic validation before controller methods
- ✅ Custom error messages
- ✅ Authorization checks

---

### 3. Views

#### Index View (`resources/views/{table}/index.blade.php`)
- Data table with sorting
- Search and filtering
- Pagination
- Bulk actions (delete, export)
- Import modal
- Export dropdown (Excel, PDF)
- Success/error messages

#### Create View (`resources/views/{table}/create.blade.php`)
- Form with all fields
- Client-side validation
- Image preview
- CSRF protection
- Error display

#### Edit View (`resources/views/{table}/edit.blade.php`)
- Pre-filled form
- Update button
- Delete button
- Change history
- Method spoofing (@method('PUT'))

#### Show View (`resources/views/{table}/show.blade.php`)
- Read-only field display
- Action buttons
- Related records section
- Formatted dates

#### PDF View (`resources/views/{table}/pdf.blade.php`)
- Professional PDF export template
- Header with logo
- Data table
- Footer with timestamp

---

### 4. Routes (`Modules/{Module}/routes/web.php`)

```php
// RESTful resource routes (7 standard routes)
Route::resource('users', UserController::class)
    ->names([
        'index' => 'backend.users.index',
        'create' => 'backend.users.create',
        'store' => 'backend.users.store',
        'show' => 'backend.users.show',
        'edit' => 'backend.users.edit',
        'update' => 'backend.users.update',
        'destroy' => 'backend.users.destroy',
    ])
    ->middleware('auth');

// Additional routes
Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])
    ->name('backend.users.bulk-delete');
Route::get('users/export/excel', [UserController::class, 'exportExcel'])
    ->name('backend.users.export.excel');
Route::get('users/export/pdf', [UserController::class, 'exportPdf'])
    ->name('backend.users.export.pdf');
Route::post('users/import', [UserController::class, 'import'])
    ->name('backend.users.import');
```

---

## Usage Examples

### Basic CRUD Operations

**Create:**
```php
// Navigate to: /users/create
// Fill form and submit
// Redirects to: /users (index) with success message
```

**Read/List:**
```php
// Navigate to: /users
// Use search, filters, sorting
// Results paginated
```

**Update:**
```php
// Navigate to: /users/{id}/edit
// Modify fields and submit
// Redirects with success message
```

**Delete:**
```php
// Click delete button
// Confirmation dialog
// Redirects with success message
```

### Advanced Features

**Bulk Delete:**
```php
// Select multiple checkboxes
// Click "Delete Selected"
// Confirmation dialog
// All selected records deleted
```

**Export:**
```php
// Click Export dropdown
// Choose Excel or PDF
// File downloads automatically
// Includes current filters
```

**Import:**
```php
// Click Import button
// Upload Excel/CSV file
// Data validated and imported
// Email notification sent
```

---

## Key Differences: Livewire vs Traditional MVC

| Feature | Livewire | Traditional MVC |
|---------|----------|-----------------|
| **Reactivity** | Real-time, no page refresh | Full page reload |
| **State Management** | Component properties | Session/Request |
| **Validation** | Inline in component | Form Request classes |
| **Routing** | Single route (component) | 7+ RESTful routes |
| **JavaScript** | Minimal required | Optional enhancement |
| **Performance** | WebSocket overhead | Standard HTTP |
| **SEO** | Good | Excellent |
| **Caching** | Limited | Full support |
| **Learning Curve** | Livewire-specific | Standard Laravel |

---

## When to Use Traditional MVC

✅ **Use Traditional MVC when:**
- Building public-facing applications (better SEO)
- Performance is critical (high traffic)
- Team prefers standard Laravel patterns
- Need full control over frontend
- Building API alongside web interface
- Heavy use of CDN/caching
- Working with legacy systems

❌ **Use Livewire when:**
- Building admin panels (internal use)
- Need real-time updates
- Want rapid development
- Minimal JavaScript requirements
- Team comfortable with Livewire
- Interactive dashboards

---

## Form Field Types Supported

The generator automatically creates appropriate form fields:

- **Text**: `<input type="text">`
- **Email**: `<input type="email">`
- **Password**: `<input type="password">`
- **Textarea**: `<textarea>`
- **Select**: `<select>` with options
- **Checkbox**: `<input type="checkbox">`
- **Radio**: `<input type="radio">`
- **File/Image**: `<input type="file">` with preview
- **Date**: `<input type="date">`
- **DateTime**: `<input type="datetime-local">`

---

## Validation Rules

Form Request automatically includes:

```php
'name' => 'required|string|max:255',
'email' => 'required|email|unique:users',
'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
'status' => 'required|in:active,inactive',
'date' => 'required|date',
```

**Custom validation:**
```php
// In UserRequest.php
public function rules()
{
    return [
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($this->user)
        ],
        'password' => 'required|min:8|confirmed',
    ];
}
```

---

## Error Handling

**Display errors in views:**
```blade
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@error('name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

**Controller error handling:**
```php
try {
    // Operation
    return redirect()->with('success', 'Success!');
} catch (\Exception $e) {
    Log::error('Error: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Failed!');
}
```

---

## Customization

### Modify Controller
```php
// Add custom method
public function archive($id)
{
    $user = User::findOrFail($id);
    $user->update(['archived' => true]);
    return redirect()->back();
}
```

### Modify Validation
```php
// In UserRequest.php
public function rules()
{
    // Add custom rules
    return array_merge(parent::rules(), [
        'custom_field' => 'required|numeric',
    ]);
}
```

### Modify Views
```blade
<!-- Extend index.blade.php -->
@extends('backend::users.index')

@section('extra-filters')
    <select name="role">
        <option value="">All Roles</option>
        <option value="admin">Admin</option>
    </select>
@endsection
```

---

## Best Practices

### 1. Keep Controllers Thin
```php
// ❌ Bad: Business logic in controller
public function store(UserRequest $request)
{
    $user = User::create($request->validated());
    $user->sendWelcomeEmail();
    $user->assignDefaultRole();
    $user->createProfile();
    // ...
}

// ✅ Good: Use service classes
public function store(UserRequest $request, UserService $service)
{
    $user = $service->create($request->validated());
    return redirect()->route('users.index');
}
```

### 2. Use Route Model Binding
```php
// ❌ Bad
public function show($id)
{
    $user = User::findOrFail($id);
}

// ✅ Good
public function show(User $user)
{
    // $user automatically injected
}
```

### 3. Eager Load Relations
```php
// ❌ Bad: N+1 query problem
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->name; // Extra query per user
}

// ✅ Good
$users = User::with('profile')->get();
```

### 4. Use Form Request Authorization
```php
public function authorize()
{
    return $this->user()->can('create', User::class);
}
```

---

## Testing

**Feature Test Example:**
```php
public function test_user_can_create_model()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post(route('backend.models.store'), [
            'name' => 'Test Model',
            'email' => 'test@example.com',
        ]);
    
    $response->assertRedirect(route('backend.models.index'));
    $this->assertDatabaseHas('models', ['name' => 'Test Model']);
}
```

---

## Performance Optimization

### 1. Query Optimization
```php
// Use select to limit columns
$users = User::select(['id', 'name', 'email'])->get();

// Use chunk for large datasets
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process
    }
});
```

### 2. Caching
```php
public function index()
{
    $users = Cache::remember('users.all', 3600, function () {
        return User::all();
    });
}
```

### 3. Pagination
```php
// Always paginate large datasets
$users = User::paginate(15);
```

---

## Troubleshooting

**Routes not working:**
```bash
php artisan route:clear
php artisan route:cache
php artisan route:list --name=users
```

**Validation not working:**
```bash
# Check Form Request is type-hinted in controller
public function store(UserRequest $request) // ✅
public function store(Request $request)     // ❌
```

**Views not found:**
```bash
php artisan view:clear
php artisan view:cache
```

**Images not uploading:**
```bash
php artisan storage:link
# Check file permissions on storage/app/public
```

---

## Migration from Livewire

To migrate existing Livewire CRUD to Traditional MVC:

1. **Backup your Livewire component**
2. **Run traditional generator:**
   ```bash
   php artisan crud:traditional users default admin backend
   ```
3. **Review generated files**
4. **Copy custom logic from Livewire component to controller**
5. **Test all CRUD operations**
6. **Update navigation links if needed**
7. **Remove Livewire component (optional)**

See `TRADITIONAL_MVC_GUIDE.md` for detailed migration instructions.

---

## Support & Documentation

- **Full Documentation**: `TRADITIONAL_MVC_GUIDE.md`
- **Livewire CRUD**: Use `php artisan crud:generate`
- **Traditional CRUD**: Use `php artisan crud:traditional`

---

## License

MIT License - See LICENSE.md
