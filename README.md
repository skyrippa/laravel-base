## About Laravel Base

Laravel Base is a base project for Laravel developers looking for a well-designed project architecture, respecting Laravel's 
folders structure, but with some layers that will allow you to build a state-of-art Laravel monolithic application.<br>
This project consists on an RESTful API with ACL based on permissions and roles, with a main SUPER ADMIN, and a CLIENT role
that would be using our product.
The CLIENT user has specific permissions and middlewares for its own endpoints.

### Client
The application client, as mentioned above, is a role set in the application either to serve as guide for developers looking to
create their own roles based on their application's needs, or ready-to-use for any developer that wishes to improve this role's
permissions. On this base project, there is only one resource, which will be better described bellow.

### Addresses
This is the only resource which the Client users can interact with on this base project. There are permissions configured for each client to have
their own addresses, secured with an auditory and ACL system already configured in the project. Any client user can do all CRUD operations,
as well as soft deleting addresses, restoring them, and retrieving auditory for each address.

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
