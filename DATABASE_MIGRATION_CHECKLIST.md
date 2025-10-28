# FPS Database Migration Checklist

**Version**: v0.1.2.12
**Total Migrations**: 81 files
**Target Database**: MySQL 8.0+
**Estimated Duration**: 5-10 minutes

---

## Pre-Migration Checklist

Before running migrations, verify:

- [ ] MySQL 8.0+ is installed and running
- [ ] Database created (e.g., `fpsv2_production`)
- [ ] Database user created with ALL PRIVILEGES
- [ ] `.env` file configured with correct database credentials
- [ ] PHP 8.2+ installed with required extensions (PDO, PDO_MySQL)
- [ ] Database connection tested: `php artisan db:show`

---

## Migration Command

Run this command from your Laravel root directory:

```bash
php artisan migrate --force
```

**Note**: The `--force` flag is required in production environment.

---

## Expected Output

You should see output similar to this:

```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (45.23ms)
Migrating: 2014_10_12_100000_create_password_reset_tokens_table
Migrated:  2014_10_12_100000_create_password_reset_tokens_table (32.15ms)
...
(continues for all 81 migrations)
```

---

## Tables Created (By Module)

### Core System Tables (9 tables)

- [x] `migrations` - Migration tracking
- [x] `users` - User accounts
- [x] `password_reset_tokens` - Password reset functionality
- [x] `failed_jobs` - Failed queue jobs
- [x] `personal_access_tokens` - Sanctum API authentication
- [x] `jobs` - Queue system for background tasks
- [x] `job_batches` - Batch queue jobs
- [x] `cache` - Application cache
- [x] `cache_locks` - Cache locking mechanism

### User Profile & Family (2 tables)

- [x] `family_members` - Family member records
- [x] `spouse_permissions` - Granular permissions for spouse access

### Protection Module (4 tables)

- [x] `protection_profiles` - Main protection profile
- [x] `life_insurance_policies` - Life insurance policies
- [x] `critical_illness_policies` - Critical illness policies
- [x] `income_protection_policies` - Income protection policies

### Savings Module (3 tables)

- [x] `savings_accounts` - Savings accounts (including Cash ISAs)
- [x] `savings_goals` - Savings goals tracking
- [x] `isa_allowance_tracking` - ISA allowance usage tracking

### Investment Module (6 tables)

- [x] `investment_accounts` - Investment accounts (including S&S ISAs)
- [x] `holdings` - Individual holdings within accounts
- [x] `investment_goals` - Investment goals
- [x] `risk_profiles` - Risk tolerance profiles
- [x] `monte_carlo_simulations` - Monte Carlo simulation results
- [x] `monte_carlo_simulation_paths` - Individual simulation paths

### Retirement Module (4 tables)

- [x] `dc_pensions` - Defined Contribution pensions
- [x] `db_pensions` - Defined Benefit pensions
- [x] `state_pensions` - State pension records
- [x] `retirement_profiles` - Retirement planning profiles

### Estate Planning Module (13 tables)

- [x] `assets` - Generic estate assets
- [x] `liabilities` - Liabilities (loans, credit cards, etc.)
- [x] `properties` - Property assets
- [x] `mortgages` - Mortgage liabilities
- [x] `business_interests` - Business ownership
- [x] `chattels` - Personal possessions (art, jewelry, etc.)
- [x] `cash_accounts` - Cash accounts
- [x] `gifts` - Gifting records (PETs, CLTs)
- [x] `trusts` - Trust structures
- [x] `iht_profiles` - Inheritance Tax profiles
- [x] `wills` - Will configurations
- [x] `bequests` - Bequest details
- [x] `beneficiaries` - Will beneficiaries

### Configuration Tables (2 tables)

- [x] `tax_configurations` - UK tax rules and thresholds
- [x] `life_expectancy_tables` - Actuarial life expectancy data

---

## Critical Fields & Constraints

### Key Foreign Keys

All module tables link to `users` table via `user_id`:
- ON DELETE CASCADE (deleting user removes all related data)
- Indexed for performance

### Joint Ownership

Tables supporting joint ownership:
- `properties` → `joint_owner_id`
- `investment_accounts` → `joint_owner_id`
- `savings_accounts` → `joint_owner_id`
- `business_interests` → `joint_owner_id`
- `chattels` → `joint_owner_id`
- `mortgages` → `joint_owner_id`

### Trust Ownership

Tables supporting trust ownership:
- `properties` → `trust_id`
- `investment_accounts` → `trust_id`
- `savings_accounts` → `trust_id`
- `business_interests` → `trust_id`
- `chattels` → `trust_id`

### Spouse Linking

- `users.spouse_id` → Links to another `users.id`
- Bidirectional relationship
- Set via Family Members module

---

## Post-Migration Steps

### Step 1: Verify All Tables Created

```bash
php artisan db:table
```

**Expected Count**: 45+ tables

### Step 2: Check Specific Tables Exist

```bash
# Check core tables
mysql -u DB_USER -p DB_NAME -e "SHOW TABLES LIKE '%users%';"
mysql -u DB_USER -p DB_NAME -e "SHOW TABLES LIKE '%protection%';"
mysql -u DB_USER -p DB_NAME -e "SHOW TABLES LIKE '%estate%';"
```

### Step 3: Seed Tax Configuration

**CRITICAL**: This must be run after migrations!

```bash
php artisan db:seed --class=TaxConfigurationSeeder --force
```

**What This Seeds**:

1. **Income Tax (2025/26)**:
   - Personal Allowance: £12,570
   - Basic Rate (20%): £12,571 - £50,270
   - Higher Rate (40%): £50,271 - £125,140
   - Additional Rate (45%): Over £125,140

2. **National Insurance (2025/26)**:
   - Primary Threshold: £12,570
   - Upper Earnings Limit: £50,270
   - Rate below UEL: 12%
   - Rate above UEL: 2%

3. **ISA Allowance**:
   - Annual Limit: £20,000
   - LISA Limit: £4,000
   - Tax Year: April 6 - April 5

4. **Pension Annual Allowance**:
   - Standard: £60,000
   - Tapered for high earners
   - MPAA: £10,000
   - Carry Forward: 3 years

5. **Inheritance Tax**:
   - Nil Rate Band (NRB): £325,000
   - Residence Nil Rate Band (RNRB): £175,000
   - IHT Rate: 40%
   - Spouse Exemption: Unlimited

6. **Life Expectancy Tables**:
   - UK ONS 2024 data
   - Age range: 20-100
   - Separate male/female tables

### Step 4: Create Admin User

```bash
php artisan tinker
```

```php
$admin = new \App\Models\User();
$admin->name = 'FPS Admin';
$admin->email = 'admin@fps.com';
$admin->password = bcrypt('your_secure_password');
$admin->email_verified_at = now();
$admin->is_admin = true;
$admin->date_of_birth = '1980-01-01';
$admin->gender = 'male';
$admin->marital_status = 'single';
$admin->save();

echo "Admin created with ID: " . $admin->id . "\n";
exit
```

### Step 5: Verify Database Integrity

```bash
# Check foreign key constraints
php artisan db:show --database=mysql

# Check indexes
mysql -u DB_USER -p DB_NAME -e "SELECT TABLE_NAME, INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'DB_NAME' ORDER BY TABLE_NAME, INDEX_NAME;"
```

---

## Rollback Procedure (If Needed)

### Rollback Last Migration

```bash
php artisan migrate:rollback --step=1
```

### Rollback All Migrations

```bash
php artisan migrate:reset
```

### Fresh Migration (DESTRUCTIVE - Drops All Tables)

```bash
php artisan migrate:fresh --force
php artisan db:seed --class=TaxConfigurationSeeder --force
```

**WARNING**: This will delete all data!

---

## Common Migration Issues

### Issue 1: "SQLSTATE[42000]: Syntax error"

**Cause**: MySQL version incompatibility

**Solution**:
- Verify MySQL 8.0+ is installed
- Check `mysql --version`
- Update MySQL if needed

### Issue 2: "Base table or view already exists"

**Cause**: Table already exists from previous migration attempt

**Solution**:
```bash
# Check migrations table
mysql -u DB_USER -p DB_NAME -e "SELECT * FROM migrations ORDER BY id DESC LIMIT 10;"

# Rollback to specific migration
php artisan migrate:rollback --step=5

# Re-run migrations
php artisan migrate --force
```

### Issue 3: "Access denied for user"

**Cause**: Incorrect database credentials or insufficient privileges

**Solution**:
1. Verify `.env` credentials
2. Test connection:
   ```bash
   mysql -u DB_USER -pDB_PASSWORD -h DB_HOST DB_NAME -e "SELECT 1;"
   ```
3. Grant privileges:
   ```sql
   GRANT ALL PRIVILEGES ON fpsv2_production.* TO 'fps_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Issue 4: "Specified key was too long"

**Cause**: String length issues with older MySQL/MariaDB

**Solution**:
- Already handled in migrations (uses `utf8mb4` charset)
- If still occurring, check MySQL version (8.0+ required)

### Issue 5: Foreign key constraint fails

**Cause**: Migration order issue or missing parent table

**Solution**:
```bash
# Check migration order
ls -l database/migrations/

# Migrations run in chronological order by filename
# If issue persists, check foreign key definitions in migration files
```

---

## Database Performance Optimization

### After Initial Migration

```bash
# Optimize tables
php artisan db:optimize
```

### Index Verification

Key indexes created automatically:
- `user_id` on all module tables
- `email` on `users` table
- `joint_owner_id` on joint ownership tables
- `trust_id` on trust ownership tables
- `spouse_id` on users table

### Query Performance

Monitor slow queries:
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
```

---

## Database Size Estimates

### Empty Database (Post-Migration)

- **Structure Only**: ~5 MB
- **With Tax Config Seeded**: ~6 MB

### Populated Database (Example User)

- **Single User, Basic Data**: ~1 MB
- **Single User, Full Data**: ~5 MB
- **100 Users, Average Data**: ~200 MB
- **1,000 Users, Average Data**: ~1.5 GB

### Growth Projections

- Monte Carlo simulations add ~2 MB per simulation (1,000 paths)
- Logs grow ~100 MB/month (depends on traffic)
- Cache grows ~50 MB (prune regularly)

---

## Backup Verification

After successful migration, create backup:

```bash
# Full database backup
mysqldump -u DB_USER -pDB_PASSWORD DB_NAME > fpsv2_backup_$(date +%Y%m%d).sql

# Compressed backup
mysqldump -u DB_USER -pDB_PASSWORD DB_NAME | gzip > fpsv2_backup_$(date +%Y%m%d).sql.gz

# Structure only (no data)
mysqldump -u DB_USER -pDB_PASSWORD --no-data DB_NAME > fpsv2_structure_$(date +%Y%m%d).sql
```

### Restore from Backup

```bash
# Decompress if needed
gunzip fpsv2_backup_20251028.sql.gz

# Restore
mysql -u DB_USER -pDB_PASSWORD DB_NAME < fpsv2_backup_20251028.sql
```

---

## Migration Completion Checklist

- [ ] All 81 migrations ran successfully
- [ ] 45+ tables created in database
- [ ] No migration errors in output
- [ ] Tax configuration seeded
- [ ] Admin user created
- [ ] Database backup created
- [ ] Foreign keys verified
- [ ] Indexes created
- [ ] Database connection tested from application
- [ ] Sample user account created and tested
- [ ] All module tables accessible

---

## Database Schema Documentation

For detailed schema information:

1. **View Table Structure**:
   ```bash
   php artisan db:table users
   php artisan db:table protection_profiles
   php artisan db:table assets
   ```

2. **Generate ER Diagram** (if needed):
   - Use MySQL Workbench
   - Reverse engineer database
   - Export as PNG/PDF

3. **Documentation Files**:
   - `DATABASE_SCHEMA_GUIDE.md` - Detailed schema documentation
   - `CODEBASE_STRUCTURE.md` - Architecture overview

---

**Migration Checklist Version**: 1.0
**FPS Version**: v0.1.2.12
**Last Updated**: October 28, 2025
**MySQL Version Required**: 8.0+

---

🤖 **Generated with [Claude Code](https://claude.com/claude-code)**
