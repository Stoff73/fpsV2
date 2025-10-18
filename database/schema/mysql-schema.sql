/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `asset_type` enum('property','pension','investment','business','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_value` decimal(15,2) NOT NULL,
  `ownership_type` enum('sole','joint','trust') COLLATE utf8mb4_unicode_ci NOT NULL,
  `beneficiary_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_iht_exempt` tinyint(1) NOT NULL DEFAULT '0',
  `exemption_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valuation_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assets_user_id_index` (`user_id`),
  CONSTRAINT `assets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `business_interests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_interests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_id` bigint unsigned DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Companies House registration number',
  `business_type` enum('sole_trader','partnership','limited_company','llp','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ownership_type` enum('individual','joint') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `current_valuation` decimal(15,2) NOT NULL,
  `valuation_date` date NOT NULL,
  `valuation_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., Market value, Book value, Expert valuation',
  `annual_revenue` decimal(15,2) DEFAULT NULL,
  `annual_profit` decimal(15,2) DEFAULT NULL,
  `annual_dividend_income` decimal(15,2) DEFAULT NULL COMMENT 'Dividend income received from this business',
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_interests_user_id_index` (`user_id`),
  KEY `business_interests_household_id_index` (`household_id`),
  KEY `business_interests_trust_id_index` (`trust_id`),
  KEY `business_interests_business_type_index` (`business_type`),
  CONSTRAINT `business_interests_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `business_interests_trust_id_foreign` FOREIGN KEY (`trust_id`) REFERENCES `trusts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `business_interests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_id` bigint unsigned DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_type` enum('current_account','savings_account','cash_isa','fixed_term_deposit','ns_and_i','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` enum('emergency_fund','savings_goal','operating_cash','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownership_type` enum('individual','joint') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `current_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,4) DEFAULT NULL COMMENT 'Annual interest rate as decimal',
  `rate_valid_until` date DEFAULT NULL,
  `is_isa` tinyint(1) NOT NULL DEFAULT '0',
  `isa_subscription_current_year` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_year` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., 2024/25',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_accounts_user_id_index` (`user_id`),
  KEY `cash_accounts_household_id_index` (`household_id`),
  KEY `cash_accounts_trust_id_index` (`trust_id`),
  KEY `cash_accounts_account_type_index` (`account_type`),
  KEY `cash_accounts_is_isa_index` (`is_isa`),
  CONSTRAINT `cash_accounts_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_accounts_trust_id_foreign` FOREIGN KEY (`trust_id`) REFERENCES `trusts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chattels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chattels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_id` bigint unsigned DEFAULT NULL,
  `chattel_type` enum('vehicle','art','antique','jewelry','collectible','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ownership_type` enum('individual','joint') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `current_value` decimal(15,2) NOT NULL,
  `valuation_date` date NOT NULL,
  `make` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vehicle make',
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vehicle model',
  `year` year DEFAULT NULL COMMENT 'Vehicle year',
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vehicle registration',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chattels_user_id_index` (`user_id`),
  KEY `chattels_household_id_index` (`household_id`),
  KEY `chattels_trust_id_index` (`trust_id`),
  KEY `chattels_chattel_type_index` (`chattel_type`),
  CONSTRAINT `chattels_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chattels_trust_id_foreign` FOREIGN KEY (`trust_id`) REFERENCES `trusts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chattels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `critical_illness_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `critical_illness_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `policy_type` enum('standalone','accelerated','additional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standalone',
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sum_assured` decimal(15,2) NOT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `premium_frequency` enum('monthly','quarterly','annually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `policy_start_date` date NOT NULL,
  `policy_term_years` int NOT NULL,
  `conditions_covered` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `critical_illness_policies_user_id_index` (`user_id`),
  CONSTRAINT `critical_illness_policies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `db_pensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `db_pensions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `scheme_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheme_type` enum('final_salary','career_average','public_sector') COLLATE utf8mb4_unicode_ci NOT NULL,
  `accrued_annual_pension` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pensionable_service_years` decimal(5,2) DEFAULT NULL,
  `pensionable_salary` decimal(10,2) DEFAULT NULL,
  `normal_retirement_age` int DEFAULT NULL,
  `revaluation_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spouse_pension_percent` decimal(5,2) DEFAULT NULL,
  `lump_sum_entitlement` decimal(15,2) DEFAULT NULL,
  `inflation_protection` enum('cpi','rpi','fixed','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `db_pensions_user_id_index` (`user_id`),
  CONSTRAINT `db_pensions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dc_pensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dc_pensions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `scheme_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheme_type` enum('workplace','sipp','personal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_fund_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `annual_salary` decimal(10,2) DEFAULT NULL,
  `employee_contribution_percent` decimal(5,2) DEFAULT NULL,
  `employer_contribution_percent` decimal(5,2) DEFAULT NULL,
  `monthly_contribution_amount` decimal(10,2) DEFAULT NULL,
  `investment_strategy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_fee_percent` decimal(5,4) DEFAULT NULL,
  `retirement_age` int DEFAULT NULL,
  `projected_value_at_retirement` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dc_pensions_user_id_index` (`user_id`),
  CONSTRAINT `dc_pensions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disability_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disability_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benefit_amount` decimal(10,2) NOT NULL,
  `benefit_frequency` enum('monthly','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `deferred_period_weeks` int NOT NULL,
  `benefit_period_months` int DEFAULT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `premium_frequency` enum('monthly','quarterly','annually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `occupation_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policy_start_date` date NOT NULL,
  `policy_term_years` int DEFAULT NULL,
  `coverage_type` enum('accident_only','accident_and_sickness') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'accident_and_sickness',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disability_policies_user_id_index` (`user_id`),
  CONSTRAINT `disability_policies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expenditure_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenditure_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `monthly_housing` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_utilities` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_food` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_transport` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_insurance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_loans` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_discretionary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_monthly_expenditure` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenditure_profiles_user_id_index` (`user_id`),
  CONSTRAINT `expenditure_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `relationship` enum('spouse','child','parent','other_dependent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_insurance_number` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `is_dependent` tinyint(1) NOT NULL DEFAULT '0',
  `education_status` enum('pre_school','primary','secondary','further_education','higher_education','graduated','not_applicable') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_members_user_id_index` (`user_id`),
  KEY `family_members_household_id_index` (`household_id`),
  KEY `family_members_relationship_index` (`relationship`),
  CONSTRAINT `family_members_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `gift_date` date NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gift_type` enum('pet','clt','exempt','small_gift','annual_exemption') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gift_value` decimal(15,2) NOT NULL,
  `status` enum('within_7_years','survived_7_years') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'within_7_years',
  `taper_relief_applicable` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gifts_user_id_index` (`user_id`),
  KEY `gifts_gift_date_index` (`gift_date`),
  CONSTRAINT `gifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `holdings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holdings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investment_account_id` bigint unsigned NOT NULL,
  `asset_type` enum('equity','bond','fund','etf','alternative','uk_equity','us_equity','international_equity','cash','property') COLLATE utf8mb4_unicode_ci NOT NULL,
  `allocation_percent` decimal(5,2) DEFAULT NULL,
  `security_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticker` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(15,6) DEFAULT NULL,
  `purchase_price` decimal(15,4) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `current_price` decimal(15,4) DEFAULT NULL,
  `current_value` decimal(15,2) NOT NULL,
  `cost_basis` decimal(15,2) DEFAULT NULL,
  `dividend_yield` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `ocf_percent` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holdings_investment_account_id_index` (`investment_account_id`),
  KEY `holdings_asset_type_index` (`asset_type`),
  CONSTRAINT `holdings_investment_account_id_foreign` FOREIGN KEY (`investment_account_id`) REFERENCES `investment_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `households`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `households` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `household_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `iht_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iht_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `marital_status` enum('single','married','widowed','divorced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_spouse` tinyint(1) NOT NULL DEFAULT '0',
  `own_home` tinyint(1) NOT NULL DEFAULT '0',
  `home_value` decimal(15,2) DEFAULT NULL,
  `nrb_transferred_from_spouse` decimal(15,2) NOT NULL DEFAULT '0.00',
  `charitable_giving_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iht_profiles_user_id_index` (`user_id`),
  CONSTRAINT `iht_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `income_protection_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `income_protection_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benefit_amount` decimal(10,2) NOT NULL,
  `benefit_frequency` enum('monthly','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `deferred_period_weeks` int NOT NULL,
  `benefit_period_months` int DEFAULT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `occupation_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policy_start_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_protection_policies_user_id_index` (`user_id`),
  CONSTRAINT `income_protection_policies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investment_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_id` bigint unsigned DEFAULT NULL,
  `ownership_type` enum('individual','joint') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `account_type` enum('isa','gia','onshore_bond','offshore_bond','vct','eis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `contributions_ytd` decimal(15,2) DEFAULT '0.00',
  `tax_year` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform_fee_percent` decimal(5,4) DEFAULT '0.0000',
  `isa_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isa_subscription_current_year` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investment_accounts_user_id_index` (`user_id`),
  KEY `investment_accounts_user_id_account_type_index` (`user_id`,`account_type`),
  KEY `investment_accounts_user_id_tax_year_index` (`user_id`,`tax_year`),
  KEY `investment_accounts_household_id_index` (`household_id`),
  KEY `investment_accounts_trust_id_index` (`trust_id`),
  CONSTRAINT `investment_accounts_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investment_accounts_trust_id_foreign` FOREIGN KEY (`trust_id`) REFERENCES `trusts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investment_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investment_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `goal_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal_type` enum('retirement','education','wealth','home') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `target_date` date NOT NULL,
  `priority` enum('high','medium','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `is_essential` tinyint(1) NOT NULL DEFAULT '0',
  `linked_account_ids` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investment_goals_user_id_index` (`user_id`),
  KEY `investment_goals_user_id_goal_type_index` (`user_id`,`goal_type`),
  CONSTRAINT `investment_goals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `isa_allowance_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `isa_allowance_tracking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tax_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cash_isa_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stocks_shares_isa_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lisa_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_allowance` decimal(10,2) NOT NULL DEFAULT '20000.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isa_allowance_tracking_user_id_tax_year_unique` (`user_id`,`tax_year`),
  CONSTRAINT `isa_allowance_tracking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `liabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `liabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `liability_type` enum('mortgage','secured_loan','personal_loan','credit_card','overdraft','hire_purchase','student_loan','business_loan','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `liability_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `monthly_payment` decimal(10,2) DEFAULT NULL,
  `interest_rate` decimal(5,4) DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `secured_against` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_priority_debt` tinyint(1) NOT NULL DEFAULT '0',
  `mortgage_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fixed_until` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `liabilities_user_id_index` (`user_id`),
  CONSTRAINT `liabilities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `life_insurance_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `life_insurance_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `policy_type` enum('term','whole_of_life','decreasing_term','family_income_benefit','level_term') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term',
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sum_assured` decimal(15,2) NOT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `premium_frequency` enum('monthly','quarterly','annually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `policy_start_date` date NOT NULL,
  `policy_term_years` int NOT NULL,
  `indexation_rate` decimal(5,4) DEFAULT '0.0000',
  `in_trust` tinyint(1) NOT NULL DEFAULT '0',
  `beneficiaries` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `life_insurance_policies_user_id_index` (`user_id`),
  CONSTRAINT `life_insurance_policies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mortgages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mortgages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `lender_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mortgage_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mortgage_type` enum('repayment','interest_only','part_and_part') COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_loan_amount` decimal(15,2) NOT NULL,
  `outstanding_balance` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,4) NOT NULL COMMENT 'Annual interest rate as decimal, e.g., 3.5% = 3.5000',
  `rate_type` enum('fixed','variable','tracker','discount') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate_fix_end_date` date DEFAULT NULL COMMENT 'Date when fixed rate ends',
  `monthly_payment` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `maturity_date` date NOT NULL,
  `remaining_term_months` int NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mortgages_property_id_index` (`property_id`),
  KEY `mortgages_user_id_index` (`user_id`),
  KEY `mortgages_mortgage_type_index` (`mortgage_type`),
  CONSTRAINT `mortgages_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mortgages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `net_worth_statements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `net_worth_statements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `statement_date` date NOT NULL,
  `total_assets` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_liabilities` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_worth` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `net_worth_statements_user_id_index` (`user_id`),
  CONSTRAINT `net_worth_statements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `account_type` enum('profit_and_loss','cashflow','balance_sheet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `line_item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., Employment Income, Mortgage Payment, Cash in Bank',
  `category` enum('income','expense','asset','liability','equity','cash_inflow','cash_outflow') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `personal_accounts_user_id_index` (`user_id`),
  KEY `personal_accounts_account_type_index` (`account_type`),
  KEY `personal_accounts_period_start_period_end_index` (`period_start`,`period_end`),
  CONSTRAINT `personal_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_id` bigint unsigned DEFAULT NULL,
  `property_type` enum('main_residence','secondary_residence','buy_to_let') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ownership_type` enum('individual','joint') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `ownership_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `county` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_date` date NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL,
  `current_value` decimal(15,2) NOT NULL,
  `valuation_date` date NOT NULL,
  `sdlt_paid` decimal(15,2) DEFAULT NULL COMMENT 'Stamp Duty Land Tax paid',
  `monthly_rental_income` decimal(10,2) DEFAULT NULL,
  `annual_rental_income` decimal(15,2) DEFAULT NULL,
  `occupancy_rate_percent` int DEFAULT NULL COMMENT 'Percentage of time property is occupied',
  `tenant_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `annual_service_charge` decimal(10,2) DEFAULT NULL,
  `annual_ground_rent` decimal(10,2) DEFAULT NULL,
  `annual_insurance` decimal(10,2) DEFAULT NULL,
  `annual_maintenance_reserve` decimal(10,2) DEFAULT NULL,
  `other_annual_costs` decimal(10,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `properties_user_id_index` (`user_id`),
  KEY `properties_household_id_index` (`household_id`),
  KEY `properties_trust_id_index` (`trust_id`),
  KEY `properties_property_type_index` (`property_type`),
  KEY `properties_ownership_type_index` (`ownership_type`),
  CONSTRAINT `properties_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_trust_id_foreign` FOREIGN KEY (`trust_id`) REFERENCES `trusts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `protection_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `protection_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `annual_income` decimal(15,2) NOT NULL,
  `monthly_expenditure` decimal(10,2) NOT NULL,
  `mortgage_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `other_debts` decimal(15,2) NOT NULL DEFAULT '0.00',
  `number_of_dependents` int NOT NULL DEFAULT '0',
  `dependents_ages` json DEFAULT NULL,
  `retirement_age` int NOT NULL DEFAULT '67',
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smoker_status` tinyint(1) NOT NULL DEFAULT '0',
  `health_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'good',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `protection_profiles_user_id_unique` (`user_id`),
  KEY `protection_profiles_user_id_index` (`user_id`),
  CONSTRAINT `protection_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recommendation_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recommendation_tracking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `recommendation_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendation_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority_score` decimal(5,2) NOT NULL DEFAULT '50.00',
  `timeline` enum('immediate','short_term','medium_term','long_term') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium_term',
  `status` enum('pending','in_progress','completed','dismissed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recommendation_tracking_user_id_status_index` (`user_id`,`status`),
  KEY `recommendation_tracking_user_id_module_index` (`user_id`,`module`),
  KEY `recommendation_tracking_recommendation_id_index` (`recommendation_id`),
  CONSTRAINT `recommendation_tracking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `retirement_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retirement_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `current_age` int NOT NULL,
  `target_retirement_age` int NOT NULL,
  `current_annual_salary` decimal(15,2) DEFAULT NULL,
  `target_retirement_income` decimal(15,2) DEFAULT NULL,
  `essential_expenditure` decimal(10,2) DEFAULT NULL,
  `lifestyle_expenditure` decimal(10,2) DEFAULT NULL,
  `life_expectancy` int DEFAULT NULL,
  `spouse_life_expectancy` int DEFAULT NULL,
  `risk_tolerance` enum('cautious','balanced','adventurous') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'balanced',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `retirement_profiles_user_id_index` (`user_id`),
  CONSTRAINT `retirement_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `risk_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `risk_tolerance` enum('cautious','balanced','adventurous') COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity_for_loss_percent` decimal(5,2) NOT NULL,
  `time_horizon_years` int NOT NULL,
  `knowledge_level` enum('novice','intermediate','experienced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `attitude_to_volatility` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esg_preference` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `risk_profiles_user_id_index` (`user_id`),
  CONSTRAINT `risk_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `savings_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `savings_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `account_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `access_type` enum('immediate','notice','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `notice_period_days` int DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `is_isa` tinyint(1) NOT NULL DEFAULT '0',
  `isa_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isa_subscription_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isa_subscription_amount` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `savings_accounts_user_id_index` (`user_id`),
  CONSTRAINT `savings_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `savings_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `savings_goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `goal_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `current_saved` decimal(15,2) NOT NULL DEFAULT '0.00',
  `target_date` date NOT NULL,
  `priority` enum('high','medium','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `linked_account_id` bigint unsigned DEFAULT NULL,
  `auto_transfer_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `savings_goals_linked_account_id_foreign` (`linked_account_id`),
  KEY `savings_goals_user_id_index` (`user_id`),
  CONSTRAINT `savings_goals_linked_account_id_foreign` FOREIGN KEY (`linked_account_id`) REFERENCES `savings_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `savings_goals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sickness_illness_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sickness_illness_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benefit_amount` decimal(10,2) NOT NULL,
  `benefit_frequency` enum('monthly','weekly','lump_sum') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lump_sum',
  `deferred_period_weeks` int DEFAULT NULL,
  `benefit_period_months` int DEFAULT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `premium_frequency` enum('monthly','quarterly','annually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `policy_start_date` date NOT NULL,
  `policy_term_years` int DEFAULT NULL,
  `conditions_covered` json DEFAULT NULL,
  `exclusions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sickness_illness_policies_user_id_index` (`user_id`),
  CONSTRAINT `sickness_illness_policies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `state_pensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `state_pensions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ni_years_completed` int NOT NULL DEFAULT '0',
  `ni_years_required` int NOT NULL DEFAULT '35',
  `state_pension_forecast_annual` decimal(10,2) DEFAULT NULL,
  `state_pension_age` int DEFAULT NULL,
  `ni_gaps` json DEFAULT NULL,
  `gap_fill_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state_pensions_user_id_index` (`user_id`),
  CONSTRAINT `state_pensions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tax_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_configurations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tax_year` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date NOT NULL,
  `config_data` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_configurations_tax_year_unique` (`tax_year`),
  KEY `tax_configurations_tax_year_index` (`tax_year`),
  KEY `tax_configurations_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trusts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trusts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `trust_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trust_type` enum('bare','interest_in_possession','discretionary','accumulation_maintenance','life_insurance','discounted_gift','loan','mixed','settlor_interested') COLLATE utf8mb4_unicode_ci NOT NULL,
  `trust_creation_date` date NOT NULL,
  `initial_value` decimal(15,2) NOT NULL,
  `current_value` decimal(15,2) NOT NULL,
  `last_valuation_date` date DEFAULT NULL,
  `discount_amount` decimal(15,2) DEFAULT NULL COMMENT 'Actuarial discount for retained income',
  `retained_income_annual` decimal(15,2) DEFAULT NULL COMMENT 'Annual income retained by settlor',
  `loan_amount` decimal(15,2) DEFAULT NULL COMMENT 'Outstanding loan balance',
  `loan_interest_bearing` tinyint(1) NOT NULL DEFAULT '0',
  `loan_interest_rate` decimal(5,4) DEFAULT NULL,
  `sum_assured` decimal(15,2) DEFAULT NULL COMMENT 'Life insurance policy sum assured',
  `annual_premium` decimal(15,2) DEFAULT NULL,
  `is_relevant_property_trust` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Subject to 10-year periodic charges',
  `last_periodic_charge_date` date DEFAULT NULL,
  `last_periodic_charge_amount` decimal(15,2) DEFAULT NULL,
  `next_tax_return_due` date DEFAULT NULL,
  `total_asset_value` decimal(15,2) DEFAULT NULL COMMENT 'Aggregated value of all assets held in trust',
  `beneficiaries` text COLLATE utf8mb4_unicode_ci COMMENT 'List of beneficiaries',
  `trustees` text COLLATE utf8mb4_unicode_ci COMMENT 'List of trustees',
  `purpose` text COLLATE utf8mb4_unicode_ci COMMENT 'Purpose of the trust',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trusts_user_id_index` (`user_id`),
  KEY `trusts_trust_type_index` (`trust_type`),
  KEY `trusts_is_relevant_property_trust_index` (`is_relevant_property_trust`),
  KEY `trusts_household_id_index` (`household_id`),
  CONSTRAINT `trusts_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trusts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spouse_id` bigint unsigned DEFAULT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `is_primary_account` tinyint(1) NOT NULL DEFAULT '1',
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `national_insurance_number` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_status` enum('employed','self_employed','retired','unemployed','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annual_employment_income` decimal(15,2) DEFAULT NULL,
  `annual_self_employment_income` decimal(15,2) DEFAULT NULL,
  `annual_rental_income` decimal(15,2) DEFAULT NULL,
  `annual_dividend_income` decimal(15,2) DEFAULT NULL,
  `annual_other_income` decimal(15,2) DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_spouse_id_index` (`spouse_id`),
  KEY `users_household_id_index` (`household_id`),
  KEY `users_role_index` (`role`),
  CONSTRAINT `users_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_spouse_id_foreign` FOREIGN KEY (`spouse_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_10_13_113656_create_tax_configurations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_10_13_113806_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_10_13_131230_create_critical_illness_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_10_13_131230_create_income_protection_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_10_13_131230_create_life_insurance_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_10_13_131230_create_protection_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_10_13_132846_create_disability_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_10_13_132846_create_sickness_illness_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_10_14_075501_create_dc_pensions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_10_14_075511_create_savings_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_10_14_075513_create_net_worth_statements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_10_14_075618_create_savings_goals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_10_14_075624_create_db_pensions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_10_14_075637_create_assets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_10_14_075637_create_liabilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_10_14_075638_create_gifts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_10_14_075638_create_iht_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_10_14_075652_create_expenditure_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_10_14_075708_create_state_pensions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_10_14_075725_create_isa_allowance_tracking_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_10_14_075746_create_retirement_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_10_14_091658_create_investment_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_10_14_091714_create_holdings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_10_14_091714_create_investment_goals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_10_14_091714_create_risk_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_10_15_070121_fix_investment_accounts_defaults',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_10_15_070221_add_isa_fields_to_investment_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_15_070439_fix_platform_fee_percent_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_15_085438_add_annual_salary_to_dc_pensions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_10_15_094650_add_additional_fields_to_liabilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_15_111259_add_notes_to_gifts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_10_15_123423_create_trusts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_15_134915_create_recommendation_tracking_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_16_080205_add_allocation_percent_to_holdings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_10_16_080903_make_purchase_date_nullable_in_holdings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_10_16_080933_update_asset_type_enum_in_holdings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_10_16_081113_make_cost_basis_nullable_in_holdings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_10_17_142646_add_spouse_linking_and_role_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_10_17_142728_create_households_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_10_17_142742_add_foreign_keys_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_10_17_142756_create_family_members_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_10_17_142814_create_properties_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_10_17_142836_create_mortgages_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_10_17_142854_create_business_interests_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_10_17_142854_create_chattels_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_10_17_142855_create_cash_accounts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_10_17_142855_create_personal_accounts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_10_17_142957_add_ownership_fields_to_investment_accounts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_10_17_143014_add_additional_fields_to_trusts_table',2);
