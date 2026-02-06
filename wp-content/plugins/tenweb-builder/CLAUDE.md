# Tenweb Builder Development Guide

## Build Commands
- `npm run build` or `gulp build` - Build all assets
- `gulp default` - Alias for build command
- Use individual tasks from gulpfile.js for targeted builds (e.g., `gulp editorCSSTask`)

## Coding Standards

### PHP
- PSR-12 code style with WordPress VIP compatibility
- Class names: PascalCase (`class Helper`)
- Method names: camelCase (`public function clearSiteCache()`)
- Use type hints and PHPDoc comments for functions
- Properly sanitize user inputs (`sanitize_text_field()`, `esc_html()`)
- Use namespaces (`namespace Tenweb_Builder\Modules`)

### JavaScript
- Use ES6 features when possible
- Prefer function declarations over expressions
- Prefix variables and functions with module name
- Properly document functions
- Use semicolons consistently

### CSS
- BEM methodology for class naming
- Mobile-first approach
- Use SCSS preprocessing
- Keep selector specificity to a minimum

### Best Practices
- Add phpcs:ignore comments when needed with explanations
- Handle errors with proper error types and messages
- Use proper escaping for outputs
- Follow WordPress hook and filter naming conventions