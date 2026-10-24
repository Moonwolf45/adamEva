## Запуск worker'а (production: systemd / supervisor)
bin/console messenger:consume geolocation --workers=3 --time-limit=3600

### После этого в cron можно поставить одну строку:
* * * * * bin/console queues:run --no-interaction >> var/log/queues.log 2>&1

MESSENGER_CRON_TRANSPORTS=email,cart,tracking

## Загружаем фикстуры:
bin/console doctrine:fixtures:load
