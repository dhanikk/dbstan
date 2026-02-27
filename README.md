# DBStan - Laravel Database Schema Analysis & Insights  

The **DBStan** package provides detailed analysis and insights into your database schema for Laravel applications. It helps identify structural issues, missing indexes, normalization problems, nullable column risks, foreign key inconsistencies, and performance concerns.

It is an essential tool for **debugging, optimizing, reviewing, and maintaining a healthy database architecture** in Laravel projects.

---

## **Important Notice: Configure Database Before Using This Package**  

Before using this package, ensure your database connection is properly configured in your Laravel application.

If the database is not configured correctly, DBStan will not be able to analyze your schema.

Make sure your `.env` file contains valid database credentials.

---

## **Security Warning**

This package exposes detailed database schema analysis.  
It is intended for **admin and development use only**.

Do **NOT** expose this tool publicly in production without proper access restrictions, as schema details may reveal sensitive structural information.

---

<p>  
<a href="https://packagist.org/search/?tags=laravel" target="_blank" rel="noopener noreferrer">#Laravel</a>&nbsp;  
<a href="https://packagist.org/search/?tags=database" target="_blank" rel="noopener noreferrer">#Database</a>&nbsp;  
<a href="https://packagist.org/search/?tags=schema" target="_blank" rel="noopener noreferrer">#Schema</a>&nbsp;  
<a href="https://packagist.org/search/?tags=php" target="_blank" rel="noopener noreferrer">#PHP</a>&nbsp;  
<a href="https://packagist.org/search/?tags=performance" target="_blank" rel="noopener noreferrer">#Performance</a>&nbsp;  
<a href="https://packagist.org/search/?tags=optimization" target="_blank" rel="noopener noreferrer">#Optimization</a>&nbsp;  
<a href="https://packagist.org/search/?tags=analysis" target="_blank" rel="noopener noreferrer">#Analysis</a>  
</p>  

---

## Documentation  

- [Features](#features)  
- [Supported Versions](#supported-versions)  
- [Installation](#installation)  
- [Commands](#commands)  
    - [Run Analysis](#run-analysis)  
    - [Export Report](#export-report)  
- [Output Categories](#output-categories)  
- [Environment Configuration](#environment-configuration)  
- [FAQs](#faqs)  
- [Contributing](#contributing)  
- [Security Vulnerabilities](#security-vulnerabilities)  
- [License](#license)  
- [Testing](#testing)  
- [Support](#get-support)  

---

## **Features**

- Analyze full database schema structure
- Detect missing indexes on foreign keys and log tables
- Identify nullable column overuse and high NULL value ratios
- Detect normalization and integrity issues (duplicate rows, orphan risks, improper foreign key naming)
- Audit trail checks (created_by, updated_by, deleted_by columns)
- Detect repeated common fields across tables
- Identify tables with too many columns or wide VARCHARs
- Highlight performance risks (large TEXT columns, JSON overuse, unbounded growth, table size)
- Detect improper pivot table structures
- Identify enum and boolean overuse
- Detect mixed domain columns (e.g., info/data/details in varchar)
- Check for missing soft deletes and timestamps
- Detect status columns missing indexes
- Detect polymorphic relation overuse and missing indexes
- Lightweight and optimized for fast schema scanning
- Supports Laravel 9, 10, and 11 with PHP 8+ compatibility
- CLI-based analysis with structured categorized output

---

## **Supported Versions**

- **PHP:** ^8.0  
- **Illuminate Support:** ^9.0 | ^10.0 | ^11.0  

---

## **Installation**

To install the package, run:

```bash
composer require itpathsolutions/dbstan
```

---

## **Commands**

### **Vendor publish(Optional)**

After installing the package, you may publish the configuration file using:

```bash
php artisan vendor:publish --tag=dbstan-config
```

This will create the configuration file at:

```bash
config/dbstan.php
```

You can customize thresholds like:

- Maximum columns per table
- Maximum VARCHAR length
- JSON column limits
- Large table size threshold
- Nullable ratio threshold

---

### **Run Analysis for Production**

To analyze your database schema:

```bash
php artisan dbstan:analyze
```

or

```bash
http://127.0.0.1:8000/dbstan
```

These both scans your entire database and displays categorized results in the browser or terminal.

---

## **Environment Configuration**

Ensure your `.env` file contains:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

After updating configuration:

```bash
php artisan config:clear
```

---

## **FAQs**

### 1. What does this package do?

DBStan analyzes your Laravel database schema and detects structural, normalization, and performance issues.

---

### 2. Does it modify my database?

No. DBStan is completely read-only.  
It does **NOT** make any changes to your database.

---

### 3. Is it safe for production?

Yes, but it is recommended to use it in development or staging environments.  
Avoid exposing schema analysis publicly.