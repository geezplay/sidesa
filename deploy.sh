#!/usr/bin/env bash
#
# ============================================================
#  SIADESA — Script Deploy Otomatis VPS (Ubuntu 22.04/24.04)
#  Frontend : Laravel + Nginx + PHP-FPM
#  Backend  : Express.js + Prisma + PostgreSQL (PM2)
#  Domain   : sidesa.geezplay.site  /  api.sidesa.geezplay.site
# ============================================================
#
#  Cara pakai:
#    1. Upload/clone proyek ke /var/www/sidesa
#    2. chmod +x deploy.sh
#    3. sudo ./deploy.sh            (instalasi lengkap + deploy)
#       sudo ./deploy.sh --update   (hanya update kode & restart)
#
set -euo pipefail

# ====================== KONFIGURASI ======================
DOMAIN="${DOMAIN:-sidesa.geezplay.site}"
API_DOMAIN="${API_DOMAIN:-api.sidesa.geezplay.site}"
APP_DIR="${APP_DIR:-/var/www/sidesa}"
APP_USER="${APP_USER:-www-data}"

DB_NAME="${DB_NAME:-siadesa_db}"
DB_USER="${DB_USER:-siadesa}"
DB_PASS="${DB_PASS:-}"                       # kosong -> dibuat otomatis
JWT_SECRET="${JWT_SECRET:-}"                 # kosong -> dibuat otomatis
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@${DOMAIN}}"

PHP_VER="${PHP_VER:-8.2}"
NODE_MAJOR="${NODE_MAJOR:-22}"

MODE="full"
if [[ "${1:-}" == "--update" ]]; then MODE="update"; fi

# ====================== WARNA LOG ======================
C_G="\033[1;32m"; C_Y="\033[1;33m"; C_R="\033[1;31m"; C_B="\033[1;36m"; C_N="\033[0m"
log()  { echo -e "${C_G}➜${C_N} $*"; }
warn() { echo -e "${C_Y}⚠${C_N} $*"; }
err()  { echo -e "${C_R}✖${C_N} $*" >&2; }
step() { echo -e "\n${C_B}==== $* ====${C_N}"; }

require_root() {
  if [[ "$(id -u)" -ne 0 ]]; then err "Jalankan sebagai root: sudo ./deploy.sh"; exit 1; fi
}

# ====================== FUNGSI INSTALL (FULL) ======================
install_stack() {
  step "Update sistem & install paket dasar"
  export DEBIAN_FRONTEND=noninteractive
  apt-get update -y
  apt-get install -y curl git unzip software-properties-common ufw ca-certificates gnupg lsb-release

  step "Install Nginx, PHP ${PHP_VER}, PostgreSQL, Node ${NODE_MAJOR}"
  apt-get install -y nginx postgresql postgresql-contrib \
    php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-mbstring php${PHP_VER}-xml \
    php${PHP_VER}-curl php${PHP_VER}-zip php${PHP_VER}-bcmath php${PHP_VER}-gd \
    php${PHP_VER}-pgsql php${PHP_VER}-sqlite3

  if ! command -v composer >/dev/null 2>&1; then
    log "Install Composer"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  fi

  if ! command -v node >/dev/null 2>&1; then
    log "Install Node.js ${NODE_MAJOR}"
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash -
    apt-get install -y nodejs
  fi

  if ! command -v pm2 >/dev/null 2>&1; then
    log "Install PM2"
    npm install -g pm2
  fi

  if ! command -v certbot >/dev/null 2>&1; then
    log "Install Certbot"
    apt-get install -y certbot python3-certbot-nginx
  fi
}

setup_database() {
  step "Konfigurasi PostgreSQL"
  if [[ -z "${DB_PASS}" ]]; then
    DB_PASS="$(openssl rand -base64 18 | tr -d '/+=' | cut -c1-20)"
    warn "DB_PASS dibuat otomatis: ${DB_PASS}  (catat & simpan)"
  fi

  sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1 \
    || sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';"

  sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1 \
    || sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME};"

  sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};" >/dev/null
  sudo -u postgres psql -c "ALTER DATABASE ${DB_NAME} OWNER TO ${DB_USER};" >/dev/null
  log "Database ${DB_NAME} & user ${DB_USER} siap."
}

deploy_backend() {
  step "Deploy Backend Express + Prisma"
  cd "${APP_DIR}/backend"

  if [[ -z "${JWT_SECRET}" ]]; then
    JWT_SECRET="$(openssl rand -hex 48)"
  fi

  cat > .env <<EOF
DATABASE_URL="postgresql://${DB_USER}:${DB_PASS}@localhost:5432/${DB_NAME}?schema=public"
JWT_SECRET="${JWT_SECRET}"
JWT_EXPIRES_IN="12h"
PORT=5000
NODE_ENV=production
FRONTEND_URL="https://${DOMAIN}"
EOF
  log "backend/.env ditulis."

  sudo -u "${APP_USER}" npm install --omit=dev
  sudo -u "${APP_USER}" npx prisma generate
  sudo -u "${APP_USER}" npx prisma db push

  # Seed hanya bila tabel user kosong
  if sudo -u "${APP_USER}" node -e "import('@prisma/client').then(({PrismaClient})=>{const p=new PrismaClient();p.user.count().then(c=>{process.exit(c>0?1:0)}).finally(()=>p.\$disconnect())})" 2>/dev/null; then
    log "Menjalankan seeder data awal..."
    sudo -u "${APP_USER}" node prisma/seed.js
  else
    warn "Data sudah ada, seeder dilewati."
  fi

  # PM2
  sudo -u "${APP_USER}" pm2 delete siadesa-api >/dev/null 2>&1 || true
  sudo -u "${APP_USER}" pm2 start src/server.js --name siadesa-api
  sudo -u "${APP_USER}" pm2 save
  env PATH="$PATH" pm2 startup systemd -u "${APP_USER}" --hp "$(eval echo ~${APP_USER})" >/dev/null 2>&1 || true
  log "Backend berjalan via PM2."
}

deploy_frontend() {
  step "Deploy Frontend Laravel"
  cd "${APP_DIR}"

  if [[ ! -f .env ]]; then cp .env.example .env; fi

  # Set/garansi nilai produksi
  sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
  sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
  sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
  grep -q "^APP_NAME=" .env && sed -i "s|^APP_NAME=.*|APP_NAME=\"SIADESA\"|" .env || echo 'APP_NAME="SIADESA"' >> .env
  grep -q "^SESSION_DRIVER=" .env && sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
  grep -q "^CACHE_STORE=" .env && sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|" .env
  grep -q "^VITE_API_URL=" .env && sed -i "s|^VITE_API_URL=.*|VITE_API_URL=https://${API_DOMAIN}|" .env || echo "VITE_API_URL=https://${API_DOMAIN}" >> .env
  log ".env frontend disetel untuk produksi."

  sudo -u "${APP_USER}" composer install --no-dev --optimize-autoloader --no-interaction
  sudo -u "${APP_USER}" npm ci || sudo -u "${APP_USER}" npm install
  sudo -u "${APP_USER}" npm run build

  if ! grep -q "^APP_KEY=base64" .env; then
    sudo -u "${APP_USER}" php artisan key:generate --force
  fi

  php artisan storage:link >/dev/null 2>&1 || true
  sudo -u "${APP_USER}" php artisan optimize:clear
  sudo -u "${APP_USER}" php artisan config:cache
  sudo -u "${APP_USER}" php artisan route:cache
  sudo -u "${APP_USER}" php artisan view:cache

  chown -R "${APP_USER}:${APP_USER}" "${APP_DIR}"
  chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" "${APP_DIR}/backend/storage"
  log "Frontend siap."
}

setup_nginx() {
  step "Konfigurasi Nginx"
  cat > /etc/nginx/sites-available/sidesa <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
    client_max_body_size 10M;
}
EOF

  cat > /etc/nginx/sites-available/siadesa-api <<EOF
server {
    listen 80;
    server_name ${API_DOMAIN};

    location / {
        proxy_pass http://127.0.0.1:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }
    client_max_body_size 10M;
}
EOF

  ln -sf /etc/nginx/sites-available/sidesa /etc/nginx/sites-enabled/sidesa
  ln -sf /etc/nginx/sites-available/siadesa-api /etc/nginx/sites-enabled/siadesa-api
  rm -f /etc/nginx/sites-enabled/default
  nginx -t
  systemctl reload nginx
  log "Nginx dikonfigurasi."
}

setup_firewall() {
  step "Konfigurasi Firewall (UFW)"
  ufw allow OpenSSH >/dev/null
  ufw allow 'Nginx Full' >/dev/null
  ufw --force enable >/dev/null
  log "Firewall aktif."
}

setup_ssl() {
  step "Pasang SSL Let's Encrypt"
  if certbot --nginx -d "${DOMAIN}" -d "${API_DOMAIN}" --non-interactive --agree-tos \
       -m "${ADMIN_EMAIL}" --redirect; then
    log "SSL terpasang & HTTP dialihkan ke HTTPS."
  else
    warn "SSL gagal otomatis. Pastikan DNS ${DOMAIN} & ${API_DOMAIN} sudah mengarah ke VPS, lalu jalankan:"
    warn "sudo certbot --nginx -d ${DOMAIN} -d ${API_DOMAIN}"
  fi
}

restart_services() {
  step "Restart layanan"
  systemctl restart php${PHP_VER}-fpm
  systemctl reload nginx
  sudo -u "${APP_USER}" pm2 restart siadesa-api >/dev/null 2>&1 || true
  log "Selesai."
}

print_summary() {
  echo -e "\n${C_G}==============================================${C_N}"
  echo -e "${C_G} SIADESA berhasil dideploy!${C_N}"
  echo -e " Web      : https://${DOMAIN}"
  echo -e " API      : https://${API_DOMAIN}"
  echo -e " DB       : ${DB_NAME} (user: ${DB_USER})"
  echo -e " Backend  : PM2 -> siadesa-api (port 5000)"
  echo -e "${C_G}==============================================${C_N}"
  echo -e "Perintah berguna:"
  echo -e "  pm2 status            # cek backend"
  echo -e "  pm2 logs siadesa-api  # log backend"
  echo -e "  php artisan about     # info Laravel"
}

# ====================== ALUR UTAMA ======================
main() {
  require_root

  if [[ "${MODE}" == "update" ]]; then
    step "MODE UPDATE"
    if [[ -d "${APP_DIR}/.git" ]]; then git -C "${APP_DIR}" pull || true; fi
    deploy_backend
    deploy_frontend
    restart_services
    print_summary
    exit 0
  fi

  warn "Script akan: install stack, buat DB, deploy backend & frontend, set Nginx, dan SSL."
  warn "Domain   : ${DOMAIN}"
  warn "API      : ${API_DOMAIN}"
  warn "App Dir  : ${APP_DIR}"
  read -rp "Lanjutkan instalasi penuh? (y/N): " ok
  [[ "${ok,,}" == "y" ]] || { err "Dibatalkan."; exit 1; }

  install_stack
  setup_database
  deploy_backend
  deploy_frontend
  setup_nginx
  setup_firewall
  setup_ssl
  restart_services
  print_summary
}

main
