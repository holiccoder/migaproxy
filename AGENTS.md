<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.29
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `mcp-development` — Develops MCP servers, tools, resources, and prompts. Activates when creating MCP tools, resources, or prompts; setting up AI integrations; debugging MCP connections; working with routes/ai.php; or when the user mentions MCP, Model Context Protocol, AI tools, AI server, or building tools for AI assistants.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

### Project Overview

This is an API-first headless SaaS application (proxy reseller) with a Filament v5 admin panel. The user-facing frontend is a Next.js app in `frontend/` that consumes the versioned REST API, while Laravel web routes in this app still redirect to `/admin/login`.

### Dual Authentication

The app uses two separate auth guards and models (`config/auth.php`):
- **`User`** (`App\Models\User`) — API consumers authenticated via Sanctum tokens. Routes: `routes/api.php`.
- **`Admin`** (`App\Models\Admin`) — Filament panel users authenticated via the `admin` session guard. Panel: `app/Providers/Filament/AdminPanelProvider.php`.

Never mix these models or guards. API controllers expect `User`; Filament operates on `Admin`.

### Key Directories

- `app/Actions/{Domain}/` — Single-purpose action classes with an `execute()` method (e.g., `AdminReplyToTicket`).
- `app/Services/{Domain}/` — Stateless service classes for complex business logic (Payments, Affiliates, Api/IPmart).
- `app/Contracts/Payments/` — Payment gateway interface. New gateways implement `PaymentGateway` and are registered in `config/payments.php`.
- `app/Http/Controllers/Api/V1/` — All API controllers live under the `V1` namespace. Follow this versioning convention.
- `app/Http/Requests/Api/V1/` — Form Requests for API validation, using **array-based** rules (not string-based).
- `app/Filament/Resources/{ModelPlural}/` — Filament resources with subdirectories (see Filament section below).
- `app/Console/Commands/` — Artisan commands; scheduled in `routes/console.php`.
- `app/Events/` & `app/Listeners/` — Event-driven integrations (e.g., `UserRegistered` → `CreateIpmartAccountForRegisteredUser`).
- `frontend/` — Next.js frontend application.

### Frontend Scope (Next.js)

- For frontend tasks, inspect and modify files under `frontend/**` by default.
- Run frontend commands from `frontend/` (for example: `npm run dev`, `npm run build`, `npm run lint`, `npm run test`).
- Do not edit Laravel backend files (`app/`, `routes/`, `config/`, `database/`, `resources/`) unless the task explicitly requires backend/API changes.

### Scheduled Commands

Defined in `routes/console.php`:
- `subscriptions:expire` — runs daily to expire ended subscriptions.
- `tickets:close-stale` — runs daily to auto-close inactive tickets.

### External Integrations

- **IPmart API** (`app/Services/Api/IPmart/DataRequest.php`) — proxy provisioning service; auto-creates accounts on user registration.
- **Social Auth** (`laravel/socialite`) — GitHub, Google, and X (Twitter) OAuth login.
- **SEO** (`ralphjsmit/laravel-seo`) — attached to blog posts via the `seo` table.
- **Chinese Payment Gateways** (`yansongda/laravel-pay`, `config/pay.php`) — Alipay, WeChat Pay, UnionPay configuration.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.
- Tests mirror the app structure: `tests/Feature/Api/V1/` for API tests, `tests/Feature/Actions/` for action tests, `tests/Feature/Console/` for command tests. Place new tests in the matching subdirectory.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.
- Define status/type values as `public const` on the model (e.g., `Order::STATUS_PENDING`, `Ticket::STATUS_OPEN`, `Subscription::STATUS_ACTIVE`). Always reference these constants — never use raw string literals for status comparisons.

=== filament/v5 rules ===

# Filament v5 Admin Panel

- The admin panel is at `/admin`, uses the `admin` guard, and is configured in `app/Providers/Filament/AdminPanelProvider.php`.
- Resources live under `app/Filament/Resources/{ModelPlural}/` with this subdirectory structure:

```
app/Filament/Resources/Plans/
├── PlanResource.php          # Resource class — delegates form/table to dedicated classes
├── Pages/
│   ├── ListPlans.php
│   ├── CreatePlan.php
│   └── EditPlan.php
├── Schemas/
│   └── PlanForm.php          # Static configure(Schema $schema) method
└── Tables/
    └── PlansTable.php        # Static configure(Table $table) method
```

- Form/table logic is extracted into `Schemas/{Model}Form.php` and `Tables/{ModelPlural}Table.php` with a `public static function configure(...)` method. The resource delegates: `return PlanForm::configure($schema);`.
- Navigation groups are set via `$navigationGroup` (e.g., `'Billing'`, `'Support'`).
- IMPORTANT: Always use `search-docs` for Filament v5 API — v5 has significant changes from v3/v4 (e.g., `Filament\Schemas\Schema` replaces `Filament\Forms\Form`).

=== mcp/core rules ===

# Laravel MCP

- Laravel MCP allows you to rapidly build MCP servers for your Laravel applications.
- IMPORTANT: laravel/mcp is very new. Always use the `search-docs` tool for authoritative documentation on writing and testing Laravel MCP servers, tools, resources, and prompts.
- IMPORTANT: Activate `mcp-development` every time you're working with an MCP-related task.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.
</laravel-boost-guidelines>
