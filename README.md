# Laravel Supervisor Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iperamuna/laravel-supervisor-manager.svg?style=flat-square)](https://packagist.org/packages/iperamuna/laravel-supervisor-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/iperamuna/laravel-supervisor-manager.svg?style=flat-square)](https://packagist.org/packages/iperamuna/laravel-supervisor-manager)

A powerful **FilamentPHP** panel for managing **Supervisor** processes directly from your Laravel application.

<img src="https://raw.githubusercontent.com/iperamuna/laravel-supervisor-manager/main/art/screenshot.png" alt="Supervisor Manager Dashboard" width="100%">

## ✨ Features

- **Dashboard Overview**: View real-time status of your Supervisor instance and all processes.
- **Smart Process Control**: 
    - **Global Controls**: Start, Stop, or Restart **all** processes with a single click from the status widget.
    - **Contextual Actions**: Configuration cards automatically toggle between Start and Stop actions based on the process state.
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

### 2. Secure File Copy Setup (Recommended)

The package uses a **secure architecture** to handle supervisor configuration files:

#### How It Works

```
Laravel writes here (your project):
storage/supervisors/laravel-queue.conf
         ↓
Laravel calls secure script with sudo:
sudo /usr/local/bin/supervisor-copy <file>
         ↓
Script copies to system directory:
/opt/homebrew/etc/supervisor.d/laravel-queue.conf
         ↓
Script runs: supervisorctl reread && supervisorctl update
```

**Why This Approach?**
- ✅ Laravel never needs write access to system directories
- ✅ Controlled sudo access to a single, auditable script
- ✅ Automatic reread/update after deployment
- ✅ Works on macOS (Homebrew) and Linux systems

#### Automated Setup

Run the included setup script:

```bash
cd vendor/iperamuna/supervisor-manager/scripts
bash setup-secure-copy.sh
```

This will:
1. Install the copy script to `/usr/local/bin/supervisor-copy`
2. Guide you through sudoers configuration
3. Test the setup automatically

#### Manual Setup

If you prefer manual installation:

**Step 1:** Install the copy script

```bash
sudo cp vendor/iperamuna/supervisor-manager/scripts/supervisor-copy /usr/local/bin/
sudo chmod +x /usr/local/bin/supervisor-copy
```

**Step 2:** Configure sudoers

```bash
sudo visudo
```

Add this line (replace `your-username` with your actual user):

```
your-username ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy *
```

For **Laravel Herd** on macOS, use your Mac username:
```
iperamuna ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy *
```

For **production servers**, use your web server user:
```
www-data ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy *
```

**Step 3:** Update your `.env`

```env
SUPERVISOR_SYSTEM_USER=your-username
SUPERVISOR_CONF_PATH=/opt/homebrew/etc/supervisor.d  # or /etc/supervisor/conf.d
SUPERVISOR_USE_SECURE_COPY=true
SUPERVISOR_LOCAL_DIR=/path/to/your/project/supervisors
```

#### Legacy Mode (Not Recommended)

If you can't use the secure copy script, you can disable it:

```env
SUPERVISOR_USE_SECURE_COPY=false
```

**Note**: This requires your web server user to have write permissions to the supervisor directory, which is a security risk.


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
- **Global Control**: The status widget (top right) allows you to **Start All**, **Stop All**, or **Restart** the supervisor daemon.
- **Smart Toggle**: The status widget automatically hides the "Start All" button if processes are running, keeping the UI clean.
- **Process Cards**: Use the **Restart** button on any process card to restart it individually.
- **Logs**: Click **LOGS** or **ERR** on a process card to view the latest log output.

### Managing Configurations
- Navigate to the **Configurations** page.
- **Create** new configurations locally.
- **Sync** them to the system directory (`/etc/supervisor/conf.d` by default).
- **Deploy** changes to reload the Supervisor daemon.
- **Start/Stop**: Each configuration card features a smart **Start/Stop** button that reflects the current running state of the process group.

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
