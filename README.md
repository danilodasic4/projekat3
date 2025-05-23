# Symfony 6 Testing Project – Technical Inspection App

## Overview

This project is a full-featured web application built using the Symfony 6 framework, intended to simulate and manage the technical inspection process for vehicle owners.

Verified users can manage their vehicles, track registration expiry, receive email reminders, apply discounts, and schedule various appointments. Administrators can oversee all activity, manage users, and handle appointments via a dedicated admin panel.

## Key Features

### For Users (After Email Verification)

#### Track Vehicle Registration Expiry

Users can view when their vehicle registration is about to expire. An email notification is sent 30 days before the expiration.

#### Discounts on Registration Costs

When checking the price of registration, users can enter a discount code (`discount20`) to receive a discounted price, which is then displayed.

#### Vehicle Management (Car CRUD)

- Add new vehicles they own.
- Edit vehicle information such as make, model, year, and registration expiration.
- Soft-delete vehicles (remains hidden, not permanently deleted).
- View all owned vehicles through a dropdown menu.

#### Appointment Scheduling for a Specific Car

Users can schedule an appointment for their vehicle by selecting:

- Appointment Type: `maintenance`, `registration`, `polishing`, `painting`
- Date & Time

If no conflicting appointment exists at the selected time, the appointment is successfully booked. Otherwise, the user is asked to pick a different slot.

#### Appointment Overview

View all appointments across all owned cars, including time and type.

### For Admins

Admins log in at `/admin/login` and have the following capabilities:

#### Block / Unblock Users

Admins can ban users (setting a `banned_at` timestamp) and unblock them if needed.

#### Manage All Appointments

View all appointments scheduled by all users with the following details:

- Car (make & model)
- User email
- Appointment type
- Scheduled date & time
- Action: Mark appointment as "Finished" via a button (AJAX-enabled)

#### Upcoming & Archived Appointments

- **Upcoming**: Appointments scheduled in the future
- **Archived**: Past or marked-as-finished appointments

#### User Management Panel

View all registered users and their current status (active or banned).

## Project URL

You can access the application at:
http://dev.symfony-6-testing-project.com:84/

## Technologies

| Component        | Version  |
|-----------------|----------|
| MySQL           | 8.0.28   |
| Docker Compose  | 1.29.2   |
| Docker          | 27.3.1   |
| Apache          | 2.4.58   |
| Ubuntu          | 24.04 LTS |
| PHP             | 8.3.6 (CLI) |
| Symfony         | 6.x      |

## Installation

### 1. Clone the project
```sh
git clone <repo_url>
cd <project_directory>
```

### 2. Start Docker containers
```sh
docker-compose up -d
```

### 3. Install PHP dependencies
Inside the engine container, run the following commands:
```sh
docker exec -it symfony-6-testing-project_engine bash
composer install
```

### 4. Migrate the database
Run the database migrations:
```sh
php bin/console doctrine:migrations:migrate
```

### 5. Load Data Fixtures
To load the necessary data, including a default user for testing purposes, run:
```sh
php bin/console doctrine:fixtures:load
```

Once the fixtures are loaded, you can log in using the following credentials:

### Email Verification

After registering, users must verify their email address before gaining full access. Below are examples of verified and unverified accounts:

#### Verified User Login:
- **Email:** danilo@gmail.com
- **Password:** danilo

#### Unverified User Login:
- **Email:** user1@example.com
- **Password:** user123

#### Admin Login:
- **URL:** http://dev.symfony-6-testing-project.com:84/admin/login
- **Email:** admin@example.com
- **Password:** admin123

### 6. Start the local server
The Symfony application is available at:
http://dev.symfony-6-testing-project.com:84/

### API Documentation
Swagger API documentation is available at:
http://dev.symfony-6-testing-project.com:84/api/doc

## Application Components

### Controllers

#### Admin Controllers
- **AppointmentController**: Manages appointment-related actions.
- **CarController**: Handles administrative car-related actions.
- **HomeController**: Admin dashboard controller.
- **LoginController**: Handles admin login.
- **UserController**: Manages user-related actions in the admin panel.

#### General Controllers
- **AccessDeniedController**: Handles access denied responses.
- **ApiDocController**: Provides API documentation.
- **AppointmentController**: Manages user appointment scheduling.
- **CarController**: CRUD operations for cars.
- **HomeController**: Main landing page of the application.
- **LoginController**: Handles user authentication.
- **RegistrationController**: Handles user registration.
- **ResetPasswordController**: Manages password reset functionality.

### Commands
- **CarsExpiringRegistrationCommand**: Checks for cars with registrations expiring soon.
  ```sh
  php bin/console app:cars-expiring
  ```

### Entities
- **AbstractVehicle**: Base class for vehicle-related entities.
- **Admin**: Represents admin users.
- **Appointment**: Stores appointment details.
- **Car**: Represents vehicles with fields `id`, `make`, `model`, `year`, `registrationExpiresAt`.
- **ResetPasswordRequest**: Handles password reset requests.
- **User**: Stores user information including `id`, `email`, `password`, `profilePicture`, `roles`.
- **VerifyUser**: Manages email verification processes.

### Forms
- **RegistrationFormType**: Fields for user registration: `email`, `password`, `profile_picture`, `birthday`, `gender`, `newsletter`.

### Resolvers
- **CarValueResolver**: Automatically resolves the Car entity for routes based on the ID.

## New Features and Functionalities

### Admin Page
Added an admin login page at `/admin/login` for managing appointments and users.

### API Routes
Implemented various API routes for cars, appointments, and users. API routes are well documented in Swagger.

### Service Layer
Refactored application logic to use a service layer for better separation of concerns.

### Logging
Integrated logging using Symfony's logging component for tracking requests, errors, and exceptions.

### Custom Exceptions
Added custom exception handling to catch and log specific errors throughout the application.

### PHPUnit Tests
Added unit and functional tests using PHPUnit to ensure the application behaves as expected.

### Remember Me
Implemented "remember me" functionality during user login for persistent sessions.

### Email Sending
Added email functionality for user verification and notifications.

### Password Reset
Implemented password reset functionality with email notifications.

### Email Verification
Added functionality to verify user email addresses upon registration.

### Appointment Scheduling
Created an endpoint for users to schedule appointments with their cars.

### Redis Cache
Integrated Redis for caching user data and reducing database load.

### Parallel Processes
Utilized Symfony's Process component to run parallel tasks, such as checking user profile picture existence.

## Docker Configuration
```yaml
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
```

## Testing
```sh
php bin/phpunit
```

## Contributing
To contribute to this project:
1. Fork the repository.
2. Create a new branch:
```sh
git checkout -b feature/your-feature-name
```
3. Submit a Pull Request.

## License
This project is licensed under the MIT License.

