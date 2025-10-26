#!/bin/bash

# Script to start mobile development environment
# This starts the Laravel server needed for the mobile app

echo "🚀 Starting Mobile Development Environment"
echo "=========================================="
echo ""

# Check if Laravel server is already running
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo "✅ Laravel server already running on port 8000"
else
    echo "🔧 Starting Laravel server on port 8000..."
    php artisan serve &
    LARAVEL_PID=$!
    echo "✅ Laravel server started (PID: $LARAVEL_PID)"
fi

echo ""
echo "📱 Your mobile app is ready to test!"
echo ""
echo "Next steps:"
echo "1. In Xcode, select a simulator (iPhone 15 Pro recommended)"
echo "2. Click the Play button (▶️) or press Cmd+R"
echo "3. The app should open and load your Laravel app"
echo ""
echo "To stop the Laravel server, run: killall php"
echo ""
