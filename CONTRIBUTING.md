# Contributing to Livewire CRUD

Thank you for considering contributing to Livewire CRUD! We welcome contributions from the community and appreciate your help in making this enterprise-grade Laravel package even better.

## 🎯 Quick Start

1. **Fork** the repository
2. **Clone** your fork locally
3. **Install** dependencies: `composer install && npm install`
4. **Create** a feature branch: `git checkout -b feature/amazing-feature`
5. **Make** your changes and add tests
6. **Run** tests: `composer test`
7. **Submit** a pull request

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Environment](#development-environment)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Issue Guidelines](#issue-guidelines)
- [Pull Request Process](#pull-request-process)
- [Release Process](#release-process)

## 🤝 Code of Conduct

This project adheres to a code of conduct that we expect all contributors to follow:

- **Be respectful** and inclusive in your language and actions
- **Be collaborative** and helpful to other contributors
- **Be patient** with maintainers who volunteer their time
- **Be constructive** in your feedback and criticism
- **Be professional** in all interactions

Harassment, trolling, or any form of discriminatory behavior will not be tolerated.

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP 8.1+** with required extensions (mbstring, openssl, pdo, tokenizer, xml)
- **Composer 2.0+** for dependency management
- **Node.js 18+** and **npm** for frontend assets
- **Git** for version control
- A **Laravel 9+** application for testing

### Setting Up Your Development Environment

1. **Fork and Clone**

   ```bash
   git clone https://github.com/your-username/livewire-crud.git
   cd livewire-crud
   ```

2. **Install Dependencies**

   ```bash
   composer install
   npm install
   ```

3. **Set Up Git Hooks** (optional but recommended)
   ```bash
   composer run setup-hooks
   ```

## 🛠 Development Environment

### Local Testing

To test your changes locally:

1. **Create a test Laravel project**

   ```bash
   composer create-project laravel/laravel test-app
   cd test-app
   ```

2. **Add your local package**

   ```json
   // composer.json
   "repositories": [
       {
           "type": "path",
           "url": "../livewire-crud"
       }
   ]
   ```

3. **Install the package**
   ```bash
   composer require "ogoungaemmanuel/livewire-crud:*"
   ```

### Code Quality Tools

We use several tools to maintain code quality:

- **StyleCI**: Automated code style fixing
- **PHPStan**: Static analysis (Level 8)
- **Pest**: Modern testing framework
- **Rector**: Automated refactoring
- **PHP CS Fixer**: Code style enforcement

Run all quality checks:

```bash
composer run quality
```

## 📝 Coding Standards

We follow modern PHP and Laravel best practices:

### PHP Standards

- **PSR-12** coding standard (evolution of PSR-2)
- **PHPDoc** blocks for all public methods and properties
- **Type hints** for all parameters and return types
- **Strict types** declaration in all PHP files
- **Modern PHP features** (PHP 8.1+ syntax)

### Laravel Conventions

- **Eloquent models** with proper relationships and accessors
- **Service classes** for complex business logic
- **Form requests** for validation
- **Resources** for API transformations
- **Jobs** for background processing

### Frontend Standards

- **Bootstrap 5** for styling (no Tailwind)
- **Alpine.js** for JavaScript reactivity
- **ES6+** modern JavaScript syntax
- **Responsive design** principles
- **Accessibility** (WCAG 2.1 AA compliance)

### Code Organization

```php
<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

/**
 * User management component with CRUD operations
 */
class UserComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;

    /**
     * Render the component view
     */
    public function render(): View
    {
        return view('livewire.user-component', [
            'users' => $this->getUsers(),
        ]);
    }

    /**
     * Get filtered users with pagination
     */
    private function getUsers(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }
}
```

## 🧪 Testing

We use **Pest** for testing with comprehensive coverage requirements:

### Test Types

1. **Unit Tests** - Test individual classes and methods
2. **Feature Tests** - Test complete workflows
3. **Browser Tests** - Test JavaScript interactions
4. **Integration Tests** - Test package installation

### Writing Tests

```php
<?php

use App\Models\User;

test('can create user through livewire component')
    ->expect(function () {
        $component = Livewire::test(UserComponent::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->call('create');

        expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
        expect($component->get('showModal'))->toBeFalse();
    });

test('validates required fields')
    ->expect(function () {
        Livewire::test(UserComponent::class)
            ->set('name', '')
            ->set('email', '')
            ->call('create')
            ->assertHasErrors(['name', 'email']);
    });
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test suite
composer test:unit
composer test:feature

# Run tests in parallel
composer test:parallel
```

### Coverage Requirements

- **Minimum 80%** overall coverage
- **Minimum 90%** for critical paths
- **100%** for security-related code

## 📬 Issue Guidelines

### Before Creating an Issue

1. **Search existing issues** to avoid duplicates
2. **Check the documentation** and FAQ
3. **Test with the latest version** of the package
4. **Prepare a minimal reproduction** case

### Issue Types

#### 🐛 Bug Reports

Use the bug report template and include:

- **Environment details** (PHP version, Laravel version, package version)
- **Steps to reproduce** the issue
- **Expected vs actual behavior**
- **Code samples** or screenshots
- **Error messages** with full stack traces

#### ✨ Feature Requests

Use the feature request template and include:

- **Clear description** of the proposed feature
- **Use cases** and business justification
- **Proposed API** or implementation approach
- **Backwards compatibility** considerations

#### ❓ Questions

For questions and support:

- Check the **documentation** first
- Use **GitHub Discussions** for general questions
- Use **Issues** only for specific problems

## 🔄 Pull Request Process

### Before Submitting

1. **Create an issue** to discuss major changes
2. **Fork the repository** and create a feature branch
3. **Write tests** for your changes
4. **Update documentation** as needed
5. **Run the test suite** and ensure all tests pass
6. **Check code quality** with our tools

### PR Requirements

- ✅ **Descriptive title** following conventional commits
- ✅ **Detailed description** of changes made
- ✅ **Issue reference** (closes #123)
- ✅ **Tests included** for new functionality
- ✅ **Documentation updated** when needed
- ✅ **No breaking changes** without major version bump
- ✅ **Code quality checks** passing

### PR Template

```markdown
## Description

Brief description of the changes made.

## Type of Change

- [ ] Bug fix (non-breaking change that fixes an issue)
- [ ] New feature (non-breaking change that adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work)
- [ ] Documentation update

## Testing

- [ ] Tests added/updated
- [ ] All tests passing
- [ ] Manual testing completed

## Checklist

- [ ] Code follows the style guidelines
- [ ] Self-review of the code completed
- [ ] Documentation updated
- [ ] No new warnings introduced
```

### Review Process

1. **Automated checks** must pass (tests, code quality)
2. **Maintainer review** for code quality and design
3. **Community feedback** may be requested
4. **Approval and merge** by maintainers

## 🚢 Release Process

We follow **Semantic Versioning** (SemVer):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality
- **PATCH** version for backwards-compatible bug fixes

### Release Schedule

- **Patch releases**: As needed for critical bugs
- **Minor releases**: Monthly for new features
- **Major releases**: Annually or for breaking changes

### Changelog

All notable changes are documented in `CHANGELOG.md` following [Keep a Changelog](https://keepachangelog.com/) format.

## 🏆 Recognition

Contributors are recognized in:

- **CHANGELOG.md** for each release
- **README.md** contributors section
- **GitHub releases** with contributor highlights
- **Special thanks** for significant contributions

## 📞 Getting Help

- **Documentation**: [README.md](README.md)
- **Discussions**: GitHub Discussions for questions
- **Issues**: GitHub Issues for bugs and features
- **Security**: Email security@example.com for security issues

## 📜 License

By contributing to Livewire CRUD, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).

---

**Thank you for contributing to Livewire CRUD!** 🎉

Your contributions help make this package better for the entire Laravel community. Every contribution, no matter how small, is valuable and appreciated.
