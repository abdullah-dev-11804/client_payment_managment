#!/bin/bash
echo "Wrapper started at $(date)" >> /var/www/html/cron_wrapper.log
/usr/bin/php /var/www/html/client-payment-system/cron/send_notifications.php >> /var/www/html/cron.log 2>&1
echo "Wrapper finished at $(date)" >> /var/www/html/cron_wrapper.log