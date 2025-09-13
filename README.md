Laravel & Vue.js Project Documentation
Prerequisites
PHP >= 8.1
Composer
Node.js >= 16.x
npm or yarn
MySQL/PostgreSQL
XAMPP (for local development)
Project Setup
1. Clone the Repository
git clone <repository-url>
cd ds_codebase
2. Backend Setup (Laravel)
Install PHP Dependencies
composer install
Environment Configuration
Copy the environment file:

cp .env.example .env
Generate application key:

php artisan key:generate
Configure your database in .env file:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
Database Setup
Create a new database in MySQL/PostgreSQL
Run migrations:
php artisan migrate:fresh --seed
3. Frontend Setup (Vue.js)
Install Node Dependencies
npm install
# or
yarn install

#if npm install not working then try below command
npm install --legacy-peer-deps
Configure your API URL in .env:
VITE_API_URL=http://localhost/salon_buddy
Running the Application
Start Backend Server
The Laravel application will be available at http://localhost/salon_buddy

Start Frontend Development Server
npm run dev
# or
yarn dev
Common Issues and Solutions
Database Connection Issues
Ensure MySQL/PostgreSQL service is running
Verify database credentials in .env
Check if database exists
Frontend Build Issues
Clear npm cache: npm cache clean --force
Delete node_modules and reinstall:
rm -rf node_modules
npm install
Laravel Issues
Clear Laravel cache:
php artisan config:clear
php artisan cache:clear
php artisan view:clear
Development Guidelines
Code Style
Follow PSR-12 for PHP code
Use ESLint and Prettier for JavaScript/Vue code
Run npm run lint before committing changes
Git Workflow
Create feature branch from develop
Make changes and commit
Push to remote
Create pull request
Deployment
Build frontend assets:

npm run build
Configure production environment

Set up web server (Apache/Nginx)
Configure SSL certificates
Set up CI/CD pipeline
Support
For any issues or questions, please contact the development team or create an issue in the repository.

If login submission is not working then should to check .env file below information as like below formate.
APP_URL=http://localhost/salon_buddy ASSET_URL=http://localhost/salon_buddy/public/ VITE_BASE_URL=http://localhost/salon_buddny VITE_APP_URL=http://localhost/salon_buddy

VITE_APP_NAME="${APP_NAME}" VITE_API_BASE_URL="${APP_URL}/api/" VITE_MAPBOX_ACCESS_TOKEN=
