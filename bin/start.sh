#!/bin/bash

# ==============================================================================
# God Stack Universal Starter for Debian 13 VPS
# ==============================================================================

# Configuration
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BIN_DIR="$PROJECT_ROOT/bin"
DATA_DIR="$PROJECT_ROOT/data"
SRC_DIR="$PROJECT_ROOT/src"
LOG_DIR="$DATA_DIR/logs"

MARIADB_DIR="$BIN_DIR/mariadb"
MYSQL_DATA="$DATA_DIR/mysql"
MYSQL_SOCKET="$MYSQL_DATA/mysql.sock"
MYSQL_PID="$MYSQL_DATA/mariadb.pid"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Check if installed
if [ ! -f "$BIN_DIR/frankenphp" ] || [ ! -d "$MARIADB_DIR" ]; then
    echo -e "${YELLOW}Environment not detected. Running installer...${NC}"
    "$BIN_DIR/install.sh"
fi

# Cleanup stale processes
echo -e "${YELLOW}Cleaning up stale processes...${NC}"
if [ -f "$MYSQL_PID" ]; then
    OLD_PID=$(cat "$MYSQL_PID")
    if ps -p $OLD_PID > /dev/null; then
        kill $OLD_PID
        sleep 2
    fi
    rm -f "$MYSQL_PID"
fi
pkill -f "$BIN_DIR/frankenphp" || true

# Start MariaDB
# Start MariaDB
echo -e "${GREEN}Starting MariaDB...${NC}"
export LD_LIBRARY_PATH="$BIN_DIR/lib:$LD_LIBRARY_PATH"

DB_USER_FLAG=""
if [ "$(id -u)" = "0" ]; then
    # If running as root, force MariaDB to run as the owner of this script
    SCRIPT_OWNER=$(stat -c '%U' "${BASH_SOURCE[0]}")
    DB_USER_FLAG="--user=$SCRIPT_OWNER"
    echo "Running MariaDB as user: $SCRIPT_OWNER"
fi

"$MARIADB_DIR/bin/mariadbd" --no-defaults --datadir="$MYSQL_DATA" --socket="$MYSQL_SOCKET" --pid-file="$MYSQL_PID" --skip-networking --default-storage-engine=InnoDB $DB_USER_FLAG >> "$LOG_DIR/mariadb.log" 2>&1 &
MARIADB_PID=$!

# Wait for MariaDB
for i in {1..30}; do
    if [ -S "$MYSQL_SOCKET" ]; then 
        chmod 777 "$MYSQL_SOCKET" || true
        break 
    fi
    sleep 1
done

# Ensure DB_HOST is localhost for socket connection (prevent TCP fallback)
cd "$SRC_DIR"
if ! grep -q "^DB_HOST=localhost" .env; then
    echo -e "${YELLOW}Enforcing DB_HOST=localhost for socket connection...${NC}"
    sed -i "s|^DB_HOST=.*|DB_HOST=localhost|" .env
    "$BIN_DIR/php" "$SRC_DIR/artisan" config:clear
fi

# If running as root (sudo), we must override DB_USERNAME to match the process owner
# for unix_socket authentication to work correctly.
if [ "$(id -u)" = "0" ]; then
    echo -e "${YELLOW}Running as root, overriding DB_USERNAME to 'root' for socket auth...${NC}"
    export DB_USERNAME=root
fi

if [ ! -S "$MYSQL_SOCKET" ]; then
    echo -e "${RED}Error: MariaDB failed to start. Check $LOG_DIR/mariadb.log${NC}"
    exit 1
fi

# Sync Schema
echo -e "${GREEN}Syncing database schema...${NC}"
# Use absolute path to ensure no "artisan undefined" issues
"$BIN_DIR/php" "$SRC_DIR/artisan" config:clear >> "$LOG_DIR/install.log" 2>&1
if ! "$BIN_DIR/php" "$SRC_DIR/artisan" migrate --force; then
    echo -e "${RED}Error: Database migrations failed! Check the output above.${NC}"
fi

# Verify table existence (diagnostic)
echo -e "${YELLOW}Verifying sessions table...${NC}"
if ! export LD_LIBRARY_PATH="$BIN_DIR/lib" && "$MARIADB_DIR/bin/mariadb" --socket="$MYSQL_SOCKET" -u root -e "USE laravel; SHOW TABLES LIKE 'sessions';" | grep -q 'sessions'; then
    echo -e "${RED}Warning: 'sessions' table not found in 'laravel' database!${NC}"
    echo "Current tables:"
    export LD_LIBRARY_PATH="$BIN_DIR/lib" && "$MARIADB_DIR/bin/mariadb" --socket="$MYSQL_SOCKET" -u root -e "USE laravel; SHOW TABLES;"
fi

# Start FrankenPHP
if [ "$(id -u)" = "0" ]; then
    echo -e "${RED}ERREUR: Ne lancez PLUS ce script avec 'sudo' !${NC}"
    echo -e "${YELLOW}Cela a pour effet de corrompre les droits (les images téléchargées sont créées sous 'root').${NC}"
    echo -e "Pour régler vos fichiers déjà corrompus, tapez : ${GREEN}sudo chown -R \$SUDO_USER:\$SUDO_USER data/ src/storage/ src/bootstrap/cache/ bin/${NC}"
    echo -e "Puis relancez normalement : ${GREEN}./bin/start.sh${NC}"
    exit 1
fi

PORT=${1:-80}
echo -e "${GREEN}Starting FrankenPHP on port $PORT...${NC}"

# If port 80 is requested, we might need to stop the system service
if [ "$PORT" = "80" ]; then
    if systemctl is-active --quiet frankenphp; then
        echo -e "${YELLOW}System FrankenPHP service detected on port 80. Stopping it...${NC}"
        sudo systemctl stop frankenphp || echo -e "${RED}Warning: Could not stop system frankenphp. Port conflict likely.${NC}"
    fi
    
    # Auto-grant cap_net_bind_service so non-root users can bind to port 80 natively
    if ! /usr/sbin/getcap "$BIN_DIR/frankenphp" 2>/dev/null | grep -q 'cap_net_bind_service'; then
        echo -e "${YELLOW}Initialisation des permissions (setcap) pour utiliser le port 80 sans sudo...${NC}"
        sudo /usr/sbin/setcap 'cap_net_bind_service=+ep' "$BIN_DIR/frankenphp"
    fi
fi

cd "$SRC_DIR"
# Use setcap or sudo for port 80 if necessary, but here we just try
"$BIN_DIR/frankenphp" php-server --domain guuu.localhost --root "$SRC_DIR/public" >> "$LOG_DIR/frankenphp.log" 2>&1 &
FRANKEN_PID=$!

# Trap for clean shutdown
cleanup() {
    echo -e "\n${YELLOW}Stopping services...${NC}"
    kill $FRANKEN_PID $MARIADB_PID 2>/dev/null || true
    wait 2>/dev/null
    echo -e "${GREEN}Shutdown complete.${NC}"
}
trap cleanup SIGINT SIGTERM

echo -e "${GREEN}God Stack is running at http://$(curl -s ifconfig.me):$PORT${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop.${NC}"
wait
