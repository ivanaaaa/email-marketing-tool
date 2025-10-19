# Email Marketing Tool - MVP

A Laravel 12 + Vue.js application for managing customers, groups, email templates, and mass email campaigns.

## 🚀 Features

- **User Authentication** - Secure login and registration (Laravel 12 Vue Starter Kit)
- **Customer Management** - Add, edit, and delete customers with email, name, sex, and birth date
- **Group Management** - Create groups and assign multiple customers to groups
- **Email Templates** - Create reusable email templates with placeholder support
- **Campaign Management** - Create, schedule, and send mass email campaigns
- **Queue Processing** - Background email sending with progress tracking
- **Batch Processing** - Efficient handling of large customer lists

## 📋 Requirements

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL
- Git

## Demo
- Customers


https://github.com/user-attachments/assets/57bb5b40-5451-4784-a39e-4c5d7ceca5ce


- Groups


https://github.com/user-attachments/assets/b5c61ed0-c238-4fdc-8438-23686e2df711


- Email Template


https://github.com/user-attachments/assets/69811672-6f93-4557-baa9-f4ac26bc66ee



- Campaigns


https://github.com/user-attachments/assets/2b51f5d5-0225-47d0-8ad7-3ad00af80094




## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ivanaaaa/email-marketing-tool
cd email-marketing-tool
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` and configure your database and email:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=email_marketing_tool
DB_USERNAME=root
DB_PASSWORD=your_password

QUEUE_CONNECTION=database

# Email Configuration (use Mailtrap for testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Create Database

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Build Frontend Assets

```bash
npm run build
# or for development with hot reload:
npm run dev
```

### 8. Create First Admin User

Before accessing the application, create an admin user:

```bash
php artisan user:create-admin
```

You will be prompted to enter:
- Name
- Email
- Password (min 8 characters)
- Password confirmation

**Note**: Registration is disabled by default. All admin users must be created via this command.

## 🏃 Running the Application

### Step 1: Start the Development Server

Open your first terminal and run:

```bash
php artisan serve
```

The application will be available at **http://localhost:8000**

---

### Step 2: Start the Frontend Development Server

Open a second terminal and run:

```bash
npm run dev
```

This runs Vite and enables hot-reload for Vue.js components.

---

### Step 3: Start the Queue Worker

Open a third terminal and run:

```bash
php artisan queue:work --verbose
```

This processes background jobs like sending emails. The `--verbose` flag shows detailed output.

**Keep this terminal running** - it processes all email campaigns!

---

### Step 4: Start the Task Scheduler (For Scheduled Campaigns)

Open a fourth terminal and run:

```bash
php artisan schedule:work
```

This checks every minute for campaigns that are ready to be sent.

**Keep this terminal running** if you want scheduled campaigns to work automatically!

---

### Quick Start Summary

**You need 4 terminal windows running simultaneously:**

| Terminal | Command | Purpose |
|----------|---------|---------|
| **1** | `php artisan serve` | Web server (Laravel) |
| **2** | `npm run dev` | Frontend (Vue.js hot-reload) |
| **3** | `php artisan queue:work --verbose` | Email sending (background jobs) |
| **4** | `php artisan schedule:work` | Campaign scheduler (checks every minute) |

---

### Accessing the Application

1. Open your browser and go to: **http://localhost:8000**
2. Login with the admin user you created
3. Start creating customers, groups, templates, and campaigns!

---

### ⚠️ Important Notes

- **Queue Worker (Terminal 3)** must be running for emails to send
- **Scheduler (Terminal 4)** must be running for scheduled campaigns to work automatically
- If you close any terminal, that feature will stop working
- You can manually trigger scheduled campaigns with: `php artisan api:check-scheduled-campaigns-command`

---

## 📝 Usage Guide

### 1. Login

**Important**: Registration is disabled for security. Create users with:
```bash
php artisan user:create-admin
```

- Navigate to `http://localhost:8000`
- Login with the credentials you created

### 2. Add Customers

- Go to **Customers** → **Add Customer**
- Fill in email, first name, last name (required)
- Optionally add sex and birth date
- Optionally assign to groups

### 3. Create Groups

- Go to **Groups** → **Create Group**
- Enter group name and description
- Assign customers to the group

### 4. Create Email Templates

- Go to **Email Templates** → **Create Template**
- Enter template name, subject, and body
- Use placeholders:
    - `{{first_name}}` - Customer's first name
    - `{{last_name}}` - Customer's last name
    - `{{full_name}}` - Customer's full name
    - `{{email}}` - Customer's email
    - `{{sex}}` - Customer's sex
    - `{{birth_date}}` - Customer's birth date

Example template:
```
Subject: Hello {{first_name}}!

Body:
Dear {{first_name}} {{last_name}},

This is a personalized email sent to {{email}}.

Best regards,
Marketing Team
```

### 5. Create and Send Campaigns

- Go to **Campaigns** → **Create Campaign**
- Enter campaign name
- Select email template
- Select target groups (at least one)
- Choose to:
    - **Save as Draft** - Leave "Schedule For" empty to save for later
    - **Schedule** - Set a future date/time to automatically send at that time
    - **Send Now** - Click "Send Now" button on a draft campaign to send immediately

**How Scheduling Works:**
- If you fill in "Schedule For" with a future date/time, the campaign automatically becomes "scheduled"
- The scheduler (Terminal 4) checks every minute for campaigns ready to send
- When the scheduled time arrives, emails are sent automatically
- You can also click "Send Immediately" on a scheduled campaign to override the schedule

### 6. Monitor Campaign Progress

- Go to **Campaigns** to view all campaigns
- Click on a campaign to see:
    - Total recipients
    - Sent count
    - Failed count
    - Progress percentage
    - Status (draft, scheduled, processing, completed, failed)

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=EmailTemplateServiceTest

# Run with coverage
php artisan test --coverage
```

## 🗄️ Database Schema

### Tables

- `users` - Application users
- `customers` - Customer data (email, name, sex, birth_date)
- `groups` - Customer groups
- `customer_group` - Many-to-many pivot table
- `email_templates` - Reusable email templates
- `campaigns` - Email campaigns
- `campaign_group` - Campaign target groups
- `jobs` - Queue jobs table
- `failed_jobs` - Failed jobs table

### Key Relationships

- User **has many** Customers, Groups, EmailTemplates, Campaigns
- Customer **belongs to many** Groups
- Group **belongs to many** Customers, Campaigns
- Campaign **belongs to** EmailTemplate
- Campaign **belongs to many** Groups

## ⚙️ Configuration

### Scheduled Tasks

Scheduled tasks are defined in `routes/console.php` (Laravel 12):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('api:check-scheduled-campaigns-command')
    ->everyMinute()
    ->withoutOverlapping();
```

### Queue Configuration

The application uses database queue driver by default. Configure in `.env`:

```env
QUEUE_CONNECTION=database
```

For better performance in production, consider using Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📧 Email Sending

The application uses **Laravel Notifications** for sending emails:

```php
// Using Laravel's notification system
$customer->notify(new CampaignEmailNotification($subject, $body));
```

### Benefits of Laravel Notifications

- ✅ Built into Laravel (no external dependencies)
- ✅ Automatic queuing support
- ✅ Easy to test and mock
- ✅ Supports multiple channels (email, SMS, Slack, etc.)
- ✅ Better error handling and logging

### Campaign Processing Flow

1. User creates campaign:
    - If `scheduled_at` is empty → Status: `draft`
    - If `scheduled_at` has value → Status: `scheduled`
2. For immediate sending: User clicks "Send Now" button
3. Campaign status set to "scheduled" with `scheduled_at = now()`
4. Job dispatched to queue (`ProcessCampaignJob`)
5. Queue worker picks up job
6. `EmailSendingService` processes campaign:
    - Updates status to "processing"
    - Fetches customers in chunks (100 at a time)
    - Replaces placeholders for each customer (e.g., `{{first_name}}` → actual name)
    - Sends notification to each customer
    - Updates sent/failed counts periodically
    - Marks campaign as "completed" when done

### Scheduled Campaigns

- Command `api:check-scheduled-campaigns-command` runs every minute (via scheduler in `routes/console.php`)
- Checks for campaigns with `status='scheduled'` AND `scheduled_at <= now()`
- Dispatches `ProcessCampaignJob` for each ready campaign
- Manual trigger: `php artisan api:check-scheduled-campaigns-command`

## 🎨 Frontend (Vue.js)

### Technology Stack

- **Vue 3** with Composition API
- **TypeScript** for type safety
- **Inertia.js** for seamless client-server communication
- **Tailwind CSS** for styling
- **shadcn-vue** component library

### Key Pages

- `Customers/Index.vue` - List customers with search
- `Customers/Create.vue` - Create new customer
- `Customers/Edit.vue` - Edit customer
- `Groups/Index.vue` - List groups with customer counts
- `Groups/Show.vue` - View group details and members
- `EmailTemplates/Index.vue` - List templates with available placeholders
- `EmailTemplates/Create.vue` - Create template with placeholder hints
- `Campaigns/Index.vue` - List campaigns with status badges
- `Campaigns/Create.vue` - Create campaign with optional scheduling
- `Campaigns/Show.vue` - Campaign details, progress, and statistics

## 🔐 Security & Authorization

### Policies

Every model has a policy ensuring users can only access their own data:

- `CustomerPolicy` - Verify customer belongs to user
- `GroupPolicy` - Verify group belongs to user
- `EmailTemplatePolicy` - Verify template belongs to user
- `CampaignPolicy` - Verify campaign belongs to user

### Validation

Form Request classes validate all inputs:
- Email uniqueness per user
- Required fields
- Date formats
- Foreign key existence
- Scheduled dates must be in the future (for create/update)

## 📝 Development Notes

### Code Quality

- PSR-12 coding standards
- Type hints on all parameters and return types
- Comprehensive DocBlocks
- Separation of concerns (Services, Controllers, Models)
- DRY principle (Don't Repeat Yourself)

### Testing Strategy

- Unit tests for services and models
- Feature tests for controllers and integration
- Factory classes for test data generation
- Database transactions in tests (automatic rollback)
