# Domain Modules (Independent & Interoperable) - Simplified Demo

## Architecture Overview

The FPS demo system uses a simplified single-layer architecture where:

- **Module Agents:** Intelligent agents that take user inputs from dynamic forms, perform calculations directly within the application code, and generate recommendations
- **Coordinating Agent:** An optional coordinating agent that aggregates recommendations from all module agents to provide holistic financial guidance

All calculations are coded directly in the application - no external services or APIs required for the demo.

### Where Calculations Are Performed

All financial calculations, projections, and analysis logic are implemented as **functions/methods within the application codebase**:

- **Agent Modules** (e.g., `agents/protection_agent.py`, `agents/savings_agent.py`, `agents/investment_agent.py`, `agents/retirement_agent.py`, `agents/estate_agent.py`)
  - Each agent module contains the calculation logic specific to that domain
  - Examples: coverage gap calculations, ISA allowance tracking, Monte Carlo simulations, IHT liability calculations

- **Calculation Utilities** (e.g., `utils/financial_calcs.py`, `utils/tax_calcs.py`, `utils/projection_calcs.py`)
  - Shared calculation functions used across multiple agents
  - Examples: compound interest, tax bracket calculations, NPV, annuity valuations

- **Recommendation Engine** (e.g., `agents/recommendation_engine.py`)
  - Rule-based logic for generating recommendations based on calculated outputs
  - Priority scoring and action plan generation

The agents directly call these calculation functions, process the results, and generate recommendations - all within the single application codebase.

### Centralized Tax, Allowances & IHT Configuration

All UK tax rates, allowances, and IHT rules are maintained in a **centralized configuration file** that can be easily updated when legislation changes (typically annually):

- **Configuration File** (e.g., `config/uk_tax_config.py` or `config/uk_tax_rules.json`)
  - **Tax Year:** Current tax year (e.g., 2024/25)
  - **Income Tax:**
    - Personal Allowance (£12,570)
    - Basic rate band (20% up to £50,270)
    - Higher rate band (40% £50,271 to £125,140)
    - Additional rate band (45% above £125,140)
    - Personal Allowance taper (£1 reduction for every £2 over £100,000)
    - Scottish income tax rates (if applicable)
    - Welsh income tax rates (if applicable)
  - **National Insurance:**
    - Employee NI rates (Class 1)
    - Employer NI rates
    - Self-employed NI rates (Class 2 and Class 4)
    - NI thresholds
  - **Capital Gains Tax:**
    - CGT allowance (£3,000 for 2024/25)
    - CGT rates (10% basic rate, 20% higher rate)
    - CGT rates for residential property (18% basic rate, 28% higher rate)
  - **Dividend Tax:**
    - Dividend allowance (£500 for 2024/25)
    - Dividend tax rates (8.75% basic, 33.75% higher, 39.35% additional)
  - **Savings Interest:**
    - Personal Savings Allowance (£1,000 basic rate, £500 higher rate, £0 additional rate)
  - **ISA Allowances:**
    - Total ISA allowance (£20,000)
    - Lifetime ISA allowance (£4,000 - counts towards total ISA allowance)
    - Junior ISA allowance (£9,000)
  - **Pension Allowances:**
    - Annual Allowance (£60,000)
    - Tapered Annual Allowance (reduction for high earners)
    - Money Purchase Annual Allowance (£10,000 - if accessed pension flexibly)
    - Lifetime Allowance (removed from April 2024, but historical values may be needed)
  - **Inheritance Tax:**
    - IHT rate (40%)
    - Reduced IHT rate for charitable giving (36% if leaving 10%+ to charity)
    - Nil-Rate Band (NRB) (£325,000)
    - Residence Nil-Rate Band (RNRB) (£175,000)
    - RNRB taper threshold (£2 million - reduces by £1 for every £2 over)
    - Transferable NRB/RNRB rules (unused portion from deceased spouse)
    - PETs taper relief (7-year rule with sliding scale)
    - CLTs lookback period (14 years)
  - **Gifting Exemptions:**
    - Annual gift exemption (£3,000)
    - Small gift exemption (£250 per recipient)
    - Wedding/civil partnership gifts (£5,000 for child, £2,500 for grandchild, £1,000 for others)
  - **Other Thresholds:**
    - High Income Child Benefit Charge threshold (£60,000)
    - Student loan repayment thresholds (Plan 1, Plan 2, Plan 4, Plan 5, Postgraduate)
    - Benefits taper rates
    - State Pension age (by date of birth)
    - State Pension amount (full new State Pension: £221.20/week for 2024/25)

**Benefits of Centralized Configuration:**
- **Single source of truth:** All calculation functions reference the same configuration
- **Easy annual updates:** When tax rules change (Budget, Finance Act), update one file
- **Version control:** Track historical tax rules by maintaining config files for previous tax years
- **Testing:** Easy to test calculations with different tax scenarios by swapping config
- **Transparency:** Clear visibility of all rates and thresholds used in calculations
- **Admin interface:** Can build a simple admin page to view/edit these values without code changes

All calculation functions in the agent modules and utility files import and reference this centralized configuration, ensuring consistency across the entire application.

---

## 1. Protection Module

### Overview
Comprehensive risk protection analysis covering all UK protection insurance products and services to ensure financial security for individuals and their families.

### Scope - UK Products & Services

#### Life Insurance
- **Term Life Insurance**
  - Level term
  - Decreasing term
  - Increasing term
  - Family income benefit
  - Renewable term
  - Convertible term
- **Whole of Life Insurance**
  - Standard whole of life
  - Maximum cover
  - Investment-based whole of life
- **Over 50s Life Insurance**

#### Critical Illness Cover
- Standalone critical illness
- Life insurance with critical illness
- Accelerated critical illness
- Additional payment critical illness
- Children's critical illness cover
- Specified illness cover

#### Income Protection
- Long-term income protection
- Short-term income protection
- Unemployment cover
- Accident, sickness and unemployment (ASU)
- Permanent health insurance (PHI)
- Executive income protection

#### Health Insurance
- Private medical insurance (PMI)
- Health cash plans
- Dental insurance
- Travel insurance

#### Other Protection Products
- Mortgage protection insurance
- Payment protection insurance (PPI)
- Buildings and contents insurance
- Keyman insurance (business)
- Shareholder protection (business)
- Relevant life insurance
- Death in service benefits (group schemes)

### Input Categories (Dynamic Form-Driven)

#### Personal Demographics
- Full name, date of birth, age
- Gender
- Marital status
- Number of dependents (ages, relationships)
- Nationality and residency status
- Occupation and industry
- Employment status (employed, self-employed, unemployed, retired)
- Smoker/non-smoker status
- Health status and pre-existing conditions

#### Current Protection Coverage
- Existing life insurance policies
  - Policy type, provider, policy number
  - Sum assured
  - Premium amount and frequency
  - Policy start date and term
  - Beneficiaries
  - Waiver of premium included?
  - Indexation/escalation terms
- Critical illness policies
  - Coverage amount
  - Conditions covered
  - Premium details
- Income protection policies
  - Benefit amount
  - Deferred period
  - Benefit period
  - Occupation class
- Health insurance policies
  - Coverage level
  - Excess amount
  - Premiums
- Group scheme benefits (death in service, income protection)
- State benefits eligibility

#### Financial Information
- Annual gross income (individual and household)
- Other income sources
- Outstanding debts and liabilities
  - Mortgage balance and term
  - Personal loans
  - Credit cards
  - Overdrafts
  - Car finance
- Monthly committed expenditure
- Emergency fund balance
- Savings and investments (liquid assets)
- Property ownership
- Business interests

#### Protection Goals & Needs
- Financial protection objectives
  - Family income replacement duration
  - Mortgage protection priority
  - Debt clearance requirements
  - Education funding for children
  - Final expenses coverage
  - Estate liquidity for inheritance tax
- Risk tolerance
- Budget constraints for premiums
- Preferred policy structures (joint vs single, level vs increasing)
- Specific illness concerns
- Preferred benefit period for income protection
- Preferred deferred period

#### Health & Lifestyle
- Height and weight (BMI)
- Smoking status and history
- Alcohol consumption
- Medical history
  - Pre-existing conditions
  - Family medical history
  - Medications
  - Previous claims
- Hazardous occupations or hobbies
- Recent travel or planned travel to high-risk areas

#### Future Plans
- Career changes planned
- Family planning (children)
- Expected income changes
- Planned property purchases
- Business ventures
- Retirement age

### Agent

The Protection Agent takes user inputs from the dynamic forms and performs calculations directly within the application code:
- Coverage gap analysis (human capital valuation)
- Policy suitability assessment
- Premium optimization
- Underwriting eligibility assessment
- Waiting period and exclusion validation
- Benefit structure optimization (term, sum assured, indexation)
- Multi-policy coordination
- Trust and legal structure recommendations

The agent then generates actionable recommendations with prioritized action plans.

### Outputs

#### Current Situation Analysis
- **Coverage Summary Dashboard**
  - Total life cover in force
  - Total critical illness cover
  - Income protection benefit amount
  - Annual premiums committed
  - Coverage adequacy score (0-100)
- **Coverage Gap Analysis**
  - Human capital calculation (income replacement need)
  - Debt protection shortfall
  - Mortgage shortfall
  - Education funding shortfall
  - Final expenses provision
  - Visual heatmap of gaps by category
- **Policy Quality Assessment**
  - Policy competitiveness (premium vs market)
  - Coverage comprehensiveness
  - Policy structure efficiency
  - Trust arrangements status
  - Indexation status
- **Risk Exposure Analysis**
  - Income loss risk
  - Critical illness financial impact
  - Death financial impact on household
  - Dependency analysis (vulnerable family members)

#### What-If Scenarios
- **Scenario Modeling**
  - Death of primary earner (immediate impact)
  - Death of secondary earner
  - Critical illness diagnosis (income loss + medical costs)
  - Long-term disability
  - Loss of employment (redundancy)
  - Multiple events (e.g., death + outstanding mortgage)
- **Affordability Scenarios**
  - Premium impact of increasing cover
  - Cost of closing all gaps
  - Phased approach to full coverage
  - Impact of removing existing policies
- **Life Stage Scenarios**
  - Protection needs at children's milestones
  - Protection needs at retirement
  - Impact of career change
  - Impact of mortgage redemption
- **Product Comparison Scenarios**
  - Level term vs decreasing term
  - Accelerated vs additional critical illness
  - Short deferred vs long deferred income protection
  - Joint life vs separate policies

#### Recommendations
- **Prioritized Action Plan**
  1. Immediate critical gaps (high urgency)
  2. Medium-term optimization
  3. Long-term enhancements
- **Product Recommendations**
  - Specific policy types recommended
  - Suggested sum assured by policy
  - Recommended term
  - Recommended premium budget
  - Indexation recommendations
  - Trust structure recommendations
  - Product provider shortlist
- **Optimization Recommendations**
  - Policies to retain
  - Policies to replace/upgrade
  - Policies to cancel
  - Premium savings opportunities
- **Action Tasks with Timelines**
  - Research and compare quotes (providers list)
  - Complete medical underwriting
  - Set up trusts
  - Review beneficiaries
  - Consolidate policies
  - Schedule annual review
- **Compliance & Suitability Notes**
  - Regulatory disclosure requirements
  - Underwriting likelihood assessment
  - Exclusions to be aware of
  - Claims process guidance

### Dashboard Design

#### Main Card (Summary View - Clickable)
**Protection Overview Card**
- Coverage adequacy score (large, prominent - traffic light colors)
- Total coverage vs need (visual bar)
- Monthly premium total
- Critical gaps indicator (count)
- Last review date
- **Click to expand** → Full Protection Dashboard

#### Detailed Protection Dashboard (After Card Click)

**Navigation Tabs:**
1. Current Situation
2. Gap Analysis
3. Recommendations
4. What-If Scenarios
5. Policy Details

**Section: Current Situation**
- Coverage summary cards (Life, Critical Illness, Income Protection, Health)
- Premium spend breakdown (pie chart)
- Coverage timeline (Gantt chart showing policy terms)
- Risk exposure heatmap

**Section: Gap Analysis**
- Interactive gap visualizations by protection type
- Human capital calculator
- Dependency impact analysis
- Affordability assessment

**Section: Recommendations**
- Prioritized action cards (drag to reorder by preference)
- Product comparison table (sortable, filterable)
- Premium impact calculator (slider adjustments)
- Provider ratings and links

**Section: What-If Scenarios**
- Scenario builder (select events, view impacts)
- Financial impact visualizations
- Comparison tools

**Section: Policy Details**
- Expandable policy cards (one per policy)
- Document repository (upload and store policy documents)
- Claims contact information
- Renewal dates and alerts

---

## 2. Savings Module

### Overview
Comprehensive savings and cash management covering all UK savings products and cash management services to optimize liquidity, emergency funds, and short-term savings goals.

### Scope - UK Products & Services

#### Cash Savings Accounts
- **Easy Access Savings**
  - Instant access savings accounts
  - Notice accounts (30, 60, 90-day)
  - Regular savings accounts
  - Online savings accounts
- **Fixed Rate Bonds**
  - 1-year fixed bonds
  - 2-year fixed bonds
  - 3-year fixed bonds
  - 5-year fixed bonds
- **Cash ISAs**
  - Easy access cash ISAs
  - Fixed rate cash ISAs
  - Notice period cash ISAs
  - Help to Buy ISAs (legacy)
- **Lifetime ISAs (LISA)**
  - Cash Lifetime ISA

#### Current Accounts
- Packaged current accounts
- High-interest current accounts
- Basic bank accounts
- Student accounts
- Joint accounts

#### National Savings & Investments (NS&I)
- Premium Bonds
- Direct Saver
- Income Bonds
- Investment Guaranteed Growth Bonds
- Junior ISAs
- Green Savings Bonds

#### Other Cash Products
- Credit union savings accounts
- Building society accounts
- Offshore savings accounts (for non-UK tax residents)

### Input Categories (Dynamic Form-Driven)

#### Personal Information
- Full name, date of birth
- UK taxpayer status
- Income tax band (basic, higher, additional)
- National Insurance number
- Residency status
- Number of dependents

#### Current Savings & Cash Holdings
- **Bank Accounts**
  - Account type (current, savings)
  - Institution name
  - Account number
  - Current balance
  - Interest rate (if applicable)
  - Monthly contributions/withdrawals
  - Access restrictions (notice period)
  - Joint or sole account
- **Cash ISAs**
  - Provider
  - Current balance
  - Subscription year
  - Interest rate
  - ISA type (easy access, fixed)
  - Maturity date (if fixed)
  - Annual subscription amount (current tax year)
- **Fixed Rate Bonds**
  - Provider
  - Balance
  - Interest rate
  - Start date
  - Maturity date
  - Early access penalties
- **NS&I Products**
  - Product type
  - Holdings/balance
- **Other Cash Holdings**
  - Premium bonds holding amount
  - Cash held at home or elsewhere
  - Foreign currency holdings

#### Income & Expenditure
- Annual gross income (all sources)
- Monthly net income
- Monthly committed expenditure
  - Housing costs
  - Utilities
  - Insurance premiums
  - Loan repayments
  - Subscriptions
  - Childcare
  - Transport
  - Food and essentials
- Discretionary spending
- Irregular expenses (annual, quarterly)

#### Financial Goals & Needs
- Emergency fund target (months of expenditure)
- Short-term savings goals
  - Goal description (holiday, car, home improvements)
  - Target amount
  - Target date
  - Priority (high, medium, low)
- ISA strategy preferences
- Risk tolerance for cash savings (none - 100% capital preservation)
- Access requirements (immediate, notice, fixed)
- Desired interest rate (competitive benchmark)

#### Future Plans
- Expected income changes
- Expected major expenses
- House purchase plans (deposit saving)
- Education funding needs
- Planned large purchases

#### ISA Allowance Tracking
- Current tax year ISA subscriptions
  - Cash ISA subscriptions (amount used)
  - Stocks & Shares ISA subscriptions (amount used) - from Investment Module
  - LISA subscriptions (if applicable - counts towards total allowance)
  - Total ISA subscriptions (sum across all ISA types)
- Total ISA allowance (£20,000 for 2024/25 tax year)
- Available ISA allowance remaining
- Previous years' ISA holdings (by type)
- LISA eligibility and usage
- Junior ISA (if applicable)

### Agent

The Savings Agent takes user inputs and performs calculations directly in the application code:
- Emergency fund adequacy calculation (3-6 months expenses)
- Rate shopping and comparison across providers
- Liquidity ladder optimization (immediate, short-term, medium-term)
- ISA allowance tracking and optimization
- Tax efficiency analysis (interest vs personal savings allowance)
- Access requirement matching
- Inflation impact analysis (real return calculation)
- Short-term goal funding projections

The agent then generates recommendations for optimal savings strategy and account selection.

### Outputs

#### Current Situation Analysis
- **Savings Dashboard Summary**
  - Total cash holdings
  - Emergency fund runway (months)
  - Weighted average interest rate
  - Annual interest income
  - ISA allowance utilization (current tax year)
    - Cash ISA subscriptions used
    - Total ISA allowance remaining (shared with Stocks & Shares ISA)
  - Liquidity profile
- **Account Performance Analysis**
  - Account-by-account comparison vs market rates
  - Underperforming accounts highlighted
  - Fee analysis
  - Access restrictions summary
- **Liquidity Ladder Visualization**
  - Immediate access funds
  - Short-notice funds (30-90 days)
  - Fixed-term funds (by maturity)
  - Visual timeline
- **Goal Progress Tracking**
  - Progress bars for each savings goal
  - On-track / behind status
  - Monthly savings rate required to meet goals
- **Tax Efficiency Analysis**
  - Personal savings allowance usage
  - Tax on savings income
  - ISA tax benefits quantified

#### What-If Scenarios
- **Emergency Scenarios**
  - Loss of income (how long covered?)
  - Major unexpected expense (car repair, boiler replacement)
  - Simultaneous emergency scenarios
- **Rate Change Impact**
  - Impact of interest rate rises on savings income
  - Impact of inflation on real value
  - Effect of switching to higher-rate accounts
- **Goal Funding Scenarios**
  - Accelerated savings (increasing contributions)
  - Delayed goals (extending timelines)
  - Multiple goals trade-offs (prioritization impacts)
- **ISA Strategy Scenarios**
  - Maximizing ISA allowance vs keeping emergency fund accessible
  - Cash ISA vs Stocks & Shares ISA allocation
  - LISA vs standard ISA for house purchase
- **Fixed vs Variable Scenarios**
  - Locking funds in fixed bonds vs retaining flexibility
  - Rate guarantee vs potential for higher variable rates

#### Recommendations
- **Prioritized Actions**
  1. Immediate: Close gaps in emergency fund
  2. Short-term: Optimize account rates
  3. Medium-term: ISA allowance strategy
  4. Long-term: Goal-based savings plans
- **Account Recommendations**
  - Suggested account types and providers
  - Specific products with rates and terms
  - Target balance per account
  - Transfer/opening instructions
- **Emergency Fund Recommendations**
  - Target emergency fund amount
  - Current shortfall
  - Monthly savings required
  - Optimal account placement (immediate access)
- **ISA Optimization**
  - ISA allowance allocation strategy (total allowance: £20,000)
    - Cash ISA allocation recommendation
    - Stocks & Shares ISA allocation recommendation (coordinate with Investment Module)
  - LISA recommendations (if eligible - counts towards total ISA allowance)
  - Transfer recommendations (ISA consolidation)
- **Rate Optimization**
  - Accounts to close/transfer
  - Better rate alternatives
  - Annual interest gain from switches
- **Short-Term Goal Funding Plans**
  - Dedicated savings accounts per goal
  - Monthly contribution requirements
  - Account type matching to timeline
  - Automatic transfer setup recommendations
- **Liquidity Management**
  - Optimal liquidity ladder structure
  - Balance between access and rates
  - Fixed bond laddering strategy

### Dashboard Design

#### Main Card (Summary View - Clickable)
**Savings Overview Card**
- Emergency fund runway (large, prominent - color-coded)
- Total cash savings
- Average interest rate vs market benchmark
- ISA allowance usage (progress bar)
- Savings goals status (X of Y on track)
- **Click to expand** → Full Savings Dashboard

#### Detailed Savings Dashboard (After Card Click)

**Navigation Tabs:**
1. Current Situation
2. Emergency Fund
3. Savings Goals
4. Recommendations
5. What-If Scenarios
6. Account Details

**Section: Current Situation**
- Total savings summary
- Liquidity ladder visualization
- Account list with performance indicators
- Interest income projection

**Section: Emergency Fund**
- Runway calculator (interactive slider)
- Monthly expenditure breakdown
- Emergency fund target vs actual (visual gauge)
- Top-up plan

**Section: Savings Goals**
- Goal cards (one per goal) with progress bars
- Add/edit/delete goals
- Funding projections
- Priority ranking (drag to reorder)

**Section: Recommendations**
- Actionable recommendation cards
- Rate comparison tables (sortable by rate, access, minimum)
- ISA strategy visual planner
- Transfer calculators

**Section: What-If Scenarios**
- Scenario builder (slider inputs for income loss, expenses, contribution changes)
- Impact visualizations
- Comparison tools

**Section: Account Details**
- Account cards (expandable) with full details
- Link to provider websites
- Interest payment history
- Maturity calendar

---

## 3. Investment Module

### Overview
Comprehensive investment planning and portfolio management covering all UK investment products and wrappers to optimize long-term growth, tax efficiency, and goal achievement.

### Scope - UK Products & Services

#### Investment Wrappers
- **ISAs (Individual Savings Accounts)**
  - Stocks & Shares ISA
  - Innovative Finance ISA
  - Lifetime ISA (LISA) - investment
  - Junior ISA (JISA)
  - Help to Buy ISA (legacy)
- **General Investment Accounts (GIA)**
  - Taxable investment accounts
  - Trading accounts
- **Investment Bonds**
  - **Onshore Bonds**
    - Investment bonds (UK-based)
    - Portfolio bonds
    - Single premium bonds
  - **Offshore Bonds**
    - Investment bonds (offshore-based)
    - Portfolio bonds (offshore)
    - International investment bonds
- **Venture Capital Trusts (VCT)**
- **Enterprise Investment Schemes (EIS)**
- **Seed Enterprise Investment Scheme (SEIS)**
- **Junior Investment Accounts**

#### Investment Products
- **Funds**
  - Unit trusts
  - OEICs (Open-Ended Investment Companies)
  - Investment trusts
  - ETFs (Exchange-Traded Funds)
  - Index tracker funds
  - Active managed funds
- **Individual Securities**
  - UK equities (LSE)
  - International equities
  - Corporate bonds
  - Government bonds (gilts)
  - Preference shares
- **Alternative Investments**
  - REITs (Real Estate Investment Trusts)
  - Commodities (gold, etc.)
  - Structured products
  - Hedge funds
- **Managed Portfolios**
  - Discretionary fund management
  - Model portfolios
  - Robo-advisor portfolios

#### Investment Platforms
- Platform providers (e.g., Hargreaves Lansdown, AJ Bell, Vanguard, Interactive Investor, Fidelity)
- Comparison of fees, product range, tools

### Input Categories (Dynamic Form-Driven)

#### Personal Information
- Full name, date of birth, age
- UK taxpayer status and tax band (basic, higher, additional)
- National Insurance number
- Residency and domicile status (UK, non-dom, expat)
- Employment status
- Annual gross income (all sources)
- Spouse/partner information (if joint planning)

#### Current Investment Holdings
- **Investment Accounts**
  - Account type (ISA, GIA, onshore bond, offshore bond, VCT, EIS)
  - Provider/platform
  - Account number
  - Current value
  - Contributions (ongoing, lump sum)
  - Fees (platform, fund, advice)
- **Individual Holdings**
  - Asset type (equity, bond, fund, ETF, alternative)
  - Security name and identifier (ISIN, ticker)
  - Quantity
  - Purchase price and date
  - Current value
  - Dividend/income yield
  - Allocation % of total portfolio
- **Portfolio Allocation**
  - Current asset allocation (equities, bonds, cash, alternatives)
  - Geographic allocation (UK, US, Europe, Asia, Emerging Markets)
  - Sector allocation
  - Currency exposure

#### Investment Goals
- **Long-Term Goals**
  - Goal description (retirement, education, wealth accumulation, home purchase)
  - Target amount
  - Target date/timeline
  - Priority (high, medium, low)
  - Essential vs aspirational
- **Return Expectations**
  - Target annual return (%)
  - Required return to meet goals
- **Income Requirements**
  - Income needed from investments (now or future)
  - Income vs growth priority

#### Risk Profile
- Risk tolerance (cautious, balanced, adventurous)
- Capacity for loss (% of portfolio)
  - Financial capacity (ability to absorb losses)
  - Emotional capacity (willingness to accept volatility)
- Investment time horizon (short <5y, medium 5-10y, long >10y)
- Previous investment experience
- Attitude to volatility and drawdowns
- Ethical/ESG investment preferences

#### Financial Situation
- Income and expenditure (disposable income)
- Emergency fund status
- Other assets (property, cash, pensions)
- Liabilities (mortgage, loans)
- Existing protection insurance
- Expected inheritance or windfalls
- Regular investment capacity (monthly contributions)

#### Tax Situation
- ISA allowance usage (current tax year)
  - Cash ISA subscriptions
  - Stocks & Shares ISA subscriptions
  - Total ISA allowance remaining
- Dividend allowance usage
- Capital gains tax (CGT) allowance usage
- Previous CGT realizations

#### Existing Contributions
- Regular monthly investments
- Annual lump sum contributions
- Planned future contributions

#### Investment Preferences
- Active vs passive preference
- Platform preference
- Fund selection criteria (cost, performance, sustainability)
- Rebalancing frequency preferences
- Ethical/ESG requirements
- Geographic or sector restrictions

#### Future Plans
- Expected income changes
- Expected life events (marriage, children, property purchase, retirement)
- Planned career changes or business ventures
- Anticipated windfalls

### Agent

The Investment Agent takes user inputs and performs calculations directly in the application code:
- Goal funding status calculation (Monte Carlo projections)
- Wrapper optimization (ISA and GIA sequencing)
- Asset allocation optimization (modern portfolio theory)
- Fee drag analysis (platform, fund, transaction fees)
- Rebalancing calculations (target vs actual allocation)
- Tax-wrapper sequencing (contributions and withdrawals)
- Tax-loss harvesting opportunities
- CGT liability management
- Dividend tax optimization
- Portfolio risk metrics (volatility, Sharpe ratio, drawdown risk)
- Performance attribution analysis
- Cost-benefit of active vs passive
- Bond taxation analysis (onshore vs offshore treatment)

The agent then generates portfolio optimization recommendations including asset allocation, wrapper strategy, product selection, and rebalancing actions.

### Outputs

#### Current Situation Analysis
- **Portfolio Dashboard Summary**
  - Total portfolio value
  - Year-to-date return (£ and %)
  - Annualized return (1y, 3y, 5y, since inception)
  - Total contributions vs current value
  - Total fees paid (annual and cumulative)
  - ISA allowance utilization (current tax year)
    - Stocks & Shares ISA subscriptions used
    - Total ISA allowance remaining (shared with Cash ISA)
  - Portfolio risk rating
- **Asset Allocation Analysis**
  - Current allocation vs target (pie charts, bar charts)
  - Diversification score
  - Geographic exposure map
  - Sector exposure
  - Currency exposure
  - Deviation from target (heatmap)
- **Holdings Analysis**
  - Top 10 holdings by value
  - Largest gainers and losers
  - Dividend income summary
  - Bond duration and credit quality
  - Liquidity profile
- **Fee Analysis**
  - Platform fees
  - Fund management fees (OCF)
  - Transaction costs
  - Advice fees
  - Total fee drag on performance
  - Comparison vs low-cost alternatives
- **Tax Efficiency**
  - Wrapper utilization (ISA, GIA, bonds)
  - Taxable income from investments
  - Unrealized capital gains
  - CGT allowance usage
  - Dividend allowance usage
  - Tax saved via ISAs
  - Bond taxation (onshore vs offshore comparison)
- **Goal Progress**
  - Goal-by-goal funding status
  - Projected vs required growth rate
  - On track / off track status
  - Probability of success (Monte Carlo simulation)

#### What-If Scenarios
- **Market Scenarios**
  - Market crash (10%, 20%, 30% drawdown)
  - Prolonged stagnation
  - High inflation scenario
  - Rising interest rate impact on bonds
- **Contribution Scenarios**
  - Increased monthly contributions
  - One-off lump sum investment
  - Contribution pause (e.g., due to income loss)
- **Allocation Scenarios**
  - More aggressive allocation (higher equity %)
  - More conservative allocation (higher bond %)
  - Sector tilt (e.g., more tech, less energy)
  - Geographic tilt (more US, more emerging markets)
- **Fee Scenarios**
  - Impact of switching to lower-cost funds
  - Platform switching savings
  - Active vs passive cost comparison
- **Tax Scenarios**
  - Maximizing ISA contributions
  - CGT harvesting strategies
  - Moving to higher/lower tax band
  - Bond wrapper tax efficiency (onshore vs offshore)
- **Goal Scenarios**
  - Accelerating goal timeline
  - Delaying goal timeline
  - Adjusting target amounts
  - Adding/removing goals

#### Recommendations
- **Prioritized Action Plan**
  1. Immediate: Critical rebalancing and tax actions
  2. Short-term: Fee optimization and contribution changes
  3. Medium-term: Wrapper strategy and goal alignment
  4. Long-term: Strategic allocation shifts
- **Asset Allocation Recommendations**
  - Target allocation by asset class
  - Rebalancing trades required (buy/sell)
  - Geographic and sector recommendations
  - Rationale for changes
- **Product Recommendations**
  - Funds/ETFs to buy
  - Funds to sell (poor performance, high fees, redundancy)
  - Specific product names and ISINs
  - Cost comparison (current vs recommended)
- **Wrapper Optimization**
  - ISA contribution strategy (prioritize ISA subscriptions)
  - GIA vs ISA allocation recommendations
  - Bond wrapper selection (onshore vs offshore based on tax position)
  - Wrapper hierarchy for new contributions
  - Transfer recommendations (consolidating accounts)
- **Fee Optimization**
  - Platform switching recommendations
  - Fund substitutions (active to passive, high-cost to low-cost)
  - Annual savings quantified
- **Tax Optimization**
  - ISA allowance maximization plan (Cash ISA and Stocks & Shares ISA allocation)
  - CGT harvesting opportunities (realize losses to offset gains)
  - Bed and ISA strategies
  - Dividend tax management
  - Bond taxation efficiency (onshore vs offshore based on tax band)
- **Rebalancing Plan**
  - Specific trades to execute
  - Timing recommendations (tax year-end, dividend dates)
  - Transaction cost estimates
- **Income Strategy**
  - Dividend income optimization
  - Income drawdown strategy (if required)
  - Natural income vs total return approach
- **ESG/Ethical Alignment**
  - ESG fund recommendations (if applicable)
  - Impact investing options

### Dashboard Design

#### Main Card (Summary View - Clickable)
**Investment Overview Card**
- Total portfolio value (large, prominent)
- YTD return (% and £, color-coded)
- Goal achievement probability (% on track)
- Asset allocation donut chart (mini)
- Rebalancing alert indicator
- **Click to expand** → Full Investment Dashboard

#### Detailed Investment Dashboard (After Card Click)

**Navigation Tabs:**
1. Portfolio Overview
2. Holdings
3. Performance
4. Goals
5. Recommendations
6. What-If Scenarios
7. Tax & Fees

**Section: Portfolio Overview**
- Total value and return metrics (cards)
- Asset allocation chart (interactive, click to drill down)
- Geographic allocation map
- Risk metrics dashboard
- Recent activity feed

**Section: Holdings**
- Sortable, filterable holdings table
  - Columns: Security, Type, Value, % of Portfolio, Return, Cost, Fees
- Expandable rows for detailed holding info
- Add/edit holdings
- Sector and geographic breakdown (interactive charts)

**Section: Performance**
- Performance line chart (time series with benchmarks)
- Attribution analysis (what drove returns)
- Comparison to benchmarks (FTSE All-Share, S&P 500, etc.)
- Income received over time

**Section: Goals**
- Goal cards (one per goal) with progress visualization
- Probability of success (Monte Carlo simulations)
- Required return vs projected return
- Funding shortfall/surplus
- Scenario adjustments (sliders for contributions, timeline, target)

**Section: Recommendations**
- Actionable recommendation cards (prioritized)
- Rebalancing trade list
- Product comparison tables
- Fee savings calculator
- ISA strategy planner

**Section: What-If Scenarios**
- Scenario builder (dropdown menu for pre-defined scenarios + custom)
- Interactive sliders (allocation, contributions, market returns)
- Visual impact charts (before/after, projected outcomes)
- Side-by-side scenario comparison

**Section: Tax & Fees**
- Fee breakdown (platform, funds, transaction, advice)
- Annual cost vs portfolio value
- Tax allowance trackers (ISA, CGT, dividend)
- Tax optimization opportunities
- Historical fee and tax paid

---

## 4. Retirement Module

### Overview
Comprehensive retirement planning covering all UK pension products, contribution optimization, retirement income strategies, and decumulation planning to ensure financial security throughout retirement.

### Scope - UK Products & Services

#### Pension Schemes
- **Defined Contribution Schemes**
  - Workplace pensions (auto-enrolment)
  - Group personal pensions
  - Master trusts (e.g., NEST, The People's Pension)
  - Stakeholder pensions
  - Self-Invested Personal Pensions (SIPPs)
  - Personal pensions
  - Executive pension plans
- **Defined Benefit Schemes**
  - Final salary pensions
  - Career average pensions
  - Public sector pensions (NHS, Teachers, Civil Service, Local Government)
  - Unfunded public sector schemes
- **State Pension**
  - New State Pension (post-2016)
  - Basic State Pension (pre-2016)
  - State Pension top-up (voluntary contributions)
  - State Pension deferral

#### International & Specialist Pensions
- QROPS (Qualifying Recognised Overseas Pension Schemes)
- QNUPS (Qualifying Non-UK Pension Schemes)
- Small Self-Administered Schemes (SSAS)
- Section 32 buy-out plans

#### Retirement Income Products
- **Annuities**
  - Lifetime annuities
  - Fixed-term annuities
  - Escalating annuities (inflation-linked)
  - Enhanced annuities (health/lifestyle factors)
  - Joint-life annuities
  - Guaranteed period annuities
- **Drawdown**
  - Flexi-access drawdown
  - Capped drawdown (legacy)
  - UFPLS (Uncrystallised Funds Pension Lump Sum)
- **Hybrid Strategies**
  - Combination of annuity and drawdown
  - Phased retirement

#### Additional Retirement Savings
- **Lifetime ISAs (LISA)** - retirement element
- **Other investments** used for retirement funding
- **Property** (downsizing, equity release)

#### State Benefits
- Pension Credit
- Attendance Allowance
- Winter Fuel Payment
- TV Licence concessions
- Other means-tested benefits

### Input Categories (Dynamic Form-Driven)

#### Personal Information
- Full name, date of birth, current age
- Gender
- Marital status
- Partner details (if joint planning)
- Health status and life expectancy estimate
- National Insurance number
- Employment status and occupation
- State Pension age (calculated based on DOB)
- Expected retirement age (target)
- Residency and domicile status

#### Current Pension Arrangements
- **Defined Contribution Pensions**
  - Scheme name and provider
  - Scheme type (workplace, SIPP, personal)
  - Member number/policy number
  - Current fund value
  - Contribution rate (employee, employer, total)
  - Contribution frequency (monthly, annual)
  - Investment allocation
  - Fees and charges (AMC, platform fee)
  - Retirement age under scheme
  - Death benefits
  - Projected fund value at retirement (scheme projection)
  - Transfer value (if considering transfers)
- **Defined Benefit Pensions**
  - Scheme name
  - Scheme type (final salary, career average)
  - Accrued benefits (annual pension amount)
  - Normal retirement age
  - Pensionable service (years)
  - Pensionable salary
  - Revaluation method
  - Spouse's pension (%)
  - Lump sum entitlement (PCLS)
  - Early/late retirement factors
  - Inflation protection (CPI, RPI, fixed %)
  - Note: DB pension information used for income projection only
- **State Pension**
  - National Insurance years completed
  - State Pension forecast (annual amount)
  - Gaps in NI record
  - Voluntary contribution eligibility
  - Spouse's State Pension position
  - State Pension age

#### Employment & Contributions
- Current employment status (employed, self-employed, unemployed, retired)
- Gross annual salary
- Employer pension contribution (% or £)
- Employee pension contribution (% or £)
- Additional voluntary contributions (AVCs)
- Self-employed pension contributions
- Net relevant earnings (for contribution limit calculations)
- Plans for future contribution increases

#### Financial Situation
- Annual gross income (all sources)
- Other assets (property, investments, savings)
- Liabilities (mortgage, loans)
- Expected inheritance
- Other retirement savings (ISAs, GIAs)
- Property equity available for retirement (downsizing, equity release)

#### Retirement Goals & Needs
- Target retirement age
- Desired retirement income (annual, in today's money)
  - Essential expenses (non-negotiable)
  - Lifestyle expenses (desirable)
  - Discretionary expenses (aspirational)
- Expected expenditure in retirement (monthly/annual)
- Planned one-off expenses in retirement (travel, gifts, home improvements)
- Life expectancy planning horizon
- Legacy goals (amount to leave to beneficiaries)
- Attitude to risk in retirement (income stability vs growth)

#### Retirement Income Strategy Preferences
- Preference for guaranteed income (annuity) vs flexible income (drawdown)
- Inflation protection priority
- Spouse/partner income protection priority
- Lump sum requirements at retirement
- Phased retirement preferences (part-time work, gradual reduction)
- Willingness to adjust spending in response to market performance

#### Health & Lifestyle
- Current health status
- Lifestyle factors (smoking, medical conditions)
- Life expectancy estimate (family history, health)
- Enhanced annuity eligibility factors

#### Tax Situation
- Current income tax band (basic, higher, additional)
- Expected tax band in retirement
- Annual pension allowance usage
- Carry forward availability (last 3 years)
- Tapered annual allowance status (high earners)
- Lifetime allowance considerations (legacy)
- Other taxable income in retirement

#### Future Plans
- Expected career changes
- Expected salary progression
- Planned redundancy or early retirement
- Plans for phased retirement or part-time work
- Plans for relocating (UK or overseas)
- Significant life events (caring responsibilities, education costs)

### Agent

The Retirement Agent takes user inputs and performs calculations directly in the application code:
- State Pension forecast validation and NI gap analysis
- Pension contribution optimization (employer, employee, tax relief)
- Annual allowance checks (including carry forward and tapering)
- Retirement income projection (DC and DB pensions, State Pension, other income)
- Retirement income gap analysis (vs target income)
- Decumulation planning (drawdown strategy, sustainable withdrawal rate)
- Annuity vs drawdown analysis (comparison of outcomes)
- Tax efficiency in retirement (PCLS timing, income phasing, tax band management)
- Pension consolidation analysis (pros/cons of combining DC pots only)
- Longevity risk assessment
- Sequence-of-returns risk modeling
- Legacy planning (pension death benefits)
- Phased retirement modeling
- Equity release/property downsizing impact

The agent then generates retirement planning recommendations including contribution strategies, accumulation plans, decumulation strategies, and product selection. Note: DB pension information is captured for income projection purposes only; no DB to DC transfer advice is provided.

### Outputs

#### Current Situation Analysis
- **Retirement Readiness Dashboard**
  - Total pension wealth (all schemes)
  - State Pension forecast
  - Projected retirement income (annual)
  - Target retirement income
  - Income replacement ratio (%)
  - Retirement readiness score (0-100)
  - Years to retirement
- **Pension Inventory**
  - List of all pensions (DC, DB, State)
  - Current values/accrued benefits
  - Contribution rates
  - Projected values at retirement
  - Fees and charges
- **Contribution Analysis**
  - Total monthly contributions (employee + employer)
  - Contribution as % of salary
  - Tax relief received
  - Employer match utilization (are you getting full employer contribution?)
  - Annual allowance usage (current tax year)
  - Carry forward availability
- **State Pension Status**
  - Years of NI contributions
  - State Pension forecast
  - NI gaps identified
  - Cost to fill gaps
  - State Pension age
- **Retirement Income Projection**
  - Projected income from DC pensions
  - Projected income from DB pensions
  - State Pension
  - Other income (investments, property)
  - Total projected income
  - Income shortfall/surplus vs target
  - Income replacement ratio
- **Tax Efficiency**
  - Current tax relief on contributions
  - Projected tax in retirement
  - Tax band comparison (now vs retirement)
  - Tax-free cash available (PCLS)

#### What-If Scenarios
- **Contribution Scenarios**
  - Impact of increasing contributions by X%
  - Impact of maximizing employer match
  - One-off lump sum contribution
  - Maximizing annual allowance each year
  - Stopping contributions early
- **Retirement Age Scenarios**
  - Early retirement impact (retire at 55, 60)
  - Delayed retirement impact (retire at 70)
  - Phased retirement (part-time from age X)
- **Investment Return Scenarios**
  - Optimistic market returns
  - Pessimistic market returns
  - Historical average returns
  - High/low inflation scenarios
- **Drawdown Scenarios**
  - Sustainable withdrawal rates (3%, 4%, 5%)
  - Variable spending rules (adjust income based on portfolio performance)
  - Spending shocks (one-off expenses)
  - Market crash in early retirement (sequence risk)
- **Annuity vs Drawdown Scenarios**
  - Full annuitization
  - Partial annuitization
  - Full drawdown
  - Hybrid approach
  - Impact on legacy
- **Longevity Scenarios**
  - Living to age 85, 90, 95, 100
  - Early death (legacy for beneficiaries)
  - Both partners' life expectancy (joint-life planning)
- **Pension Consolidation Scenarios**
  - Combining multiple DC pots (impact on fees, simplification)
  - Timing of consolidation
- **State Pension Scenarios**
  - Filling NI gaps
  - Deferring State Pension (extra income later)

#### Recommendations
- **Prioritized Action Plan**
  1. Immediate: Maximize employer match, fill NI gaps
  2. Short-term: Increase contributions, optimize allowances
  3. Medium-term: Consolidate pensions, review investment strategy
  4. Long-term: Decumulation planning, product selection
- **Contribution Recommendations**
  - Increase employee contributions to X% (quantified impact)
  - Maximize employer match (if not already)
  - Utilize carry forward (if available)
  - AVCs or SIPP top-ups
  - Salary sacrifice recommendations
  - Self-employed contribution plan
- **State Pension Optimization**
  - Fill NI gaps (specific years, cost, benefit)
  - Voluntary Class 2/3 contributions
  - State Pension deferral analysis (if beneficial)
- **Pension Consolidation**
  - DC pensions to consolidate (list)
  - Recommended consolidation platform (SIPP provider)
  - Fees saved
  - Simplification benefits
  - Considerations (exit penalties, guarantees to preserve)
  - Note: DB pensions are recorded for income projection only (no transfer advice provided)
- **Investment Strategy**
  - Asset allocation recommendations (lifecycle/glidepath strategy)
  - Risk-appropriate portfolio for years to retirement
  - De-risking timeline (moving to bonds as retirement approaches)
  - Fund selection (active vs passive)
- **Decumulation Strategy**
  - Recommended approach (annuity, drawdown, hybrid)
  - Sustainable withdrawal rate
  - Income phasing strategy (tax efficiency)
  - PCLS strategy (take full 25% or phase?)
  - Sequence of withdrawals (which pots first)
  - Drawdown product recommendations (providers, fees)
  - Annuity product recommendations (if applicable)
- **Tax Optimization**
  - PCLS timing recommendations
  - Income phasing to manage tax bands
  - Tax-efficient withdrawal sequencing
  - Spouse income splitting (if applicable)
- **Retirement Income Plan**
  - Year-by-year income projection
  - Income sources timeline (when each pension starts)
  - Cash flow visualization
  - Income vs expenditure gap management
- **Legacy Planning**
  - Pension death benefits (nomination of beneficiaries)
  - Drawdown vs annuity for legacy goals
  - Inheritance tax considerations
- **Product Recommendations**
  - SIPP provider recommendations (for consolidation/contributions)
  - Annuity providers and quotes (if annuity recommended)
  - Drawdown platform recommendations
  - Fee comparison

### Dashboard Design

#### Main Card (Summary View - Clickable)
**Retirement Overview Card**
- Retirement readiness score (large, color-coded gauge)
- Projected retirement income vs target (bar chart)
- Years to retirement countdown
- Total pension wealth
- Income gap/surplus indicator
- **Click to expand** → Full Retirement Dashboard

#### Detailed Retirement Dashboard (After Card Click)

**Navigation Tabs:**
1. Retirement Readiness
2. Pension Inventory
3. Contributions & Allowances
4. Projections
5. Recommendations
6. What-If Scenarios
7. Decumulation Planning

**Section: Retirement Readiness**
- Readiness score (large gauge)
- Income replacement ratio
- Target income vs projected income (visual comparison)
- Key metrics cards (total pension wealth, years to retirement, State Pension forecast)

**Section: Pension Inventory**
- Pension scheme cards (expandable, one per scheme)
  - Scheme name, type, value, contributions, fees
- State Pension card
- Total pension wealth summary
- Consolidation opportunities highlighted

**Section: Contributions & Allowances**
- Current contribution breakdown (employee, employer, tax relief)
- Contribution as % of salary
- Annual allowance tracker (usage, remaining, carry forward)
- Contribution optimization recommendations
- Salary sacrifice calculator

**Section: Projections**
- Retirement income projection (line chart over time)
- Income sources stacked bar chart (DC, DB, State, other)
- Accumulation projection (pension pot growth over time)
- Cash flow in retirement (income vs expenditure timeline)
- Probability of success (Monte Carlo simulation)

**Section: Recommendations**
- Actionable recommendation cards (prioritized)
- Contribution increase calculator (sliders)
- Consolidation recommendations
- State Pension optimization actions
- Investment strategy recommendations

**Section: What-If Scenarios**
- Scenario builder (dropdowns and sliders)
  - Adjust retirement age, contributions, returns, spending
- Visual impact charts (before/after projections)
- Side-by-side scenario comparison table
- Sensitivity analysis (tornado chart showing most impactful variables)

**Section: Decumulation Planning**
- Annuity vs drawdown comparison tool
- Sustainable withdrawal rate calculator
- Income sequencing visualizer (which pots to draw from when)
- PCLS strategy planner
- Drawdown simulator (interactive)
- Longevity risk assessment
- Legacy planning tools

---

## 5. Estate Planning Module

### Overview
Comprehensive estate planning covering inheritance tax (IHT) calculations, personal financial accounts (P&L, income, cashflow, balance sheet), gifting strategies, probate readiness, and legacy planning to ensure efficient wealth transfer and estate management.

### Scope - UK Products & Services

#### Estate Planning Services
- Will writing
- Lasting Power of Attorney (LPA)
  - Property and financial affairs
  - Health and welfare
- Probate services
- Trust creation and management
  - Discretionary trusts
  - Life interest trusts
  - Bare trusts
  - Charitable trusts
- Estate administration
- Tax planning and mitigation

#### Inheritance Tax (IHT) Planning
- Nil-Rate Band (NRB) utilization
- Residence Nil-Rate Band (RNRB)
- Spousal exemption
- Charity exemption
- Business Property Relief (BPR)
- Agricultural Property Relief (APR)
- IHT deferral and payment plans

#### Gifting Strategies
- Potentially Exempt Transfers (PETs)
- Chargeable Lifetime Transfers (CLTs)
- Annual gift exemptions (£3,000)
- Small gift exemptions (£250)
- Wedding/civil partnership gifts
- Regular gifts out of income
- Gifts to charities
- Gifts to political parties
- 7-year rule management (PETs)
- 14-year rule management (CLTs - lookback period for cumulative total)

#### Asset Protection
- Life insurance in trust
- Pension death benefits (nomination)
- Property ownership structures (joint tenants, tenants in common)
- Business succession planning
- Asset freezing techniques

#### Personal Accounts
- Personal Profit & Loss (P&L) statement
- Personal income statement
- Personal cashflow statement
- Personal balance sheet (statement of net worth)
- Net worth tracking over time

### Input Categories (Dynamic Form-Driven)

#### Personal Information
- Full name, date of birth, age
- Marital status
- Spouse/partner details
- Children and dependents (names, ages, relationships)
- Grandchildren (if applicable)
- Domicile status (UK domiciled, non-dom, deemed-dom)
- Residency status
- Life expectancy estimate

#### Estate Assets
- **Property**
  - Main residence (address, value, ownership %, mortgage balance)
  - Additional properties (buy-to-let, holiday homes)
  - Overseas property
  - Ownership structure (sole, joint tenants, tenants in common)
- **Financial Assets**
  - Bank accounts (balances)
  - Savings and cash ISAs
  - Investment accounts (ISAs, GIAs, offshore bonds)
  - Pensions (total value, death benefit type, beneficiaries nominated?)
  - Life insurance policies (in trust or not)
  - Business interests (shareholdings, business value)
  - Loans owed to you
- **Personal Property**
  - Vehicles
  - Jewelry, art, antiques
  - Other valuables
- **Digital Assets**
  - Cryptocurrency holdings
  - Online accounts and subscriptions

#### Estate Liabilities
- Mortgage balances (main residence and additional properties)
- Personal loans
- Credit card debt
- Business loans
- Liabilities owed

#### Income & Expenditure (Current)
- **Income**
  - Employment income (salary, bonus)
  - Self-employment income
  - Pension income (State Pension, private pensions)
  - Rental income
  - Investment income (dividends, interest)
  - Other income (royalties, etc.)
- **Expenditure**
  - Housing costs (mortgage/rent, utilities, insurance, maintenance)
  - Living costs (food, transport, leisure)
  - Insurance premiums
  - Loan repayments
  - Savings and investments
  - Discretionary spending
  - One-off expenses

#### Gifting History
- Gifts made in the last 7 years (PETs)
  - Date of gift
  - Recipient
  - Value
  - Type (cash, property, other)
  - Exemption claimed (annual, small, wedding, etc.)
- Chargeable Lifetime Transfers (CLTs) made in the last 14 years
  - Date of transfer
  - Recipient (typically trusts)
  - Value
  - IHT paid at time of transfer (if any)
  - Type of trust or entity
- Regular gifts out of income (history and ongoing)
- Charitable donations

#### Current Estate Planning Arrangements
- **Will**
  - Will in place? (Yes/No)
  - Date of last will
  - Executors named
  - Beneficiaries and bequests
  - Testamentary trusts included?
  - Guardianship provisions (for minor children)
- **Lasting Power of Attorney (LPA)**
  - Property & financial affairs LPA in place?
  - Health & welfare LPA in place?
  - Attorneys appointed (names)
- **Trusts**
  - Existing trusts (type, value, beneficiaries, trustees)
  - Assets held in trust
- **Life Insurance in Trust**
  - Policies in trust (policy details, value, trustees, beneficiaries)
- **Pension Death Benefits**
  - Nominations completed?
  - Beneficiaries nominated
  - Expression of wish forms filed?
- **Business Succession Plans**
  - Succession plan in place?
  - Shareholder agreements
  - Key person insurance

#### Estate Goals & Objectives
- Primary beneficiaries (spouse, children, grandchildren, charities)
- Legacy goals (amount to leave to each beneficiary)
- IHT minimization priority (high, medium, low)
- Charitable giving intentions
- Specific bequests (property, items, sums to individuals)
- Business succession goals
- Guardianship preferences for minor children
- Executors and trustees (preferred individuals)
- Funeral wishes

#### Family Circumstances
- Blended families (previous marriages, step-children)
- Dependents with special needs
- Family disputes or estrangement
- Vulnerable beneficiaries
- Non-UK resident beneficiaries

#### Future Plans
- Expected inheritance to receive
- Planned property transactions (downsizing, purchases)
- Plans for large gifts
- Planned charitable giving
- Expected changes in income or expenditure
- Planned business sale or exit

### Agent

The Estate Agent takes user inputs and performs calculations directly in the application code:
- IHT estimation (total liability based on current estate)
- Nil-Rate Band and Residence Nil-Rate Band calculations
- Transferable NRB from deceased spouse calculation
- Gifting strategy timeline modeling (PETs, CLTs, annual exemptions)
- 7-year rule tracking for PETs (taper relief calculations)
- 14-year rule tracking for CLTs (cumulative total calculations)
- Probate readiness assessment (documentation, liquidity for IHT payment)
- Estate liquidity analysis (can IHT be paid without forced sales?)
- Net worth calculation (assets - liabilities)
- Personal P&L statement generation
- Personal income statement generation
- Personal cashflow analysis (income vs expenditure, surplus/deficit)
- Personal balance sheet generation (assets, liabilities, net worth)
- Trust structure recommendations
- Life insurance needs for IHT (coverage gap)
- Business Property Relief (BPR) and Agricultural Property Relief (APR) eligibility
- Charitable giving tax efficiency
- Estate distribution modeling (who gets what, when, and how much tax)

The agent then generates estate planning recommendations including IHT mitigation strategies, gifting timelines, trust structures, documentation needs, and probate readiness actions.

### Outputs

#### Current Situation Analysis
- **Estate Dashboard Summary**
  - Total gross estate value
  - Total liabilities
  - Net estate value
  - Estimated IHT liability
  - Effective IHT rate (%)
  - Net inheritance to beneficiaries (after IHT)
  - Estate planning readiness score (0-100)
- **Net Worth Statement (Personal Balance Sheet)**
  - Assets (categorized: property, financial, personal, business)
  - Liabilities
  - Net worth
  - Net worth trend over time (if historical data available)
- **Personal P&L (Income Statement)**
  - Total income (by source)
  - Total expenditure (by category)
  - Net surplus/deficit (disposable income)
  - Annual, monthly breakdown
- **Cashflow Statement**
  - Cash inflows (by source)
  - Cash outflows (by category)
  - Net cashflow
  - Monthly cashflow projection
- **IHT Liability Breakdown**
  - Gross estate value
  - Less: liabilities
  - Less: exemptions (spouse, charity)
  - Less: reliefs (BPR, APR)
  - Taxable estate
  - Less: Nil-Rate Band (£325,000 + transferable NRB)
  - Less: Residence Nil-Rate Band (£175,000 + transferable RNRB, if applicable)
  - IHT due (at 40%)
  - IHT liability by asset type
- **Gifting History & Transfers Tracker**
  - PETs made in last 7 years (timeline visualization)
  - PETs still within 7-year window with taper relief
  - CLTs made in last 14 years (timeline visualization)
  - CLTs cumulative total calculation
  - Cumulative gifts vs NRB
  - Annual exemptions used
- **Estate Planning Documentation Status**
  - Will: ✅ In place / ❌ Missing / ⚠️ Outdated
  - LPA (Property & Financial Affairs): Status
  - LPA (Health & Welfare): Status
  - Trusts: Status
  - Life insurance in trust: Status
  - Pension nominations: Status
  - Letter of wishes: Status
  - Asset register: Status
- **Probate Readiness Assessment**
  - Liquidity for IHT payment (cash available vs IHT due)
  - Documentation completeness
  - Executor readiness
  - Potential forced sales required?
  - Probate complexity score

#### What-If Scenarios
- **Gifting Scenarios**
  - Impact of gifting £X now (PET strategy)
  - Regular gifts out of income (annual impact)
  - Immediate charity donation (IHT saving)
  - Gifting property to children (impact and risks)
- **Asset Value Scenarios**
  - Property value increase/decrease (impact on IHT)
  - Investment portfolio growth (impact on estate value)
  - Business valuation changes
- **Spending Scenarios**
  - Increased spending in retirement (reduce estate, reduce IHT)
  - Large one-off expenses (impact on net worth and IHT)
  - Downsizing property (reduce estate value)
- **Longevity Scenarios**
  - Death in 5 years, 10 years, 20 years (PETs falling out of estate, taper relief)
  - Both spouses' death order (spousal exemption and transferable NRB impact)
- **Trust Scenarios**
  - Assets moved into trust (impact on IHT)
  - Life insurance in trust (IHT saving)
  - Discretionary trust for children
- **Business Succession Scenarios**
  - Business sale (impact on estate value)
  - Business succession to family (BPR utilization)
  - Business gifting strategy
- **Charity Scenarios**
  - Leaving 10% to charity (reduced IHT rate to 36%)
  - Large charitable bequest
- **Pension Scenarios**
  - Impact of spending pension in lifetime vs leaving as inheritance
  - Pension death benefits (tax-free vs taxed)

#### Recommendations
- **Prioritized Action Plan**
  1. Immediate: Critical documentation gaps (will, LPA)
  2. Short-term: Gifting opportunities, insurance needs
  3. Medium-term: Trust structures, BPR investments
  4. Long-term: Strategic estate reduction, succession planning
- **IHT Mitigation Strategies**
  - Gifting plan (specific amounts, recipients, timing)
  - Regular gifts out of income (quantified strategy)
  - Charitable giving (optimize for 36% rate if leaving >10%)
  - Pension spend-down strategy (use pension income, preserve other assets)
  - Life insurance to cover IHT (policy amount, in trust)
  - BPR/APR investments (to reduce taxable estate)
  - Trust strategies (assets to move, trust type)
  - Spousal exemption utilization (asset transfers)
- **Gifting Timeline**
  - Year-by-year gifting plan (amounts, recipients)
  - PETs tracker with 7-year countdown and taper relief schedule
  - CLTs tracker with 14-year lookback and cumulative total
  - Annual exemptions utilization plan (£3,000/year)
  - Regular gifts out of income plan (monthly/annual amounts)
- **Documentation & Legal Actions**
  - Update will (if outdated or missing)
  - Create/update LPA (property & financial affairs, health & welfare)
  - Set up trusts (type, assets, beneficiaries, trustees)
  - Life insurance in trust (policy details, trustees)
  - Pension death benefit nominations (complete expression of wish forms)
  - Asset register (create and maintain)
  - Letter of wishes (guidance for executors)
  - Business succession documentation (shareholder agreements)
- **Liquidity Planning**
  - Ensure sufficient liquid assets to pay IHT (avoid forced property sales)
  - Life insurance to provide liquidity
  - Payment plan options (HMRC installments for property)
- **Executor & Trustee Recommendations**
  - Suggested executors (individuals or professional)
  - Suggested trustees (for trusts)
  - Backup executors/trustees
- **Business Succession**
  - Succession plan (handover to family, management buyout, sale)
  - BPR strategy (hold business for 2 years for 100% relief)
  - Key person insurance
  - Shareholder protection
- **Personal Financial Optimization**
  - Increase disposable income (reduce expenses)
  - Surplus cashflow allocation (savings, investments, gifting)
  - Net worth growth strategies
- **Estate Distribution Strategy**
  - Optimal distribution to beneficiaries (tax-efficient)
  - Use of trusts for control and protection
  - Phased inheritance (lifetime gifts + bequest)

### Dashboard Design

#### Main Card (Summary View - Clickable)
**Estate Planning Overview Card**
- Estimated IHT liability (large, prominent, color-coded)
- Net estate value
- Estate planning readiness score (gauge)
- Net worth
- Documentation status indicators (✅ ❌ ⚠️)
- **Click to expand** → Full Estate Planning Dashboard

#### Detailed Estate Planning Dashboard (After Card Click)

**Navigation Tabs:**
1. Overview & Net Worth
2. IHT Liability
3. Gifting Strategy
4. Personal Accounts (P&L, Cashflow, Balance Sheet)
5. Recommendations
6. What-If Scenarios
7. Documentation & Probate

**Section: Overview & Net Worth**
- Net worth statement (assets, liabilities, net worth)
- Net worth trend chart (over time)
- Asset allocation pie chart (property, financial, personal, business)
- Estate value summary
- Key metrics cards (gross estate, IHT liability, net to beneficiaries)

**Section: IHT Liability**
- IHT calculation breakdown (step-by-step)
- Visual waterfall chart (gross estate → taxable estate → IHT due)
- IHT liability by asset type (bar chart)
- Nil-Rate Band and RNRB utilization (progress bars)
- Exemptions and reliefs applied
- Effective IHT rate

**Section: Gifting Strategy**
- Gifting timeline (Gantt chart showing recommended gifts over time)
- PETs tracker (7-year countdown for each gift with taper relief visualization)
- CLTs tracker (14-year lookback with cumulative total calculation)
- Annual exemptions tracker (usage per tax year)
- Regular gifts out of income plan
- Gifting impact on IHT (before/after)
- Charity gifting optimizer (calculate 36% rate eligibility)

**Section: Personal Accounts**
- **Sub-tab: P&L (Income Statement)**
  - Income breakdown (pie chart and table)
  - Expenditure breakdown (pie chart and table)
  - Surplus/deficit (large card)
  - Monthly and annual views
- **Sub-tab: Cashflow**
  - Cashflow statement (inflows, outflows, net)
  - Monthly cashflow chart (line graph)
  - Cashflow projection (next 12 months)
- **Sub-tab: Balance Sheet**
  - Assets (categorized list and values)
  - Liabilities (categorized list and values)
  - Net worth calculation
  - Net worth trend chart

**Section: Recommendations**
- Actionable recommendation cards (prioritized by urgency)
- IHT mitigation strategies (expandable cards with detail)
- Gifting recommendations (amounts, recipients, timing)
- Documentation actions (e.g., "Update your will")
- Trust recommendations
- Insurance recommendations

**Section: What-If Scenarios**
- Scenario builder (interactive sliders and dropdowns)
  - Adjust estate value, gifting amounts, asset growth, longevity
- Visual impact charts (IHT liability before/after)
- Side-by-side scenario comparison table
- Sensitivity analysis (what factors have biggest impact on IHT)

**Section: Documentation & Probate**
- Documentation checklist (interactive, tick items off)
  - Will, LPA, trusts, insurance, pension nominations, asset register
- Probate readiness score (gauge)
- Liquidity analysis (cash available vs IHT due)
- Executor information
- Document repository (upload and store estate documents)
- Key contacts (solicitors, financial advisors, accountants)

---

## Dashboard Integration & Navigation

### Main Dashboard (Unified Household View)
The Main Dashboard provides a high-level, aggregated view of the user's complete financial situation across all five modules, displaying:

#### Dashboard Cards (Clickable for Detail)
1. **Protection Card** → Click to open Protection Dashboard
2. **Savings Card** → Click to open Savings Dashboard
3. **Investment Card** → Click to open Investment Dashboard
4. **Retirement Card** → Click to open Retirement Dashboard
5. **Estate Planning Card** → Click to open Estate Planning Dashboard

#### Additional Main Dashboard Elements
- **Goal Progress Overview**: Visual tracker for all financial goals across modules (e.g., retirement readiness, emergency fund, education savings, IHT reduction)
- **Priority Actions Feed**: Top 5 prioritized actions from across all modules (e.g., "Close life insurance gap," "Maximize ISA allowance," "Fill NI gaps")
- **Net Worth Snapshot**: Total assets, liabilities, net worth (integrated from Estate Planning and all modules)
- **Cashflow Summary**: Monthly income, expenditure, surplus/deficit (integrated from Estate Planning personal accounts)
- **ISA Allowance Tracker**: Total ISA allowance with breakdown (Cash ISA subscriptions, Stocks & Shares ISA subscriptions, remaining allowance) - prominently displayed with progress bar
- **Alerts & Notifications**: Regulatory changes, policy renewals, contribution deadlines, market alerts

### Bidirectional Navigation
- **From Main Dashboard**: Click any module card → Opens detailed module dashboard
- **From Module Dashboard**: Click "Return to Main Dashboard" or breadcrumb navigation
- **Cross-Module Navigation**: Direct links from one module dashboard to related sections in other modules (e.g., from Retirement Dashboard → Investment Dashboard to review pension investments)

### Responsive & User-Friendly Design
- **Mobile-First Design**: All dashboards optimized for mobile, tablet, and desktop
- **Progressive Disclosure**: Summary cards expand to show detail; drill-down for deeper analysis
- **Interactive Visualizations**: Charts, graphs, sliders, and calculators for user exploration
- **Clear Call-to-Actions**: Buttons for "Take Action," "Run Scenario," "Update Information"
- **Guided Workflows**: Wizards for complex tasks (e.g., pension consolidation, trust setup)

---

## Module Coordination

### Coordinating Agent (Optional)

An optional coordinating agent can aggregate recommendations from all module agents to provide holistic financial guidance:

- Takes recommendations from all 5 module agents
- Identifies conflicts and trade-offs (e.g., emergency fund vs pension contributions)
- Applies priority rules (e.g., protection before investment, emergency fund before goals)
- Generates a unified, prioritized action plan
- Coordinates cross-module strategies:
  - ISA allowance optimization (Savings + Investment)
  - Protection adequacy before aggressive investing
  - IHT impact on retirement withdrawals
  - Trust structures across Protection, Investment, and Estate

---

## Summary

This simplified demo version provides:
- **Complete UK product coverage** across all five financial planning domains
- **Extensive input categories** via dynamically driven forms, capturing all necessary user data
- **Rich outputs** including current situation analysis, what-if scenarios, and actionable recommendations
- **Clear, interactive dashboards** with clickable cards for drill-down and bidirectional navigation
- **Simplified architecture** with all calculations performed directly in the application code by intelligent agents
- **Optional coordination** for holistic financial planning through a coordinating agent

This design ensures FPS delivers a professional-grade, comprehensive, and user-friendly financial planning demo for UK individuals and families - without the complexity of external services or MCP integration.
