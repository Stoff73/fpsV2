# FPS Manual End-to-End Testing Protocol

**Version**: v0.1.2.5
**Last Updated**: October 27, 2025
**Testing Environment**: Local Development / Staging
**Estimated Time**: 4-6 hours for complete testing

---

## 📋 Pre-Testing Setup

### Environment Preparation

- [ ] **Task 0.1**: Verify both servers are running
  - [ ] Laravel backend: `php artisan serve` (port 8000)
  - [ ] Vite frontend: `npm run dev` (port 5173)
  - [ ] Memcached: Running (optional but recommended)

- [ ] **Task 0.2**: Clear all caches
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

- [ ] **Task 0.3**: Verify database is accessible
  - [ ] Check MySQL is running
  - [ ] Database `laravel` exists
  - [ ] All migrations are up to date: `php artisan migrate:status`

- [ ] **Task 0.4**: Create fresh test user account
  - Email: `test@fps.com`
  - Password: `TestPassword123!`
  - **OR** use existing demo account: `demo@fps.com` / `password`

- [ ] **Task 0.5**: Open browser DevTools Console
  - Monitor for JavaScript errors throughout testing
  - Check Network tab for failed API requests

---

## 🔐 SECTION 1: Authentication & Registration

### 1.1 User Registration

- [ ] **Task 1.1.1**: Navigate to registration page
  - URL: `http://localhost:8000/register`
  - Page loads without errors
  - Registration form displays correctly

- [ ] **Task 1.1.2**: Test validation errors
  - [ ] Submit empty form → See validation errors
  - [ ] Enter mismatched passwords → See error message
  - [ ] Enter invalid email format → See validation error
  - [ ] Enter weak password → See strength requirements

- [ ] **Task 1.1.3**: Successfully register new user
  - [ ] Fill all required fields:
    - Name: "Test User"
    - Email: "test@fps.com" (or unique email)
    - Password: "TestPassword123!"
    - Confirm Password: "TestPassword123!"
  - [ ] Click "Register" button
  - [ ] Redirected to onboarding OR dashboard
  - [ ] No JavaScript errors in console

**✅ Expected Result**: User account created, logged in automatically, redirected to appropriate page

---

### 1.2 User Login

- [ ] **Task 1.2.1**: Logout (if currently logged in)
  - [ ] Click profile/avatar menu
  - [ ] Click "Logout"
  - [ ] Redirected to login page

- [ ] **Task 1.2.2**: Navigate to login page
  - URL: `http://localhost:8000/login`
  - Login form displays correctly

- [ ] **Task 1.2.3**: Test validation errors
  - [ ] Submit empty form → See validation errors
  - [ ] Enter wrong password → See "Invalid credentials" error
  - [ ] Enter non-existent email → See error

- [ ] **Task 1.2.4**: Successfully login
  - [ ] Email: "test@fps.com"
  - [ ] Password: "TestPassword123!"
  - [ ] Click "Login" button
  - [ ] Redirected to dashboard
  - [ ] User name displays in header/nav

**✅ Expected Result**: Logged in successfully, dashboard loads, user authenticated

---

### 1.3 Password Reset (if implemented)

- [ ] **Task 1.3.1**: Click "Forgot Password" link
- [ ] **Task 1.3.2**: Enter email address
- [ ] **Task 1.3.3**: Check for success message or email sent confirmation

---

## 🎯 SECTION 2: Onboarding Flow

### 2.1 Onboarding Start

- [ ] **Task 2.1.1**: Navigate to onboarding (if not auto-redirected)
  - URL: `http://localhost:8000/onboarding`
  - Welcome screen displays

- [ ] **Task 2.1.2**: Review welcome message
  - [ ] Application introduction displays
  - [ ] "Get Started" or "Begin" button visible

**✅ Expected Result**: Onboarding wizard loads, ready to begin

---

### 2.2 Personal Information Step

- [ ] **Task 2.2.1**: Fill personal information
  - [ ] Date of Birth: Select valid date (e.g., 1980-01-15)
  - [ ] Gender: Select option
  - [ ] Marital Status: Select (single/married/widowed/divorced)
  - [ ] National Insurance Number: Enter valid format (e.g., AB123456C)

- [ ] **Task 2.2.2**: Fill address
  - [ ] Address Line 1: "123 Test Street"
  - [ ] City: "London"
  - [ ] County: "Greater London"
  - [ ] Postcode: "SW1A 1AA"
  - [ ] Phone: "07700 900000"

- [ ] **Task 2.2.3**: Click "Next" or "Continue"
  - [ ] Form validates successfully
  - [ ] Progress to next step
  - [ ] No errors in console

**✅ Expected Result**: Personal information saved, moved to next onboarding step

---

### 2.3 Employment & Income Step

- [ ] **Task 2.3.1**: Fill employment details
  - [ ] Occupation: "Software Developer"
  - [ ] Employer: "Tech Company Ltd"
  - [ ] Industry: "Technology"
  - [ ] Employment Status: "Employed"

- [ ] **Task 2.3.2**: Fill income fields
  - [ ] Annual Employment Income: £50,000
  - [ ] Annual Self-Employment Income: £0 (or leave empty)
  - [ ] Annual Rental Income: £0
  - [ ] Annual Dividend Income: £0
  - [ ] Annual Other Income: £0

- [ ] **Task 2.3.3**: Fill expenditure fields (v0.1.2.5 NEW)
  - [ ] Monthly Expenditure: £3,000
  - [ ] Annual Expenditure: £36,000
  - [ ] Verify calculation matches (monthly × 12)

- [ ] **Task 2.3.4**: Click "Next" or "Continue"
  - [ ] Form validates successfully
  - [ ] Progress to next step

**✅ Expected Result**: Income and expenditure saved, moved to next step

---

### 2.4 Domicile Information Step (v0.1.2.5 NEW)

- [ ] **Task 2.4.1**: Fill domicile status
  - [ ] Domicile Status: Select "UK Domiciled" or "Non-UK Domiciled"
  - [ ] Country of Birth: Select from dropdown (searchable)

- [ ] **Task 2.4.2**: If Non-UK Domiciled selected
  - [ ] UK Arrival Date field appears
  - [ ] Select date of arrival in UK
  - [ ] Verify years UK resident calculation displays
  - [ ] Check for deemed domicile status message (15 of 20 years rule)

- [ ] **Task 2.4.3**: Click "Next" or "Continue"
  - [ ] Form validates successfully
  - [ ] Progress to next step

**✅ Expected Result**: Domicile information saved correctly, calculations display

---

### 2.5 Assets Step

- [ ] **Task 2.5.1**: Add property (if applicable)
  - [ ] Click "Add Property" button
  - [ ] Property Type: "Residential"
  - [ ] Address: "456 Asset Lane, London, SW1A 2AA"
  - [ ] Current Value: £300,000
  - [ ] Purchase Price: £250,000
  - [ ] Purchase Date: 2020-01-01
  - [ ] Country: "United Kingdom" (v0.1.2.5 NEW)
  - [ ] Ownership Type: "Individual"
  - [ ] Click "Save"
  - [ ] Property appears in list

- [ ] **Task 2.5.2**: Add savings account
  - [ ] Click "Add Savings Account"
  - [ ] Institution: "HSBC"
  - [ ] Account Type: "Cash"
  - [ ] Current Balance: £20,000
  - [ ] Is ISA: Check YES
  - [ ] Verify country field is hidden (ISAs must be UK)
  - [ ] ISA Type: "Cash"
  - [ ] ISA Subscription Year: "2025/26"
  - [ ] ISA Subscription Amount: £20,000
  - [ ] Click "Save"
  - [ ] Account appears in list

- [ ] **Task 2.5.3**: Add non-ISA savings account (v0.1.2.5 country test)
  - [ ] Click "Add Savings Account"
  - [ ] Institution: "Foreign Bank"
  - [ ] Account Type: "Cash"
  - [ ] Current Balance: £10,000
  - [ ] Is ISA: Check NO
  - [ ] Country: Select "Spain" or other country (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Verify country saved correctly

- [ ] **Task 2.5.4**: Click "Next" or "Continue"
  - [ ] Assets saved successfully
  - [ ] Progress to next step

**✅ Expected Result**: All assets added successfully, country tracking working

---

### 2.6 Liabilities Step

- [ ] **Task 2.6.1**: Add mortgage (if property added)
  - [ ] Click "Add Mortgage"
  - [ ] Select Property: Choose property from dropdown
  - [ ] Lender: "Nationwide"
  - [ ] Mortgage Type: "Repayment"
  - [ ] Original Amount: £200,000
  - [ ] Outstanding Balance: £180,000
  - [ ] Interest Rate: 4.5%
  - [ ] Term Years: 20
  - [ ] Start Date: 2020-01-01
  - [ ] Country: "United Kingdom" (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Mortgage appears in list

- [ ] **Task 2.6.2**: Mark liabilities as reviewed (v0.1.2.5 NEW)
  - [ ] Check "I have reviewed my liabilities" checkbox (if exists)
  - [ ] OR ensure liabilities_reviewed flag is set

- [ ] **Task 2.6.3**: Click "Next" or "Continue"
  - [ ] Liabilities saved successfully
  - [ ] Progress to next step

**✅ Expected Result**: Liabilities recorded, moved forward

---

### 2.7 Complete Onboarding

- [ ] **Task 2.7.1**: Review summary (if summary step exists)
  - [ ] All entered data displays correctly
  - [ ] No missing critical information

- [ ] **Task 2.7.2**: Complete onboarding
  - [ ] Click "Complete" or "Finish" button
  - [ ] Redirected to dashboard
  - [ ] Success message displays
  - [ ] Onboarding marked as complete

**✅ Expected Result**: Onboarding completed, dashboard loads with populated data

---

## 📊 SECTION 3: Dashboard

### 3.1 Main Dashboard

- [ ] **Task 3.1.1**: Verify dashboard loads
  - URL: `http://localhost:8000/dashboard`
  - Page loads without errors
  - All dashboard cards display

- [ ] **Task 3.1.2**: Check dashboard components
  - [ ] Net Worth summary card displays
  - [ ] Total assets amount shows
  - [ ] Total liabilities amount shows
  - [ ] Net worth calculation correct (assets - liabilities)

- [ ] **Task 3.1.3**: Check module quick links
  - [ ] Protection module card displays
  - [ ] Savings module card displays
  - [ ] Investment module card displays
  - [ ] Retirement module card displays
  - [ ] Estate Planning module card displays

- [ ] **Task 3.1.4**: Profile Completeness Alert (v0.1.2.5 NEW)
  - [ ] Check if profile completeness alert displays at top
  - [ ] Verify completeness percentage shows
  - [ ] Check progress bar displays
  - [ ] Review missing fields list (if any)
  - [ ] Test "Complete Your Profile" link navigation
  - [ ] Test dismiss button (alert should reappear on refresh if <100%)

- [ ] **Task 3.1.5**: Quick Actions
  - [ ] Quick action buttons display
  - [ ] Each quick action is clickable
  - [ ] Quick actions navigate to correct pages

**✅ Expected Result**: Dashboard fully functional, all cards display correctly, profile completeness tracking works

---

## 👤 SECTION 4: User Profile

### 4.1 Personal Information

- [ ] **Task 4.1.1**: Navigate to profile
  - URL: `http://localhost:8000/profile`
  - Profile page loads
  - All tabs/sections visible

- [ ] **Task 4.1.2**: View personal information
  - [ ] Name displays correctly
  - [ ] Email displays correctly
  - [ ] Date of birth displays
  - [ ] Gender displays
  - [ ] Marital status displays

- [ ] **Task 4.1.3**: Edit personal information
  - [ ] Click "Edit" button
  - [ ] Modify phone number
  - [ ] Modify address
  - [ ] Click "Save"
  - [ ] Success message displays
  - [ ] Changes persist after page refresh

**✅ Expected Result**: Personal information displays and edits successfully

---

### 4.2 Income & Occupation

- [ ] **Task 4.2.1**: View income & occupation section
  - [ ] Occupation displays
  - [ ] Employer displays
  - [ ] All income sources display with amounts

- [ ] **Task 4.2.2**: Edit income
  - [ ] Click "Edit" button
  - [ ] Modify Annual Employment Income: £55,000
  - [ ] Add Annual Rental Income: £6,000
  - [ ] Click "Save"
  - [ ] Success message displays
  - [ ] Total income recalculates
  - [ ] Changes persist

**✅ Expected Result**: Income edits save and total updates correctly

---

### 4.3 Expenditure (v0.1.2.5 NEW)

- [ ] **Task 4.3.1**: View expenditure section
  - [ ] Monthly Expenditure displays
  - [ ] Annual Expenditure displays
  - [ ] Verify calculation consistency

- [ ] **Task 4.3.2**: Edit expenditure
  - [ ] Click "Edit" button
  - [ ] Modify Monthly Expenditure: £3,500
  - [ ] Verify Annual Expenditure auto-calculates (£42,000)
  - [ ] Click "Save"
  - [ ] Success message displays
  - [ ] Profile completeness score updates

**✅ Expected Result**: Expenditure fields work, calculations correct, completeness updates

---

### 4.4 Domicile Information (v0.1.2.5 NEW)

- [ ] **Task 4.4.1**: Navigate to domicile section
  - [ ] Domicile status displays
  - [ ] Country of birth displays
  - [ ] UK arrival date displays (if non-UK domiciled)
  - [ ] Years UK resident calculation shows
  - [ ] Deemed domicile status explanation displays

- [ ] **Task 4.4.2**: Edit domicile information
  - [ ] Click "Edit" button
  - [ ] Change domicile status
  - [ ] Change country of birth
  - [ ] If non-UK domiciled, set UK arrival date
  - [ ] Verify years UK resident recalculates
  - [ ] Check deemed domicile message updates (15/20 years rule)
  - [ ] Click "Save"
  - [ ] Success message displays

- [ ] **Task 4.4.3**: Test deemed domicile calculation
  - [ ] Set UK arrival date to 10 years ago → Not deemed domiciled
  - [ ] Set UK arrival date to 16 years ago → Deemed domiciled (15/20 rule)
  - [ ] Verify explanation text updates accordingly

**✅ Expected Result**: Domicile information editable, calculations correct, explanations display

---

### 4.5 Family Members

- [ ] **Task 4.5.1**: View family members section
  - [ ] Family members list displays
  - [ ] "Add Family Member" button visible

- [ ] **Task 4.5.2**: Add non-spouse family member
  - [ ] Click "Add Family Member"
  - [ ] Name: "Child One"
  - [ ] Relationship: "Child"
  - [ ] Date of Birth: 2015-05-10
  - [ ] Is Dependent: Check YES
  - [ ] Click "Save"
  - [ ] Family member appears in list
  - [ ] Profile completeness updates (dependants check)

- [ ] **Task 4.5.3**: Add spouse (if married)
  - [ ] Click "Add Family Member"
  - [ ] Relationship: "Spouse"
  - [ ] Name: "Spouse Name"
  - [ ] Email: "spouse@fps.com" (NEW - v0.1.2.5 spouse linking)
  - [ ] Verify email field appears for spouse
  - [ ] Click "Save"
  - [ ] If new email: Spouse account created automatically
  - [ ] If existing email: Accounts linked bidirectionally
  - [ ] Spouse_id populated
  - [ ] Marital status updated to "married" for both
  - [ ] Profile completeness updates (spouse_linked check)

- [ ] **Task 4.5.4**: Edit family member
  - [ ] Click "Edit" on family member
  - [ ] Modify details
  - [ ] Click "Save"
  - [ ] Changes persist

- [ ] **Task 4.5.5**: Delete family member
  - [ ] Click "Delete" on family member
  - [ ] Confirm deletion
  - [ ] Family member removed from list

**✅ Expected Result**: Family management works, spouse linking functions correctly

---

### 4.6 Profile Completeness Check

- [ ] **Task 4.6.1**: Review completeness score
  - [ ] Navigate to profile or dashboard
  - [ ] Profile completeness percentage displays
  - [ ] Missing fields list shows (if any)

- [ ] **Task 4.6.2**: Complete all required fields for 100%
  - [ ] Spouse linked (if married)
  - [ ] At least one dependant added
  - [ ] Domicile information filled
  - [ ] At least one income source > 0
  - [ ] Expenditure filled (monthly & annual)
  - [ ] At least one asset added
  - [ ] Liabilities reviewed (flag set)
  - [ ] Protection profile created with at least one policy

- [ ] **Task 4.6.3**: Verify 100% completeness achievable (v0.1.2.5 NEW)
  - [ ] Complete all required fields
  - [ ] Refresh page
  - [ ] Check completeness score = 100%
  - [ ] Verify "Profile Complete" message displays
  - [ ] No missing fields listed
  - [ ] Comprehensive plans should show "Personalized Plan" badge

**✅ Expected Result**: 100% profile completeness achievable with all fields filled

---

## 💰 SECTION 5: Net Worth Module

### 5.1 Net Worth Overview

- [ ] **Task 5.1.1**: Navigate to Net Worth
  - URL: `http://localhost:8000/net-worth`
  - Net Worth overview page loads
  - Summary cards display

- [ ] **Task 5.1.2**: Check summary calculations
  - [ ] Total Assets amount displays
  - [ ] Total Liabilities amount displays
  - [ ] Net Worth calculation correct (assets - liabilities)
  - [ ] Charts display (if any)

- [ ] **Task 5.1.3**: Check asset breakdown
  - [ ] Properties list displays
  - [ ] Savings accounts list displays
  - [ ] Investments list displays
  - [ ] Business interests list displays
  - [ ] Chattels list displays
  - [ ] Each category shows total value

**✅ Expected Result**: Net worth overview accurate, all asset categories display

---

### 5.2 Property Management

- [ ] **Task 5.2.1**: Navigate to Properties tab
  - Click "Property" or "Properties" navigation
  - Properties list displays
  - All added properties show

- [ ] **Task 5.2.2**: Add new property
  - [ ] Click "Add Property" button
  - [ ] Modal/form opens
  - [ ] **Step 1: Basic Information**
    - Property Type: "Buy to Let"
    - Address Line 1: "789 Investment Road"
    - City: "Manchester"
    - Postcode: "M1 1AA"
    - Purchase Date: 2018-06-15
    - Purchase Price: £200,000
    - Current Value: £250,000
  - [ ] **Step 2: Ownership**
    - Ownership Type: "Individual" or "Joint"
    - Country: Select "United Kingdom" or other (v0.1.2.5 NEW)
    - If Joint: Ownership Percentage: 50%
  - [ ] **Step 3: Financial Details** (if applicable)
    - SDLT Paid: £3,500
    - Monthly Rental Income: £1,200
    - Annual Rental Income: £14,400
  - [ ] **Step 4: Costs** (if applicable)
    - Annual Service Charge: £500
    - Annual Ground Rent: £250
    - Annual Insurance: £300
  - [ ] Click "Save" or "Complete"
  - [ ] Property appears in list
  - [ ] Total assets recalculates

- [ ] **Task 5.2.3**: View property details
  - [ ] Click on property card
  - [ ] Property detail page opens
  - [ ] All property information displays
  - [ ] Tabs for Overview, Financials, Documents (if implemented)

- [ ] **Task 5.2.4**: Edit property
  - [ ] Click "Edit Property" button
  - [ ] Modify Current Value: £260,000
  - [ ] Modify Country: Change to "Spain" (v0.1.2.5 test)
  - [ ] Click "Save"
  - [ ] Success message displays
  - [ ] Changes persist
  - [ ] Net worth updates

- [ ] **Task 5.2.5**: Add mortgage to property
  - [ ] Click "Add Mortgage" or "Mortgages" tab
  - [ ] Lender: "Barclays"
  - [ ] Mortgage Type: "Interest Only"
  - [ ] Outstanding Balance: £150,000
  - [ ] Interest Rate: 5.0%
  - [ ] Monthly Payment: £625
  - [ ] Start Date: 2018-06-15
  - [ ] Term Years: 25
  - [ ] Country: "United Kingdom" (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Mortgage displays in property financials
  - [ ] Total liabilities increases
  - [ ] Net worth recalculates

- [ ] **Task 5.2.6**: Property calculations
  - [ ] Net rental income calculates (rental - costs)
  - [ ] Property equity calculates (value - mortgage)
  - [ ] Charts display property performance (if implemented)

- [ ] **Task 5.2.7**: Delete property
  - [ ] Click "Delete Property"
  - [ ] Confirm deletion
  - [ ] Property removed from list
  - [ ] Net worth recalculates
  - [ ] Associated mortgage also deleted

**✅ Expected Result**: Property management fully functional, country tracking works, calculations accurate

---

### 5.3 Savings Accounts

- [ ] **Task 5.3.1**: Navigate to Savings/Cash tab
  - Savings accounts list displays
  - All accounts show with balances

- [ ] **Task 5.3.2**: Add ISA account
  - [ ] Click "Add Savings Account"
  - [ ] Institution: "Lloyds"
  - [ ] Account Type: "Cash"
  - [ ] Current Balance: £15,000
  - [ ] Is ISA: Check YES
  - [ ] Verify Country field is HIDDEN (ISAs must be UK)
  - [ ] ISA Type: "Cash"
  - [ ] ISA Subscription Year: "2025/26"
  - [ ] ISA Subscription Amount: £15,000
  - [ ] Interest Rate: 4.5%
  - [ ] Click "Save"
  - [ ] Account appears in list
  - [ ] Verify country = "United Kingdom" in database
  - [ ] ISA allowance tracker updates (if displayed)

- [ ] **Task 5.3.3**: Add non-ISA foreign account (v0.1.2.5 country test)
  - [ ] Click "Add Savings Account"
  - [ ] Institution: "Deutsche Bank"
  - [ ] Account Type: "Cash"
  - [ ] Current Balance: £25,000
  - [ ] Is ISA: Check NO
  - [ ] Country: Select "Germany" (v0.1.2.5 NEW - field should be visible)
  - [ ] Interest Rate: 3.0%
  - [ ] Click "Save"
  - [ ] Account appears with German flag or country indicator
  - [ ] Verify country saved correctly

- [ ] **Task 5.3.4**: Test ISA allowance tracking
  - [ ] Total ISA contributions for current tax year displays
  - [ ] Remaining ISA allowance calculates (£20,000 - subscribed)
  - [ ] Warning shows if approaching/exceeding £20,000 limit
  - [ ] Cross-module ISA tracking (savings + investment ISAs)

- [ ] **Task 5.3.5**: Edit savings account
  - [ ] Click "Edit" on savings account
  - [ ] Modify Current Balance
  - [ ] If ISA: Verify country cannot be changed (locked to UK)
  - [ ] If non-ISA: Change country to different country
  - [ ] Click "Save"
  - [ ] Changes persist

- [ ] **Task 5.3.6**: Set as emergency fund
  - [ ] Mark account as "Emergency Fund"
  - [ ] Emergency fund status displays
  - [ ] Emergency fund calculations update (runway, coverage)

- [ ] **Task 5.3.7**: Delete savings account
  - [ ] Click "Delete"
  - [ ] Confirm deletion
  - [ ] Account removed
  - [ ] Net worth recalculates

**✅ Expected Result**: Savings accounts work, ISA country locked to UK, non-ISA allows country selection

---

### 5.4 Investments

- [ ] **Task 5.4.1**: Navigate to Investments tab
  - Investment accounts list displays

- [ ] **Task 5.4.2**: Add investment account
  - [ ] Click "Add Investment Account"
  - [ ] Account Name: "Vanguard ISA"
  - [ ] Account Type: "Stocks & Shares ISA"
  - [ ] Institution: "Vanguard"
  - [ ] Current Value: £30,000
  - [ ] Is ISA: Check YES (if separate field)
  - [ ] Verify Country field hidden (ISAs must be UK)
  - [ ] Click "Save"
  - [ ] Account appears in list
  - [ ] ISA allowance updates

- [ ] **Task 5.4.3**: Add non-ISA investment (v0.1.2.5 test)
  - [ ] Click "Add Investment Account"
  - [ ] Account Name: "US Brokerage"
  - [ ] Account Type: "General Investment Account"
  - [ ] Institution: "Interactive Brokers"
  - [ ] Current Value: £50,000
  - [ ] Is ISA: NO
  - [ ] Country: Select "United States" (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Verify country saved

- [ ] **Task 5.4.4**: Add holdings to account
  - [ ] Click on investment account
  - [ ] Click "Add Holding"
  - [ ] Symbol/Ticker: "VWRL"
  - [ ] Name: "Vanguard FTSE All-World"
  - [ ] Quantity: 100
  - [ ] Purchase Price: £80
  - [ ] Current Price: £95
  - [ ] Click "Save"
  - [ ] Holding displays
  - [ ] Gain/Loss calculates

- [ ] **Task 5.4.5**: View portfolio analysis
  - [ ] Asset allocation chart displays
  - [ ] Performance metrics show
  - [ ] Total portfolio value correct

**✅ Expected Result**: Investment accounts work, ISA tracking correct, country field behavior proper

---

### 5.5 Business Interests

- [ ] **Task 5.5.1**: Navigate to Business tab
  - Business interests list displays

- [ ] **Task 5.5.2**: Add business interest
  - [ ] Click "Add Business Interest"
  - [ ] Business Name: "Tech Startup Ltd"
  - [ ] Ownership Percentage: 25%
  - [ ] Current Valuation: £100,000
  - [ ] Business Type: "Private Limited Company"
  - [ ] Country: Select "United Kingdom" (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Business appears in list
  - [ ] Net worth updates

**✅ Expected Result**: Business interests manageable, country tracking works

---

### 5.6 Chattels

- [ ] **Task 5.6.1**: Navigate to Chattels tab
  - Chattels list displays

- [ ] **Task 5.6.2**: Add chattel
  - [ ] Click "Add Chattel"
  - [ ] Item Name: "Jewelry Collection"
  - [ ] Description: "Gold and diamond pieces"
  - [ ] Current Value: £15,000
  - [ ] Purchase Date: 2015-08-20
  - [ ] Country: Select "United Kingdom" (v0.1.2.5 NEW)
  - [ ] Click "Save"
  - [ ] Chattel appears in list

**✅ Expected Result**: Chattels manageable, included in net worth

---

## 🛡️ SECTION 6: Protection Module

### 6.1 Protection Dashboard

- [ ] **Task 6.1.1**: Navigate to Protection
  - URL: `http://localhost:8000/protection`
  - Protection dashboard loads
  - Profile Completeness Alert displays at top (v0.1.2.5 NEW)

- [ ] **Task 6.1.2**: Check profile completeness warning
  - [ ] Alert displays if profile incomplete
  - [ ] Shows completeness percentage
  - [ ] Lists missing fields with priority badges
  - [ ] "Complete Your Profile" link works
  - [ ] Dismiss button functions

**✅ Expected Result**: Protection dashboard loads, completeness alert functional

---

### 6.2 Protection Profile

- [ ] **Task 6.2.1**: Create/Edit protection profile
  - [ ] Click "Create Profile" or "Edit Profile"
  - [ ] Annual Income: £50,000
  - [ ] Monthly Expenditure: £3,000
  - [ ] Number of Dependents: 2
  - [ ] Mortgage Balance: £180,000
  - [ ] Other Debts: £10,000
  - [ ] Retirement Age: 65
  - [ ] Click "Save"
  - [ ] Profile saved successfully

- [ ] **Task 6.2.2**: View protection needs analysis
  - [ ] Human capital calculation displays
  - [ ] Total protection needs amount shows
  - [ ] Breakdown by category (life, CI, IP) displays
  - [ ] Adequacy score displays (gauge chart)

**✅ Expected Result**: Protection profile creates, analysis calculates correctly

---

### 6.3 Life Insurance Policies

- [ ] **Task 6.3.1**: Add life insurance policy
  - [ ] Click "Add Policy" → "Life Insurance"
  - [ ] Provider: "Aviva"
  - [ ] Policy Number: "LI123456"
  - [ ] Policy Type: "Term"
  - [ ] Sum Assured: £250,000
  - [ ] Premium Amount: £25
  - [ ] Premium Frequency: "Monthly"
  - [ ] Policy Start Date: 2020-01-01
  - [ ] Policy Term Years: 20
  - [ ] In Trust: Check YES or NO
  - [ ] Beneficiaries: Add if applicable
  - [ ] Click "Save"
  - [ ] Policy appears in list
  - [ ] Coverage gap recalculates

- [ ] **Task 6.3.2**: View policy details
  - [ ] Click on policy
  - [ ] All details display correctly
  - [ ] Annual premium calculates (monthly × 12)
  - [ ] Remaining term shows

**✅ Expected Result**: Life insurance policies manageable, coverage calculations update

---

### 6.4 Critical Illness Policies

- [ ] **Task 6.4.1**: Add critical illness policy
  - [ ] Click "Add Policy" → "Critical Illness"
  - [ ] Provider: "Legal & General"
  - [ ] Sum Assured: £100,000
  - [ ] Premium Amount: £30
  - [ ] Premium Frequency: "Monthly"
  - [ ] Policy Start Date: 2021-06-01
  - [ ] Policy Term Years: 15
  - [ ] Click "Save"
  - [ ] Policy appears in list

**✅ Expected Result**: Critical illness policies manageable

---

### 6.5 Income Protection Policies

- [ ] **Task 6.5.1**: Add income protection policy
  - [ ] Click "Add Policy" → "Income Protection"
  - [ ] Provider: "Royal London"
  - [ ] Monthly Benefit: £3,000
  - [ ] Deferred Period: 4 weeks
  - [ ] Benefit Period: "To retirement"
  - [ ] Premium Amount: £40
  - [ ] Premium Frequency: "Monthly"
  - [ ] Click "Save"
  - [ ] Policy appears in list

**✅ Expected Result**: Income protection policies manageable

---

### 6.6 Protection Analysis & Recommendations

- [ ] **Task 6.6.1**: View coverage gap analysis
  - [ ] Navigate to Analysis tab
  - [ ] Coverage gap chart displays
  - [ ] Life insurance gap shows
  - [ ] Critical illness gap shows
  - [ ] Income protection gap shows
  - [ ] Color coding (red/amber/green) correct

- [ ] **Task 6.6.2**: View adequacy score
  - [ ] Overall adequacy score displays (0-100%)
  - [ ] Gauge chart shows score
  - [ ] Score interpretation displays
  - [ ] Recommendations based on score

- [ ] **Task 6.6.3**: View recommendations
  - [ ] Navigate to Recommendations tab
  - [ ] AI-generated recommendations display
  - [ ] Priority levels assigned (High/Medium/Low)
  - [ ] Action items listed
  - [ ] Potential savings/benefits quantified

- [ ] **Task 6.6.4**: View scenario analysis
  - [ ] Death scenario displays
  - [ ] Critical illness scenario displays
  - [ ] Disability scenario displays
  - [ ] Each scenario shows financial impact
  - [ ] Family coverage projections show

**✅ Expected Result**: Analysis complete, recommendations relevant, scenarios informative

---

### 6.7 Comprehensive Protection Plan

- [ ] **Task 6.7.1**: Generate comprehensive plan
  - [ ] Navigate to Protection dashboard
  - [ ] Click "Generate Comprehensive Plan" button
  - [ ] Plan generates (may take a few seconds)
  - [ ] Redirected to plan view

- [ ] **Task 6.7.2**: Review plan completeness section (v0.1.2.5 NEW)
  - [ ] Plan completeness banner displays at top
  - [ ] Shows completeness percentage
  - [ ] Displays plan type badge:
    - "Personalized Plan" (blue) if 100% complete
    - "Generic Plan" (amber) if incomplete
  - [ ] Severity-based disclaimer displays:
    - Critical (<50%): Red warning
    - Warning (50-99%): Amber caution
    - Success (100%): Green confirmation
  - [ ] Missing fields list displays (if any)
  - [ ] "Complete Your Profile" button works

- [ ] **Task 6.7.3**: Review plan sections
  - [ ] Executive Summary
  - [ ] User Profile
  - [ ] Financial Summary
  - [ ] Current Coverage
  - [ ] Protection Needs
  - [ ] Coverage Analysis
  - [ ] Recommendations
  - [ ] Scenario Analysis
  - [ ] Optimized Strategy
  - [ ] Implementation Timeline
  - [ ] Next Steps

- [ ] **Task 6.7.4**: Test plan actions
  - [ ] Download as PDF button (if implemented)
  - [ ] Print plan button
  - [ ] Share plan button (if implemented)
  - [ ] Back to dashboard button

**✅ Expected Result**: Comprehensive plan generates, completeness warnings display, all sections present

---

## 💰 SECTION 7: Savings Module

### 7.1 Savings Dashboard

- [ ] **Task 7.1.1**: Navigate to Savings
  - URL: `http://localhost:8000/savings`
  - Savings dashboard loads
  - Summary cards display

- [ ] **Task 7.1.2**: Check savings summary
  - [ ] Total savings amount displays
  - [ ] Emergency fund status shows
  - [ ] ISA allowance tracker displays
  - [ ] Savings goals list shows (if any)

**✅ Expected Result**: Savings dashboard functional, summaries accurate

---

### 7.2 Emergency Fund

- [ ] **Task 7.2.1**: View emergency fund analysis
  - [ ] Emergency fund amount displays
  - [ ] Monthly expenditure shows
  - [ ] Runway calculation displays (months coverage)
  - [ ] Adequacy assessment shows
  - [ ] Target emergency fund recommendation displays

- [ ] **Task 7.2.2**: Set/adjust emergency fund target
  - [ ] Recommended: 3-6 months expenses
  - [ ] Set custom target (e.g., 6 months = £18,000)
  - [ ] Progress bar shows current vs target

**✅ Expected Result**: Emergency fund tracking works, calculations correct

---

### 7.3 ISA Allowance Tracking

- [ ] **Task 7.3.1**: View ISA allowance
  - [ ] Current tax year displays (e.g., 2025/26)
  - [ ] Total ISA allowance: £20,000
  - [ ] Used amount from Cash ISAs displays
  - [ ] Used amount from S&S ISAs displays
  - [ ] Total used calculates correctly
  - [ ] Remaining allowance calculates (£20,000 - used)
  - [ ] Warning if approaching limit

- [ ] **Task 7.3.2**: Test cross-module ISA tracking
  - [ ] Verify Cash ISAs from Savings module counted
  - [ ] Verify S&S ISAs from Investment module counted
  - [ ] Total across both modules correct
  - [ ] No double counting

**✅ Expected Result**: ISA tracking accurate across modules, £20,000 limit enforced

---

### 7.4 Savings Goals

- [ ] **Task 7.4.1**: Add savings goal
  - [ ] Click "Add Savings Goal"
  - [ ] Goal Name: "Holiday Fund"
  - [ ] Target Amount: £5,000
  - [ ] Target Date: 2026-08-01
  - [ ] Current Amount: £1,500
  - [ ] Linked Account: Select savings account
  - [ ] Click "Save"
  - [ ] Goal appears in list

- [ ] **Task 7.4.2**: View goal progress
  - [ ] Progress bar displays
  - [ ] Percentage complete calculates
  - [ ] Amount remaining shows
  - [ ] Monthly savings required calculates
  - [ ] On-track indicator shows

- [ ] **Task 7.4.3**: Update goal progress
  - [ ] Click "Update Progress"
  - [ ] Add £500 to current amount
  - [ ] Save
  - [ ] Progress updates
  - [ ] Charts update

**✅ Expected Result**: Savings goals manageable, progress tracking works

---

## 📈 SECTION 8: Investment Module

### 8.1 Investment Dashboard

- [ ] **Task 8.1.1**: Navigate to Investment
  - URL: `http://localhost:8000/investment`
  - Investment dashboard loads
  - Portfolio summary displays

- [ ] **Task 8.1.2**: Check portfolio summary
  - [ ] Total portfolio value displays
  - [ ] Overall gain/loss shows
  - [ ] Percentage return calculates
  - [ ] Asset allocation chart displays
  - [ ] Top holdings list shows

**✅ Expected Result**: Investment dashboard functional, portfolio summaries accurate

---

### 8.2 Portfolio Analysis

- [ ] **Task 8.2.1**: View asset allocation
  - [ ] Asset allocation pie/donut chart displays
  - [ ] Breakdown by asset class (Equities, Bonds, Cash, etc.)
  - [ ] Percentages calculate correctly
  - [ ] Target allocation comparison (if set)

- [ ] **Task 8.2.2**: View performance metrics
  - [ ] Total return (£ and %) displays
  - [ ] Time-weighted return shows
  - [ ] Money-weighted return shows
  - [ ] Benchmark comparison (if available)

- [ ] **Task 8.2.3**: View holdings
  - [ ] All holdings list displays
  - [ ] Each holding shows:
    - Symbol/Ticker
    - Name
    - Quantity
    - Purchase Price
    - Current Price
    - Gain/Loss (£ and %)
    - Weight in portfolio

**✅ Expected Result**: Portfolio analysis comprehensive, calculations accurate

---

### 8.3 Risk Profile

- [ ] **Task 8.3.1**: Complete risk assessment
  - [ ] Navigate to Risk Profile section
  - [ ] Answer risk questionnaire
  - [ ] Submit responses
  - [ ] Risk score calculates (e.g., Conservative, Moderate, Aggressive)
  - [ ] Risk profile displays

- [ ] **Task 8.3.2**: View risk-adjusted recommendations
  - [ ] Asset allocation recommendations based on risk profile
  - [ ] Portfolio rebalancing suggestions
  - [ ] Risk vs return projections

**✅ Expected Result**: Risk profile assessment works, recommendations align with risk tolerance

---

### 8.4 Monte Carlo Simulation (Background Jobs)

- [ ] **Task 8.4.1**: Trigger Monte Carlo simulation
  - [ ] Navigate to Investment Projections
  - [ ] Click "Run Monte Carlo Simulation"
  - [ ] Simulation queues (background job)
  - [ ] "Processing..." or loading indicator shows

- [ ] **Task 8.4.2**: Wait for simulation completion
  - [ ] Check queue worker is running: `php artisan queue:work database`
  - [ ] Simulation completes (may take 30-60 seconds for 1,000 iterations)
  - [ ] Results display when complete

- [ ] **Task 8.4.3**: View simulation results
  - [ ] Probability distribution chart displays
  - [ ] Percentile projections show (10th, 50th, 90th)
  - [ ] Expected portfolio value at various time horizons
  - [ ] Probability of achieving goals

**✅ Expected Result**: Monte Carlo simulations run successfully, results meaningful

---

### 8.5 Investment Goals

- [ ] **Task 8.5.1**: Add investment goal
  - [ ] Click "Add Investment Goal"
  - [ ] Goal Name: "Retirement Fund"
  - [ ] Target Amount: £500,000
  - [ ] Target Date: 2050-12-31
  - [ ] Risk Tolerance: Moderate
  - [ ] Click "Save"
  - [ ] Goal appears

- [ ] **Task 8.5.2**: View goal projections
  - [ ] Goal progress displays
  - [ ] Required monthly contributions calculate
  - [ ] Probability of achieving goal shows
  - [ ] Scenario analysis available

**✅ Expected Result**: Investment goals manageable, projections helpful

---

## 🏖️ SECTION 9: Retirement Module

### 9.1 Retirement Dashboard

- [ ] **Task 9.1.1**: Navigate to Retirement
  - URL: `http://localhost:8000/retirement`
  - Retirement dashboard loads
  - Retirement readiness score displays

- [ ] **Task 9.1.2**: Check retirement summary
  - [ ] Total pension pot value displays
  - [ ] Projected pension income shows
  - [ ] State pension estimate shows
  - [ ] Retirement gap analysis displays
  - [ ] Readiness score (0-100%) shows

**✅ Expected Result**: Retirement dashboard functional, readiness score calculates

---

### 9.2 Pension Management

- [ ] **Task 9.2.1**: Add DC pension
  - [ ] Click "Add Pension" → "Defined Contribution"
  - [ ] Scheme Name: "Workplace Pension"
  - [ ] Provider: "Nest"
  - [ ] Current Value: £50,000
  - [ ] Employee Contribution Rate: 5%
  - [ ] Employer Contribution Rate: 3%
  - [ ] Total Contribution Rate: 8%
  - [ ] Expected Retirement Age: 65
  - [ ] Click "Save"
  - [ ] Pension appears in list

- [ ] **Task 9.2.2**: Add DB pension
  - [ ] Click "Add Pension" → "Defined Benefit"
  - [ ] Scheme Name: "Public Sector Pension"
  - [ ] Years of Service: 20
  - [ ] Final Salary: £50,000
  - [ ] Accrual Rate: 1/60 or 1/80
  - [ ] Expected Annual Pension: Calculates automatically
  - [ ] Click "Save"
  - [ ] Pension appears in list

- [ ] **Task 9.2.3**: Add State Pension
  - [ ] Click "Add State Pension"
  - [ ] National Insurance Years: 35 (full entitlement)
  - [ ] Expected Annual Amount: £11,502 (2025/26 full state pension)
  - [ ] State Pension Age: 67 (or user's SPA)
  - [ ] Click "Save"
  - [ ] State pension displays

**✅ Expected Result**: All pension types manageable, calculations correct

---

### 9.3 Pension Projections

- [ ] **Task 9.3.1**: View pension projections
  - [ ] Navigate to Projections section
  - [ ] Projection chart displays (growth over time)
  - [ ] Projected pot value at retirement shows
  - [ ] Estimated annual income at retirement calculates
  - [ ] Different scenarios available (optimistic, realistic, pessimistic)

- [ ] **Task 9.3.2**: Test contribution changes
  - [ ] Adjust contribution rate (e.g., increase to 10%)
  - [ ] View updated projections
  - [ ] Additional pension pot at retirement calculates
  - [ ] Extra annual income shows

**✅ Expected Result**: Pension projections realistic, contribution impact clear

---

### 9.4 Annual Allowance Check

- [ ] **Task 9.4.1**: View annual allowance
  - [ ] Current tax year allowance: £60,000
  - [ ] Current year contributions display
  - [ ] Remaining allowance calculates
  - [ ] Carry forward from previous 3 years shows (if applicable)
  - [ ] Warning if approaching/exceeding allowance

- [ ] **Task 9.4.2**: Test annual allowance warning
  - [ ] Add contributions totaling > £60,000
  - [ ] Verify warning displays
  - [ ] Tax charge calculation shows (if exceeded)

**✅ Expected Result**: Annual allowance tracking accurate, warnings timely

---

### 9.5 Retirement Readiness

- [ ] **Task 9.5.1**: View readiness score
  - [ ] Overall readiness score displays (0-100%)
  - [ ] Score breakdown by category:
    - Pension savings level
    - Contribution rate
    - Years to retirement
    - Projected income vs target
  - [ ] Gauge chart displays score
  - [ ] Score interpretation displays

- [ ] **Task 9.5.2**: View recommendations
  - [ ] Recommendations based on readiness score
  - [ ] Suggested actions listed
  - [ ] Priority levels assigned
  - [ ] Impact of actions quantified

**✅ Expected Result**: Retirement readiness assessment comprehensive, actionable

---

## 🏛️ SECTION 10: Estate Planning Module

### 10.1 Estate Dashboard

- [ ] **Task 10.1.1**: Navigate to Estate Planning
  - URL: `http://localhost:8000/estate`
  - Estate dashboard loads
  - Profile Completeness Alert displays at top (v0.1.2.5 NEW)
  - Summary cards display

- [ ] **Task 10.1.2**: Check profile completeness (v0.1.2.5 NEW)
  - [ ] Alert shows completeness percentage
  - [ ] Lists missing fields (especially spouse link if married)
  - [ ] Priority badges display
  - [ ] Dismiss functionality works

- [ ] **Task 10.1.3**: Check estate summary
  - [ ] Gross estate value displays
  - [ ] Total liabilities display
  - [ ] Net estate value calculates
  - [ ] IHT liability estimate shows
  - [ ] Probate readiness score displays

**✅ Expected Result**: Estate dashboard functional, completeness alert works, IHT calculations display

---

### 10.2 Net Worth Analysis

- [ ] **Task 10.2.1**: View net worth breakdown
  - [ ] Assets aggregated from all modules:
    - Properties
    - Savings
    - Investments
    - Pensions
    - Business interests
    - Chattels
    - Estate assets
  - [ ] Each category total displays
  - [ ] Grand total assets calculates

- [ ] **Task 10.2.2**: View liabilities breakdown
  - [ ] Mortgages total displays
  - [ ] Other liabilities total displays
  - [ ] Total liabilities calculates

- [ ] **Task 10.2.3**: View net worth calculation
  - [ ] Net worth = Total assets - Total liabilities
  - [ ] Calculation correct
  - [ ] Charts display asset/liability breakdown

**✅ Expected Result**: Net worth aggregation accurate across all modules

---

### 10.3 IHT Calculation

- [ ] **Task 10.3.1**: View IHT calculation
  - [ ] Gross taxable estate displays
  - [ ] Available NRB (Nil Rate Band): £325,000
  - [ ] Available RNRB (Residence NRB): £175,000 (if applicable)
  - [ ] Total threshold calculates (NRB + RNRB)
  - [ ] Taxable amount calculates (estate - thresholds)
  - [ ] IHT liability calculates (40% of taxable amount)

- [ ] **Task 10.3.2**: Test spouse exemption (if married)
  - [ ] Spouse link established in profile
  - [ ] Spouse exemption applies
  - [ ] Transferable NRB/RNRB displays
  - [ ] Second death IHT calculation available

- [ ] **Task 10.3.3**: Test RNRB eligibility
  - [ ] Own home (property) exists
  - [ ] Property left to direct descendants
  - [ ] RNRB available: £175,000
  - [ ] RNRB taper if estate > £2 million

**✅ Expected Result**: IHT calculations accurate, thresholds correct, spouse exemption works

---

### 10.4 Gifting Strategy

- [ ] **Task 10.4.1**: View gifting recommendations
  - [ ] Navigate to Gifting section
  - [ ] Current gifting allowances display:
    - Annual exemption: £3,000
    - Small gifts: £250 per person
    - Wedding gifts: £5,000 (child), £2,500 (grandchild), £1,000 (other)
  - [ ] PET (Potentially Exempt Transfer) explanation displays
  - [ ] 7-year rule explained
  - [ ] Taper relief chart shows

- [ ] **Task 10.4.2**: Add gift
  - [ ] Click "Add Gift"
  - [ ] Recipient: "Child One"
  - [ ] Gift Type: "Cash"
  - [ ] Amount: £10,000
  - [ ] Date: 2024-01-15
  - [ ] Click "Save"
  - [ ] Gift appears in list

- [ ] **Task 10.4.3**: View PET analysis
  - [ ] PET timeline displays
  - [ ] Each gift shows time remaining until exempt (7 years)
  - [ ] Taper relief percentage shows for gifts 3-7 years old
  - [ ] Potential IHT liability on PETs calculates

- [ ] **Task 10.4.4**: View gifting strategy recommendations
  - [ ] Optimal gifting strategy displays
  - [ ] Annual exemption utilization shows
  - [ ] Long-term IHT savings potential quantifies
  - [ ] Recommended gifting schedule displays

**✅ Expected Result**: Gifting features work, PET tracking accurate, recommendations helpful

---

### 10.5 Trust Planning

- [ ] **Task 10.5.1**: View trust recommendations
  - [ ] Navigate to Trusts section
  - [ ] Trust strategy recommendations display
  - [ ] Types of trusts explained (bare, interest in possession, discretionary)
  - [ ] Benefits and tax implications outlined

- [ ] **Task 10.5.2**: Add trust
  - [ ] Click "Add Trust"
  - [ ] Trust Name: "Family Trust"
  - [ ] Trust Type: "Discretionary Trust"
  - [ ] Date Established: 2023-06-01
  - [ ] Trustees: Add names
  - [ ] Beneficiaries: Add family members
  - [ ] Trust Value: £50,000
  - [ ] Click "Save"
  - [ ] Trust appears in list

- [ ] **Task 10.5.3**: View trust tax implications
  - [ ] Periodic charges (10-year anniversary)
  - [ ] Exit charges
  - [ ] IHT treatment displays

**✅ Expected Result**: Trust planning features functional, tax implications explained

---

### 10.6 Life Policy Strategy (v0.1.2.5 NEW)

- [ ] **Task 10.6.1**: Navigate to Life Policy Strategy section
  - [ ] Life policy options display
  - [ ] Whole of Life vs Self-Insurance comparison shows

- [ ] **Task 10.6.2**: View Whole of Life analysis
  - [ ] IHT liability amount displays
  - [ ] Whole of Life policy cost estimates
  - [ ] Advantages/disadvantages listed
  - [ ] Suitable scenarios explained

- [ ] **Task 10.6.3**: View Self-Insurance analysis
  - [ ] Investment approach explained
  - [ ] Projected growth scenarios show
  - [ ] Comparison vs Whole of Life policy
  - [ ] Risk factors outlined

- [ ] **Task 10.6.4**: View recommendations
  - [ ] Personalized recommendation based on:
    - Age
    - IHT liability
    - Risk tolerance
    - Financial situation
  - [ ] Action steps listed

**✅ Expected Result**: Life policy strategy feature works, comparisons helpful

---

### 10.7 Second Death IHT Planning (v0.1.2.5 NEW)

- [ ] **Task 10.7.1**: View second death IHT calculator (if married)
  - [ ] Navigate to Second Death Planning
  - [ ] Spouse details display
  - [ ] Combined estate value calculates
  - [ ] Transferable NRB displays (up to £650,000 total)
  - [ ] Transferable RNRB displays (up to £350,000 total)

- [ ] **Task 10.7.2**: Calculate second death IHT
  - [ ] Combined estate value: Assets + Spouse assets
  - [ ] Total threshold: £1,000,000 (£650k NRB + £350k RNRB)
  - [ ] Second death IHT liability calculates
  - [ ] Comparison vs single death IHT shows
  - [ ] IHT savings from spouse exemption quantified

**✅ Expected Result**: Second death planning works for married couples, calculations accurate

---

### 10.8 Comprehensive Estate Plan

- [ ] **Task 10.8.1**: Generate comprehensive estate plan
  - [ ] Navigate to Estate dashboard
  - [ ] Click "Generate Comprehensive Estate Plan"
  - [ ] Plan generates
  - [ ] Redirected to plan view

- [ ] **Task 10.8.2**: Review plan completeness section (v0.1.2.5 NEW)
  - [ ] Plan completeness banner displays
  - [ ] Shows completeness percentage
  - [ ] Plan type badge displays:
    - "Personalized Plan" (blue) if 100%
    - "Generic Plan" (amber) if incomplete
  - [ ] Severity-based disclaimer:
    - Critical (<50%): Red warning
    - Warning (50-99%): Amber caution
    - Success (100%): Green confirmation
  - [ ] Missing fields list (including spouse link if married)
  - [ ] "Complete Your Profile" button works

- [ ] **Task 10.8.3**: Review plan sections
  - [ ] Executive Summary
  - [ ] Current Estate Position
  - [ ] Net Worth Analysis
  - [ ] IHT Liability Calculation
  - [ ] Gifting Strategy
  - [ ] Trust Planning Options
  - [ ] Life Policy Strategy (v0.1.2.5 NEW)
  - [ ] Second Death Planning (if married) (v0.1.2.5 NEW)
  - [ ] Probate Readiness
  - [ ] Recommendations
  - [ ] Action Plan
  - [ ] Next Steps

- [ ] **Task 10.8.4**: Test plan actions
  - [ ] Download/Print functionality
  - [ ] Share plan (if implemented)
  - [ ] Back to dashboard

**✅ Expected Result**: Comprehensive estate plan generates, completeness warnings display, all sections present

---

## 🎯 SECTION 11: Actions & Recommendations

### 11.1 Actions Dashboard

- [ ] **Task 11.1.1**: Navigate to Actions
  - URL: `http://localhost:8000/actions`
  - Actions dashboard loads
  - Consolidated recommendations from all modules display

- [ ] **Task 11.1.2**: View aggregated recommendations
  - [ ] Recommendations from Protection module
  - [ ] Recommendations from Savings module
  - [ ] Recommendations from Investment module
  - [ ] Recommendations from Retirement module
  - [ ] Recommendations from Estate module

- [ ] **Task 11.1.3**: Filter recommendations
  - [ ] Filter by module
  - [ ] Filter by priority (High/Medium/Low)
  - [ ] Filter by status (Pending/In Progress/Complete)
  - [ ] Search functionality

- [ ] **Task 11.1.4**: Manage actions
  - [ ] Mark action as "In Progress"
  - [ ] Mark action as "Complete"
  - [ ] Add notes to action
  - [ ] Set reminder/due date

**✅ Expected Result**: Actions dashboard aggregates all recommendations, filtering works

---

## 🌐 SECTION 12: Holistic Plan

### 12.1 Holistic Financial Plan

- [ ] **Task 12.1.1**: Navigate to Holistic Plan
  - URL: `http://localhost:8000/holistic-plan`
  - Holistic plan page loads

- [ ] **Task 12.1.2**: Generate holistic plan
  - [ ] Click "Generate Holistic Plan"
  - [ ] CoordinatingAgent processes all modules
  - [ ] Plan generates (may take longer due to cross-module analysis)
  - [ ] Plan displays

- [ ] **Task 12.1.3**: Review holistic plan sections
  - [ ] Executive Summary
  - [ ] Current Financial Position (all modules)
  - [ ] Protection Analysis
  - [ ] Savings Analysis
  - [ ] Investment Analysis
  - [ ] Retirement Readiness
  - [ ] Estate Planning
  - [ ] Cross-Module Insights
  - [ ] Prioritized Recommendations
  - [ ] Comprehensive Action Plan
  - [ ] Timeline & Milestones

- [ ] **Task 12.1.4**: Review cross-module insights
  - [ ] ISA allowance coordination (Savings + Investment)
  - [ ] Asset allocation across all accounts
  - [ ] Cash flow analysis (Income - Expenditure)
  - [ ] Discretionary income for savings/investments
  - [ ] Total protection gap vs net worth
  - [ ] Retirement readiness vs estate plan

**✅ Expected Result**: Holistic plan integrates all modules, cross-module insights valuable

---

## 💷 SECTION 13: UK Taxes & Allowances

### 13.1 Tax Information

- [ ] **Task 13.1.1**: Navigate to UK Taxes
  - URL: `http://localhost:8000/uk-taxes`
  - Tax information page loads

- [ ] **Task 13.1.2**: View current tax year information
  - [ ] Current tax year displays (e.g., 2025/26)
  - [ ] Tax year dates: April 6 - April 5

- [ ] **Task 13.1.3**: View income tax information
  - [ ] Personal Allowance: £12,570
  - [ ] Basic Rate: 20% (£12,571 - £50,270)
  - [ ] Higher Rate: 40% (£50,271 - £125,140)
  - [ ] Additional Rate: 45% (over £125,140)
  - [ ] Personal Allowance taper explanation

- [ ] **Task 13.1.4**: View National Insurance rates
  - [ ] Class 1 (Employee) rates
  - [ ] Class 2 (Self-employed) rates
  - [ ] Class 4 (Self-employed) rates
  - [ ] Thresholds display

- [ ] **Task 13.1.5**: View savings & investment allowances
  - [ ] ISA Allowance: £20,000
  - [ ] Personal Savings Allowance: £1,000 (Basic) / £500 (Higher)
  - [ ] Dividend Allowance: £500
  - [ ] Capital Gains Tax Allowance: £3,000

- [ ] **Task 13.1.6**: View pension allowances
  - [ ] Annual Allowance: £60,000
  - [ ] Carry Forward rules explained (3 years)
  - [ ] Money Purchase Annual Allowance: £10,000
  - [ ] Lifetime Allowance: Abolished (note)

- [ ] **Task 13.1.7**: View IHT thresholds
  - [ ] Nil Rate Band: £325,000
  - [ ] Residence Nil Rate Band: £175,000
  - [ ] IHT Rate: 40%
  - [ ] Spouse exemption explanation
  - [ ] PET 7-year rule
  - [ ] Taper relief chart

**✅ Expected Result**: All UK tax information accurate for 2025/26, clearly presented

---

## ⚙️ SECTION 14: Settings & Preferences

### 14.1 Account Settings

- [ ] **Task 14.1.1**: Navigate to Settings
  - URL: `http://localhost:8000/settings`
  - Settings page loads

- [ ] **Task 14.1.2**: Change password
  - [ ] Click "Change Password"
  - [ ] Current Password: Enter current
  - [ ] New Password: Enter new (strong)
  - [ ] Confirm Password: Re-enter new
  - [ ] Click "Update Password"
  - [ ] Success message displays
  - [ ] Re-login with new password works

- [ ] **Task 14.1.3**: Update email
  - [ ] Click "Change Email"
  - [ ] New Email: Enter new email
  - [ ] Confirm Password: Enter current password
  - [ ] Click "Update Email"
  - [ ] Verification email sent (if implemented)
  - [ ] Email updates

- [ ] **Task 14.1.4**: Notification preferences
  - [ ] Email notifications toggle
  - [ ] Recommendation alerts toggle
  - [ ] Report generation notifications toggle
  - [ ] Save preferences

**✅ Expected Result**: Account settings functional, password/email changes work

---

### 14.2 Data Management

- [ ] **Task 14.2.1**: Export data
  - [ ] Click "Export Data"
  - [ ] Choose format (CSV/JSON/PDF)
  - [ ] Export initiates
  - [ ] File downloads

- [ ] **Task 14.2.2**: Delete account (if implemented)
  - [ ] Click "Delete Account"
  - [ ] Warning displays
  - [ ] Confirm deletion
  - [ ] Account deleted (test with caution!)

**✅ Expected Result**: Data export works, account deletion functional (if implemented)

---

## 🛠️ SECTION 15: Admin Panel (Admin Users Only)

### 15.1 Admin Access

- [ ] **Task 15.1.1**: Login as admin
  - Email: `admin@fps.com`
  - Password: `admin123456`
  - Login successful

- [ ] **Task 15.1.2**: Navigate to Admin Panel
  - URL: `http://localhost:8000/admin`
  - Admin panel loads
  - Admin dashboard displays

**✅ Expected Result**: Admin can access admin panel

---

### 15.2 User Management

- [ ] **Task 15.2.1**: View all users
  - [ ] Users list displays
  - [ ] User count shows
  - [ ] Search functionality works

- [ ] **Task 15.2.2**: View user details
  - [ ] Click on user
  - [ ] User profile displays
  - [ ] User data summary shows
  - [ ] Activity log displays (if implemented)

- [ ] **Task 15.2.3**: Edit user
  - [ ] Click "Edit User"
  - [ ] Modify user details
  - [ ] Click "Save"
  - [ ] Changes persist

- [ ] **Task 15.2.4**: Delete user
  - [ ] Click "Delete User"
  - [ ] Confirm deletion
  - [ ] User deleted
  - [ ] User data deleted (cascade)

**✅ Expected Result**: User management functional, admin controls work

---

### 15.3 System Settings

- [ ] **Task 15.3.1**: View system settings
  - [ ] Tax year configuration displays
  - [ ] Tax rates displays
  - [ ] Allowances displays
  - [ ] System parameters display

- [ ] **Task 15.3.2**: Update tax configuration (if editable)
  - [ ] Click "Edit Tax Config"
  - [ ] Modify allowances/rates
  - [ ] Click "Save"
  - [ ] New values apply system-wide

**✅ Expected Result**: System settings accessible, configurations manageable

---

### 15.4 Database Backup & Restore

- [ ] **Task 15.4.1**: Create database backup
  - [ ] Click "Create Backup"
  - [ ] Backup process runs
  - [ ] Backup file created in `storage/app/backups/`
  - [ ] Backup appears in backups list
  - [ ] Filename includes timestamp

- [ ] **Task 15.4.2**: Download backup
  - [ ] Click "Download" on backup
  - [ ] Backup file downloads
  - [ ] File is valid SQL dump

- [ ] **Task 15.4.3**: Restore from backup (CAUTION)
  - [ ] Click "Restore" on backup
  - [ ] Confirmation warning displays
  - [ ] Confirm restore
  - [ ] Database restores successfully
  - [ ] Application functions with restored data

**✅ Expected Result**: Backup/restore system works, data preserved

---

## 📱 SECTION 16: Responsiveness & UX

### 16.1 Desktop View (1920×1080)

- [ ] **Task 16.1.1**: Test on large desktop
  - [ ] All pages layout correctly
  - [ ] Navigation is intuitive
  - [ ] Charts/graphs display properly
  - [ ] No horizontal scrolling
  - [ ] Forms are appropriately sized

**✅ Expected Result**: Desktop view optimized, professional appearance

---

### 16.2 Laptop View (1366×768)

- [ ] **Task 16.2.1**: Test on standard laptop
  - [ ] All pages fit within viewport
  - [ ] Navigation accessible
  - [ ] Forms remain usable
  - [ ] Charts adjust to screen size

**✅ Expected Result**: Laptop view functional, no layout issues

---

### 16.3 Tablet View (768×1024)

- [ ] **Task 16.3.1**: Test on tablet (iPad size)
  - [ ] Responsive navigation (hamburger menu if implemented)
  - [ ] Cards stack appropriately
  - [ ] Forms remain usable
  - [ ] Touch targets appropriately sized
  - [ ] Charts responsive

**✅ Expected Result**: Tablet view usable, touch-friendly

---

### 16.4 Mobile View (375×667)

- [ ] **Task 16.4.1**: Test on mobile (iPhone size)
  - [ ] Mobile navigation works (hamburger menu)
  - [ ] All content accessible
  - [ ] Forms mobile-optimized
  - [ ] Touch targets large enough (44×44 px minimum)
  - [ ] No horizontal scrolling
  - [ ] Charts mobile-friendly

**✅ Expected Result**: Mobile view fully functional, mobile-first design evident

---

## 🐛 SECTION 17: Error Handling & Edge Cases

### 17.1 Form Validation

- [ ] **Task 17.1.1**: Test all form validation
  - [ ] Empty required fields show errors
  - [ ] Invalid email formats rejected
  - [ ] Date validations work (e.g., no future dates where inappropriate)
  - [ ] Numeric validations work (e.g., positive numbers only)
  - [ ] Min/max length validations work

**✅ Expected Result**: Comprehensive form validation, user-friendly error messages

---

### 17.2 API Error Handling

- [ ] **Task 17.2.1**: Test API failures (stop Laravel server)
  - [ ] Stop `php artisan serve`
  - [ ] Try to submit a form
  - [ ] User-friendly error message displays
  - [ ] No JavaScript console errors break the app
  - [ ] Restart server
  - [ ] Retry action succeeds

**✅ Expected Result**: Graceful degradation, user informed of issues

---

### 17.3 Empty States

- [ ] **Task 17.3.1**: Test with no data
  - [ ] Create new user with no data
  - [ ] Navigate to each module
  - [ ] Empty state messages display
  - [ ] "Add First..." CTAs display
  - [ ] No broken UI elements

**✅ Expected Result**: Empty states informative, encourage data entry

---

### 17.4 Large Data Sets

- [ ] **Task 17.4.1**: Test with many items
  - [ ] Add 20+ properties
  - [ ] Add 50+ savings accounts
  - [ ] Check pagination works (if implemented)
  - [ ] Check scrolling/loading behavior
  - [ ] Performance acceptable

**✅ Expected Result**: Application handles large data sets, pagination/virtualization works

---

## 🔒 SECTION 18: Security Testing

### 18.1 Authentication

- [ ] **Task 18.1.1**: Test unauthenticated access
  - [ ] Logout
  - [ ] Try to access `http://localhost:8000/dashboard`
  - [ ] Redirected to login page
  - [ ] Try to access `/profile`, `/protection`, etc.
  - [ ] All redirected to login

**✅ Expected Result**: Protected routes require authentication

---

### 18.2 Authorization

- [ ] **Task 18.2.1**: Test user data isolation
  - [ ] Login as User A
  - [ ] Note a property ID belonging to User A
  - [ ] Login as User B
  - [ ] Try to access User A's property via URL
  - [ ] Access denied (403/404)

**✅ Expected Result**: Users cannot access other users' data

---

### 18.3 CSRF Protection

- [ ] **Task 18.3.1**: Check CSRF tokens
  - [ ] Inspect form source code
  - [ ] CSRF token present in forms
  - [ ] Submitting without token fails

**✅ Expected Result**: CSRF protection active on all forms

---

### 18.4 XSS Protection

- [ ] **Task 18.4.1**: Test XSS in inputs
  - [ ] Enter `<script>alert('XSS')</script>` in text field
  - [ ] Submit form
  - [ ] View data
  - [ ] Script does not execute (text is escaped)

**✅ Expected Result**: XSS attempts neutralized, data escaped properly

---

## 🚀 SECTION 19: Performance Testing

### 19.1 Page Load Times

- [ ] **Task 19.1.1**: Measure page load times
  - [ ] Dashboard: < 2 seconds
  - [ ] Protection dashboard: < 2 seconds
  - [ ] Estate dashboard: < 2 seconds
  - [ ] Comprehensive plans: < 5 seconds

**✅ Expected Result**: Page load times acceptable

---

### 19.2 API Response Times

- [ ] **Task 19.2.1**: Check API response times (DevTools Network tab)
  - [ ] GET requests: < 500ms
  - [ ] POST requests: < 1 second
  - [ ] Complex calculations (IHT, projections): < 3 seconds

**✅ Expected Result**: API responses timely

---

### 19.3 Caching

- [ ] **Task 19.3.1**: Test cache behavior
  - [ ] Load dashboard (cache miss - slower)
  - [ ] Reload dashboard (cache hit - faster)
  - [ ] Modify data
  - [ ] Reload dashboard (cache invalidated, recalculates)

**✅ Expected Result**: Caching improves performance, invalidation works

---

## ✅ SECTION 20: Final Checklist

### 20.1 Regression Testing

- [ ] **Task 20.1.1**: Re-test critical paths
  - [ ] Complete registration → onboarding → dashboard flow
  - [ ] Add asset → View in net worth → Delete asset
  - [ ] Create protection policy → View coverage gap → Edit policy
  - [ ] Generate comprehensive plan → View plan → Return to dashboard

**✅ Expected Result**: Critical paths work end-to-end

---

### 20.2 Browser Compatibility

- [ ] **Task 20.2.1**: Test in Chrome
- [ ] **Task 20.2.2**: Test in Firefox
- [ ] **Task 20.2.3**: Test in Safari (if on Mac)
- [ ] **Task 20.2.4**: Test in Edge

**✅ Expected Result**: Application works in all modern browsers

---

### 20.3 Console Errors

- [ ] **Task 20.3.1**: Check for JavaScript errors
  - [ ] Open DevTools Console
  - [ ] Navigate through entire application
  - [ ] No red errors in console
  - [ ] Warnings are minimal and non-critical

**✅ Expected Result**: Clean console, no critical errors

---

### 20.4 Network Errors

- [ ] **Task 20.4.1**: Check for failed requests
  - [ ] Open DevTools Network tab
  - [ ] Navigate through application
  - [ ] No failed requests (red 4xx/5xx errors)
  - [ ] All API calls succeed

**✅ Expected Result**: All network requests successful

---

## 📝 Testing Notes & Issues Log

Use this section to record any issues found during testing:

### Issue Template

**Issue #**: [Number]
**Date**: [Date found]
**Tester**: [Your name]
**Section**: [Section number and name]
**Severity**: [Critical / High / Medium / Low]

**Description**:
[What is the issue?]

**Steps to Reproduce**:
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected Result**:
[What should happen]

**Actual Result**:
[What actually happened]

**Screenshots/Evidence**:
[Attach or describe]

**Status**: [Open / In Progress / Fixed / Closed]

---

### Issues Log

**Issue #1**: [Example]
**Date**: October 27, 2025
**Section**: 5.2 - Property Management
**Severity**: Medium

**Description**: Country selector not displaying in property edit form

**Steps to Reproduce**:
1. Navigate to Net Worth > Property
2. Click "Edit" on existing property
3. Country field does not display

**Expected Result**: Country selector should appear in edit form

**Actual Result**: Field is missing

**Status**: Open

---

## 🎉 Testing Completion

Once all sections are complete:

- [ ] **Task**: Review all checkboxes
- [ ] **Task**: Confirm all Expected Results achieved
- [ ] **Task**: Document all issues found
- [ ] **Task**: Prioritize issues for fixing
- [ ] **Task**: Re-test fixed issues
- [ ] **Task**: Sign off on testing completion

---

**Testing Completed By**: ___________________________

**Date**: ___________________________

**Signature**: ___________________________

**Overall Status**: ✅ PASS / ⚠️ PASS WITH ISSUES / ❌ FAIL

**Notes**:

---

**End of E2E Manual Testing Protocol**

**Version**: v0.1.2.5
**Total Test Tasks**: 500+
**Estimated Duration**: 4-6 hours
**Last Updated**: October 27, 2025
