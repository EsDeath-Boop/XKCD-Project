#!/bin/bash

# setup_cron.sh - Automatically configure CRON job for XKCD comic delivery

echo "Setting up CRON job for XKCD comic delivery..."

# Get the current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
CRON_PHP_PATH="$SCRIPT_DIR/cron.php"

# Check if cron.php exists
if [ ! -f "$CRON_PHP_PATH" ]; then
    echo "Error: cron.php not found in $SCRIPT_DIR"
    exit 1
fi

# Find PHP executable
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo "Error: PHP executable not found. Please install PHP."
    exit 1
fi

echo "PHP found at: $PHP_PATH"
echo "CRON script path: $CRON_PHP_PATH"

# Create the CRON job entry (runs daily at 9:00 AM)
CRON_ENTRY="0 9 * * * $PHP_PATH $CRON_PHP_PATH >> $SCRIPT_DIR/cron.log 2>&1"

# Check if the CRON job already exists
if crontab -l 2>/dev/null | grep -F "$CRON_PHP_PATH" >/dev/null; then
    echo "CRON job already exists for this script."
    echo "Current CRON entries containing this script:"
    crontab -l 2>/dev/null | grep -F "$CRON_PHP_PATH"
else
    # Add the CRON job
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
    
    if [ $? -eq 0 ]; then
        echo "✅ CRON job added successfully!"
        echo "📅 Schedule: Daily at 9:00 AM"
        echo "📝 Command: $CRON_ENTRY"
        echo "📄 Logs: $SCRIPT_DIR/cron.log"
    else
        echo "❌ Error: Failed to add CRON job"
        exit 1
    fi
fi

# Test the PHP script
echo ""
echo "🧪 Testing the PHP script..."
$PHP_PATH "$CRON_PHP_PATH"

echo ""
echo "✅ Setup complete!"
echo ""
echo "📋 To view all CRON jobs: crontab -l"
echo "✏️  To edit CRON jobs: crontab -e"
echo "🗑️  To remove this job: crontab -e (then delete the line containing '$CRON_PHP_PATH')"
echo ""
echo "📁 Check these files for logs:"
echo "   - $SCRIPT_DIR/cron.log (CRON execution logs)"
echo "   - $SCRIPT_DIR/email_log.txt (Email sending logs)"