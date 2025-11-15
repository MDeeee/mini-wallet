# Mini Wallet Application

> A high-performance digital wallet application built with Laravel 12 and Vue 3

## í¾¯ Features

- âœ… Money transfers with 1.5% commission
- âœ… Real-time updates via Pusher
- âœ… Concurrent transaction handling
- âœ… RESTful API with Sanctum auth
- âœ… Comprehensive test suite with Pest
- âœ… Docker containerization

## í» ï¸ Tech Stack

- Laravel 12 (PHP 8.3+)
- PostgreSQL 16
- Redis 7
- Vue 3 + Vite
- Pest testing

## íº€ Quick Start

1. Configure Pusher credentials in `.env`
2. Run: `docker-compose up -d`
3. Run: `docker-compose exec app composer install`
4. Run: `docker-compose exec app php artisan migrate --seed`
5. Access: http://localhost:8000

## í³š Documentation

See `PRACTICAL_PLAN.md` for complete architecture details.

**Status:** âœ… Phase 1 Complete
