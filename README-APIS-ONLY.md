# Lift SaaS — APIs Only (controllers + routes)

**Generated:** 2025-10-12T21:25:18.079250 UTC

- Copy `routes/api.lift.php` into your project and add at end of `routes/api.php`:
  ```php
  require __DIR__.'/api.lift.php';
  ```
- Copy `app/Http/Controllers/Api/v1/*` into your project (merge folders).
- Make sure your **models, migrations, middleware, and tenancy scope** from earlier step exist.
- Rate limits / roles rely on your existing middleware config.
