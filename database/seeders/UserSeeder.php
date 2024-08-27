<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Teacher
        User::factory()->create([
            'name' => 'Teacher',
            'email' => 'teacher@app.com',
            'is_student' => false,
        ]);

        // Single Student
        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@app.com',
            'is_student' => true,
        ]);

        // Students
        User::factory(1000)->create([
            'is_student' => true,
        ]);

        $this->command->info('Users table seeded!');
    }
}
