## About Laravel Base

Laravel Base is a base project for Laravel developers looking
for a well-designed project architecture, respecting Laravel's 
folders structure, but with some layers that will allow you
to build an state-of-art Laravel monolithic application

## Dependencies

### [Laravel Auditing](http://www.laravel-auditing.com/)

This package will help you understand changes in your Eloquent models, by providing information
about possible discrepancies and anomalies that could indicate business concerns or suspect activities. [Laravel Auditing](http://www.laravel-auditing.com/)

### [Laravel Permission](https://spatie.be/docs/laravel-permission/v4/prerequisites)

This package allows you to manage user permissions and roles in a database.

### [Laravel Passport](https://laravel.com/docs/8.x/passport)

- php artisan passport:install

## Development

List of some useful commands for developers to use this package

- php artisan key:generate
- php artisan migrate
- php artisan passport:install --uuids
- php artisan db:seed
- php artisan db:seed --class=DemoSeeder

#### RUN
- php artisan serve
- php artisan queue:listen

#### RUN DEV
During the development phase, specially for front-end developers using our APIs,
it is common to run migrations and seeders multiple times.
The command bellow is a quality of life feature in this package that
will simplify the commands our developers need to run in order to use our application
in a CI/CD environment

- php artisan app:refresh (refresh database, seeds, demo seeds and passport credentials)
