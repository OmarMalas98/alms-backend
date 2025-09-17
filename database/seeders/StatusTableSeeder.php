<?php

namespace Database\Seeders;

use App\Models\Other\Status;
use Illuminate\Database\Seeder;

class StatusTableSeeder extends Seeder
{
    public function run()
    {
        // Add seed data for statuses table
        $statuses = [
            [
                'name' => 'Draft',
            ],
            [
                'name' => 'Published',
            ],
            [
                'name' => 'Archived',
            ]
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
