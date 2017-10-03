2.3.1
============
* a7abb5c Merged in add_by_receipt_payment (pull request #115) add "by_receipt" payment type
* 2421f94 Merged in #1081_adding_field_of_pers_data_to_online_form (pull request #114) add field with url of consent with processing of personal data
* 6eea5eb Merged in hide_children_ages_input_if_children_ages_not_display (pull request #112) hide children input
* 31f5b87 Merge branches '#25_dynamic_sales_report' and 'master' of bitbucket.org:MaxiBookingTeam/maxibooking-hotel into #25_dynamic_sales_report


2.3.0
============
* merged #1053#1051#864#1037#1027#1021#988#837
* fixes some recently bugs
* move from parameters.yml to hotel_config
* and so on, and so on

2.2.4
============
* add query statistic

2.2.3
============
* add rounded price
* add mb user to recipients
* fix config bug in dev mode
* fix #1020

2.2.2
============
* move channelmanager update in background to RabbitMQ Queue

2.2.1
============
* form iframe change size

2.2.0
============
* \#968 rebuild message restriction
* add messagetypes fixtures
* add messagetypes migrations
* change some fixtures 

2.1.1
============
* \#974 fix generate doc fms
* split queues 
* add dedicated consumer cache recalculate 

2.1.0
============
* add mail restriction for different mailer types
* add global swiftmailer log

2.0.0-beta.3
============
* merge Xpedia branch
* merge translation branch

2.0.0-beta.2
============
 
* add universal cli command [reference](http://redmine.maxi-booking.ru/issues/926#note-3)
* fix protected upload path in stamp and documents

2.0.0-beta.1
============
* doctrine mongodb vendor update
* add acl cache
* add fork acl provider https://github.com/webmalc/MongoDBAclBundle.git
* add primes to PackageRepository::fetch()
* change images to Image document in Hotel  and RoomType
* change logo in mail templates
* check empty email addresses before generate mail (add to log if emtpy) 
* check is file exists before attach to mail
* add priority to roomType images
* each client in own file folder
* change task runner (rabbitmq consumer) to multi clients
* add service get all clients 
* add generator for ChannelManager pull queue in RMQ
* add complex indexes in some repos
* add MBHBillingBundle, move maintenance commands
* add cache:clear command for all users


1.5.10
============
* online form improvements && fixes
* package searchDelay
* cashDocuments - search by payer
* user locale

1.5.9
=============
* new rabbitmq (redmine #644)
* fix translations

1.5.8
=============
* booking.com: fix (redmine #717)
* chessboard: fix
* merged translations

1.5.7
=============
* channelManager: overview (redmine #667)
* add validation to package accommodation entity

1.5.6
=============
* booking.com: pull old reservations (redmine #590)
* booking.com: errors && logs (redmine #595)
* booking.com: auto tariffs fix (redmine #608)
* booking.com: config validation (redmine #642)
* booking.com: validation
* services dates (redmine #49)
* package main form: tariff field (redmine #602)

1.5.5
=============
* online form fixes

1.5.4
=============
* new fixtures and tests

1.5.3
=============
* docker redis port
* chessboard bug fixes
* fix package total (redmine #572)

1.5.2
=============
* fix rabbitmq consumer
* minor fix ostrovok

1.5.1
=============
* chessboard bug fixes

1.5.0
=============
* online form in iframe (redmine #487) 
* negative promotions (redmine #537)
