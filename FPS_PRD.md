# FPS (Financial Planning System) - Product Requirements Document (PRD)

**Document Version**: 1.0
**Date**: 2025-10-13
**Project**: FPS Financial Planning System - UK Market
**Status**: Draft for Development

---

## Executive Summary

### Product Vision

The Financial Planning System (FPS) is a comprehensive, interactive web-based financial planning platform designed for UK individuals and families. FPS empowers users to take control of their complete financial picture through five integrated modules covering Protection, Savings, Investment, Retirement, and Estate Planning.

### Business Objectives

1. **Comprehensive Financial Planning**: Provide users with a single platform to manage all aspects of their financial life
2. **UK-Specific**: Tailored exclusively for UK tax rules, products, regulations, and financial services
3. **Actionable Recommendations**: Generate intelligent, prioritized recommendations based on user-specific data
4. **Interactive & Engaging**: Deliver an intuitive, visual, interactive user experience
5. **Educational**: Help users understand complex financial concepts through clear visualizations and scenarios

### Target Users

- **Primary**: UK residents aged 25-65 with disposable income seeking holistic financial planning
- **Secondary**: Financial advisors using the platform as a demonstration or analysis tool
- **Tertiary**: UK families planning for major life events (home purchase, retirement, education, inheritance)

### Key Differentiators

- **Holistic Approach**: All five financial planning domains in one platform
- **UK-Focused**: Complete coverage of UK tax rules, products, and allowances
- **Intelligent Agents**: AI-powered agents providing personalized recommendations
- **What-If Scenarios**: Interactive scenario modeling across all modules
- **Coordinated Planning**: Cross-module optimization (e.g., ISA allowances, tax efficiency)

---

## Product Overview

### System Architecture

**Single-Layer Architecture:**
- **Module Agents**: Intelligent agents (Protection, Savings, Investment, Retirement, Estate) that process user inputs and perform calculations
- **Coordinating Agent**: Optional agent that aggregates recommendations across all modules
- **Centralized Configuration**: UK tax rules, allowances, and thresholds maintained in a single configuration file

### Five Core Modules

| Module | Purpose | Key Outputs |
|--------|---------|-------------|
| **Protection** | Risk protection analysis (life insurance, critical illness, income protection) | Coverage adequacy score, gap analysis, policy recommendations |
| **Savings** | Cash management and emergency fund planning | Emergency fund runway, savings goals progress, ISA optimization |
| **Investment** | Portfolio management and long-term growth | Portfolio performance, asset allocation, goal probability |
| **Retirement** | Pension planning and retirement readiness | Retirement readiness score, income projection, contribution optimization |
| **Estate** | Inheritance tax planning and wealth transfer | IHT liability, net worth, gifting strategy, probate readiness |

### Technology Approach

- **Web Application**: Responsive, modern web interface
- **Backend Framework**: Laravel 10.x (PHP 8.2+)
- **Frontend Framework**: Vue.js 3 with Vuex state management
- **Database**: MySQL 8.0+ with InnoDB engine
- **Caching**: Memcached for performance optimization
- **Charting**: ApexCharts for all visualizations

---

## Functional Requirements

### FR-1: User Management

**FR-1.1: User Registration & Authentication**
- Users can register with email and password
- Secure authentication with password requirements
- Email verification required
- Password reset functionality
- Session management

**FR-1.2: User Profile**
- Personal information (name, DOB, contact details)
- Family information (spouse, dependents)
- Tax status and residency
- Profile can be edited at any time

---

### FR-2: Protection Module

**FR-2.1: Input Collection**
- Dynamic forms for collecting:
  - Personal demographics (age, gender, occupation, health status)
  - Current protection coverage (life insurance, critical illness, income protection, health insurance)
  - Financial information (income, debts, expenditure, assets)
  - Protection goals and needs
  - Future plans

**FR-2.2: Coverage Gap Analysis**
- Calculate human capital (income replacement need over time)
- Analyze coverage gaps by category (debt protection, mortgage, education, final expenses)
- Generate coverage adequacy score (0-100)
- Identify priority gaps

**FR-2.3: Policy Assessment**
- Evaluate existing policies for competitiveness and suitability
- Assess premium value vs market rates
- Check policy structure efficiency (term, indexation, trusts)

**FR-2.4: Recommendations**
- Generate prioritized action plan
- Recommend specific policy types, sum assured, and terms
- Identify policies to retain, replace, or cancel
- Provide premium optimization strategies
- Recommend trust structures

**FR-2.5: What-If Scenarios**
- Model impact of death, critical illness, or disability
- Test affordability scenarios (varying coverage levels)
- Compare product types (level vs decreasing term, joint vs single)

**FR-2.6: Protection Dashboard**
- Summary card showing coverage adequacy score, total coverage, premiums
- Detailed dashboard with tabs: Current Situation, Gap Analysis, Recommendations, What-If Scenarios, Policy Details
- Interactive charts (coverage heatmap, premium breakdown, timeline)

---

### FR-3: Savings Module

**FR-3.1: Input Collection**
- Dynamic forms for collecting:
  - Current savings and cash holdings (bank accounts, ISAs, bonds, NS&I)
  - Income and expenditure details
  - Short-term savings goals (target amount, date, priority)
  - ISA allowance tracking
  - Access requirements

**FR-3.2: Emergency Fund Analysis**
- Calculate emergency fund runway (months of expenditure covered)
- Identify shortfall or surplus
- Recommend target emergency fund amount

**FR-3.3: Liquidity Ladder Optimization**
- Categorize funds by access (immediate, short-notice, fixed-term)
- Visualize liquidity ladder timeline
- Recommend optimal balance between access and rates

**FR-3.4: ISA Allowance Tracking**
- Track current tax year ISA subscriptions (Cash ISA, Stocks & Shares ISA)
- Display total ISA allowance (£20,000 for 2024/25)
- Calculate remaining ISA allowance
- Coordinate with Investment Module for total tracking

**FR-3.5: Rate Optimization**
- Compare user's account rates vs market benchmarks
- Identify underperforming accounts
- Recommend higher-rate alternatives

**FR-3.6: Goal Progress Tracking**
- Track progress toward each savings goal
- Calculate required monthly savings to meet goals
- Flag goals that are off-track

**FR-3.7: Recommendations**
- Prioritized action plan (emergency fund, rate optimization, ISA strategy, goals)
- Specific account and provider recommendations
- Transfer instructions

**FR-3.8: What-If Scenarios**
- Emergency scenarios (loss of income, unexpected expenses)
- Rate change impact
- Goal funding scenarios (accelerated vs delayed)
- ISA strategy scenarios (Cash ISA vs Stocks & Shares ISA allocation)

**FR-3.9: Savings Dashboard**
- Summary card showing emergency fund runway, total savings, ISA usage, goals status
- Detailed dashboard with tabs: Current Situation, Emergency Fund, Savings Goals, Recommendations, What-If Scenarios, Account Details
- Interactive charts (liquidity ladder, goal progress bars, interest rate comparison)

---

### FR-4: Investment Module

**FR-4.1: Input Collection**
- Dynamic forms for collecting:
  - Current investment holdings (ISAs, GIAs, onshore/offshore bonds, VCTs, EIS)
  - Individual securities and funds (with identifiers, quantities, values)
  - Portfolio allocation (asset class, geography, sector)
  - Investment goals (target amount, date, priority)
  - Risk profile (tolerance, capacity for loss, time horizon)
  - ISA allowance usage
  - Investment preferences (active vs passive, ESG, platform)

**FR-4.2: Portfolio Analysis**
- Calculate total portfolio value, returns (YTD, 1y, 3y, 5y)
- Analyze asset allocation vs target
- Assess diversification (geographic, sector, currency)
- Calculate portfolio risk metrics (volatility, Sharpe ratio)

**FR-4.3: Holdings Analysis**
- List top holdings by value
- Identify largest gainers and losers
- Calculate dividend income
- Assess liquidity profile

**FR-4.4: Fee Analysis**
- Calculate total fees (platform, fund, transaction, advice)
- Quantify fee drag on performance
- Compare to low-cost alternatives

**FR-4.5: Tax Efficiency Analysis**
- Track ISA vs GIA allocation
- Monitor taxable income from investments
- Track unrealized capital gains
- Monitor CGT and dividend allowance usage
- Calculate tax saved via ISAs

**FR-4.6: Goal Funding Analysis**
- Calculate goal funding status (on-track vs off-track)
- Run Monte Carlo simulations for goal probability
- Identify required vs projected growth rates

**FR-4.7: Recommendations**
- Prioritized action plan
- Asset allocation recommendations with rebalancing trades
- Product recommendations (funds/ETFs to buy/sell)
- Wrapper optimization (ISA contribution strategy, bond selection)
- Fee optimization (platform/fund switches)
- Tax optimization (ISA maximization, CGT harvesting)

**FR-4.8: What-If Scenarios**
- Market scenarios (crash, stagnation, high inflation)
- Contribution scenarios (increased/decreased/paused)
- Allocation scenarios (more aggressive/conservative, sector tilts)
- Fee scenarios (switching to low-cost alternatives)
- Tax scenarios (ISA maximization, tax bracket changes)
- Goal scenarios (adjusted timelines or amounts)

**FR-4.9: Investment Dashboard**
- Summary card showing portfolio value, YTD return, goal probability, asset allocation, rebalancing alerts
- Detailed dashboard with tabs: Portfolio Overview, Holdings, Performance, Goals, Recommendations, What-If Scenarios, Tax & Fees
- Interactive charts (asset allocation, performance vs benchmarks, Monte Carlo results, geographic map, sector breakdown)

---

### FR-5: Retirement Module

**FR-5.1: Input Collection**
- Dynamic forms for collecting:
  - Current pension arrangements (DC pensions, DB pensions, State Pension)
  - Employment and contribution details
  - Financial situation (income, assets, liabilities)
  - Retirement goals (target age, desired income, expenditure plans)
  - Retirement income strategy preferences (annuity vs drawdown)
  - Health and lifestyle factors

**FR-5.2: Retirement Readiness Analysis**
- Calculate retirement readiness score (0-100)
- Project retirement income from all sources (DC, DB, State, other)
- Calculate income replacement ratio
- Identify income shortfall or surplus vs target

**FR-5.3: Pension Inventory**
- List all pension schemes with current values
- Display contribution rates and fees
- Project fund values at retirement age

**FR-5.4: Contribution Analysis**
- Calculate total monthly contributions (employee + employer)
- Check if maximizing employer match
- Track annual allowance usage (including carry forward and tapering)
- Calculate tax relief received

**FR-5.5: State Pension Analysis**
- Display NI years completed and State Pension forecast
- Identify NI gaps and calculate cost to fill
- Assess State Pension deferral options

**FR-5.6: Retirement Income Projection**
- Project year-by-year income in retirement
- Model income from different sources (DC, DB, State, other)
- Create income timeline visualization

**FR-5.7: Decumulation Planning**
- Compare annuity vs drawdown strategies
- Calculate sustainable withdrawal rates
- Model income phasing for tax efficiency
- Plan PCLS (Pension Commencement Lump Sum) strategy
- Analyze sequence-of-returns risk

**FR-5.8: Recommendations**
- Prioritized action plan
- Contribution recommendations (increase to X%, maximize employer match, AVCs)
- State Pension optimization (fill NI gaps, voluntary contributions)
- Pension consolidation (DC pensions only - list schemes, platform recommendation, fee savings)
- Investment strategy (asset allocation, de-risking timeline)
- Decumulation strategy (annuity vs drawdown, withdrawal rate, PCLS timing)
- Tax optimization (income phasing, withdrawal sequencing)

**FR-5.9: What-If Scenarios**
- Contribution scenarios (increased/decreased contributions, lump sums)
- Retirement age scenarios (early, delayed, phased)
- Investment return scenarios (optimistic, pessimistic, historical average)
- Drawdown scenarios (sustainable withdrawal rates, spending shocks, market crash)
- Annuity vs drawdown comparison
- Longevity scenarios (living to 85, 90, 95, 100)
- State Pension scenarios (filling NI gaps, deferring State Pension)

**FR-5.10: Retirement Dashboard**
- Summary card showing readiness score, projected income vs target, years to retirement, total pension wealth, income gap/surplus
- Detailed dashboard with tabs: Retirement Readiness, Pension Inventory, Contributions & Allowances, Projections, Recommendations, What-If Scenarios, Decumulation Planning
- Interactive charts (readiness gauge, income projection, accumulation timeline, drawdown simulator, annuity vs drawdown comparison)

---

### FR-6: Estate Planning Module

**FR-6.1: Input Collection**
- Dynamic forms for collecting:
  - Estate assets (property, financial assets, personal property, business interests)
  - Estate liabilities (mortgages, loans, debts)
  - Income and expenditure (for P&L and cashflow statements)
  - Gifting history (PETs in last 7 years, CLTs in last 14 years)
  - Current estate planning arrangements (will, LPA, trusts, insurance in trust, pension nominations)
  - Estate goals and objectives
  - Family circumstances

**FR-6.2: IHT Liability Calculation**
- Calculate gross estate value
- Deduct liabilities, exemptions, and reliefs
- Calculate Nil-Rate Band (NRB) and Residence Nil-Rate Band (RNRB) with transferable amounts
- Calculate IHT due at 40% (or 36% if 10%+ to charity)
- Show IHT liability breakdown (step-by-step waterfall calculation)

**FR-6.3: Net Worth Analysis**
- Generate personal balance sheet (assets, liabilities, net worth)
- Track net worth trend over time

**FR-6.4: Personal P&L and Cashflow**
- Generate personal Profit & Loss statement (income vs expenditure)
- Generate cashflow statement (cash inflows vs outflows)
- Calculate net surplus/deficit (disposable income)
- Project monthly cashflow

**FR-6.5: Gifting Strategy**
- Track PETs (Potentially Exempt Transfers) in last 7 years with 7-year countdown
- Track CLTs (Chargeable Lifetime Transfers) in last 14 years with cumulative total
- Model taper relief on PETs
- Track annual exemptions (£3,000), small gift exemptions (£250), wedding gifts
- Model regular gifts out of income

**FR-6.6: Probate Readiness Assessment**
- Assess documentation completeness (will, LPA, trusts, insurance, pension nominations)
- Analyze liquidity for IHT payment (cash available vs IHT due)
- Calculate probate complexity score

**FR-6.7: Recommendations**
- Prioritized action plan
- IHT mitigation strategies (gifting plan, trusts, life insurance, BPR investments, charitable giving)
- Gifting timeline (year-by-year plan with PET and CLT tracking)
- Documentation actions (update will, create LPA, set up trusts, life insurance in trust, pension nominations)
- Liquidity planning (ensure sufficient assets to pay IHT)
- Business succession recommendations

**FR-6.8: What-If Scenarios**
- Gifting scenarios (impact of gifting £X, regular gifts, charity donations)
- Asset value scenarios (property value changes, investment growth)
- Spending scenarios (increased spending, downsizing)
- Longevity scenarios (death in 5, 10, 20 years - PETs falling out of estate)
- Trust scenarios (assets moved into trust, life insurance in trust)
- Charity scenarios (leaving 10%+ to charity for 36% IHT rate)

**FR-6.9: Estate Planning Dashboard**
- Summary card showing estimated IHT liability, net estate value, readiness score, net worth, documentation status
- Detailed dashboard with tabs: Overview & Net Worth, IHT Liability, Gifting Strategy, Personal Accounts (P&L, Cashflow, Balance Sheet), Recommendations, What-If Scenarios, Documentation & Probate
- Interactive charts (net worth statement, IHT waterfall chart, gifting timeline with PET/CLT tracking, asset allocation, cashflow projection)

---

### FR-7: Main Dashboard (Unified View)

**FR-7.1: Dashboard Overview**
- Display all five module cards (Protection, Savings, Investment, Retirement, Estate)
- Each card shows key metrics and is clickable to open detailed module dashboard
- Goal progress overview across all modules
- Priority actions feed (top 5 actions from all modules)
- Net worth snapshot (total assets, liabilities, net worth)
- Cashflow summary (monthly income, expenditure, surplus/deficit)
- ISA allowance tracker (total allowance with breakdown by Cash ISA and Stocks & Shares ISA, remaining allowance)
- Alerts and notifications

**FR-7.2: Navigation**
- Click module card to open detailed module dashboard
- Breadcrumb navigation to return to main dashboard
- Cross-module navigation links (e.g., from Retirement → Investment)

---

### FR-8: Coordinating Agent (Optional)

**FR-8.1: Recommendation Aggregation**
- Collect recommendations from all five module agents
- Identify conflicts and trade-offs (e.g., emergency fund vs pension contributions)
- Apply priority rules (protection before investment, emergency fund before goals)
- Generate unified, prioritized action plan

**FR-8.2: Cross-Module Optimization**
- ISA allowance optimization (Savings + Investment)
- Protection adequacy checks before aggressive investing
- IHT impact on retirement withdrawal strategy
- Trust structures across Protection, Investment, and Estate

---

### FR-9: Centralized UK Tax Configuration

**FR-9.1: Tax Configuration File**
- Maintain centralized JSON configuration file with all UK tax rules
- Include all tax year data (2024/25 and historical years)
- Configuration includes:
  - Income tax (personal allowance, bands, rates, tapering)
  - National Insurance (employee, employer, self-employed rates and thresholds)
  - Capital Gains Tax (allowance, rates)
  - Dividend tax (allowance, rates)
  - Savings interest (Personal Savings Allowance)
  - ISA allowances (total, LISA, Junior ISA)
  - Pension allowances (annual allowance, tapering, MPAA)
  - Inheritance tax (IHT rate, NRB, RNRB, taper thresholds, PET/CLT rules)
  - Gifting exemptions (annual, small gift, wedding)
  - State Pension (full amount, age by DOB)

**FR-9.2: Configuration Management**
- Easy annual updates when tax rules change
- Version control for historical tax years
- All calculation functions reference centralized config

---

### FR-10: Calculations & Algorithms

**FR-10.1: Protection Calculations**
- Human capital valuation (income replacement need over time)
- Coverage gap analysis by category
- Premium affordability calculations

**FR-10.2: Savings Calculations**
- Emergency fund runway (months of expenditure)
- Goal funding projections (compound interest with contributions)
- ISA allowance tracking and optimization

**FR-10.3: Investment Calculations**
- Portfolio returns (time-weighted, money-weighted)
- Asset allocation deviation from target
- Fee drag calculation
- Tax efficiency metrics (CGT, dividend allowance usage)
- Monte Carlo simulations for goal probability (1,000+ iterations)

**FR-10.4: Retirement Calculations**
- Pension projection (DC growth with contributions)
- DB pension income projection
- State Pension forecast validation
- Annual allowance calculations (including carry forward and tapering)
- Sustainable withdrawal rate (4% rule, dynamic strategies)
- Annuity vs drawdown comparison
- Longevity risk modeling

**FR-10.5: Estate Calculations**
- IHT liability calculation (NRB, RNRB, exemptions, reliefs)
- Transferable NRB/RNRB from deceased spouse
- PET taper relief calculation (7-year rule)
- CLT cumulative total calculation (14-year lookback)
- Net worth calculation (assets - liabilities)
- Personal P&L (income - expenditure)
- Cashflow projection

---

## Non-Functional Requirements

### NFR-1: Performance

- **Page Load Time**: All pages load within 2 seconds on standard broadband
- **Calculation Speed**: Simple calculations complete instantly (<500ms)
- **Complex Calculations**: Monte Carlo simulations complete within 5 seconds or run as background jobs
- **Caching**: Memcached caching for tax config, calculation results, and dashboard data
- **Database Queries**: Optimized with proper indexing, response time <100ms for standard queries

### NFR-2: Usability

- **Responsive Design**: Mobile-first design, optimized for mobile, tablet, and desktop
- **Intuitive Navigation**: Clear navigation with breadcrumbs, max 3 clicks to any feature
- **Progressive Disclosure**: Summary views with drill-down to detailed views
- **Clear Visualizations**: Charts and graphs are easy to interpret with legends and tooltips
- **Accessibility**: WCAG 2.1 Level AA compliance

### NFR-3: Security

- **Data Encryption**: HTTPS only, all data encrypted in transit (TLS 1.3)
- **Password Security**: Bcrypt hashing, minimum password requirements
- **Authentication**: Session-based authentication with CSRF protection
- **Authorization**: Role-based access control (users can only access their own data)
- **Input Validation**: All user inputs validated and sanitized
- **Financial Data Protection**: Sensitive fields (account numbers, policy numbers) encrypted at rest

### NFR-4: Reliability

- **Uptime**: 99.5% uptime target
- **Error Handling**: Graceful error handling with user-friendly messages
- **Data Backup**: Daily automated backups with 30-day retention
- **Disaster Recovery**: Recovery time objective (RTO) of 4 hours

### NFR-5: Scalability

- **Concurrent Users**: Support 100 concurrent users initially
- **Database**: MySQL with proper indexing and query optimization
- **Caching**: Memcached for horizontal scalability
- **Queue System**: Database-backed Laravel queues for background jobs

### NFR-6: Maintainability

- **Code Quality**: Clean, well-documented code following Laravel best practices
- **Testing**: Comprehensive test coverage (unit tests, feature tests, architecture tests with Pest)
- **Version Control**: Git repository with clear commit history
- **Documentation**: Technical documentation for all major components

### NFR-7: Compatibility

- **Browsers**: Support latest versions of Chrome, Firefox, Safari, Edge
- **Mobile**: iOS 14+, Android 10+
- **Screen Sizes**: Responsive from 320px (mobile) to 2560px (desktop)

### NFR-8: Compliance

- **UK Tax Rules**: Accurate implementation of all UK tax rules and regulations
- **Data Protection**: GDPR compliance for user data handling
- **Financial Regulations**: Disclaimer that FPS is for demonstration purposes only, not regulated financial advice

---

## User Experience Requirements

### UX-1: Onboarding

- **Registration**: Simple 3-step registration (email, password, basic info)
- **Profile Setup**: Guided wizard to complete initial profile
- **Module Introduction**: Brief introduction to each module with tooltips

### UX-2: Dashboard Experience

- **At-a-Glance View**: Main dashboard shows key metrics from all modules without scrolling
- **Clickable Cards**: Module cards are visually distinct and clearly clickable
- **Visual Hierarchy**: Most important information (scores, gaps, alerts) prominent and color-coded

### UX-3: Forms & Data Entry

- **Dynamic Forms**: Forms adapt based on user inputs (progressive disclosure)
- **Clear Labels**: All form fields have clear labels and help text
- **Validation**: Real-time validation with clear error messages
- **Save Progress**: Forms can be saved partially and completed later
- **Pre-filled Defaults**: Sensible defaults where applicable

### UX-4: Visualizations

- **ApexCharts**: All charts use ApexCharts for consistency and interactivity
- **Interactive**: Charts are interactive (hover for tooltips, zoom, pan)
- **Color-Coded**: Use traffic light colors for scores and status (green = good, amber = caution, red = critical)
- **Legends**: Clear legends and axis labels on all charts

### UX-5: Recommendations

- **Prioritized**: Recommendations clearly prioritized (Immediate, Short-term, Medium-term, Long-term)
- **Actionable**: Each recommendation has clear action steps
- **Quantified**: Impact of each recommendation quantified (£ saved, % improvement)
- **Sortable**: Users can reorder recommendations by preference (drag-and-drop)

### UX-6: What-If Scenarios

- **Interactive Sliders**: Sliders for adjusting parameters (contributions, returns, age, etc.)
- **Real-Time Updates**: Charts update in real-time as sliders adjust
- **Comparison View**: Side-by-side comparison of scenarios
- **Save Scenarios**: Users can save and name scenarios for later review

### UX-7: Mobile Experience

- **Touch-Optimized**: Buttons and interactive elements sized for touch
- **Swipeable Tabs**: Tabs swipeable on mobile
- **Collapsible Sections**: Accordions for long content on mobile
- **Simplified Views**: Simplified charts on small screens

---

## Success Criteria

### Quantitative Metrics

1. **User Engagement**:
   - Average session duration > 15 minutes
   - Users complete at least 3 out of 5 modules within first month

2. **Calculation Accuracy**:
   - 100% accuracy for tax calculations (verified against HMRC rules)
   - 100% accuracy for IHT calculations (verified against HMRC guidance)

3. **Performance**:
   - 95% of pages load within 2 seconds
   - 95% of calculations complete within 1 second

4. **Usability**:
   - User satisfaction score > 4.0/5.0
   - Task completion rate > 85%

### Qualitative Metrics

1. **User Feedback**: Positive feedback on clarity, usability, and actionability of recommendations
2. **Recommendation Quality**: Users find recommendations relevant and valuable
3. **Visual Appeal**: Users find dashboards and charts visually appealing and easy to interpret

---

## Timeline & Milestones

### Phase 1: Foundation (Weeks 1-3)
- ✅ Project setup (Laravel, Vue.js, MySQL, Memcached)
- ✅ User authentication
- ✅ Main dashboard layout
- ✅ Centralized UK tax configuration

### Phase 2: Protection Module (Weeks 4-6)
- ✅ Protection forms and data models
- ✅ Protection agent calculations
- ✅ Protection dashboard and visualizations
- ✅ Recommendations and scenarios

### Phase 3: Savings Module (Weeks 7-9)
- ✅ Savings forms and data models
- ✅ Savings agent calculations
- ✅ Savings dashboard and visualizations
- ✅ ISA allowance tracking

### Phase 4: Investment Module (Weeks 10-13)
- ✅ Investment forms and data models
- ✅ Investment agent calculations
- ✅ Monte Carlo simulations
- ✅ Investment dashboard and visualizations

### Phase 5: Retirement Module (Weeks 14-17)
- ✅ Retirement forms and data models
- ✅ Retirement agent calculations
- ✅ Decumulation planning
- ✅ Retirement dashboard and visualizations

### Phase 6: Estate Planning Module (Weeks 18-21)
- ✅ Estate forms and data models
- ✅ Estate agent calculations (IHT, net worth, P&L, cashflow)
- ✅ Gifting strategy and tracking
- ✅ Estate dashboard and visualizations

### Phase 7: Coordinating Agent (Week 22)
- ✅ Coordinating agent implementation
- ✅ Cross-module optimization
- ✅ Unified action plan

### Phase 8: Testing & Refinement (Weeks 23-24)
- ✅ Comprehensive testing (Pest unit, feature, architecture tests)
- ✅ API testing (Postman collections)
- ✅ Performance optimization
- ✅ UI/UX refinement
- ✅ Documentation

### Total Timeline: 24 weeks (6 months)

---

## Assumptions & Constraints

### Assumptions

1. Users have basic financial literacy
2. Users can provide accurate financial data (account balances, policy details)
3. UK tax rules are implemented accurately as of 2024/25 tax year
4. Internet connection available for web access

### Constraints

1. **UK Only**: System is designed exclusively for UK tax rules and products
2. **No Financial Advice**: FPS provides information and recommendations but is not regulated financial advice
3. **No External Integrations**: Demo version does not integrate with banks, investment platforms, or pension providers
4. **Manual Data Entry**: Users manually enter all financial data (no automated import)
5. **DB Pensions**: DB pension information captured for projection only - no DB to DC transfer advice

---

## Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| **Tax Rule Changes** | High | Medium | Centralized configuration allows quick updates |
| **Calculation Errors** | High | Low | Comprehensive unit testing with Pest |
| **Performance Issues (Monte Carlo)** | Medium | Medium | Background queues, caching, limit iterations to 1,000 |
| **User Data Security** | High | Low | HTTPS, encryption, CSRF protection, secure authentication |
| **Browser Compatibility** | Low | Low | Test on all major browsers, responsive design |

---

## Out of Scope (Future Enhancements)

1. **Bank/Platform Integrations**: Automated data import from financial institutions
2. **Real-Time Market Data**: Live investment prices and performance
3. **Document Management**: Upload and storage of policy documents (basic repository only)
4. **Multi-User/Advisor Access**: Advisor accounts to view multiple client portfolios
5. **Mobile Apps**: Native iOS/Android apps (web-first approach)
6. **AI Chatbot**: Conversational interface for data entry and questions
7. **Scenario Comparisons**: More than 2 scenarios compared side-by-side
8. **International Tax Rules**: Non-UK tax jurisdictions

---

## Glossary

- **Agent**: Intelligent module that processes user inputs and generates recommendations
- **APR**: Agricultural Property Relief (IHT relief for agricultural assets)
- **BPR**: Business Property Relief (IHT relief for business assets)
- **CGT**: Capital Gains Tax
- **CLT**: Chargeable Lifetime Transfer (gifts to trusts, subject to immediate IHT charge)
- **DB**: Defined Benefit pension
- **DC**: Defined Contribution pension
- **GIA**: General Investment Account (taxable)
- **IHT**: Inheritance Tax
- **ISA**: Individual Savings Account (tax-free wrapper)
- **LISA**: Lifetime ISA (£4,000 annual allowance, counts towards total ISA allowance)
- **LPA**: Lasting Power of Attorney
- **MPAA**: Money Purchase Annual Allowance (£10,000 - reduced annual allowance after pension access)
- **NRB**: Nil-Rate Band (£325,000 IHT threshold)
- **PCLS**: Pension Commencement Lump Sum (25% tax-free cash from pension)
- **PET**: Potentially Exempt Transfer (gifts to individuals, exempt if donor survives 7 years)
- **RNRB**: Residence Nil-Rate Band (£175,000 additional IHT threshold for main residence)
- **SIPP**: Self-Invested Personal Pension
- **VCT**: Venture Capital Trust (tax-efficient investment)

---

## Appendices

### Appendix A: UK Tax Year 2024/25 Key Figures

- **Personal Allowance**: £12,570
- **Basic Rate Tax**: 20% (up to £50,270)
- **Higher Rate Tax**: 40% (£50,271 to £125,140)
- **Additional Rate Tax**: 45% (above £125,140)
- **ISA Allowance**: £20,000
- **Pension Annual Allowance**: £60,000
- **CGT Allowance**: £3,000
- **Dividend Allowance**: £500
- **IHT Nil-Rate Band**: £325,000
- **IHT Residence Nil-Rate Band**: £175,000
- **IHT Rate**: 40%
- **Annual Gift Exemption**: £3,000
- **Full New State Pension**: £11,502.40 per year

### Appendix B: Calculation Examples

*Detailed calculation examples for each module will be documented during development.*

---

**End of Product Requirements Document**
