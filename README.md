### install components ###
###### composer install #######

### DB ###
###### .env set params your DB ######
###### execute console command: "php bin/console doctrine:migrations:migrate" ####### 

### Базовое заполненеие БД ###
###### Так как системы создания пользователей не реализована, так же как не реализована система создания рейсов, чтобы не заполнять в ручную бд, создан генератор базовых значений ######
###### Чтобы запустить генератор нужно ввести команду: bin/console app:default-data-create #######

### Email ###
###### в файле config/services.yaml:parameters необходимо заполнить параметры для отправки писем ######
###### нужно на странице https://myaccount.google.com/lesssecureapps разрешить небезопасные приложения))) ######
###### на странице https://mail.google.com/mail/u/0/#settings/fwdandpop включить IMAP ######

### User documentation ###
##### for address: #####
###### /ticket-booking ######
###### /ticket-cancel-booking ######
###### /ticket-sale ######
###### /ticket-cancel-sale ######
##### need Get params: #####
###### secret_key - user secret key ######
###### flight - id flight ######
###### seat - seat number from 0 to 150 ######

##### for address: #####
###### /events ######
##### need Get params: #####
###### secret_key - user secret key ######
###### flight - id flight ######
###### event - event name ######
