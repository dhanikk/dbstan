# DBStan - Laravel Database Schema Analysis & Insights  

[![Latest Version](https://img.shields.io/packagist/v/itpathsolutions/dbstan.svg?style=flat-square)](https://packagist.org/packages/itpathsolutions/dbstan)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/itpathsolutions/dbstan.svg?style=flat-square)](https://packagist.org/packages/itpathsolutions/dbstan)
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue?style=flat-square)]()
[![Laravel](https://img.shields.io/badge/laravel-9%20to%2013-red?style=flat-square)]()

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
    - [Vendor Publish](#vendor-publish)  
    - [Run Analysis for Production](#run-analysis-for-production)  
- [Output Categories](#output-categories)  
- [Environment Configuration](#environment-configuration)  
- [FAQs](#faqs)  
- [Contributing](#contributing)  
- [Security Vulnerabilities](#security-vulnerabilities)  
- [License](#license)

---

## **Features**

- Analyze full database schema structure
- Detect missing indexes on foreign keys and log tables
- Identify nullable column overuse and high NULL value ratios
- Detect normalization and integrity issues (duplicate rows, orphan risks, improper foreign key naming)
- Audit trail checks (created_by, updated_by, deleted_by columns)
- Detect repeated common fields across tables
- Identify tables with too many columns or wide VARCHARs
- Detect tables missing a PRIMARY KEY
- Highlight performance risks (large TEXT columns, JSON overuse, unbounded growth, table size)
- Detect storage engine issues (non-InnoDB tables and mixed engines)
- Detect charset/collation mismatches (utf8 vs utf8mb4 and inconsistent collations)
- Analyze auto-increment overflow risk on high-growth tables
- Identify low-cardinality indexes that may be ineffective
- Recommend composite indexes for common filtering patterns (e.g., user_id + status)
- Analyze overall database size and table storage dominance
- Detect improper pivot table structures
- Identify enum and boolean overuse
- Detect mixed domain columns (e.g., info/data/details in varchar)
- Check for missing soft deletes and timestamps
- Detect datatype mismatch using column-name heuristics (price/amount, email, *_at, is_/has_)
- Detect status columns missing indexes
- Detect polymorphic relation overuse and missing indexes
- Lightweight and optimized for fast schema scanning
- Supports Laravel 9, 10, 11, 12, and 13 with PHP 8.1+ compatibility
- CLI-based analysis with structured categorized output

---

## **Supported Versions**

- **PHP:** ^8.1 | ^8.2 | ^8.3 | ^8.4 | ^8.5  
- **Illuminate Support:** ^9.0 | ^10.0 | ^11.0 | ^12.0 | ^13.0  

---

## **Installation**

To install the package, run:

```bash
composer require itpathsolutions/dbstan
```

<button type="button" onclick="navigator.clipboard.writeText('composer require itpathsolutions/dbstan')">Copy Install Command</button>

---

## **Commands**

### **Vendor Publish**

After installing the package, you may publish the configuration file using:

```bash
php artisan vendor:publish --tag=dbstan-config
```

<button type="button" onclick="navigator.clipboard.writeText('php artisan vendor:publish --tag=dbstan-config')">Copy Vendor Publish Command</button>

This will create the configuration file at:

```bash
config/dbstan.php
```

You can customize thresholds like:

- Maximum columns per table
- Maximum VARCHAR length
- JSON column limits
- Large table size threshold
- Total database size threshold
- Unusually large table ratio threshold
- Table dominance ratio threshold
- Nullable ratio threshold
- Auto-increment risk thresholds
- Index cardinality thresholds

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

Both options scan your entire database and display categorized results in the browser or terminal.

---

## **Output Categories**

DBStan organizes its findings into four main categories:

### 1. **Structure Issues**
- Tables with too many columns
- Wide VARCHAR fields
- Missing timestamps or soft deletes
- Boolean and enum overuse
- Nullable column overuse
- Large TEXT columns
- Data type appropriateness
- Missing primary key detection
- Mixed domain columns
- Repeated common fields across tables
- Pivot table structure issues

### 2. **Integrity Issues**
- Duplicate rows detection
- Foreign key naming inconsistencies
- Cascading action problems
- Possible orphan risks
- Unique constraint violations

### 3. **Performance Issues**
- Missing indexes on foreign keys
- Missing indexes on log tables
- Missing indexes on status columns
- High NULL value ratios
- Table size analysis
- Database size threshold analysis
- Unusually large table and table-dominance detection
- Auto-increment overflow and growth risk analysis
- Low-cardinality/useless index detection
- High-cardinality index misuse heuristics (standalone status/flag indexes)
- Composite index recommendations (user_id + status/state)
- Unbounded growth risks

### 4. **Architecture Issues**
- Audit trail implementation (created_by, updated_by, deleted_by)
- JSON column overuse
- Polymorphic relation overuse
- Storage engine consistency (InnoDB and mixed-engine detection)
- Charset and collation consistency checks

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

---

## **Contributing**

Contributions are welcome!  

If you'd like to contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

Please ensure:
- Your code follows PSR-12 coding standards
- All tests pass before submitting
- You include tests for new features

For major changes, please open an issue first to discuss what you would like to change.

---

## **Security Vulnerabilities**

If you discover a security vulnerability within DBStan, please send an email to [enquiry@itpathsolutions.com](mailto:enquiry@itpathsolutions.com).  

All security vulnerabilities will be promptly addressed.

For more details, see our [Security Policy](SECURITY.md).

---

## **License**

DBStan is open-sourced software licensed under the [MIT license](LICENSE).
