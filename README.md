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

✔ Analyze full database schema structure
✔ Detect missing indexes on foreign keys and log tables
✔ Identify nullable column overuse and high NULL value ratios
✔ Detect normalization and integrity issues (duplicate rows, orphan risks, improper foreign key naming)
✔ Audit trail checks (created_by, updated_by, deleted_by columns)
✔ Detect repeated common fields across tables
✔ Identify tables with too many columns or wide VARCHARs
✔ Highlight performance risks (large TEXT columns, JSON overuse, unbounded growth, table size)
✔ Detect improper pivot table structures
✔ Identify enum and boolean overuse
✔ Detect mixed domain columns (e.g., info/data/details in varchar)
✔ Check for missing soft deletes and timestamps
✔ Detect status columns missing indexes
✔ Detect polymorphic relation overuse and missing indexes
✔ Lightweight and optimized for fast schema scanning
✔ Supports Laravel 9, 10, and 11 with PHP 8+ compatibility
✔ CLI-based analysis with structured categorized output

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

### **Run Analysis**

To analyze your database schema:

```bash
php artisan dbstan:analyze
```

This command scans your entire database and displays categorized results in the terminal.

---


## **Output Categories**


Each check is designed to catch specific schema issues. Here’s what some of the key checks do:

- **Audit Trail Check:** Ensures tables have `created_by`, `updated_by`, and `deleted_by` columns for tracking changes and maintaining data integrity.
- **JSON Column Overuse:** Warns if a table uses too many JSON columns, which can hurt performance and maintainability.
- **Boolean Overuse:** Flags tables with excessive boolean/tinyint(1) columns, suggesting use of ENUM or state machine instead.
- **Polymorphic Relation Overuse:** Detects multiple polymorphic relations in a table and missing composite indexes for them.
- **Foreign Key Cascading Rules:** Identifies foreign keys using `NO ACTION` or `RESTRICT` on delete, which may cause orphaned records.
- **Duplicate Rows Risk:** Warns if a table lacks PRIMARY or UNIQUE constraints, risking duplicate data.
- **Foreign Key Naming Convention:** Flags columns that look like foreign keys but don’t follow the `_id` naming convention.
- **Possible Orphan Risk:** Detects `_id` columns without foreign key constraints or nullable foreign keys.
- **Unique Constraint Violations:** Finds columns (like `email`, `slug`) with duplicate values where uniqueness is expected.
- **Log Table Indexing:** Ensures `created_at` and `user_id` are indexed in log tables for query performance.
- **Missing Foreign Key Indexes:** Flags foreign key columns missing indexes.
- **High NULL Value Ratio:** Warns if nullable columns have a high percentage of NULLs.
- **Status Column Index:** Checks if `status`/`state` columns are indexed for efficient filtering.
- **Table Size Analysis:** Alerts if a table exceeds a size threshold (e.g., 100MB).
- **Unbounded Growth Risk:** Detects tables likely to grow indefinitely (log/event/audit) without proper indexing.
- **Data Type Appropriateness:** Flags columns (e.g., `price`) using inappropriate types (e.g., INT instead of DECIMAL).
- **Enum Overuse:** Warns about too many ENUM columns or ENUMs with too many values.
- **Large TEXT Columns:** Flags large TEXT columns that may impact performance.
- **Missing Soft Deletes:** Warns if `deleted_at` is missing for soft deletes.
- **Missing Timestamps:** Warns if `created_at`/`updated_at` are missing.
- **Mixed Domain Columns:** Detects columns storing mixed data types (e.g., info/data/details in varchar).
- **Nullable Column Overuse:** Flags nullable columns without clear justification.
- **Pivot Table Structure:** Detects improper pivot table design (should have exactly two FK columns, no id/timestamps).
- **Repeated Common Fields:** Flags repeated fields (email, phone, etc.) across tables.
- **Too Many Columns:** Warns if a table has more columns than recommended.
- **Wide Varchar Columns:** Flags VARCHAR columns exceeding recommended length.

### ❌ ERROR
Critical schema problems that require immediate attention:
- Foreign key without index
- Broken foreign key relationships
- Invalid constraints
- Duplicate rows risk (no PRIMARY or UNIQUE)

### ⚠️ WARNING
Structural or design concerns:
- Too many nullable columns
- Overuse of JSON, ENUM, or boolean columns
- Excessive number of columns in a table
- Wide VARCHAR columns
- Improper pivot table structure
- Mixed domain columns (e.g., info/data/details in varchar)
- Improper foreign key naming
- Polymorphic relation overuse

### 💡 SUGGESTION
Improvement recommendations:
- Missing timestamps
- Missing soft deletes
- Repeated fields across tables
- Data type appropriateness (e.g., price as INT)
- Status columns missing indexes

### 🚨 PERFORMANCE RISK
Potential performance bottlenecks:
- Large TEXT fields
- Unindexed foreign keys or log tables
- High NULL value ratio
- Table size exceeds threshold
- Unbounded growth risk (log/event/audit tables)
- Heavy JSON usage

### 🛡️ INTEGRITY
Referential and data integrity issues:
- Orphan risk (missing or nullable foreign keys)
- Foreign key cascading rules (NO ACTION/RESTRICT)
- Unique constraint violations (duplicate values)

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

### 4. Can I use this in CI/CD?

Yes. You can integrate:

```bash
php artisan dbstan:analyze
```

into your CI pipeline to maintain schema quality standards.

---

### 5. Which Laravel versions are supported?

Laravel 9, 10, and 11 with PHP 8+ compatibility.

---

## **Contributing**

We welcome contributions from the community!

Feel free to **Fork** the repository and submit a Pull Request.

Please refer to the [CONTRIBUTING](https://github.com/your-username/dbstan/blob/main/CONTRIBUTING.md) guidelines for details.

---

## **Security Vulnerabilities**

If you discover any security vulnerability, please report it responsibly through the repository's security policy.

---

## **License**

This package is open-source and available under the MIT License.

---

## **Get Support**  
- Feel free to [contact us](https://www.itpathsolutions.com/contact-us/) if you have any questions.  
- If you find this project helpful, please give us a ⭐ [Star](https://github.com/dhanikk/dbstan/stargazers).  

## **You may also find our other useful packages:**  
- [MySQL Info Package 🚀](https://packagist.org/packages/itpathsolutions/mysqlinfo)  
- [PHP Info Package 🚀](https://packagist.org/packages/itpathsolutions/phpinfo)  
- [Role Wise Session Manager Package 🚀](https://packagist.org/packages/itpathsolutions/role-wise-session-manager)  
- [Authinfo - User Login Tracker 🚀](https://packagist.org/packages/itpathsolutions/authinfo)   
- [Chatbot Package 🚀](https://packagist.org/packages/itpathsolutions/chatbot)   