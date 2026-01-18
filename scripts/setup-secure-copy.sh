#!/bin/bash
# Supervisor Manager Setup Script
# This script helps set up the secure copy mechanism for Laravel Supervisor Manager
#
# Run with: bash setup-secure-copy.sh

echo "=========================================="
echo "Supervisor Manager - Secure Copy Setup"
echo "=========================================="
echo ""

# Detect current user
CURRENT_USER=$(whoami)
echo "Current user: $CURRENT_USER"
echo ""

# Detect supervisor config directory
if [ -d "/opt/homebrew/etc/supervisor.d" ]; then
    SUPERVISOR_DIR="/opt/homebrew/etc/supervisor.d"
    echo "✓ Detected Homebrew supervisor directory: $SUPERVISOR_DIR"
elif [ -d "/etc/supervisor/conf.d" ]; then
    SUPERVISOR_DIR="/etc/supervisor/conf.d"
    echo "✓ Detected system supervisor directory: $SUPERVISOR_DIR"
else
    echo "⚠ Warning: Could not auto-detect supervisor directory"
    echo "  Common paths: /etc/supervisor/conf.d or /opt/homebrew/etc/supervisor.d"
    echo ""
    read -p "Enter your supervisor config directory path: " SUPERVISOR_DIR
fi

echo ""
echo "=========================================="
echo "Step 1: Install the Copy Script"
echo "=========================================="
echo ""
echo "Installing supervisor-copy script to /usr/local/bin/..."

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [ -f "$SCRIPT_DIR/supervisor-copy" ]; then
    sudo cp "$SCRIPT_DIR/supervisor-copy" /usr/local/bin/supervisor-copy
    sudo chmod +x /usr/local/bin/supervisor-copy
    echo "✓ Copy script installed successfully"
else
    echo "✗ Error: supervisor-copy script not found in $SCRIPT_DIR"
    echo "  Please ensure you're running this from the scripts directory"
    exit 1
fi

echo ""
echo "=========================================="
echo "Step 2: Configure Sudoers"
echo "=========================================="
echo ""
echo "To allow Laravel to run the copy script without password,"
echo "you need to add a sudoers entry."
echo ""
echo "Add the following line to your sudoers file:"
echo ""
echo "----------------------------------------"
echo "$CURRENT_USER ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy *"
echo "----------------------------------------"
echo ""
echo "Run: sudo visudo"
echo "Then add the line above at the end of the file."
echo ""
read -p "Press Enter when you've completed this step..."

echo ""
echo "=========================================="
echo "Step 3: Test the Setup"
echo "=========================================="
echo ""

# Create a test config file
TEST_FILE="/tmp/test-supervisor-config.conf"
cat > "$TEST_FILE" << 'EOF'
[program:test-worker]
command=/bin/sleep 60
autostart=false
autorestart=false
EOF

echo "Testing sudo copy script..."
if sudo /usr/local/bin/supervisor-copy "$TEST_FILE" 2>/dev/null; then
    echo "✓ Copy script works! Cleaning up test file..."
    sudo rm -f "$SUPERVISOR_DIR/test-supervisor-config.conf"
    rm -f "$TEST_FILE"
else
    echo "✗ Copy script test failed. Please check:"
    echo "  1. The sudoers entry is correct"
    echo "  2. Supervisor is running"
    echo "  3. The supervisor directory exists: $SUPERVISOR_DIR"
    rm -f "$TEST_FILE"
    exit 1
fi

echo ""
echo "=========================================="
echo "✓ Setup Complete!"
echo "=========================================="
echo ""
echo "Configuration summary:"
echo "  • Copy script: /usr/local/bin/supervisor-copy"
echo "  • System user: $CURRENT_USER"
echo "  • Supervisor dir: $SUPERVISOR_DIR"
echo ""
echo "Update your .env file with:"
echo ""
echo "SUPERVISOR_SYSTEM_USER=$CURRENT_USER"
echo "SUPERVISOR_CONF_PATH=$SUPERVISOR_DIR"
echo "SUPERVISOR_USE_SECURE_COPY=true"
echo ""
echo "You can now use the Supervisor Manager safely!"
echo ""
