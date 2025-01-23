# RMG Finance - A Microfinance System 🎉📊💼

RMG Finance is a comprehensive microfinance management system designed for efficiency and smooth user experience. Built with cutting-edge technologies, it ensures seamless management of microfinance operations. 🌟💻✨

---

## **Technologies Used** 🚀🔧🖥️

-   **Laravel 11**: Backend framework
-   **Livewire 3**: Interactive frontend framework
-   **Tailwind CSS**: Utility-first CSS framework
-   **DaisyUI**: UI components for Tailwind
-   **MaryUI**: Additional UI design elements

---

## **Features** 📝📈⚙️

1. **Client Management**
2. **Accounts Management**
3. **Loans Management**
4. **Transactions and Receipt Management**
5. **Notification System**
6. **Roles and Permissions Management**

---

## **Requirements** 🛠️📋✅

-   PHP version **8.2 or higher**

---

## **Installation Instructions** 🖥️📂💡

1. **Clone the Repository:**

    ```bash
    git clone <repository-url>
    ```

2. **Navigate to the Project Directory:**

    ```bash
    cd <project-directory>
    ```

3. **Install Dependencies:**

    ```bash
    composer install
    npm install
    ```

4. **Environment Configuration:**

    - Create a `.env` file by copying `.env.example` 🎛️
    - Set up database configuration:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=<your_database_name>
        DB_USERNAME=<your_database_user>
        DB_PASSWORD=<your_database_password>
        ```

5. **Run Migrations:**

    ```bash
    php artisan migrate
    ```

6. **Seed the Database:**
    - Seeders are located in the `database/seeders` folder.
    - Run the seeding command:
        ```bash
        php artisan db:seed
        ```
        **Notes:**
        - For SuperAdmin, create a user with `id=1` and run the seeder.
        - For Staff, create a user with `id=2` and run the seeder.
        - All other users will be considered customers.

---

## **Mail Configuration** ✉️📧⚡

-   Use a mail testing service like [Mailtrap.io](https://mailtrap.io).
-   Fill out mail credentials in the `.env` file:
    ```
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=<your_username>
    MAIL_PASSWORD=<your_password>
    MAIL_ENCRYPTION=tls
    ```

---

## **Realtime Notifications** 🔔📲📡

This system uses **Pusher** for real-time updates. Configure Pusher in the `.env` file:

```bash
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=<your_cluster>
```

Additionally, set up Vite variables for Pusher:

```bash
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

---

## **Scheduler Tasks** ⏰📆💼

RMG Finance uses Laravel Scheduler for periodic tasks. Add the following cron job to your server:

```bash
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

This runs the Laravel scheduler every minute, and Laravel will execute the registered tasks based on their defined schedules.

---

## **Starting the Application** 🖥️🚀🔑

1. Start the Laravel development server:
    ```bash
    php artisan serve
    ```
2. Start the Vite development server:
    ```bash
    npm run dev
    ```
3. Start the Laravel queue worker:
    ```bash
    php artisan queue:work
    ```

---

## **Happy Exploration!** 🎉🎉🎉

Enjoy exploring and managing your microfinance operations with RMG Finance. 😊👍✨

---

### **Contact Information** 📞📨🌐

For further assistance or inquiries, feel free to reach out.

Good luck with your exploration! 😉👍🎤
