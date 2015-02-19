MaxiBooking Hotel project
========================

###Installation###
    
1. git clone git@bitbucket.org:MaxiBookingTeam/maxibooking-hotel.git
2. install nodejs, npm, uglify-js, uglifycss, less
3. create app/config/parameters.yml
4. composer install
5. scripts/permissions.sh
6. bin/console assets:install --symlink
7. bin/console fos:js-routing:dump
8. bin/console assetic:dump
9. add crontab tasks (bin/console mbh:env:clear; bin/console mbh:cache:generate --no-debug --env=prod; bin/console mbh:cache:check --no-debug) 