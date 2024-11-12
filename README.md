RMG FINANCE SYSTEM
    This is software is built on laravel 11 , livewire 3, tailwind, daisy ui and maryui.
    It is completely designed for smoothness 
    
    Features of the software
    > Client Management
    > Accounts Management
    > Loans Management
    > Transactions and receipt Management
    > Notification system
    > Roles and Permissions Management


.env configurations
DB_CONNECTION
    create a database give it a name then migrate by running the following command
        [ php artisan migrate ]
    
Mail 
    use any mail tester of your choices
    fill out with credentials given to u
    suitable for testing for this project -> mailtrap.io

Realtime handler
Pusher 
    BROADCAST_DRIVER=pusher
    PUSHER_APP_ID=
    PUSHER_APP_KEY=
    PUSHER_APP_SECRET=
    # PUSHER_HOST=127.0..1
    # PUSHER_PORT=443
    PUSHER_SCHEME=https
    PUSHER_APP_CLUSTER=""

then this for vite
    VITE_PUSHER_APP_KEY ="${PUSHER_APP_KEY}"
    VITE_PUSHER_HOST ="${PUSHER_HOST}"
    VITE_PUSHER_PORT ="${PUSHER_PORT}"
    VITE_PUSHER_SCHEME = "${PUSHER_SCHEME}"
    VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"


########################

After migration 
    check out the seeders folder
    make sure you seed the database with this command 
    [ php artisan db:seed]

    for SuperAdmin seeder
        creating a user with id 1 in the users table then seed
    
    for Staff seeder
        creating a user with id 2 in the users table then seed

    Then the rest will be customers 


Start the servers
    php artisan serve
    npm run dev
    php artisan queue:work

Happy Exploration 😉👍