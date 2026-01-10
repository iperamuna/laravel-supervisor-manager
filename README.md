# Laravel Supervisor Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iperamuna/supervisor-manager.svg?style=flat-square)](https://packagist.org/packages/iperamuna/supervisor-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/iperamuna/supervisor-manager.svg?style=flat-square)](https://packagist.org/packages/iperamuna/supervisor-manager)

A powerful **FilamentPHP** panel for managing **Supervisor** processes directly from your Laravel application.

<img src="https://raw.githubusercontent.com/iperamuna/supervisor-manager/main/art/screenshot.png" alt="Supervisor Manager Dashboard" width="100%">

## ✨ Features

- **Dashboard Overview**: View real-time status of your Supervisor instance and all processes.
- **Process Management**: Start, stop, and restart individual processes (or groups) with a single click.
- **Log Viewer**: View `stdout` and `stderr` logs live in a modal (tailing the last 10KB of output).
- **Configuration Manager**:
    - Manage `.conf` files directly from the UI.
    - Sync configurations to your system's Supervisor directory.
    - Deploy changes (reread/update) via the UI.
    - Detect orphaned (system-only) or unsynced (local-only) configurations.
- **Filament Powered**: Built with Filament V3/V4, offering a beautiful, responsive, and dark-mode compatible UI.
- **Secure**: Uses XML-RPC to communicate with Supervisor (localhost only recommended).

## 🚀 Installation

You can install the package via composer:

```bash
composer require iperamuna/supervisor-manager
```

After installing, run the installation command to publish assets and configure the connection:

```bash
php artisan supervisor-manager:install
```

This command will prompt you for:
1. The **URL path** for the dashboard (default: `supervisor`).
2. The **Supervisor XML-RPC** credentials (URL, Port, Username, Password).

## ⚙️ Configuration

### 1. Enabling XML-RPC in Supervisor
For this package to work, you must enable the HTTP server in your `supervisord.conf` file (usually in `/etc/supervisord.conf` or `/etc/supervisor/supervisord.conf`).

Add or uncomment the following section:

```ini
[inet_http_server]
port=127.0.0.1:9125
username=user
password=strongpass
```

> **⚠️ Security Warning**: Only bind to `127.0.0.1` to prevent external access. This interface gives full control over your processes.

### 2. Permissions (Optional)
If you wish to use the **"Deploy"** feature (which runs `supervisorctl reread` and `supervisorctl update`), the user running your Laravel app (e.g., `www-data`) needs sudo privileges for `supervisorctl`.

Run `sudo visudo` and add:

```
www-data ALL=(root) NOPASSWD: /usr/bin/supervisorctl *
```

(Replace `www-data` with your web server user if different).

## 🛡️ Access Control

By default, the package uses `App\Models\User` in the configuration. To control who can access the dashboard, ensure your User model implements `FilamentUser` and defines the `canAccessPanel` method, or configure a different user model in `config/supervisor-manager.php`.

```php
// app/Models/User.php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'admin@example.com';
    }
}
```

## 🔧 Usage

Visit the dashboard at `/supervisor` (or your configured path).

### Managing Processes
- The **Dashboard** shows a grid of all monitored processes.
- Use the **Restart** button on any process card to restart it.
- Click **LOGS** or **ERR** on a process card to view the latest log output.

### Managing Configurations
- Navigate to the **Configurations** page.
- **Create** new configurations locally.
- **Sync** them to the system directory (`/etc/supervisor/conf.d` by default).
- **Deploy** changes to reload the Supervisor daemon.

## 🛠️ Development

If you are contributing to the package and need to update the styles:

1. Install dependencies:
```bash
composer install
```
2. Build the Tailwind assets:
```bash
composer build
```

## 🤝 Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
