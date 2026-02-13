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
✔ Detect missing indexes on foreign keys  
✔ Identify nullable column risks  
✔ Detect normalization issues  
✔ Detect repeated fields across tables  
✔ Identify tables with too many nullable columns  
✔ Highlight performance risks (TEXT, JSON overuse, etc.)  
✔ Detect foreign key inconsistencies  
✔ Identify unused or suspicious tables  
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

DBStan categorizes findings into the following types:

### ❌ ERROR
Critical schema problems that require immediate attention:
- Foreign key without index  
- Broken foreign key relationships  
- Invalid constraints  

### ⚠️ WARNING
Structural or design concerns:
- Too many nullable columns  
- Overuse of JSON columns  
- Excessive number of columns in a table  

### 💡 SUGGESTION
Improvement recommendations:
- Missing timestamps  
- Repeated fields across tables  
- Normalization opportunities  

### 🚨 PERFORMANCE RISK
Potential performance bottlenecks:
- Large TEXT fields  
- Unindexed foreign keys  
- Repeated indexed fields  
- Heavy JSON usage  

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