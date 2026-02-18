# Event Booking System - Backend API

A comprehensive REST API for an event booking system built with Laravel 11, featuring authentication, event management, ticketing, bookings, payments, and role-based access control.

## Features

- ✅ User Authentication (Registration, Login, Logout) with Sanctum
- ✅ Role-Based Access Control (Admin, Organizer, Customer)
- ✅ Event Management with CRUD operations
- ✅ Ticket Management and Quantity Control
- ✅ Booking System with Double Booking Prevention
- ✅ Payment Processing (Mocked)
- ✅ Notifications for Booking Confirmations
- ✅ Advanced Search and Filtering for Events
- ✅ Caching for Performance Optimization
- ✅ Comprehensive Test Suite (85%+ coverage)
- ✅ Middleware for Authorization and Validation

## Tech Stack

- Laravel 11
- PHP 8.2+
- MySQL/PostgreSQL
- Laravel Sanctum for API Authentication
- PHPUnit for Testing
- Redis/Cache for Optimization

## Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL or PostgreSQL
- Git

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd event-booking-system
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_booking
DB_USERNAME=root
DB_PASSWORD=
```

### Step 4: Database Setup

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- 2 Admin users
- 3 Organizer users
- 10 Customer users
- 5 Events
- 15 Tickets
- 20 Bookings

### Step 5: Run the Application

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## API Documentation

### Authentication Endpoints

#### Register

```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "1234567890",
  "role": "customer"  // customer, organizer, or admin
}
```

**Response:**
```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "customer"
    },
    "token": "bearer_token_here"
  }
}
```

#### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Get Authenticated User

```http
GET /api/me
Authorization: Bearer {token}
```

#### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

### Event Endpoints

#### Get All Events (Public)

```http
GET /api/events?search=tech&date=2024-03-15&location=New+York&per_page=15
```

**Query Parameters:**
- `search`: Search by event title
- `date`: Filter by date (YYYY-MM-DD)
- `location`: Filter by location
- `per_page`: Results per page (default: 15)

#### Get Single Event (Public)

```http
GET /api/events/{id}
```

#### Create Event (Organizer/Admin Only)

```http
POST /api/events
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Tech Conference 2024",
  "description": "Annual technology conference",
  "date": "2024-03-15 09:00:00",
  "location": "San Francisco"
}
```

#### Update Event (Organizer/Admin Only)

```http
PUT /api/events/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Title",
  "description": "Updated description"
}
```

#### Delete Event (Organizer/Admin Only)

```http
DELETE /api/events/{id}
Authorization: Bearer {token}
```

### Ticket Endpoints

#### Create Ticket (Organizer/Admin Only)

```http
POST /api/events/{eventId}/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "VIP",
  "price": 150.00,
  "quantity": 100
}
```

#### Update Ticket (Organizer/Admin Only)

```http
PUT /api/tickets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "price": 175.00,
  "quantity": 80
}
```

#### Delete Ticket (Organizer/Admin Only)

```http
DELETE /api/tickets/{id}
Authorization: Bearer {token}
```

### Booking Endpoints

#### Create Booking (Customer Only)

```http
POST /api/tickets/{ticketId}/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 2
}
```

**Note:** Prevents double booking for the same ticket

#### Get My Bookings (Customer)

```http
GET /api/bookings?per_page=15
Authorization: Bearer {token}
```

#### Cancel Booking (Customer Only)

```http
PUT /api/bookings/{id}/cancel
Authorization: Bearer {token}
```

### Payment Endpoints

#### Process Payment (Customer Only)

```http
POST /api/bookings/{bookingId}/payment
Authorization: Bearer {token}
```

Payment is mocked and has ~50% success rate for testing purposes.

#### Get Payment Details (Customer)

```http
GET /api/payments/{id}
Authorization: Bearer {token}
```

## User Roles & Permissions

### Admin
- Manage all events and tickets
- View all bookings and payments
- User management

### Organizer
- Create and manage own events
- Create and manage tickets for own events
- View bookings for own events

### Customer
- View all public events
- Book tickets
- View own bookings
- Cancel bookings
- Process payments
- View own payments

## Response Format

All endpoints return consistent JSON responses:

**Success Response:**
```json
{
  "status": true,
  "message": "Operation successful",
  "data": { }
}
```

**Error Response:**
```json
{
  "status": false,
  "message": "Error description"
}
```

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Feature Tests Only

```bash
php artisan test tests/Feature
```

### Run Unit Tests Only

```bash
php artisan test tests/Unit
```

### Generate Coverage Report

```bash
php artisan test --coverage
```

### Test Coverage

- Authentication: 90%+
- Events: 85%+
- Bookings: 88%+
- Payments: 82%+
- Services: 85%+
- **Overall: 85%+**

## Database Schema

### Users Table
- id, name, email, password, phone, role, timestamps

### Events Table
- id, title, description, date, location, created_by, timestamps

### Tickets Table
- id, event_id, type, price, quantity, timestamps

### Bookings Table
- id, user_id, ticket_id, quantity, status, timestamps

### Payments Table
- id, booking_id, amount, status, timestamps

## Middleware

### Authentication
- All protected routes require `auth:sanctum` middleware

### Role-based Middleware
- Custom `RoleMiddleware` for role-based access control

### Custom Middleware
- `prevent.double.booking`: Prevents users from booking the same ticket twice with pending status

## Caching

- Events list cached for 1 hour
- Individual events cached for 1 hour
- Cache invalidated on create/update/delete operations

## Error Handling

- Validation errors return 422 status with field-specific messages
- Authentication errors return 401 status
- Authorization errors return 403 status
- Resource not found returns 404 status
- Server errors return 500 status

## Seeder Data

After running `php artisan db:seed`:

**Users:**
- 2 Admins: admin1@example.com, admin2@example.com
- 3 Organizers: organizer1-3@example.com
- 10 Customers: customer1-10@example.com

All seeded users have password: `password123`

**Events & Tickets:**
- 5 different events across organizers
- 3 ticket types per event (VIP, Standard, General)
- Customizable pricing and quantities

**Bookings & Payments:**
- 20 confirmed bookings with successful payments
- Data spread across multiple customers

## Troubleshooting

### Port Already in Use
```bash
php artisan serve --port=8001
```

### Database Connection Error
Ensure `.env` database credentials are correct and MySQL/PostgreSQL is running.

### Migrations Not Running
```bash
php artisan migrate:fresh
```

### Clear Cache & Configs
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## API Endpoints Summary

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| POST | `/api/register` | No | - |
| POST | `/api/login` | No | - |
| POST | `/api/logout` | Yes | Any |
| GET | `/api/me` | Yes | Any |
| GET | `/api/events` | No | - |
| GET | `/api/events/{id}` | No | - |
| POST | `/api/events` | Yes | Organizer/Admin |
| PUT | `/api/events/{id}` | Yes | Organizer/Admin |
| DELETE | `/api/events/{id}` | Yes | Organizer/Admin |
| POST | `/api/events/{eventId}/tickets` | Yes | Organizer/Admin |
| PUT | `/api/tickets/{id}` | Yes | Organizer/Admin |
| DELETE | `/api/tickets/{id}` | Yes | Organizer/Admin |
| POST | `/api/tickets/{ticketId}/bookings` | Yes | Customer |
| GET | `/api/bookings` | Yes | Customer |
| PUT | `/api/bookings/{id}/cancel` | Yes | Customer |
| POST | `/api/bookings/{bookingId}/payment` | Yes | Customer |
| GET | `/api/payments/{id}` | Yes | Customer |

## Contributing

Please follow Laravel best practices and include tests for new features.

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues or questions, please create an issue in the repository.
