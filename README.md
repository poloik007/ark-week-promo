# Arkon Week Promo — PrestaShop 8 Module

Displays a promotional badge on discounted and in-stock products in listings and product pages.

## Requirements
- PrestaShop 8.x
- PHP 8.1+
- Node.js (for SCSS compilation only)

## Installation
1. Copy `arkweekpromo/` folder to `/modules/`
2. Go to Back Office → Modules → Module Manager
3. Search "Arkon Week Promo" and click Install

## Configuration
Back Office → Modules → Arkon Week Promo → Configure

- Enable/disable the badge globally
- Edit badge text per language
- Choose text and background colors

## Development (Webpack + SCSS)
npm install
npm run build    # production build
npm run watch    # development watch mode

## How it works
Hooks into `displayProductPriceBlock` (before old_price position).
Checks if the product has an active price reduction AND is available for order. Badge text is stored per language.
