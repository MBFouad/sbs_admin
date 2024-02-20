# SBS Symfony Application

This is a Symfony application designed to [brief description of the application].

## Prerequisites

Before you begin, ensure you have met the following requirements:

- PHP >= 8.1
- Composer (https://getcomposer.org/)
- MySQL or another supported database system

## Installation Steps

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/MBFouad/sbs_admin.git
   cd  sbs_admin

2. **Install Dependencies:**
   ```bash
   composer install
   ```
3. **Configure Environment Variables:**

   Create a copy of the .env file: cp .env.dist .env
   Update the database connection parameters in .env to match your database setup.
4. **Create the Database:**
   ```bash
   php bin/console doctrine:database:create
   ```
5. **Run Migrations:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
6. **Start the Development Server:**
   ```bash
    php bin/console server:start
    ```

## Importing Sample Data

To import sample data, run the following command:

```bash
php bin/console ugo:orders:import
``` 

## Testing

### Execute tests using PHPUnit:

   ```bash
   ./vendor/bin/phpunit
   ```

## Cross-Origin Request Blocked:

currently the application accept request from localhost:[0-9]+ to change that you can update the
config/packages/nelmio_cors.yaml file to accept request from any origin

### development steps

- composer install
- bin/console make:entity
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate
- sudo apt-get install sqlite3
- sudo apt-get install php8.1-sqlite3

Cross-Origin Request Blocked:

The Same Origin Policy disallows reading the remote resource at http://localhost:8000/api/employees. (Reason: CORS
request did not succeed).