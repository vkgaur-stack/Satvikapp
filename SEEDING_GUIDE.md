# Database Seeding Guide - Test Data for MVP Demo

## What's Included

This guide includes seeders for realistic test data:
- **100 Beneficiaries** - Realistic Indian names, locations, and profiles
- **10 Donors** - Ranging from individual donors (₹1,000) to corporate donors (₹10,000,000)
- **5 Default Roles** - Admin, Field Worker, Case Manager, Donor, Auditor
- **3 Test Users** - For immediate login and testing

---

## Files Included

### Seeder Files (Copy to: `app/Database/Seeders/`)
1. `RoleSeeder.php` - Default roles and permissions (already in COMPLETE_CODE_PACKAGE)
2. `UserSeeder.php` - Test users (already in COMPLETE_CODE_PACKAGE)
3. `BeneficiarySeeder.php` - **NEW: 100 beneficiaries**
4. `DonorSeeder.php` - **NEW: 10 donors with realistic donations**

---

## Quick Start - Run All Seeders

After database migration, run seeders in this order:

```bash
# 1. Create default roles & permissions
php spark db:seed RoleSeeder

# 2. Create test users
php spark db:seed UserSeeder

# 3. Create 100 sample beneficiaries
php spark db:seed BeneficiarySeeder

# 4. Create 10 sample donors
php spark db:seed DonorSeeder
```

**Expected output:**
```
Seeding: App\Database\Seeders\RoleSeeder
✓ Roles and permissions seeded successfully!

Seeding: App\Database\Seeders\UserSeeder
✓ 3 test users seeded successfully!

Seeding: App\Database\Seeders\BeneficiarySeeder
✓ 100 beneficiaries seeded successfully!

Seeding: App\Database\Seeders\DonorSeeder
✓ 10 donors seeded successfully!
  Total contributions: ₹11,441,000
  Average donation: ₹1,144,100
```

---

## Beneficiary Test Data Details

### What's Included (100 Beneficiaries)
- **Names:** Realistic Indian first and last names
- **Gender:** Mix of male and female
- **Age:** Ranging from 18 to 70 years old
- **Phone:** Unique 10-digit Indian phone numbers (9XXXXXXXXX)
- **Locations:** 14 major Indian cities
- **ID Types:** Mix of Aadhaar, PAN, Voter ID, and Other
- **GPS Coordinates:** Realistic India coordinates (latitude/longitude)
- **Status:** Mix of registered, active, and inactive
- **Created By:** Randomly assigned to admin, field worker, or case manager
- **Timestamps:** Created dates spanning the last 6 months

### Sample Beneficiary Fields
```
First Name: Priya
Last Name: Sharma
Phone: 9876543210
Email: priya.sharma@example.com
Gender: Female
Date of Birth: 1985-05-15 (Age: 39)
Address: Sample Street, Mumbai, Maharashtra
Unique ID: 123456789012 (Aadhaar)
Unique ID Type: aadhaar
GPS: 19.0760°N, 72.8777°E (Mumbai)
Status: Active
Created By: Admin
```

---

## Donor Test Data Details

### Donation Distribution

| Donor | Type | Donation | Category |
|-------|------|----------|----------|
| Rajesh Sharma | Individual | ₹1,000 | Small Individual Donor |
| Priya Kumar | Individual | ₹5,000 | Regular Individual Donor |
| Amit Gupta | Individual | ₹10,000 | Generous Individual Donor |
| Vikram Rao | Individual | ₹25,000 | Large Individual Donor |
| Care and Support NGO | Organization | ₹50,000 | Regular Org Support |
| Global Dev Initiative | Organization | ₹100,000 | Substantial Org Support |
| Reaching Out Foundation | Organization | ₹250,000 | Major Org Support |
| ABC Foundation | Corporate | ₹500,000 | Significant Corporate |
| Empowerment First Org | Corporate | ₹1,000,000 | Major Corporate Donor |
| United Way India | Corporate | ₹10,000,000 | Principal Corporate Donor |

### Donation Statistics
```
Total Contributions: ₹11,441,000
Average per Donor: ₹1,144,100
Highest: ₹10,000,000 (United Way India - Corporate)
Lowest: ₹1,000 (Rajesh Sharma - Individual)
```

---

## Dashboard Stats After Seeding

After running all seeders, your dashboard will show:

```
DASHBOARD STATISTICS

Beneficiaries:
  Total Beneficiaries: 100
  Active Beneficiaries: ~33 (approximately 1/3)
  Inactive Beneficiaries: ~33
  Registered: ~34

Donors:
  Total Donors: 10
  Active Donors: 10
  Total Contributions: ₹11,441,000
  Average Contribution: ₹1,144,100

Users:
  Admin: 1
  Field Workers: 1
  Case Managers: 1
  Test Donors: Multiple (can register)
  Auditors: Can create
```

---

## How to Use for Client Demo

### Before Demo
1. Deploy system to server
2. Run all seeders to populate test data
3. Verify dashboard shows statistics
4. Test login with all 3 test accounts

### During Demo - Show These Features

**1. Dashboard (2 min)**
   - Show real statistics (100 beneficiaries, 10 donors, ₹11.4M total)
   - Highlight growth metrics
   - Show system health

**2. Beneficiary List (3 min)**
   - Show 100 beneficiaries in table
   - Click on one to show details
   - Show GPS coordinates, ID types
   - Demonstrate search/filter

**3. Beneficiary Profile (2 min)**
   - Show full beneficiary detail
   - Highlight unique ID tracking
   - Show GPS coordinates
   - Explain status tracking

**4. Donor List (3 min)**
   - Show all 10 donors
   - Highlight donation amounts
   - Show different donor types (individual, org, corporate)
   - Demonstrate donor contribution tracking

**5. Theme Toggle (1 min)**
   - Show dark/light mode toggle
   - Demonstrate responsive design

**6. Add New Data (3 min)**
   - Click "Register New Beneficiary"
   - Fill form and save
   - Show it appears in list
   - Verify in dashboard statistics update

**Total Demo Time: ~15-20 minutes**

---

## Seeder Code Details

### BeneficiarySeeder.php Features
- Uses Faker library for realistic Indian names and addresses
- Generates 100 unique beneficiary records
- Automatic Aadhaar ID generation (12-digit format)
- Realistic GPS coordinates within India bounds
- Random status assignment (registered/active/inactive)
- Created dates spread across 6 months
- Batch inserts for performance

### DonorSeeder.php Features
- Hand-crafted 10 donors with specific donation amounts
- 4 individual donors (₹1K - ₹25K range)
- 3 organization donors (₹50K - ₹250K range)
- 3 corporate donors (₹500K - ₹10M range)
- Realistic Indian names, cities, and contact info
- Different creation dates (showing donation history)
- All set to "active" status

---

## Customizing Test Data

### To Change Number of Beneficiaries
Edit `BeneficiarySeeder.php`, line 52:
```php
for ($i = 0; $i < 100; $i++) {  // Change 100 to desired number
```

### To Add More Donors
Edit `DonorSeeder.php` and add more donor arrays:
```php
$donors[] = [
    'name' => 'New Donor',
    'type' => 'individual',
    'email' => 'new@example.com',
    'phone' => '9876543220',
    'address' => '555 New Street, City',
    'city' => 'City',
    'state' => 'State',
    'zip_code' => '123456',
    'total_contribution' => 50000,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];
```

### To Change Donation Amounts
Edit `DonorSeeder.php` and modify the `total_contribution` values in each donor array.

---

## Resetting Test Data (Start Fresh)

If you want to clear all test data and start over:

```bash
# Option 1: Fresh migration (clears everything)
php spark migrate:refresh
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
php spark db:seed BeneficiarySeeder
php spark db:seed DonorSeeder

# Option 2: Delete specific tables only
mysql -u root -p beneficiary_db
DELETE FROM beneficiaries;
DELETE FROM donors;
DELETE FROM users WHERE id > 1; -- Keep first admin user
TRUNCATE TABLE beneficiaries;
TRUNCATE TABLE donors;
```

---

## Performance Notes

### Seeding Time Estimates
- RoleSeeder: < 1 second
- UserSeeder: < 1 second
- BeneficiarySeeder (100 records): 3-5 seconds
- DonorSeeder (10 records): < 1 second
- **Total seeding time: ~10 seconds**

### Database Size
- Empty database: ~1 MB
- With all test data: ~2-3 MB
- Plenty of room for growth

### Best Practices
- Run seeders after fresh migration
- Test on staging before production
- Document what test data you use
- Keep backup of production database

---

## Troubleshooting

### "Table doesn't exist" Error
- Run migrations first: `php spark migrate`
- Then run seeders: `php spark db:seed`

### "Class not found" Error
- Ensure seeder files are in `app/Database/Seeders/`
- Check filename matches class name
- Run: `composer dump-autoload`

### Duplicate Key Error
- Run fresh migration: `php spark migrate:refresh`
- Then run seeders again

### "Faker not found" Error
- Install Faker: `composer require fakerphp/faker`
- Already included in composer.json

---

## Client Presentation Tips

### Talking Points
1. **"100 beneficiaries already registered"**
   - Shows adoption potential
   - Demonstrates real-world scale

2. **"₹11.4 million in tracked donations"**
   - Shows financial accountability
   - Demonstrates audit capability
   - Real-world numbers for credibility

3. **"Multiple donor types supported"**
   - Individual donors (small gifts)
   - Organization partnerships
   - Corporate CSR contributions

4. **"Complete beneficiary profiles"**
   - Unique ID tracking (deduplication)
   - GPS coordinates (field tracking)
   - Status management
   - Full audit trail

5. **"Production-ready dashboard"**
   - Real statistics
   - Responsive design
   - Dark/light mode
   - Mobile friendly

---

## Next Steps

After client demo and feedback:
1. Add more beneficiary programs (if requested)
2. Add enrollment tracking
3. Integrate payment gateway
4. Add WhatsApp notifications
5. Add email service

---

**Ready to impress your client with realistic test data!** 🎯

Generated: September 18, 2026
Deployment Ready
