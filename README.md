# PHP Laravel Backend

## Setup

- Install backend dependencies: `composer install`
- Install frontend dependencies: `pnpm i`
- Rename '.env.template' to '.env'
- Generate app key: `php artisan key:generate`
- Clear config cache: `php artisan config:clear`
- Run a migration: `php artisan migrate`
- Run a seed: `php artisan db:seed`
- Link storage folder to public folder: `php artisan storage:link`
- Serve backend with: `php artisan serve`
- Serve frontend with: `pnpm dev`
- Check the server by making GET request on this route: `/`
- Check the database connection by making GET request on this route: `/database`
- Create database manually or by making GET request on this route: `/database/install/`

## Support

- Twilio SSL Certificate issue on windows
<https://support.twilio.com/hc/en-us/articles/235279367-Twilio-PHP-helper-library-SSL-certificate-problem-on-Windows>
