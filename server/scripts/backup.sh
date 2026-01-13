#!/bin/bash
# Daily backup script for WordPress database

DATE=$(date +%Y%m%d)
BACKUP_DIR=/opt/poptropica/backups

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Load environment variables
source /opt/poptropica/.env

# Database backup
docker exec mariadb mysqldump -u root -p$DB_ROOT_PASSWORD wordpress > $BACKUP_DIR/wordpress_$DATE.sql

# Compress
gzip -f $BACKUP_DIR/wordpress_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: wordpress_$DATE.sql.gz"
