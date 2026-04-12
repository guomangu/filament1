#!/bin/bash

# ==============================================================================
# God Stack Universal Installer for Debian 13 VPS
# ==============================================================================
set -e

# Configuration
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BIN_DIR="$PROJECT_ROOT/bin"
DATA_DIR="$PROJECT_ROOT/data"
SRC_DIR="$PROJECT_ROOT/src"
LOG_DIR="$DATA_DIR/logs"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}   God Stack Universal Installer v2.1              ${NC}"
echo -e "${GREEN}====================================================${NC}"

# 0. System Checks & Permissions
echo -e "${YELLOW}[1/6] Checking system and permissions...${NC}"

# Check for required system tools
# Check for required system tools
# We also check for libncurses6 to ensure we have a base for our polyfill
REQUIRED_PACKAGES="curl tar xz-utils git"

# Check if we need to install anything
NEEDS_INSTALL=0
for pkg in $REQUIRED_PACKAGES; do
    if ! dpkg -s $pkg >/dev/null 2>&1; then
        NEEDS_INSTALL=1
        break
    fi
done

# Explicitly check for libncurses
if ! dpkg -s libncurses6 >/dev/null 2>&1 && ! dpkg -s libncurses5 >/dev/null 2>&1; then
    NEEDS_INSTALL=1
fi

if [ "$NEEDS_INSTALL" -eq 1 ]; then
    echo "Installing required system packages..."
    sudo apt update
    sudo apt install -y curl tar xz-utils git libncurses6 libtinfo6 || echo "Warning: Package installation failed. Proceeding anyway..."
fi

# Fix home directory permissions if strict (typical on some VPS)
USER_HOME="/home/$(whoami)"
if [ -d "$USER_HOME" ]; then
    CURRENT_PERMS=$(stat -c %a "$USER_HOME")
    if [ "$CURRENT_PERMS" != "755" ]; then
        echo "Updating home directory permissions to 755 for web server access..."
        chmod 755 "$USER_HOME"
    fi
fi

# Ensure directories exist
mkdir -p "$BIN_DIR" "$DATA_DIR/mysql" "$DATA_DIR/storage" "$BIN_DIR/lib" "$LOG_DIR" "$BIN_DIR/.core"

# 1. Download Portable Binaries
echo -e "${YELLOW}[2/6] Downloading portable binaries...${NC}"

# FrankenPHP
if [ ! -f "$BIN_DIR/frankenphp" ]; then
    echo "Downloading FrankenPHP..."
    curl -L https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o "$BIN_DIR/frankenphp"
    chmod +x "$BIN_DIR/frankenphp"
fi

# Node.js (v20 LTS)
if [ ! -d "$BIN_DIR/node" ]; then
    echo "Downloading Node.js (Portable)..."
    NODE_VERSION="v20.11.1"
    curl -L "https://nodejs.org/dist/$NODE_VERSION/node-$NODE_VERSION-linux-x64.tar.xz" -o node.tar.xz
    tar -xJf node.tar.xz -C "$BIN_DIR"
    mv "$BIN_DIR/node-$NODE_VERSION-linux-x64" "$BIN_DIR/node"
    rm node.tar.xz
fi

# Composer
if [ ! -f "$BIN_DIR/composer.phar" ]; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | "$BIN_DIR/frankenphp" php-cli -- --install-dir="$BIN_DIR" --filename=composer.phar
    chmod +x "$BIN_DIR/composer.phar"
fi

# MariaDB (Binary Tarball)
if [ ! -d "$BIN_DIR/mariadb" ]; then
    echo "Downloading MariaDB (Portable)..."
    MARIA_VER="11.4.3"
    MARIADB_URL="https://archive.mariadb.org/mariadb-$MARIA_VER/bintar-linux-systemd-x86_64/mariadb-$MARIA_VER-linux-systemd-x86_64.tar.gz"
    curl -L "$MARIADB_URL" -o mariadb.tar.gz
    tar -xzf mariadb.tar.gz -C "$BIN_DIR"
    mv "$BIN_DIR/mariadb-$MARIA_VER-linux-systemd-x86_64" "$BIN_DIR/mariadb"
    rm mariadb.tar.gz
fi

# Polyfill missing libraries (specifically libncurses.so.5 for MariaDB on Debian 13)
echo -e "${YELLOW}Configuring Library Polyfills...${NC}"

# Ensure we can use system tools
export PATH=$PATH:/usr/sbin:/sbin

# Precise search order:
# 1. Exact Debian/Ubuntu Multiarch locations (Fastest, Safest)
# 2. ldconfig cache (Reliable if updated)
# 3. Recursive find (Slow, fallback)

POSSIBLE_NCURSES=(
    "/usr/lib/x86_64-linux-gnu/libncurses.so.6"
    "/lib/x86_64-linux-gnu/libncurses.so.6"
    "/usr/lib64/libncurses.so.6"
    "/lib64/libncurses.so.6"
    "/usr/lib/libncurses.so.6"
    "/lib/libncurses.so.6"
)

FOUND_LIB=""

echo "Debug: Checking specific paths..."
for path in "${POSSIBLE_NCURSES[@]}"; do
    if [ -f "$path" ]; then
        FOUND_LIB="$path"
        echo "  -> Found at $path"
        break
    fi
done

if [ -z "$FOUND_LIB" ]; then
    echo "Debug: Checking ldconfig..."
    # nuances: awk prints last field which is the path
    FOUND_LIB=$(ldconfig -p 2>/dev/null | grep "libncurses.so.6" | head -n 1 | awk '{print $NF}')
fi

if [ -z "$FOUND_LIB" ]; then
    echo "Debug: Running broad search (this may take a moment)..."
    FOUND_LIB=$(find /usr/lib /lib /usr/lib64 /lib64 -name "libncurses.so.6*" -print -quit 2>/dev/null)
fi

# Fallback to version 5 if systems actually have it but it's just not in path
if [ -z "$FOUND_LIB" ]; then
    FOUND_LIB=$(find /usr/lib /lib /usr/lib64 /lib64 -name "libncurses.so.5*" -print -quit 2>/dev/null)
fi


if [ -n "$FOUND_LIB" ]; then
    echo "Polyfilling libncurses.so.5 using: $FOUND_LIB"
    ln -sf "$FOUND_LIB" "$BIN_DIR/lib/libncurses.so.5"
    
    # Setup libtinfo.so.5 (often required alongside ncurses)
    # 1. Try to find a sibling tinfo
    TINFO_CANDIDATE=$(echo "$FOUND_LIB" | sed 's/ncurses/tinfo/')
    
    if [ -f "$TINFO_CANDIDATE" ]; then
         echo "  -> Found sibling libtinfo: $TINFO_CANDIDATE"
         ln -sf "$TINFO_CANDIDATE" "$BIN_DIR/lib/libtinfo.so.5"
    else
         # 2. Try to find tinfo via ldconfig
         TINFO_SYS=$(ldconfig -p 2>/dev/null | grep "libtinfo.so.6" | head -n 1 | awk '{print $NF}')
         if [ -n "$TINFO_SYS" ]; then
              echo "  -> Found system libtinfo: $TINFO_SYS"
              ln -sf "$TINFO_SYS" "$BIN_DIR/lib/libtinfo.so.5"
         else
              # 3. Fallback: specific debian path check for tinfo
              if [ -f "/usr/lib/x86_64-linux-gnu/libtinfo.so.6" ]; then
                   ln -sf "/usr/lib/x86_64-linux-gnu/libtinfo.so.6" "$BIN_DIR/lib/libtinfo.so.5"
              else
                   # 4. Last resort: Link ncurses to tinfo (often works as they are bundled)
                   echo "  -> Warning: Could not find libtinfo independently. Aliasing to libncurses."
                   ln -sf "$FOUND_LIB" "$BIN_DIR/lib/libtinfo.so.5"
              fi
         fi
    fi
else
    echo -e "${RED}CRITICAL WARNING: Could not detect libncurses.so.6 or .5 on this system.${NC}"
    echo "Database initialization will likely fail."
    echo "Debug info:"
    uname -a
    echo "ldconfig output for ncurses:"
    ldconfig -p | grep curses || echo "No curses in ldconfig"
fi

echo "Debug: Final contents of logical library path ($BIN_DIR/lib):"
ls -l "$BIN_DIR/lib"

# Try to install libncurses5 if possible and polyfill failed
if [ ! -f "$BIN_DIR/lib/libncurses.so.5" ]; then
    echo "Attempting to install libncurses5 via apt..."
    sudo apt update && sudo apt install -y libncurses5 || echo "libncurses5 package not found, relying on polyfill."
fi

# 2. Wrapper Creation
echo -e "${YELLOW}[3/6] Creating wrappers...${NC}"

# Absolute base path for internal use in wrappers
BASE_DIR=$(readlink -f "$PROJECT_ROOT")

# PHP Wrapper (Direct frankenphp call, very simple)
cat <<EOF > "$BIN_DIR/php"
#!/bin/bash
export LD_LIBRARY_PATH="$BASE_DIR/bin/lib:\$LD_LIBRARY_PATH"
# Strip common flags that frankenphp php-cli doesn't handle natively
ARGS=()
SKIP_NEXT=0
for arg in "\$@"; do
    if [ "\$SKIP_NEXT" -eq 1 ]; then
        SKIP_NEXT=0
        continue
    fi
    if [[ "\$arg" == "-d" ]]; then
        SKIP_NEXT=1
        continue
    fi
    if [[ "\$arg" == -d* ]]; then
        continue
    fi
    ARGS+=("\$arg")
done
exec "$BASE_DIR/bin/frankenphp" php-cli "\${ARGS[@]}"
EOF
chmod +x "$BIN_DIR/php"

# Artisan Wrapper (Absolute Path)
cat <<EOF > "$BIN_DIR/artisan"
#!/bin/bash
cd "$BASE_DIR/src"
exec "$BIN_DIR/php" artisan "\$@"
EOF
chmod +x "$BIN_DIR/artisan"

# Composer Wrapper
cat <<EOF > "$BIN_DIR/composer"
#!/bin/bash
PROJECT_ROOT="$BASE_DIR"
export LD_LIBRARY_PATH="\$PROJECT_ROOT/bin/lib:\$LD_LIBRARY_PATH"
export PHP_BINARY="\$PROJECT_ROOT/bin/php"
# Call frankenphp directly to avoid double-processing in php wrapper
exec "\$PROJECT_ROOT/bin/frankenphp" php-cli "\$PROJECT_ROOT/bin/composer.phar" "\$@"
EOF
chmod +x "$BIN_DIR/composer"

# Synchronize PATH for install session
mkdir -p "$BIN_DIR/.core"
ln -sf "$BIN_DIR/php" "$BIN_DIR/.core/php"
ln -sf "$BIN_DIR/node/bin/node" "$BIN_DIR/.core/node"
ln -sf "$BIN_DIR/node/bin/npm" "$BIN_DIR/.core/npm"
export PATH="$BIN_DIR/.core:$PATH"

# 3. Application Setup
echo -e "${YELLOW}[4/6] Setting up application...${NC}"
cd "$SRC_DIR"

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from example."
fi

# Configure .env (Database Paths)
# Handle potential commented lines in .env.example
SOCK_PATH=$(readlink -f "$DATA_DIR/mysql/mysql.sock")
sed -i "s|^#\? *DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sed -i "s|^#\? *DB_SOCKET=.*|DB_SOCKET=$SOCK_PATH|" .env
sed -i "s|^#\? *DB_HOST=.*|DB_HOST=localhost|" .env
sed -i "s|^#\? *DB_PORT=.*|DB_PORT=|" .env
sed -i "s|^#\? *DB_DATABASE=.*|DB_DATABASE=laravel|" .env
sed -i "s|^#\? *DB_USERNAME=.*|DB_USERNAME=$(whoami)|" .env
sed -i "s|^#\? *DB_PASSWORD=.*|DB_PASSWORD=|" .env

# Add only core binaries to PATH during install to avoid artisan wrapper conflicts
ln -sf "$BIN_DIR/node/bin/node" "$BIN_DIR/.core/node"
ln -sf "$BIN_DIR/node/bin/npm" "$BIN_DIR/.core/npm"
export PATH="$BIN_DIR/.core:$PATH"
export PHP="$BIN_DIR/php"
export PHP_BINARY="$BIN_DIR/php"

# PHP Dependencies
"$BIN_DIR/composer" install --no-interaction --prefer-dist

# App Key
# App Key
if ! grep -q "^APP_KEY=base64:" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d'=' -f2)" ]; then
    echo "Generating Application Key..."
    # Use frankenphp directly to avoid any wrapper issues with argument passing
    # Use frankenphp directly to avoid any wrapper issues with argument passing
    "$BIN_DIR/frankenphp" php-cli "$SRC_DIR/artisan" key:generate --force || true
    
    # Verify and Fallback
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "Artisan key:generate failed or didn't update .env. Using OpenSSL fallback..."
        NEW_KEY="base64:$(openssl rand -base64 32)"
        # Escape for sed
        ESCAPED_KEY=$(echo "$NEW_KEY" | sed 's/[\/&]/\\&/g')
        sed -i "s|^APP_KEY=.*|APP_KEY=$ESCAPED_KEY|" .env
        echo "Generated key via OpenSSL: $NEW_KEY"
    fi
fi

# Node Dependencies
npm install && npm run build

# 4. Database Initialization
echo -e "${YELLOW}[5/6] Initializing database...${NC}"
MARIADB_DIR="$BIN_DIR/mariadb"
MYSQL_DATA="$DATA_DIR/mysql"
MYSQL_PID="$MYSQL_DATA/mariadb.pid"
MYSQL_SOCKET="$DATA_DIR/mysql/mysql.sock" # Define socket path explicitly

# Export Library Path GLOBALLY for this section so all mariadb tools see the polyfill
export LD_LIBRARY_PATH="$BIN_DIR/lib:$LD_LIBRARY_PATH"

# Force kill any stale mariadbd processes to avoid file lock errors (error 11)
echo "Ensuring no stale Database processes are running..."
pkill -f "$MARIADB_DIR/bin/mariadbd" || true
sleep 2

if [ -z "$(ls -A "$MYSQL_DATA")" ]; then
    echo "Installing default system tables..."
    "$MARIADB_DIR/scripts/mariadb-install-db" --user=$(whoami) --datadir="$MYSQL_DATA" --basedir="$MARIADB_DIR" --auth-root-authentication-method=normal
fi

# 5. Start temporary MariaDB for seeding & user setup
echo "Starting temporary Database for setup..."
# If root, use root, otherwise use current user
DB_USER_FLAG=""
if [ "$(id -u)" = "0" ]; then DB_USER_FLAG="--user=root"; fi

"$MARIADB_DIR/bin/mariadbd" --no-defaults --datadir="$MYSQL_DATA" --socket="$MYSQL_SOCKET" --pid-file="$MYSQL_PID" --skip-networking --skip-grant-tables --default-storage-engine=InnoDB $DB_USER_FLAG >> "$LOG_DIR/mariadb.log" 2>&1 &
TEMP_PID=$!

# Wait for it to start
for i in {1..30}; do
    if [ -S "$MYSQL_SOCKET" ]; then break; fi
    sleep 1
done

if [ ! -S "$MYSQL_SOCKET" ]; then
    echo -e "${RED}Error: Temporary MariaDB failed to start. Check $LOG_DIR/mariadb.log${NC}"
    exit 1
fi

# Ensure current user exists in MariaDB and database is created
echo "Ensuring MariaDB accounts and 'laravel' database are configured..."
# Since we are in --skip-grant-tables, we can connect as root without password.
# We create the database BEFORE flushing privileges to be safe.
"$MARIADB_DIR/bin/mariadb" --socket="$MYSQL_SOCKET" -u root -e "
    CREATE DATABASE IF NOT EXISTS laravel;
    FLUSH PRIVILEGES;
    CREATE USER IF NOT EXISTS '$(whoami)'@'localhost' IDENTIFIED VIA unix_socket;
    GRANT ALL PRIVILEGES ON *.* TO '$(whoami)'@'localhost' WITH GRANT OPTION;
    ALTER USER 'root'@'localhost' IDENTIFIED VIA unix_socket;
    FLUSH PRIVILEGES;
" || {
    echo -e "${RED}Warning: Manual user configuration failed. Attempting to proceed...${NC}"
}
# Sync Schema
echo -e "${GREEN}Syncing database schema...${NC}"
# Use the wrapper which correctly handles paths
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

# Shutdown temp DB
kill $TEMP_PID
wait $TEMP_PID 2>/dev/null || true

# 5. Final Permissions
echo -e "${YELLOW}[6/6] Finalizing permissions...${NC}"

# Ensure current user owns everything (in case root start messed it up)
CURRENT_USER=$(whoami)
echo "Ensuring $CURRENT_USER owns storage and bootstrap/cache..."

# Try simple chown first, if fails, use sudo
# Note: storage and bootstrap are in src/ (PWD), but data is in $DATA_DIR (absolute)
if ! chown -R "$CURRENT_USER":"$CURRENT_USER" storage bootstrap/cache "$DATA_DIR" 2>/dev/null; then
    echo -e "${YELLOW}Detected root-owned files. Fixing ownership with sudo...${NC}"
    sudo chown -R "$CURRENT_USER":"$CURRENT_USER" storage bootstrap/cache "$DATA_DIR"
fi

chmod -R 775 storage bootstrap/cache "$DATA_DIR"
# Use frankenphp directly to avoid any wrapper issues with argument passing
"$BIN_DIR/frankenphp" php-cli "$SRC_DIR/artisan" config:clear
"$BIN_DIR/frankenphp" php-cli "$SRC_DIR/artisan" storage:link --force

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}   Installation Complete! Use ./bin/start.sh       ${NC}"
echo -e "${GREEN}====================================================${NC}"
