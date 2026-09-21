<?php

namespace App\Database\Seeders;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class DonorSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('en_IN');
        $db = \Config\Database::connect();
        $builder = $db->table('donors');

        // Sample Indian donor names
        $individualFirstNames = [
            'Rajesh', 'Priya', 'Amit', 'Anjali', 'Vikram', 'Neha', 'Arun', 'Deepa',
            'Suresh', 'Kavya', 'Rohan', 'Seema', 'Harish', 'Geeta', 'Naveen', 'Pooja'
        ];

        $individualLastNames = [
            'Sharma', 'Singh', 'Patel', 'Kumar', 'Gupta', 'Rao', 'Nair', 'Verma',
            'Joshi', 'Yadav', 'Bansal', 'Sinha', 'Das', 'Dutta', 'Mehta', 'Chopra'
        ];

        $organizationNames = [
            'ABC Foundation',
            'Care and Support NGO',
            'Global Development Initiative',
            'Mercy International',
            'Hope for All India',
            'Community Action Trust',
            'Empowerment First Organization',
            'United Way India',
            'Reaching Out Foundation',
            'Social Impact Partners'
        ];

        $cities = [
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Pune',
            'Ahmedabad', 'Jaipur', 'Kolkata', 'Chandigarh'
        ];

        $states = [
            'Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Tamil Nadu',
            'Gujarat', 'Rajasthan', 'West Bengal', 'Punjab', 'Haryana'
        ];

        $types = ['individual', 'organization', 'corporate'];
        $statuses = ['active', 'inactive', 'pending'];

        // Donation amounts ranging from 1,000 to 10,000,000
        $donationAmounts = [
            1000,           // Individual - small donation
            5000,           // Individual - regular donation
            10000,          // Individual - generous donation
            25000,          // Individual - large donation
            50000,          // Organization - regular support
            100000,         // Organization - substantial support
            250000,         // Organization - major support
            500000,         // Corporate - significant contribution
            1000000,        // Corporate - major donation
            10000000        // Corporate - principal donor
        ];

        $donors = [];

        // Donor 1: Individual - Small Donor
        $donors[] = [
            'name' => 'Rajesh ' . $individualLastNames[array_rand($individualLastNames)],
            'type' => 'individual',
            'email' => 'rajesh.donor@gmail.com',
            'phone' => '9876543210',
            'address' => '123 Bandra Street, Mumbai',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip_code' => '400050',
            'total_contribution' => 1000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-12 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 2: Individual - Regular Donor
        $donors[] = [
            'name' => 'Priya ' . $individualLastNames[array_rand($individualLastNames)],
            'type' => 'individual',
            'email' => 'priya.supporter@gmail.com',
            'phone' => '9876543211',
            'address' => '456 Connaught Place, Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'zip_code' => '110001',
            'total_contribution' => 5000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 3: Individual - Generous Donor
        $donors[] = [
            'name' => 'Amit ' . $individualLastNames[array_rand($individualLastNames)],
            'type' => 'individual',
            'email' => 'amit.giving@gmail.com',
            'phone' => '9876543212',
            'address' => '789 MG Road, Bangalore',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'zip_code' => '560001',
            'total_contribution' => 10000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 4: Individual - Large Donor
        $donors[] = [
            'name' => 'Vikram ' . $individualLastNames[array_rand($individualLastNames)],
            'type' => 'individual',
            'email' => 'vikram.philanthropist@gmail.com',
            'phone' => '9876543213',
            'address' => '321 Jubilee Hills, Hyderabad',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'zip_code' => '500033',
            'total_contribution' => 25000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 5: Organization - Regular Support
        $donors[] = [
            'name' => 'Care and Support NGO',
            'type' => 'organization',
            'email' => 'contact@careandsuport.org',
            'phone' => '9876543214',
            'address' => '654 K.K. Nagar, Chennai',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'zip_code' => '600078',
            'total_contribution' => 50000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-9 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 6: Organization - Substantial Support
        $donors[] = [
            'name' => 'Global Development Initiative',
            'type' => 'organization',
            'email' => 'grants@globaldev.org',
            'phone' => '9876543215',
            'address' => '987 Model Town, Pune',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'zip_code' => '411016',
            'total_contribution' => 100000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 7: Organization - Major Support
        $donors[] = [
            'name' => 'Reaching Out Foundation',
            'type' => 'organization',
            'email' => 'donations@reachingoutfound.org',
            'phone' => '9876543216',
            'address' => '111 Science City, Ahmedabad',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'zip_code' => '380058',
            'total_contribution' => 250000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-11 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 8: Corporate - Significant Contribution
        $donors[] = [
            'name' => 'ABC Foundation (Corporate)',
            'type' => 'corporate',
            'email' => 'csr@abcfoundation.com',
            'phone' => '9876543217',
            'address' => '222 Corporate Plaza, Gurgaon',
            'city' => 'Gurgaon',
            'state' => 'Haryana',
            'zip_code' => '122001',
            'total_contribution' => 500000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 9: Corporate - Major Donation
        $donors[] = [
            'name' => 'Empowerment First Organization',
            'type' => 'corporate',
            'email' => 'corporate.giving@empower.org',
            'phone' => '9876543218',
            'address' => '333 Business Hub, Bangalore',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'zip_code' => '560102',
            'total_contribution' => 1000000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 months')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Donor 10: Corporate - Principal Donor (Highest)
        $donors[] = [
            'name' => 'United Way India (Principal)',
            'type' => 'corporate',
            'email' => 'partnerships@unitedwayindia.org',
            'phone' => '9876543219',
            'address' => '444 Premier Building, Mumbai',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip_code' => '400051',
            'total_contribution' => 10000000,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Insert all donors
        $builder->insertBatch($donors);

        echo "✓ 10 donors seeded successfully!\n";
        echo "  Total contributions: ₹" . number_format(array_sum(array_column($donors, 'total_contribution')), 0, ',') . "\n";
        echo "  Average donation: ₹" . number_format(array_sum(array_column($donors, 'total_contribution')) / 10, 0, ',') . "\n";
    }
}
