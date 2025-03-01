```
                                  🍊 Syracuse DataDay 🍊
                     A comprehensive data platform for the Salt City

```

```
____                                          ____        _        ____
  / ___| _   _ _ __ __ _  ___ _   _ ___  ___  |  _ \  __ _| |_ __ _|  _ \  __ _ _   _
  \___ \| | | | '__/ _` |/ __| | | / __| / _ \ | | | |/ _` | __/ _` | | | |/ _` | | | |
   ___) | |_| | | | (_| | (__| |_| \__ \|  __/ | |_| | (_| | || (_| | |_| | (_| | |_| |
  |____/ \__, |_|  \__,_|\___|\__,_|___/ \___| |____/ \__,_|\__\__,_|____/ \__,_|\__, |
         |___/                                                                      |___/
```

## 🏛️ About

Syracuse DataDay is a Laravel-based application that aggregates and analyzes various datasets from the City of Syracuse. From property assessments to code violations, this platform provides a comprehensive view of Syracuse's urban landscape.

## 📊 Datasets

### Property Assessments

-   Complete property assessment records
-   Tax information
-   Property classifications
-   Valuation data

### Code Violations

-   Violation records
-   Compliance dates
-   Inspector assignments
-   Status tracking

### Parcel Maps

-   Geographic coordinates
-   Property dimensions
-   Land use classifications
-   Neighborhood data

### Rental Registry

-   Registration status
-   Inspection records
-   Validity periods
-   Property owner information

### Vacant Properties

-   Vacancy status
-   VPR certifications
-   Neighborhood mapping
-   Owner details

## 🛠️ Technical Stack

-   Laravel Framework
-   MariaDB
-   PHP 8.x
-   CSV Data Processing

## 🚀 Getting Started

1. Clone the repository:

```bash
git clone https://github.com/rbur0425/syracuse-dataday.git
```

2. Install dependencies:

```bash
composer install
```

3. Set up your environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations:

```bash
php artisan migrate
```

5. Seed the database:

```bash
php artisan db:seed
```

## 📁 Data Structure

```
storage/
└── app/
    └── dataday/
        ├── Assessment_Final_Roll_(2024).csv
        ├── code_violations.csv
        ├── parcel_map.csv
        ├── rental_registry.csv
        └── vacant_properties.csv
```

## 🏆 Features Coming Soon

-   Interactive data visualization
-   Neighborhood analytics
-   Property status tracking
-   Geographic mapping interface
-   Trend analysis tools

## 🎓 Go Orange!

Built with pride in Syracuse, home of the Orange 🍊

## 📄 License

This project is licensed under the MIT License - see the LICENSE.md file for details.

---

```
    _____
   /     \
  /       \
 /__________\
    |  |  |
    |  |  |
    |  |  |
  Carrier Dome
```

_"Knowledge crowns those who seek to understand it" - Syracuse University Motto_
