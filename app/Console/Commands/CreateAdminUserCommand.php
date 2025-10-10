<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:create-admin';

    /**
     * The console command description.
     */
    protected $description = 'Create an admin user for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Create Admin User');
        $this->info('================');
        $this->newLine();

        // Get user input
        $name = $this->ask('Enter name');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password (min 8 characters)');
        $passwordConfirmation = $this->secret('Confirm password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        // Create user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->newLine();
            $this->info('✓ Admin user created successfully!');
            $this->newLine();
            $this->table(
                ['ID', 'Name', 'Email'],
                [[$user->id, $user->name, $user->email]]
            );
            $this->newLine();
            $this->info('You can now login at: ' . config('app.url'));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
