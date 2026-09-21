<?php

namespace App\Database\Seeders;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class BeneficiarySeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('en_IN'); // Indian locale for realistic names
        $db = \Config\Database::connect();
        $builder = $db->table('beneficiaries');

        // Sample Indian names for beneficiaries
        $firstNames = [
            'Priya', 'Anjali', 'Kavya', 'Neha', 'Deepa', 'Sneha', 'Pooja', 'Meera',
            'Rajesh', 'Amit', 'Rahul', 'Rohan', 'Arun', 'Vikas', 'Sanjay', 'Manoj',
            'Isha', 'Ravi', 'Arjun', 'Bhavna', 'Shruti', 'Divya', 'Sneha', 'Rekha',
            'Suresh', 'Ramesh', 'Harish', 'Kiran', 'Anita', 'Geeta', 'Vandana', 'Seema',
            'Akshay', 'Nikhil', 'Pawan', 'Ashok', 'Lokesh', 'Naveen', 'Gaurav', 'Sachin'
        ];

        $lastNames = [
            'Sharma', 'Singh', 'Patel', 'Kumar', 'Gupta', 'Rao', 'Nair', 'Verma',
            'Joshi', 'Yadav', 'Bansal', 'Sinha', 'Das', 'Dutta', 'Mehta', 'Chopra',
            'Tripathi', 'Mishra', 'Reddy', 'Iyer', 'Nambiar', 'Chandra', 'Saxena', 'Desai'
        ];

        $cities = [
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune', 'Ahmedabad',
            'Jaipur', 'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Surat', 'Bhopal', 'Visakhapatnam',
            'Varanasi', 'Meerut', 'Aurangabad', 'Nashik', 'Jabalpur', 'Cuttack', 'Guwahati', 'Chandigarh'
        ];

        $states = [
            'Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Tamil Nadu', 'West Bengal', 'Gujarat',
            'Rajasthan', 'Uttar Pradesh', 'Madhya Pradesh', 'Punjab', 'Haryana', 'Odisha', 'Assam'
        ];

        $idTypes = ['aadhaar', 'pan', 'voter_id', 'other'];

        $statuses = ['registered', 'active', 'inactive'];

        $programs = [
            'Healthcare Program',
            'Education Scholarship',
            'Vocational Training',
            'Food Security',
            'Disaster Relief',
            'Women Empowerment'
        ];

        $beneficiaries = [];
        $usedPhones = [];

        for ($i = 0; $i < 100; $i++) {
            // Generate unique phone numbers
            do {
                $phone = '9' . rand(100000000, 999999999);
            } while (in_array($phone, $usedPhones));
            $usedPhones[] = $phone;

            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $dob = $faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d');
            $gender = rand(0, 1) ? 'male' : 'female';

            // Generate realistic Aadhaar (12 digits)
            $aadhaar = str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

            $beneficiaries[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'email' => strtolower($firstName . '.' . $lastName . '@example.com'),
                'gender' => $gender,
                'date_of_birth' => $dob,
                'address' => $faker->streetAddress() . ', ' . $cities[array_rand($cities)] . ', ' . $states[array_rand($states)],
                'unique_id' => $aadhaar,
                'unique_id_type' => $idTypes[array_rand($idTypes)],
                'latitude' => round($faker->latitude(19.0760, 28.7041), 4), // India coordinates
                'longitude' => round($faker->longitude(68.1017, 97.3025), 4),
                'status' => $statuses[array_rand($statuses)],
                'created_by' => rand(1, 3), // Created by admin, worker, or manager
                'created_at' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
            ];
        }

        // Insert in batches to avoid memory issues
        $batchSize = 10;
        for ($i = 0; $i < count($beneficiaries); $i += $batchSize) {
            $batch = array_slice($beneficiaries, $i, $batchSize);
            $builder->insertBatch($batch);
        }

        echo "✓ 100 beneficiaries seeded successfully!\n";
    }
}
