# Technology Stack & Languages Used

## Programming Languages

### 1. **PHP** (Primary Backend Language)
- **Version:** PHP 8.2+
- **Framework:** Laravel 12.0
- **Usage:** 
  - All backend controllers (`app/Http/Controllers/`)
  - Models (`app/Models/`)
  - Services (`app/Services/`)
  - Policies (`app/Policies/`)
  - Notifications (`app/Notifications/`)
  - Commands (`app/Console/Commands/`)
  - Migrations (`database/migrations/`)
  - Tests (`tests/`)
  - Seeders (`database/seeders/`)

### 2. **JavaScript** (Frontend Interactivity)
- **Runtime:** Node.js (via npm)
- **Frameworks/Libraries:**
  - **Alpine.js** (v3.4.2) - Lightweight JavaScript framework for reactive UI
  - **Axios** (v1.11.0) - HTTP client for API requests
- **Usage:**
  - Frontend interactivity (`resources/js/app.js`)
  - Theme switching (`resources/js/theme.js`)
  - Bootstrap scripts (`resources/js/bootstrap.js`)

### 3. **CSS** (Styling)
- **Framework:** Tailwind CSS (v3.1.0)
- **Plugins:**
  - `@tailwindcss/forms` - Form styling
  - `autoprefixer` - CSS vendor prefixing
- **Usage:**
  - Application styles (`resources/css/app.css`)
  - Tailwind utilities and components

### 4. **SQL** (Database)
- **Database Systems:**
  - SQLite (development - `database/database.sqlite`)
  - MySQL 8.0 (production - configured in Docker)
- **Usage:**
  - Database migrations (Laravel migration files)
  - Raw queries in some controllers
  - Database seeders

### 5. **Blade Templates** (Laravel Templating)
- **Type:** PHP-based templating engine
- **Usage:**
  - All views (`resources/views/`)
  - Layouts, components, partials
  - Mixes PHP logic with HTML

### 6. **YAML** (Configuration)
- **Usage:**
  - Docker Compose configuration (`docker-compose.yml`)
  - CI/CD configuration (if any)

### 7. **JSON** (Configuration & Data)
- **Usage:**
  - Package configuration (`package.json`)
  - Composer configuration (`composer.json`)
  - API responses
  - Configuration files

### 8. **Markdown** (Documentation)
- **Usage:**
  - README files
  - Documentation files
  - Session summaries
  - Planning documents

### 9. **Shell/Bash** (Scripts)
- **Usage:**
  - Build scripts
  - Deployment scripts
  - Docker entrypoints

### 10. **INI** (Configuration)
- **Usage:**
  - PHP configuration (`docker/php/php.ini`)
  - Nginx configuration snippets

### 11. **Nginx Config** (Web Server)
- **Usage:**
  - Nginx server configuration (`docker/nginx/default.conf`)

---

## Technology Stack Breakdown

### Backend Stack
- **Language:** PHP 8.2+
- **Framework:** Laravel 12.0
- **Authentication:** Laravel Sanctum
- **Database:** SQLite (dev) / MySQL 8.0 (prod)
- **Cache/Queue:** Redis 7
- **Testing:** PHPUnit 11.5.3
- **Static Analysis:** PHPStan, Larastan

### Frontend Stack
- **CSS Framework:** Tailwind CSS 3.1.0
- **JavaScript Framework:** Alpine.js 3.4.2
- **HTTP Client:** Axios 1.11.0
- **Build Tool:** Vite 7.0.7
- **PostCSS:** 8.4.31

### Development Tools
- **Package Manager:** Composer (PHP), npm (JavaScript)
- **Containerization:** Docker & Docker Compose
- **Web Server:** Nginx
- **Mail Testing:** MailHog
- **Database Admin:** phpMyAdmin

### Code Quality Tools
- **PHP Code Style:** Laravel Pint, PHP CS Fixer
- **Static Analysis:** PHPStan 2.1, Larastan 3.7
- **Testing:** PHPUnit, Mockery

---

## File Extensions Summary

| Extension | Language/Framework | Count (Approx) |
|-----------|-------------------|----------------|
| `.php` | PHP/Laravel | 100+ files |
| `.blade.php` | Blade Templates | 50+ files |
| `.js` | JavaScript | 5+ files |
| `.css` | CSS | 2+ files |
| `.json` | JSON | 5+ files |
| `.yml` / `.yaml` | YAML | 1 file |
| `.md` | Markdown | 10+ files |
| `.sql` | SQL | 1+ files |
| `.ini` | INI Config | 1+ files |
| `.conf` | Nginx Config | 1 file |

---

## Language Distribution

```
PHP (Backend Logic)          ████████████████████ 70%
Blade Templates (Views)      ████████ 20%
JavaScript (Frontend)        ███ 5%
CSS (Styling)                ██ 3%
Other (Config, Docs)         ██ 2%
```

---

**Last Updated:** November 3, 2025  
**Project:** Enhanced Follow-up & Discipleship System

