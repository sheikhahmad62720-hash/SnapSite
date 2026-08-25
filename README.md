# SnapSite

A single-page website screenshot generator. Enter a URL, pick a device, and capture any public website in seconds.

## Tech Stack

- Laravel 13
- Vue 3 + Inertia.js
- Tailwind CSS
- Playwright + Chromium

## Setup

```bash
composer install
npm install
npx playwright install chromium
cp .env.example .env
php artisan key:generate
npm run build
```

## Run

```bash
php artisan dev
```

Open `http://localhost:8000`

## How It Works

1. Enter a public website URL
2. Select device preset or custom dimensions
3. Choose viewport or full page screenshot
4. Pick image format (PNG, JPEG, WEBP)
5. Click Generate
6. Preview and download the screenshot

## License

MIT
