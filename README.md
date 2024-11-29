Symfony 6 Testing Project
Overview

This project is built using the Symfony 6 framework, leveraging Docker for container orchestration, with API documentation supported via Swagger (NelMioApiDocBundle).
The application includes user management, vehicle management, authentication, and features custom commands and resolvers.
Project URL

You can access the application at:
http://dev.symfony-6-testing-project.com:84/
Technologies
Component	Version
MySQL	8.0.28
Docker Compose	1.29.2
Docker	27.3.1
Apache	2.4.58
Ubuntu	24.04 LTS
PHP	8.3.6 (CLI)
Symfony	6.x

Installation
1. Clone the project

git clone <repo_url>
cd <project_directory>

2. Start Docker containers

docker-compose up -d

3. Install PHP dependencies

Inside the engine container, run the following commands:

docker exec -it symfony-6-testing-project_engine bash
composer install

4. Migrate the database

Run the database migrations:

php bin/console doctrine:migrations:migrate

5.Load Data Fixtures

To load the necessary data, including a default user for testing purposes, run the following command inside the engine container:

php bin/console doctrine:fixtures:load

Once the fixtures are loaded, you can log in using the following credentials:

    Email: user@example.com
    Password: user123

These credentials can be used to access the application and test various features.

6. Start the local server

The Symfony application is available at:
http://dev.symfony-6-testing-project.com:84/
API Documentation

Swagger API documentation is available at:
http://dev.symfony-6-testing-project.com:84/api/doc


Application Components
Controllers

    HomeController
        Route: /
        Description: Main landing page of the application.
        Swagger: Implemented.

    CarController
        Routes: /cars, /cars/{id}.
        Description: CRUD operations for car management.
        Swagger: Implemented.

    LoginController
        Routes: /login, /logout.
        Description: Handles user authentication.
        Swagger: Implemented.

    RegistrationController
        Route: /register.
        Description: Handles user registration.
        Swagger: Implemented.

Commands

    CarsExpiringRegistrationCommand
        Description: Checks for cars with registrations expiring soon.
        Run the command:

        php bin/console app:cars-expiring

Entities

    Car
        Fields: id, make, model, year, registrationExpiresAt.
        Repository: CarRepository.

    User
        Fields: id, email, password, profilePicture, roles.
        Repository: UserRepository.

Forms

    RegistrationFormType
        Fields for user registration: email, password, profile_picture, birthday, gender, newsletter.

Resolvers

    CarValueResolver
        Automatically resolves the Car entity for routes based on the id.

----------------------
Directory Structure

    src/Controller/
    Contains all the application controllers.

    src/Command/
    Custom Symfony commands.

    src/Entity/
    Doctrine entities.

    src/Form/
    Symfony form definitions.

    src/Resolver/
    Custom parameter resolvers.

----------------------
Docker Configuration
docker-compose.yml

version: '2'

networks:
    default:
    symfony-6-testing-project_default:
        external:
            name: symfony-6-testing-project_default

services:
    front:
        image: nginx:alpine
        container_name: symfony-6-testing-project_front
        ports:
            - 84:80
        volumes:
            - .:/var/www:rw
            - ./config/docker/front/symfony-6-testing-project.conf:/etc/nginx/conf.d/symfony-6-testing-project.conf:ro
        working_dir: /var/www
        networks:
            default:
                aliases:
                    - dev.symfony-6-testing-project.com

    db:
        image: mysql/mysql-server:8.0.28
        container_name: symfony-6-testing-project_db
        command: --default-authentication-plugin=mysql_native_password
        environment:
            MYSQL_ROOT_HOST: '%'
            MYSQL_ALLOW_EMPTY_PASSWORD: 'true'
        ports:
            - 3307:3306

    engine:
        build: ./config/docker/engine/
        container_name: symfony-6-testing-project_engine
        ports:
            - 3001:3001
        volumes:
            - .:/var/www:rw
            - ./config/docker/engine/php.ini:/usr/local/etc/php/conf.d/custom.ini:ro
            - ./public/uploads/profile_pictures:/var/www/html/public/uploads/profile_pictures:rw  
        working_dir: /var/www
        networks:
            - default
            - symfony-6-testing-project_default


Testing

php bin/phpunit

Contributing

To contribute to this project:

    Fork the repository.
    Create a new branch:

git checkout -b feature/your-feature-name

Submit a Pull Request.


License

This project is licensed under the MIT License.