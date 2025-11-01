# Investment Module - Professional-Grade Tools & Analytics Addendum

**Project**: TenGo Financial Planning System
**Version**: 0.2.0 (Enhanced)
**Date**: November 1, 2025
**Addendum to**: INVESTMENT_FINANCIAL_PLANNING_EXPANSION.md

---

## Executive Summary

This addendum enhances the Investment Module expansion plan with **professional-grade quantitative tools and analytics** used by financial advisers, portfolio managers, and institutional investors. These tools transform TenGo into a **truly professional investment planning system** that goes beyond basic portfolio tracking.

### Professional Tools Being Added

1. **Modern Portfolio Theory (MPT) & Efficient Frontier Analysis**
2. **Linear Regression & Multi-Factor Analysis**
3. **Risk-Adjusted Performance Metrics Suite**
4. **Mean-Variance Optimization (Markowitz Model)**
5. **Correlation & Covariance Matrix Analysis**
6. **Advanced Risk Metrics (VaR, CVaR, Maximum Drawdown)**
7. **Factor Investing & Smart Beta Analysis**
8. **Goal-Based Investing with Glide Paths**
9. **Tax-Aware Portfolio Optimization**
10. **Liability-Driven Investment (LDI) Framework**

---

## Part 1: Quantitative Analytics & Portfolio Optimization

### 1.1 Modern Portfolio Theory (MPT) & Efficient Frontier

**Purpose**: Identify the optimal portfolio allocation that maximizes return for a given level of risk.

**Components to Build:**
- `EfficientFrontier.vue` (visualization component)
- `PortfolioOptimizer.vue` (optimization interface)
- Backend: `EfficientFrontierCalculator.php` service
- Backend: `MarkowitzOptimizer.php` service

**Features:**

**Efficient Frontier Calculation:**
- Plot efficient frontier curve (hyperbola)
- Identify minimum variance portfolio
- Calculate tangency portfolio (highest Sharpe ratio)
- Show current portfolio position relative to frontier
- Interactive point selection on frontier

**Capital Allocation Line (CAL):**
- Integrate risk-free rate (UK Gilts - currently ~4.5%)
- Calculate optimal portfolio mix (risky assets + risk-free)
- Show Sharpe ratio as slope of CAL

**Portfolio Optimization:**
```
Objective: Maximize (Expected Return - Risk Aversion × Variance)

Subject to:
- Sum of weights = 1 (fully invested)
- Weights >= 0 (no short selling, or allow with constraint)
- Optional: Minimum/maximum holding constraints
- Optional: Sector/geographic constraints
```

**Mathematical Foundation:**
```
Expected Portfolio Return: E(Rp) = Σ(wi × E(Ri))
Portfolio Variance: σp² = Σ Σ (wi × wj × σij)
Sharpe Ratio: (E(Rp) - Rf) / σp
```

**User Workflow:**
1. Select holdings to optimize
2. Set risk tolerance (conservative to aggressive)
3. Set constraints (min/max per holding, no short selling)
4. Run optimization algorithm
5. View efficient frontier with recommended allocation
6. Compare current vs. optimal allocation
7. Generate rebalancing trades

**Visualization:**
- X-axis: Portfolio Standard Deviation (Risk)
- Y-axis: Expected Return
- Points: Individual assets
- Curve: Efficient frontier
- Star: Current portfolio
- Diamond: Optimal portfolio for user's risk profile
- Line: Capital Allocation Line

**API Endpoints:**
```
POST   /api/investment/optimize/efficient-frontier
POST   /api/investment/optimize/markowitz
GET    /api/investment/optimize/current-position
POST   /api/investment/optimize/tangency-portfolio
```

---

### 1.2 Mean-Variance Optimization (Markowitz Model)

**Purpose**: Calculate optimal portfolio weights using mean-variance analysis.

**Components to Build:**
- Backend: `MeanVarianceOptimizer.php` service
- Backend: `QuadraticProgramming.php` (solver)

**Optimization Approaches:**

**1. Minimum Variance Portfolio:**
```
Minimize: σp²
Subject to: Σwi = 1, wi >= 0
```

**2. Maximum Sharpe Ratio:**
```
Maximize: (E(Rp) - Rf) / σp
Subject to: Σwi = 1, wi >= 0
```

**3. Target Return:**
```
Minimize: σp²
Subject to: E(Rp) = Target Return, Σwi = 1, wi >= 0
```

**4. Risk Parity:**
```
Equalize risk contribution from each asset
Weight inversely proportional to volatility
```

**Implementation:**
- Use quadratic programming for convex optimization
- Lagrange multiplier technique for constraints
- Consider using external library (e.g., PHP-ML or call Python service)

**Advanced Constraints:**
```
- Maximum concentration: wi <= 20% (prevent single holding dominance)
- Sector limits: Σ(wi for sector) <= 40%
- Geographic limits: UK <= 40%, US <= 40%, etc.
- Turnover constraint: Minimize transaction costs
- Tax constraint: Avoid realizing large gains
```

---

### 1.3 Correlation & Covariance Matrix Analysis

**Purpose**: Understand asset relationships for diversification.

**Components to Build:**
- `CorrelationMatrix.vue` (heatmap visualization)
- `CovarianceAnalysis.vue`
- Backend: `CovarianceMatrixCalculator.php` service

**Features:**

**Correlation Matrix:**
- Calculate pairwise correlations for all holdings
- Heatmap visualization (-1 to +1)
- Identify highly correlated assets (potential redundancy)
- Identify negative correlations (diversification opportunities)
- Time-period selection (1yr, 3yr, 5yr, 10yr)

**Covariance Matrix:**
- Calculate covariance matrix for all holdings
- Used as input for mean-variance optimization
- Show contribution to portfolio variance
- Identify risk concentrations

**Diversification Score:**
```
Diversification Ratio = (Σ(wi × σi)) / σp

Where:
- wi = weight of asset i
- σi = standard deviation of asset i
- σp = portfolio standard deviation

Score > 1 indicates diversification benefit
```

**Practical Applications:**
1. **Identify Redundant Holdings**: Correlation > 0.90
2. **Find Diversifiers**: Correlation < 0.30 or negative
3. **Sector Concentration**: High intra-sector correlation
4. **Geographic Diversification**: Cross-region correlations
5. **Asset Class Diversification**: Stocks vs. Bonds correlation

**Visualization:**
- Heatmap: Color-coded correlation matrix
- Network graph: Assets as nodes, correlation as edge thickness
- Dendrogram: Hierarchical clustering of correlated assets

---

### 1.4 Linear Regression & Multi-Factor Analysis

**Purpose**: Decompose portfolio returns and identify factor exposures.

**Components to Build:**
- `FactorAnalysis.vue`
- `RegressionAnalysis.vue`
- Backend: `LinearRegressionAnalyzer.php` service
- Backend: `FactorExposureCalculator.php` service

**Single-Factor Regression (CAPM):**
```
Ri = α + β × Rm + ε

Where:
- Ri = Return of asset i
- α (Alpha) = Excess return over expected
- β (Beta) = Sensitivity to market movements
- Rm = Market return (benchmark)
- ε = Residual/idiosyncratic risk
```

**Calculate for Each Holding:**
- Alpha: Outperformance vs. benchmark
- Beta: Market sensitivity (β > 1 = more volatile)
- R-squared: % of variance explained by market
- Standard error: Precision of estimates

**Multi-Factor Regression (Fama-French 3-Factor):**
```
Ri = α + β1×Rm + β2×SMB + β3×HML + ε

Factors:
- Rm: Market risk premium
- SMB: Size factor (Small Minus Big)
- HML: Value factor (High Minus Low book-to-market)
```

**Extended Models:**
- **Fama-French 5-Factor**: Add Profitability (RMW) and Investment (CMA)
- **Carhart 4-Factor**: Add Momentum (MOM) factor
- **Custom Factors**: ESG, Quality, Low Volatility

**Factor Exposure Analysis:**
```
Portfolio Factor Exposure = Σ(wi × βi,factor)

Example:
If Value factor β = 0.80:
- Positive exposure to value stocks
- Underweight growth stocks
- May underperform in growth rallies
```

**Practical Applications:**
1. **Style Drift Detection**: Has portfolio become more growth/value?
2. **Factor Timing**: Adjust exposure based on market cycle
3. **Risk Decomposition**: Attribute risk to specific factors
4. **Performance Attribution**: Explain returns via factors
5. **Smart Beta Validation**: Verify intended factor tilts

**API Endpoints:**
```
POST   /api/investment/analysis/regression
POST   /api/investment/analysis/multi-factor
GET    /api/investment/analysis/factor-exposure
POST   /api/investment/analysis/style-drift
```

---

## Part 2: Advanced Risk Metrics

### 2.1 Value at Risk (VaR) & Conditional VaR (CVaR)

**Purpose**: Quantify downside risk and tail risk.

**Components to Build:**
- `RiskMetrics.vue`
- `VaRAnalysis.vue`
- Backend: `VaRCalculator.php` service
- Backend: `CVaRCalculator.php` service

**Value at Risk (VaR):**

**Definition**: Maximum loss expected with X% confidence over Y time period.

**Example**: 95% confidence, 1-month VaR = £3,000
- "There is 95% confidence the portfolio will not lose more than £3,000 next month"
- 5% chance of losing more than £3,000

**Calculation Methods:**

**1. Historical Simulation:**
```
- Take historical returns (e.g., last 252 trading days)
- Sort returns from worst to best
- VaR at 95% = 5th percentile loss
- Simple, no distribution assumptions
```

**2. Variance-Covariance (Parametric):**
```
VaR = μ - (z × σ)

Where:
- μ = Expected return
- z = Z-score for confidence level (1.65 for 95%, 2.33 for 99%)
- σ = Portfolio standard deviation

Assumes normal distribution (may underestimate tail risk)
```

**3. Monte Carlo Simulation:**
```
- Run 10,000 simulations of portfolio returns
- Based on expected returns, volatility, correlations
- VaR = 5th percentile of simulated outcomes
- Most accurate, computationally intensive
```

**Conditional Value at Risk (CVaR / Expected Shortfall):**

**Definition**: Average loss in the worst X% of scenarios.

```
CVaR = Average of all losses exceeding VaR threshold

Example:
95% VaR = £3,000
95% CVaR = £5,200 (average loss when VaR is exceeded)
```

**Why CVaR is Superior to VaR:**
- Considers entire tail, not just cutoff point
- Subadditive (respects diversification)
- More conservative measure
- Preferred by regulators (Basel III)

**Risk Reporting:**
```
Confidence Levels:
- 90% VaR/CVaR (moderate confidence)
- 95% VaR/CVaR (standard)
- 99% VaR/CVaR (conservative, stress scenarios)

Time Horizons:
- 1-day (day traders)
- 1-month (typical for individuals)
- 1-year (long-term investors)
```

**User Interface:**
```
Current Portfolio Value: £250,000

Risk Metrics (95% confidence, 1-month):
┌─────────────────────────────────────┐
│ Value at Risk (VaR):     £7,250     │
│ Conditional VaR (CVaR):  £11,800    │
│                                     │
│ Interpretation:                     │
│ • 95% chance losses < £7,250        │
│ • In worst 5% cases, average        │
│   loss = £11,800                    │
└─────────────────────────────────────┘
```

**Stress Testing:**
- Calculate VaR/CVaR for market crash scenarios
- -20% market decline (2020 COVID)
- -40% market decline (2008 Financial Crisis)
- Sector-specific shocks

---

### 2.2 Maximum Drawdown & Downside Risk Metrics

**Purpose**: Measure peak-to-trough losses and asymmetric risk.

**Components to Build:**
- `DrawdownAnalysis.vue`
- Backend: `DrawdownCalculator.php` service

**Maximum Drawdown:**

**Definition**: Largest peak-to-trough decline before reaching new high.

```
MDD = (Trough Value - Peak Value) / Peak Value × 100%

Example:
Peak: £100,000 (Jan 2020)
Trough: £65,000 (Mar 2020)
Recovery: £110,000 (Nov 2020)

MDD = -35%
Recovery Time = 10 months
```

**Drawdown Metrics:**
- Current drawdown from peak
- Maximum drawdown (historical)
- Average drawdown
- Recovery time (time to new high)
- Drawdown frequency
- Deepest drawdowns (top 5 historical)

**Calmar Ratio:**
```
Calmar Ratio = Annualized Return / Maximum Drawdown

Example:
Annual Return: 8%
Max Drawdown: 20%
Calmar = 8% / 20% = 0.40

Higher is better (more return per unit of drawdown risk)
```

**Downside Deviation:**

**Definition**: Standard deviation of returns below target (e.g., 0% or risk-free rate).

```
Only measure volatility of negative returns
Ignores upside volatility (which investors like)

Downside Deviation = √(Σ(min(Ri - Target, 0)²) / n)
```

**Sortino Ratio:**
```
Sortino Ratio = (Return - Risk-Free Rate) / Downside Deviation

vs.

Sharpe Ratio = (Return - Risk-Free Rate) / Standard Deviation

Sortino is preferred for asymmetric distributions
```

**Practical Applications:**
1. **Risk Tolerance Assessment**: Can user stomach 30% drawdown?
2. **Portfolio Comparison**: Fund A (MDD: -15%) vs. Fund B (MDD: -25%)
3. **Recovery Planning**: How long to recover from potential drawdown?
4. **Stop-Loss Triggers**: Set alerts for specific drawdown levels
5. **Rebalancing Triggers**: Rebalance after 10% drawdown

---

### 2.3 Risk-Adjusted Performance Metrics Suite

**Purpose**: Evaluate returns relative to risk taken.

**Components to Build:**
- `RiskAdjustedReturns.vue`
- Backend: Enhanced `PerformanceAnalyzer.php` service

**Complete Metrics Suite:**

**1. Sharpe Ratio** (Already mentioned, expanded)
```
Sharpe = (Rp - Rf) / σp

Interpretation:
< 0: Underperforming risk-free rate
0-1: Subpar risk-adjusted returns
1-2: Good risk-adjusted returns
2-3: Very good
> 3: Excellent (rare)

Annualize: Multiply by √(periods per year)
Example: Monthly Sharpe × √12 = Annual Sharpe
```

**2. Sortino Ratio**
```
Sortino = (Rp - Rf) / σdownside

Focuses on downside risk only
Typically higher than Sharpe (ignores upside volatility)
```

**3. Information Ratio**
```
IR = (Rp - Rb) / Tracking Error

Where:
- Rb = Benchmark return
- Tracking Error = Std. dev. of (Rp - Rb)

Measures consistency of outperformance
IR > 0.50 is good, > 1.0 is excellent
```

**4. Treynor Ratio**
```
Treynor = (Rp - Rf) / β

Return per unit of systematic (market) risk
Useful for well-diversified portfolios
```

**5. Jensen's Alpha**
```
α = Rp - [Rf + β × (Rm - Rf)]

Excess return after adjusting for market risk
α > 0: Outperformance
α < 0: Underperformance
```

**6. M² (Modigliani-Modigliani Measure)**
```
M² = Rp × (σm / σp) + Rf × (1 - σm / σp)

Risk-adjusted return scaled to market volatility
Easier to interpret than Sharpe (in return units)
```

**7. Omega Ratio**
```
Ω = Probability-weighted gains / Probability-weighted losses

Ω > 1: Expected gains exceed losses
Considers entire distribution shape
```

**8. Calmar Ratio** (mentioned above)
```
Calmar = Annual Return / Maximum Drawdown
```

**Comparative Dashboard:**
```
┌──────────────────────────────────────────────────────┐
│ Risk-Adjusted Performance Metrics                    │
├──────────────────────────────────────────────────────┤
│ Metric          │ Portfolio │ Benchmark │ Difference │
├──────────────────────────────────────────────────────┤
│ Sharpe Ratio    │   1.42    │   1.18    │   +0.24   │
│ Sortino Ratio   │   2.08    │   1.65    │   +0.43   │
│ Information R.  │   0.68    │    —      │     —     │
│ Treynor Ratio   │   8.2%    │   6.9%    │   +1.3%   │
│ Jensen's Alpha  │  +2.1%    │    0%     │   +2.1%   │
│ Calmar Ratio    │   0.45    │   0.38    │   +0.07   │
│ Max Drawdown    │  -18.2%   │  -21.5%   │   +3.3%   │
└──────────────────────────────────────────────────────┘
```

---

## Part 3: Factor Investing & Smart Beta

### 3.1 Factor Exposure Analysis

**Purpose**: Identify portfolio exposure to systematic investment factors.

**Components to Build:**
- `FactorExposure.vue`
- `SmartBetaAnalysis.vue`
- Backend: `FactorAnalyzer.php` service

**Five Core Factors** (Proven across time, markets, asset classes):

**1. Value Factor**
```
Definition: Stocks trading below intrinsic value
Metrics: Low P/E, Low P/B, High Dividend Yield
Historical Premium: ~3-4% per year
Beta Calculation: Regression against HML (High Minus Low)

Example Holdings:
- UK: Lloyds, Legal & General, Persimmon
- US: JPMorgan, ExxonMobil, AT&T
```

**2. Momentum Factor**
```
Definition: Stocks with strong recent performance
Lookback: 6-12 months, excluding most recent month
Historical Premium: ~6-8% per year
Beta Calculation: Regression against UMD (Up Minus Down)

Example Holdings:
- Recent 12-month winners
- Avoid recent 1-month (reversal effect)
```

**3. Quality Factor**
```
Definition: Companies with strong fundamentals
Metrics: High ROE, Low Debt/Equity, Stable Earnings
Defensive: Outperforms in downturns
Beta Calculation: Regression against quality factor index

Example Holdings:
- UK: Unilever, Diageo, GSK
- US: Microsoft, J&J, Visa
```

**4. Size Factor**
```
Definition: Small-cap stocks outperform large-cap
Historical Premium: ~2-3% per year (diminished recently)
Beta Calculation: Regression against SMB (Small Minus Big)

Considerations:
- Higher risk (volatility, liquidity)
- Tax implications (higher turnover)
```

**5. Low Volatility Factor**
```
Definition: Stocks with below-market volatility
Paradox: Lower risk, similar/higher returns
Defensive: Outperforms in bear markets
Beta Calculation: Regression against low-vol index

Example Holdings:
- Utilities, consumer staples, healthcare
- Avoid: Technology, small-cap, emerging markets
```

**Factor Exposure Report:**
```
┌─────────────────────────────────────────────────┐
│ Portfolio Factor Exposure                       │
├─────────────────────────────────────────────────┤
│ Factor        │ Beta  │ Exposure │ Interpretation │
├─────────────────────────────────────────────────┤
│ Market (β)    │ 1.05  │  Neutral │ Market-like    │
│ Value (HML)   │ 0.32  │  Moderate│ Value tilt     │
│ Momentum      │ -0.08 │  Low     │ Slight anti-M  │
│ Quality       │ 0.58  │  High    │ Quality focus  │
│ Size (SMB)    │ -0.15 │  Low     │ Large-cap bias │
│ Low Volatility│ 0.41  │  Moderate│ Defensive tilt │
└─────────────────────────────────────────────────┘

Strategy Implications:
✓ Quality + Value combination (good)
✓ Low momentum exposure (market-neutral)
⚠ Large-cap bias (missing small-cap premium)
```

**Multi-Factor Portfolio Construction:**
```
Equal Risk Contribution:
- 20% exposure to each of 5 factors
- Diversification across factor premiums
- Reduce single-factor risk

Dynamic Factor Allocation:
- Overweight factors with strong recent momentum
- Rotate based on economic cycle
- Value (early cycle), Growth (mid cycle), Defensive (late cycle)
```

---

### 3.2 Smart Beta Strategies

**Purpose**: Systematic factor-based strategies beyond market-cap weighting.

**Components to Build:**
- `SmartBetaBuilder.vue`
- Backend: `SmartBetaOptimizer.php` service

**Smart Beta Approaches:**

**1. Equal Weighting**
```
Weight = 1 / N for each of N holdings

vs. Market-Cap Weighting (concentration risk)

Benefits:
- Reduces concentration in mega-caps
- Implicitly tilts to size and value factors
- Higher turnover (rebalancing required)
```

**2. Fundamental Weighting**
```
Weight by fundamentals:
- Revenue
- Earnings
- Book value
- Dividends

Benefits:
- Value tilt (avoid overpriced stocks)
- Mean reversion
```

**3. Risk Parity**
```
Equal risk contribution from each holding

Weight_i = 1 / σi (inversely proportional to volatility)

Benefits:
- Balanced risk exposure
- No single holding dominates risk
- Can use leverage for lower-vol assets
```

**4. Minimum Variance**
```
Minimize portfolio variance (from mean-variance optimization)

Benefits:
- Low volatility factor exposure
- Downside protection
- Lower returns in bull markets
```

**5. Maximum Diversification**
```
Maximize Diversification Ratio:
DR = (Σ wi σi) / σp

Benefits:
- Capture diversification benefit
- Reduce concentration risk
```

**6. Maximum Sharpe Ratio**
```
Tangency portfolio from efficient frontier

Benefits:
- Theoretically optimal
- Best risk-adjusted returns
- Requires accurate expected returns (difficult)
```

**Implementation Example:**
```
User Workflow:
1. Select holdings eligible for strategy
2. Choose smart beta approach (e.g., Risk Parity)
3. Set constraints (min/max weights)
4. Calculate optimal weights
5. Compare to current market-cap weighting
6. Generate rebalancing trades
7. Backtest historical performance
```

---

## Part 4: Goal-Based Investing & Glide Paths

### 4.1 Goal-Based Asset Allocation

**Purpose**: Align portfolio allocation with specific financial goals and time horizons.

**Components to Build:**
- `GoalBasedAllocation.vue`
- `GlidePathVisualizer.vue`
- Backend: `GoalBasedAllocator.php` service
- Backend: `GlidePathGenerator.php` service

**Goal-Based Framework:**

**Classify Goals by:**
1. **Time Horizon**: 0-5 years (short), 5-10 (medium), 10+ (long)
2. **Priority**: Essential vs. Aspirational
3. **Flexibility**: Fixed (house deposit) vs. Flexible (retirement)
4. **Liability Type**: Lump sum (house) vs. Income stream (retirement)

**Asset Allocation by Goal:**

**Short-Term Goals (0-5 years): House Deposit, Education**
```
Allocation: 30/70 Equity/Bonds (Conservative)

Rationale:
- Limited time for recovery from losses
- Liquidity needed
- Capital preservation priority

Holdings:
- Bonds: UK Gilts, Investment-Grade Corporate
- Stocks: Large-cap, dividend-paying, defensive sectors
- Cash: 20-30% for liquidity
```

**Medium-Term Goals (5-10 years): Child's University**
```
Allocation: 60/40 Equity/Bonds (Balanced)

Rationale:
- Some time for growth
- Gradual de-risking as goal approaches
- Balance growth and protection

Glide Path:
Year 1-3: 70/30
Year 4-6: 60/40
Year 7-8: 50/50
Year 9-10: 40/60
```

**Long-Term Goals (10+ years): Retirement, Wealth Building**
```
Allocation: 80/20 or 90/10 Equity/Bonds (Aggressive)

Rationale:
- Long time horizon absorbs volatility
- Equity premium compounds
- Can recover from downturns

Holdings:
- Diverse equities: Global, emerging markets, small-cap
- Growth-oriented: Technology, innovation
- Minimal bonds (growth phase)
```

**Liability-Driven Investment (LDI) Approach:**

**For Fixed Liabilities (e.g., £30k university fees in 10 years):**
```
Calculate Present Value:
PV = £30,000 / (1.04)^10 = £20,273 (assuming 4% discount rate)

Investment Strategy:
1. Match liability with bond duration
2. Buy 10-year gilt with £20,273 face value
3. Eliminates market risk (liability hedged)
4. OR: Invest in growth portfolio + glide path to de-risk
```

**Example: Education Goal (£30k in 10 years)**
```
Current Value: £15,000
Required Return: 7.2% per year

Strategy:
Years 1-5: Aggressive Growth Portfolio (90/10)
- Target: 9-10% return
- Accept volatility
- Build capital

Years 6-8: Balanced Portfolio (60/40)
- De-risk as goal approaches
- 7-8% return target
- Reduce drawdown risk

Years 9-10: Conservative Portfolio (30/70)
- Capital preservation
- 4-5% return target
- High liquidity (bonds mature, low-vol stocks)
```

---

### 4.2 Dynamic Glide Paths

**Purpose**: Automate asset allocation changes as goals approach.

**Components to Build:**
- `GlidePathDesigner.vue`
- Backend: `GlidePathEngine.php` service

**Glide Path Types:**

**1. Time-Based (Linear):**
```
Equity Allocation = Starting% - (Years Elapsed × Annual Reduction)

Example: Retirement in 20 years
Start: 90% equity
End: 30% equity
Annual Reduction: 3% per year

Year 1: 90%
Year 5: 75%
Year 10: 60%
Year 15: 45%
Year 20: 30%
```

**2. Time-Based (Non-Linear):**
```
Slower de-risking early, faster as goal approaches

Example:
Years 1-10: -2% per year (90% → 70%)
Years 11-15: -4% per year (70% → 50%)
Years 16-20: -5% per year (50% → 30%)

Rationale:
- Early: Maximize growth while time permits
- Late: Aggressive protection as goal nears
```

**3. Funding-Based (Dynamic):**
```
Adjust glide path based on funding status

Funding Ratio = Current Value / Goal Target

If Funding Ratio > 100%: De-risk faster (goal achieved)
If Funding Ratio < 80%: Stay aggressive longer (need growth)
If Funding Ratio = 90-100%: Follow planned glide path

Example:
Goal: £100k in 10 years
Current Value: £120k (120% funded)
Action: Shift to 40/60 immediately (lock in surplus)
```

**4. Volatility-Controlled:**
```
Maintain target portfolio volatility

Target: 12% annual volatility
Current: 15% → Reduce equity, add bonds
Current: 9% → Increase equity (if time horizon allows)

Dynamic Rebalancing:
- Monitor realized volatility monthly
- Adjust allocation to maintain target
```

**5. Trigger-Based:**
```
Milestones trigger allocation shifts

Retirement Glide Path:
Age 45 (20 years out): 90/10
Age 50 (15 years out): 80/20
Age 55 (10 years out): 70/30 (trigger: 10-year milestone)
Age 60 (5 years out): 50/50 (trigger: 5-year milestone)
Age 65 (retirement): 40/60 (trigger: retirement date)
```

**Visualization:**
```
┌─────────────────────────────────────────────────┐
│ Retirement Glide Path (20 years)               │
├─────────────────────────────────────────────────┤
│                                                 │
│ 100% ┤                                          │
│      │ ■■■■■■■■                                │
│  80% ┤        ■■■■■■■                          │
│      │               ■■■■■■                    │
│  60% ┤                     ■■■■■              │
│      │                          ■■■■          │
│  40% ┤                              ■■■       │
│      │                                 ■■     │
│  20% ┤                                   ■■   │
│      │                                      ■ │
│   0% └─────────────────────────────────────── │
│      Now   5yr   10yr   15yr   20yr Retire   │
│                                                 │
│ ■ Equity Allocation    □ Bond Allocation       │
└─────────────────────────────────────────────────┘
```

**Rebalancing Rules:**
- Check allocation quarterly
- Rebalance if drift > 5% from glide path
- Tax-aware rebalancing (use new contributions first)
- Gradual shifts (avoid market timing)

---

## Part 5: Tax-Aware Portfolio Optimization

### 5.1 Tax-Loss Harvesting Engine

**Purpose**: Systematically realize capital losses to offset gains and reduce tax.

**Components to Build:**
- `TaxLossHarvester.vue`
- Backend: `TaxLossHarvestingEngine.php` service
- Backend: `WashSaleChecker.php` service

**Tax-Loss Harvesting Process:**

**Step 1: Identify Candidates**
```
Scan all holdings for unrealized losses

Criteria:
- Current Value < Purchase Cost Basis
- Loss > Minimum Threshold (e.g., £500)
- Holding Period > 30 days (avoid wash sale)

Example:
Holding: Vanguard FTSE 100 ETF
Purchase Price: £5,000 (50 units @ £100)
Current Value: £4,200 (50 units @ £84)
Unrealized Loss: £800 ✓ (candidate)
```

**Step 2: Avoid Wash Sale Rule**
```
UK: 30-Day Rule (Bed & Breakfast)
- Cannot repurchase same/substantially identical security
- Within 30 days before or after sale

US: Similar 30-day rule (if applicable for international holdings)

Strategies:
1. Wait 31 days to repurchase (market risk)
2. Buy similar but different security immediately
3. Double-up (buy more, wait 31 days, sell original)
```

**Step 3: Find Replacement Securities**
```
Replacement must:
- Similar asset class exposure
- Different enough to avoid wash sale
- Low correlation (< 0.95)

Example Replacements:
Sell: Vanguard FTSE 100 ETF
Buy:  iShares FTSE 100 ETF (different provider)
Buy:  Vanguard FTSE 250 ETF (different index)
Buy:  UK Large-Cap Active Fund (different strategy)

Maintain exposure while harvesting loss
```

**Step 4: Calculate Tax Benefit**
```
Realized Loss: £800

Tax Savings:
- Offset capital gains elsewhere: £800 × 20% CGT = £160
- OR: Carry forward to future years
- OR: Offset income (up to £3,000 in US, not UK)

Compound Benefit:
Reinvest £160 tax savings → Additional growth
Over 20 years at 7%: £160 → £619
```

**Step 5: Track Cost Basis**
```
New cost basis = Replacement purchase price

Original: 50 units @ £100 = £5,000 basis
Sold: £4,200 (realized £800 loss)
Bought Replacement: 50 units @ £84 = £4,200 basis

Future Gain/Loss calculated from new £4,200 basis
```

**Automated Harvesting Strategy:**
```
Frequency: Monthly or Quarterly scan
Threshold: Harvest losses > £500
Replacement: Automatic suggestion of similar securities
Wash Sale: Automatic tracking and alerts

Reporting:
- YTD harvested losses: £2,400
- Tax savings (20% CGT): £480
- Holdings replaced: 5
- Wash sales avoided: 2 (auto-delayed)
```

---

### 5.2 Tax-Efficient Asset Location

**Purpose**: Optimize which assets go in which tax wrapper (ISA vs. GIA vs. Pension).

**Components to Build:**
- `AssetLocationOptimizer.vue`
- Backend: `AssetLocationOptimizer.php` service

**UK Tax Wrappers:**

**ISA (Individual Savings Account):**
```
Benefits:
- Tax-free dividends and interest
- Tax-free capital gains
- No CGT on rebalancing
- Withdrawals tax-free anytime

Limits:
- £20,000 annual contribution
- Must be individual ownership (not joint)

Optimal Holdings for ISA:
1. High-dividend stocks/funds (20% tax savings)
2. Actively traded holdings (no CGT on rebalancing)
3. High-turnover strategies (no CGT on internal trades)
4. Bonds (tax-free interest)
5. REITs (dividends taxed as income, 45% for high earners)
```

**GIA (General Investment Account):**
```
Tax Treatment:
- Dividends: £1,000 allowance, then 8.75%/33.75%/39.35%
- Capital Gains: £6,000 allowance, then 10%/20%
- Interest: Taxed as income

Limits:
- No contribution limit
- Joint ownership possible

Optimal Holdings for GIA:
1. Index funds (low turnover, minimal distributions)
2. Growth stocks (no dividends, only CGT on sale)
3. Accumulation funds (defer dividends)
4. Holdings under £6,000 per year in gains (use allowance)
```

**Pension (SIPP):**
```
Benefits:
- Tax relief on contributions (20%/40%/45%)
- Tax-free growth (no income tax or CGT)
- 25% tax-free lump sum on withdrawal

Limits:
- £60,000 annual allowance
- Locked until age 55 (rising to 57)

Optimal Holdings for Pension:
1. Maximum growth assets (long time horizon)
2. High-income bonds (tax drag eliminated)
3. International stocks (avoid dividend withholding)
4. Alternative assets (property, commodities)
```

**Asset Location Strategy:**

**Priority 1: ISA (£20,000 annual allowance)**
```
Fill ISA with:
1. High-dividend UK equity funds (FTSE 100, dividends ~4%)
2. REITs (distributions taxed as income, 45% for higher earners)
3. Bonds (interest fully taxable in GIA)
4. Actively managed funds (high turnover → CGT in GIA)

Tax Alpha: ~1-2% per year vs. holding in GIA
```

**Priority 2: Pension (£60,000 annual allowance)**
```
Fill Pension with:
1. Highest-growth equity funds (maximize compounding)
2. Corporate bonds (high income, fully taxable in GIA)
3. International equities (avoid US dividend withholding*)
4. Emerging markets (high growth + high turnover)

*UK/US tax treaty: 15% WHT on US dividends (unavoidable in all wrappers)

Tax Alpha: 20-45% upfront (tax relief) + 0.5-1.5% per year (growth)
```

**Priority 3: GIA (unlimited)**
```
Remaining holdings:
1. Low-turnover index funds (FTSE All-Share, S&P 500)
2. Accumulation funds (defer distributions)
3. Growth stocks (tech, minimal dividends)
4. ETFs (tax-efficient structure)

Strategies:
- Harvest losses to offset gains
- Spread sales across tax years (use £6,000 CGT allowance)
- Gift to spouse (utilize both CGT allowances)
```

**Example Optimization:**

**Before Optimization:**
```
ISA (£100k):
- Vanguard LS60 (60/40 fund, moderate dividends)

GIA (£100k):
- FTSE 100 High Dividend Fund (dividends ~5%)
- UK REITs (distributions ~6%)

Annual Tax: ~£1,500 (dividends) + £200 (REITs) = £1,700
```

**After Optimization:**
```
ISA (£100k):
- FTSE 100 High Dividend Fund (£60k)
- UK REITs (£40k)

GIA (£100k):
- Vanguard Global All Cap Index (accumulation)
- Growth stocks (low/no dividends)

Annual Tax: ~£200 (minimal distributions)
Annual Tax Savings: £1,500 × 20 years = £30,000 + compounding
```

**Dynamic Rebalancing:**
```
When Rebalancing:
1. Execute in ISA first (no tax impact)
2. Use new contributions to rebalance (avoid selling in GIA)
3. If must sell in GIA:
   - Harvest losses first
   - Sell winners under £6,000 gain (use CGT allowance)
   - Spread sales across tax years
```

---

### 5.3 Tax-Aware Rebalancing

**Purpose**: Rebalance portfolio while minimizing tax impact.

**Components to Build:**
- `TaxAwareRebalancer.vue`
- Backend: Enhanced `TaxAwareRebalancer.php` service

**Rebalancing Methods (Tax-Aware):**

**Method 1: Contribution-Based Rebalancing**
```
Use new contributions to restore target allocation
No selling required → No CGT

Example:
Target: 60/40 Stocks/Bonds
Current: £100k (70/30 - £70k stocks, £30k bonds)
New Contribution: £10k

Tax-Aware Action:
Buy £10k bonds → New: £110k (64/36)
Closer to 60/40 without triggering CGT
```

**Method 2: Wash Sale Avoidance**
```
When selling losers:
- Ensure 31-day gap before repurchasing same security
- Buy similar security immediately (maintain exposure)

Example:
Overweight Tech: Hold NASDAQ 100 ETF (at loss)
Action:
1. Sell NASDAQ 100 ETF → Realize £1,000 loss
2. Buy S&P 500 ETF immediately (similar exposure)
3. Wait 31 days
4. Switch back to NASDAQ 100 if desired
```

**Method 3: Tax-Loss Harvesting During Rebalancing**
```
When rebalancing, prioritize:
1. Sell losers first → Harvest losses
2. Use losses to offset winners that need selling
3. Minimize net CGT liability

Example:
Need to sell: £10k stocks, £5k bonds
Holdings:
- Stock A: £5k gain
- Stock B: £3k loss
- Bond C: £2k gain
- Bond D: £1k loss

Tax-Aware Actions:
1. Sell Stock B (£3k loss) + Stock A partial (£3k gain) → Net £0 CGT
2. Sell Bond D (£1k loss) + Bond C partial (£1k gain) → Net £0 CGT
3. Carry forward any unused losses
```

**Method 4: Tolerance Band Rebalancing**
```
Only rebalance when drift exceeds threshold
Reduces transaction costs and taxes

Example:
Target: 60/40 Stocks/Bonds
Tolerance Band: ±5%
Rebalance only if: Stocks < 55% or > 65%

Current: 62/38 → No action (within band)
Current: 67/33 → Rebalance (exceeded threshold)
```

**Method 5: Preferential Lot Selection**
```
When selling, choose specific lots to minimize CGT

FIFO: First In, First Out (default)
LIFO: Last In, First Out
HIFO: Highest In, First Out (minimize gain)
Specific Lot: Manually select

Example:
Need to sell 100 shares of Stock A:

Lot 1: 50 shares @ £10 (£2 gain per share = £100 gain)
Lot 2: 50 shares @ £11 (£1 gain per share = £50 gain)
Lot 3: 50 shares @ £13 (£1 loss per share = -£50 loss)

Tax-Aware Selection:
1. Sell Lot 3 first (realize £50 loss)
2. Sell Lot 2 next (realize £50 gain)
3. Net: £0 CGT (loss offset gain)

vs. FIFO: Sell Lot 1 (£100 gain) + Lot 2 (£50 gain) = £150 CGT
```

**Rebalancing Decision Engine:**
```
Inputs:
- Current allocation vs. target
- Unrealized gains/losses per holding
- CGT allowance remaining (£6,000)
- Available cash for contributions
- Tax wrappers (ISA/GIA/Pension)

Algorithm:
1. Calculate required trades for target allocation
2. Check if contribution-only rebalancing sufficient
3. If selling required:
   a. Sell losers first (tax loss harvesting)
   b. Match losers with winners (offset gains)
   c. Use CGT allowance (£6,000)
   d. Defer excess gains to next tax year
4. Execute trades in tax-optimal order:
   - ISA rebalancing (no tax, do first)
   - GIA rebalancing (tax-aware, do last)
   - Pension rebalancing (no tax, anytime)

Output:
- Specific trade list with tax implications
- Estimated CGT liability
- Tax-loss harvesting opportunities
- Recommendations for next tax year
```

---

## Part 6: Implementation Priorities & Enhancements

### 6.1 High-Priority Professional Tools (Weeks 1-4)

**Week 1-2: Core Quantitative Analytics**
- [ ] Efficient Frontier Calculator
- [ ] Markowitz Mean-Variance Optimizer
- [ ] Correlation/Covariance Matrix Analysis
- [ ] Portfolio Optimization Interface

**Week 3-4: Risk Metrics & Performance**
- [ ] VaR/CVaR Calculator (3 methods)
- [ ] Maximum Drawdown Analysis
- [ ] Complete Risk-Adjusted Returns Suite (Sharpe, Sortino, IR, etc.)
- [ ] Linear Regression & Beta Calculation

### 6.2 Medium-Priority Tools (Weeks 5-7)

**Week 5: Factor Analysis**
- [ ] Multi-Factor Regression (Fama-French)
- [ ] Factor Exposure Calculator
- [ ] Smart Beta Strategy Builder

**Week 6: Goal-Based & Tax Optimization**
- [ ] Goal-Based Asset Allocator
- [ ] Glide Path Generator
- [ ] Tax-Loss Harvesting Engine
- [ ] Asset Location Optimizer

**Week 7: Advanced Features**
- [ ] Tax-Aware Rebalancer
- [ ] Dynamic Glide Paths (funding-based)
- [ ] Liability-Driven Investment Framework

### 6.3 Enhanced Database Schema

**Additional Tables:**

```sql
-- Store efficient frontier calculations
CREATE TABLE efficient_frontier_calculations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    calculation_date DATE NOT NULL,
    holdings_snapshot JSON NOT NULL,
    frontier_points JSON NOT NULL,  -- Array of {return, risk} points
    tangency_portfolio JSON NOT NULL,
    min_variance_portfolio JSON NOT NULL,
    current_portfolio_position JSON NOT NULL,
    risk_free_rate DECIMAL(5,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, calculation_date)
);

-- Store factor exposures
CREATE TABLE factor_exposures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    holding_id BIGINT UNSIGNED,
    analysis_date DATE NOT NULL,
    market_beta DECIMAL(6,4),
    alpha DECIMAL(6,4),
    r_squared DECIMAL(5,4),
    value_factor DECIMAL(6,4),
    size_factor DECIMAL(6,4),
    momentum_factor DECIMAL(6,4),
    quality_factor DECIMAL(6,4),
    low_vol_factor DECIMAL(6,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, analysis_date)
);

-- Store risk metrics
CREATE TABLE risk_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    calculation_date DATE NOT NULL,
    portfolio_value DECIMAL(15,2),
    var_95_1month DECIMAL(15,2),
    cvar_95_1month DECIMAL(15,2),
    var_99_1month DECIMAL(15,2),
    cvar_99_1month DECIMAL(15,2),
    max_drawdown DECIMAL(5,2),
    current_drawdown DECIMAL(5,2),
    sharpe_ratio DECIMAL(6,4),
    sortino_ratio DECIMAL(6,4),
    calmar_ratio DECIMAL(6,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, calculation_date)
);

-- Store tax-loss harvesting opportunities
CREATE TABLE tax_loss_harvesting_opportunities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    holding_id BIGINT UNSIGNED NOT NULL,
    identified_date DATE NOT NULL,
    unrealized_loss DECIMAL(10,2),
    potential_tax_saving DECIMAL(10,2),
    replacement_suggestions JSON,
    wash_sale_cleared_date DATE,
    status VARCHAR(20) DEFAULT 'pending',  -- pending, executed, dismissed
    executed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (holding_id) REFERENCES holdings(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
);

-- Store glide path configurations
CREATE TABLE glide_paths (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    investment_goal_id BIGINT UNSIGNED NOT NULL,
    glide_path_type VARCHAR(50) NOT NULL,  -- linear, non-linear, funding-based, volatility-controlled
    starting_equity_percent DECIMAL(5,2) NOT NULL,
    ending_equity_percent DECIMAL(5,2) NOT NULL,
    parameters JSON,  -- Type-specific parameters
    milestones JSON,  -- Array of {date, target_equity_percent}
    auto_rebalance BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (investment_goal_id) REFERENCES investment_goals(id) ON DELETE CASCADE
);
```

---

## Part 7: API Endpoints (Complete List)

### Quantitative Analytics
```
POST   /api/investment/analytics/efficient-frontier
POST   /api/investment/analytics/optimize-portfolio
POST   /api/investment/analytics/correlation-matrix
POST   /api/investment/analytics/covariance-matrix
GET    /api/investment/analytics/diversification-score
```

### Risk Metrics
```
POST   /api/investment/risk/var-cvar
GET    /api/investment/risk/maximum-drawdown
GET    /api/investment/risk/downside-deviation
POST   /api/investment/risk/stress-test
GET    /api/investment/risk/risk-adjusted-returns
```

### Factor Analysis
```
POST   /api/investment/factors/regression-analysis
POST   /api/investment/factors/multi-factor-analysis
GET    /api/investment/factors/factor-exposure
POST   /api/investment/factors/style-drift
```

### Smart Beta
```
POST   /api/investment/smart-beta/equal-weight
POST   /api/investment/smart-beta/risk-parity
POST   /api/investment/smart-beta/minimum-variance
POST   /api/investment/smart-beta/maximum-sharpe
POST   /api/investment/smart-beta/fundamental-weight
```

### Goal-Based Investing
```
POST   /api/investment/goals/:id/glide-path
PUT    /api/investment/goals/:id/glide-path
GET    /api/investment/goals/:id/glide-path/status
POST   /api/investment/goals/:id/allocation-optimization
```

### Tax Optimization
```
GET    /api/investment/tax/harvest-opportunities
POST   /api/investment/tax/harvest-execute
GET    /api/investment/tax/wash-sale-check
POST   /api/investment/tax/asset-location-optimize
POST   /api/investment/tax/rebalance-tax-aware
GET    /api/investment/tax/cgt-forecast
```

---

## Part 8: Service Architecture (Complete)

```
app/Services/Investment/

├── Analytics/
│   ├── EfficientFrontierCalculator.php
│   ├── MarkowitzOptimizer.php
│   ├── CorrelationMatrixCalculator.php
│   ├── CovarianceMatrixCalculator.php
│   └── DiversificationScorer.php
│
├── Risk/
│   ├── VaRCalculator.php (Historical, Parametric, Monte Carlo)
│   ├── CVaRCalculator.php
│   ├── DrawdownAnalyzer.php
│   ├── DownsideRiskCalculator.php
│   └── RiskAdjustedReturnsCalculator.php
│
├── Factors/
│   ├── LinearRegressionAnalyzer.php
│   ├── MultiFactorAnalyzer.php
│   ├── FactorExposureCalculator.php
│   └── StyleDriftDetector.php
│
├── SmartBeta/
│   ├── EqualWeightOptimizer.php
│   ├── RiskParityOptimizer.php
│   ├── MinimumVarianceOptimizer.php
│   ├── MaximumSharpeOptimizer.php
│   ├── FundamentalWeightOptimizer.php
│   └── MaximumDiversificationOptimizer.php
│
├── GoalBased/
│   ├── GoalBasedAllocator.php
│   ├── GlidePathGenerator.php
│   ├── GlidePathEngine.php (auto-rebalancing)
│   └── LiabilityDrivenInvestor.php
│
├── TaxOptimization/
│   ├── TaxLossHarvestingEngine.php
│   ├── WashSaleChecker.php
│   ├── AssetLocationOptimizer.php
│   ├── TaxAwareRebalancer.php
│   ├── CGTForecaster.php
│   └── LotSelectionOptimizer.php
│
└── Utilities/
    ├── QuadraticProgramming.php (for optimization)
    ├── MatrixOperations.php (linear algebra)
    └── StatisticalFunctions.php (regression, correlation, etc.)
```

---

## Part 9: Testing Requirements

### Unit Tests (100+ new tests)

```
tests/Unit/Services/Investment/Analytics/
├── EfficientFrontierCalculatorTest.php
├── MarkowitzOptimizerTest.php
└── CorrelationMatrixCalculatorTest.php

tests/Unit/Services/Investment/Risk/
├── VaRCalculatorTest.php
├── CVaRCalculatorTest.php
└── DrawdownAnalyzerTest.php

tests/Unit/Services/Investment/Factors/
├── LinearRegressionAnalyzerTest.php
└── MultiFactorAnalyzerTest.php

tests/Unit/Services/Investment/SmartBeta/
├── RiskParityOptimizerTest.php
└── MinimumVarianceOptimizerTest.php

tests/Unit/Services/Investment/GoalBased/
├── GlidePathGeneratorTest.php
└── GoalBasedAllocatorTest.php

tests/Unit/Services/Investment/TaxOptimization/
├── TaxLossHarvestingEngineTest.php
├── WashSaleCheckerTest.php
└── AssetLocationOptimizerTest.php
```

### Feature Tests

```
tests/Feature/Investment/
├── EfficientFrontierAnalysisTest.php
├── PortfolioOptimizationTest.php
├── RiskMetricsCalculationTest.php
├── FactorAnalysisTest.php
├── SmartBetaStrategyTest.php
├── GlidePathManagementTest.php
└── TaxOptimizationTest.php
```

---

## Part 10: External Dependencies & Libraries

### PHP Libraries to Consider:

```composer
"require": {
    // Existing
    "php": "^8.1",
    "laravel/framework": "^10.10",

    // NEW: Mathematical/Statistical
    "markrogoyski/math-php": "^2.9",  // Linear algebra, statistics
    "php-ai/php-ml": "^0.10",         // Machine learning (optional)

    // NEW: Optimization (if available, or build custom)
    // OR: Call Python microservice for scipy.optimize
}
```

### JavaScript Libraries (Frontend):

```json
"dependencies": {
    // Existing
    "vue": "^3.5.22",
    "apexcharts": "^5.3.5",

    // NEW: Advanced Charting
    "d3": "^7.9.0",                   // For correlation matrix heatmap
    "plotly.js": "^2.35.0",           // 3D efficient frontier plots

    // NEW: Matrix Operations (client-side)
    "mathjs": "^12.4.0",              // Matrix calculations
    "simple-statistics": "^7.8.3"     // Statistical functions
}
```

### Optional: Python Microservice for Heavy Computation

```python
# requirements.txt
numpy>=1.26.0
scipy>=1.11.0      # Optimization (scipy.optimize.minimize)
pandas>=2.1.0
scikit-learn>=1.3.0
statsmodels>=0.14.0

# Expose via Flask/FastAPI
# Investment module calls via HTTP for:
# - Mean-variance optimization (quadratic programming)
# - Multi-factor regression
# - Monte Carlo simulations (already in Laravel, but could move)
```

---

## Part 11: User Experience & Visualization

### Dashboard Enhancements

**Investment Dashboard - New "Analytics" Tab:**
```
┌──────────────────────────────────────────────────────┐
│ Investment Analytics & Optimization                  │
├──────────────────────────────────────────────────────┤
│                                                      │
│ Portfolio Health Score: 82/100                       │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│                                                      │
│ Quick Actions:                                       │
│ [Optimize Portfolio] [View Efficient Frontier]      │
│ [Run Tax Harvesting] [Analyze Risk Metrics]         │
│                                                      │
│ ┌────────────────────┬────────────────────────────┐ │
│ │ Risk-Adjusted      │ Factor Exposures           │ │
│ │ Returns            │                            │ │
│ │                    │ Market: 1.05 (Neutral)     │ │
│ │ Sharpe: 1.42       │ Value:  0.32 (Moderate)    │ │
│ │ Sortino: 2.08      │ Quality: 0.58 (High)       │ │
│ │ Calmar: 0.45       │ Size:   -0.15 (Large-cap)  │ │
│ └────────────────────┴────────────────────────────┘ │
│                                                      │
│ ┌────────────────────┬────────────────────────────┐ │
│ │ Risk Metrics       │ Tax Optimization           │ │
│ │                    │                            │ │
│ │ VaR (95%): £7,250  │ Harvest Opps: 3            │ │
│ │ Max DD: -18.2%     │ Potential Saving: £840     │ │
│ │ Current DD: -2.1%  │ ISA Efficiency: 87%        │ │
│ └────────────────────┴────────────────────────────┘ │
│                                                      │
│ Recent Optimizations:                                │
│ • Portfolio rebalanced (2 days ago)                  │
│ • Tax loss harvested: £1,200 (1 week ago)           │
│ • Glide path adjusted for Goal #2 (3 weeks ago)     │
└──────────────────────────────────────────────────────┘
```

### Efficient Frontier Visualization

```
┌─────────────────────────────────────────────────────┐
│ Efficient Frontier - Optimal Portfolio Selection    │
├─────────────────────────────────────────────────────┤
│                                                     │
│ Expected Return (%)                                 │
│   12% ┤                                    ◆        │
│       │                                 ●●●         │
│   10% ┤                              ●●●            │
│       │                           ●●●               │
│    8% ┤                        ●●● ★                │
│       │                     ●●●                     │
│    6% ┤                  ●●●                        │
│       │               ●●●                           │
│    4% ┤            ●●●   •  •                       │
│       │         ●●●         •     •                 │
│    2% ┤      ●●●              •       •             │
│       │   ●●●                                       │
│    0% └───────────────────────────────────────────  │
│        0%   5%   10%  15%  20%  25%  30%            │
│                 Standard Deviation (%)              │
│                                                     │
│ ●●● Efficient Frontier    ★ Current Portfolio      │
│  •  Individual Holdings   ◆ Optimal (Your Risk)    │
│                                                     │
│ ┌─────────────────────────────────────────────────┐│
│ │ Current vs. Optimal Allocation                  ││
│ │                                                 ││
│ │ Asset Class   │ Current │ Optimal │ Difference ││
│ │───────────────┼─────────┼─────────┼────────────││
│ │ UK Equity     │   35%   │   30%   │    -5%     ││
│ │ US Equity     │   25%   │   30%   │    +5%     ││
│ │ Intl Equity   │   10%   │   15%   │    +5%     ││
│ │ Bonds         │   25%   │   20%   │    -5%     ││
│ │ Cash          │    5%   │    5%   │     0%     ││
│ └─────────────────────────────────────────────────┘│
│                                                     │
│ [Generate Rebalancing Trades] [View Details]       │
└─────────────────────────────────────────────────────┘
```

---

## Part 12: Timeline Revision (Updated)

### Enhanced Timeline (8 Weeks Total)

**Weeks 1-2: Core Quantitative Analytics (Phase 1A)**
- Efficient Frontier & Markowitz Optimization
- Correlation/Covariance Matrix Analysis
- Mean-Variance Portfolio Optimizer
- Portfolio Optimization UI

**Weeks 3-4: Core Financial Planning (Phase 1B)**
- Comprehensive Investment Plan (original)
- Recommendations System (original)
- What-If Scenarios (original)
- Integration of optimization into plan

**Weeks 5-6: Risk & Performance Analytics (Phase 2A)**
- VaR/CVaR Calculators
- Maximum Drawdown Analysis
- Risk-Adjusted Returns Suite
- Linear Regression & Factor Analysis

**Weeks 7-8: Tax & Goal-Based Tools (Phase 2B)**
- Tax-Loss Harvesting Engine
- Asset Location Optimizer
- Goal-Based Allocator with Glide Paths
- Tax-Aware Rebalancer

**Weeks 9-10: Polish & Integration (Phase 3)**
- Cross-module integration
- Comprehensive testing
- Performance optimization
- Documentation & user guides

---

## Conclusion

This addendum enhances the Investment Module with **professional-grade quantitative tools** that match or exceed capabilities found in adviser software like:

- **Morningstar Office**
- **eMoney Advisor**
- **MoneyGuidePro**
- **RightCapital**

### Key Differentiators

1. **Modern Portfolio Theory**: Full Markowitz optimization with efficient frontier
2. **Risk Analytics**: VaR, CVaR, Maximum Drawdown, complete risk-adjusted metrics
3. **Factor Analysis**: Multi-factor regression, smart beta, style analysis
4. **Tax Optimization**: Systematic tax-loss harvesting, asset location, tax-aware rebalancing
5. **Goal-Based**: Dynamic glide paths, liability-driven investing
6. **Risk-Aligned**: All features integrated with user's risk profile

### Professional Validation

These tools are based on:
- **Nobel Prize-winning research** (Markowitz, Fama-French)
- **Industry-standard metrics** (Sharpe, Sortino, VaR)
- **Regulatory frameworks** (Basel III uses CVaR)
- **Academic research** (Factor investing, glide paths)
- **Tax optimization** (Real-world strategies from advisers)

The result is an **investment planning system** that provides:
✅ **Professional-grade analytics**
✅ **Risk-based portfolio construction**
✅ **Goal-oriented planning**
✅ **Tax-efficient strategies**
✅ **Actionable recommendations**

---

**Document Version**: 1.0 (Addendum)
**Last Updated**: November 1, 2025
**Author**: Claude (TenGo Development Team)
**Cross-Reference**: INVESTMENT_FINANCIAL_PLANNING_EXPANSION.md
