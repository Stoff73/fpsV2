<template>
  <PublicLayout>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-centre">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Financial Calculators
        </h1>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto">
          Free tools to help you understand your finances better
        </p>
      </div>
    </div>

    <!-- Calculators Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Calculator Navigation -->
      <div class="flex flex-wrap gap-2 mb-8 justify-centre">
        <button
          v-for="calc in calculators"
          :key="calc.id"
          @click="activeCalculator = calc.id"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colours',
            activeCalculator === calc.id
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
          ]"
        >
          {{ calc.name }}
        </button>
      </div>

      <!-- Income Tax Calculator -->
      <div v-if="activeCalculator === 'income-tax'" class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Income Tax Calculator</h2>
        <p class="text-gray-600 mb-8">Calculate your UK income tax and National Insurance contributions for 2025/26.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Annual Gross Income</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="incomeTax.income"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="50000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Pension Contributions</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="incomeTax.pension"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="0"
                />
              </div>
            </div>

            <button
              @click="calculateIncomeTax"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colours"
            >
              Calculate
            </button>
          </div>

          <div v-if="incomeTax.result" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Results</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Gross Income:</span>
                <span class="font-semibold">{{ formatCurrency(incomeTax.result.gross) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Pension Contributions:</span>
                <span class="font-semibold">{{ formatCurrency(incomeTax.result.pension) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Taxable Income:</span>
                <span class="font-semibold">{{ formatCurrency(incomeTax.result.taxable) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between">
                <span class="text-gray-600">Income Tax:</span>
                <span class="font-semibold text-red-600">{{ formatCurrency(incomeTax.result.tax) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">National Insurance:</span>
                <span class="font-semibold text-red-600">{{ formatCurrency(incomeTax.result.ni) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Net Income:</span>
                <span class="font-bold text-green-600">{{ formatCurrency(incomeTax.result.net) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Effective Tax Rate:</span>
                <span class="font-semibold">{{ incomeTax.result.effectiveRate }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mortgage Calculator -->
      <div v-if="activeCalculator === 'mortgage'" class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Mortgage Affordability Calculator</h2>
        <p class="text-gray-600 mb-8">Calculate how much you can afford to borrow and your monthly repayments.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Annual Income</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="mortgage.income"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="50000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Property Value</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="mortgage.propertyValue"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="250000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Deposit</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="mortgage.deposit"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="25000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
              <input
                v-model.number="mortgage.interestRate"
                type="number"
                step="0.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="5.5"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mortgage Term (years)</label>
              <input
                v-model.number="mortgage.term"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="25"
              />
            </div>

            <button
              @click="calculateMortgage"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colours"
            >
              Calculate
            </button>
          </div>

          <div v-if="mortgage.result" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Results</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Property Value:</span>
                <span class="font-semibold">{{ formatCurrency(mortgage.result.propertyValue) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Deposit ({{ mortgage.result.ltv }}% LTV):</span>
                <span class="font-semibold">{{ formatCurrency(mortgage.result.deposit) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Loan Amount:</span>
                <span class="font-semibold">{{ formatCurrency(mortgage.result.loanAmount) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Monthly Payment:</span>
                <span class="font-bold text-blue-600">{{ formatCurrency(mortgage.result.monthlyPayment) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Interest:</span>
                <span class="font-semibold">{{ formatCurrency(mortgage.result.totalInterest) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Repayment:</span>
                <span class="font-semibold">{{ formatCurrency(mortgage.result.totalRepayment) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-900">
                  <strong>Maximum Affordable:</strong><br />
                  Based on 4.5x income: {{ formatCurrency(mortgage.result.maxAffordable) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loan Repayment Calculator -->
      <div v-if="activeCalculator === 'loan'" class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Loan Repayment Calculator</h2>
        <p class="text-gray-600 mb-8">Calculate monthly payments and total interest on personal loans.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Loan Amount</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="loan.amount"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="10000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% APR)</label>
              <input
                v-model.number="loan.rate"
                type="number"
                step="0.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="8.9"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Loan Term (months)</label>
              <input
                v-model.number="loan.term"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="36"
              />
            </div>

            <button
              @click="calculateLoan"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colours"
            >
              Calculate
            </button>
          </div>

          <div v-if="loan.result" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Results</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Loan Amount:</span>
                <span class="font-semibold">{{ formatCurrency(loan.result.amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Interest Rate:</span>
                <span class="font-semibold">{{ loan.result.rate }}% APR</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Term:</span>
                <span class="font-semibold">{{ loan.result.term }} months</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Monthly Payment:</span>
                <span class="font-bold text-blue-600">{{ formatCurrency(loan.result.monthlyPayment) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Interest:</span>
                <span class="font-semibold text-red-600">{{ formatCurrency(loan.result.totalInterest) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Repayment:</span>
                <span class="font-semibold">{{ formatCurrency(loan.result.totalRepayment) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Emergency Fund Calculator -->
      <div v-if="activeCalculator === 'emergency-fund'" class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Emergency Fund Calculator</h2>
        <p class="text-gray-600 mb-8">Calculate how much you should save for emergencies (3-6 months of expenses).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Expenses</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="emergencyFund.monthlyExpenses"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="2500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Savings</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="emergencyFund.currentSavings"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="5000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Target Months</label>
              <select
                v-model.number="emergencyFund.targetMonths"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="3">3 months</option>
                <option :value="6">6 months</option>
                <option :value="9">9 months</option>
                <option :value="12">12 months</option>
              </select>
            </div>

            <button
              @click="calculateEmergencyFund"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colours"
            >
              Calculate
            </button>
          </div>

          <div v-if="emergencyFund.result" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Results</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Monthly Expenses:</span>
                <span class="font-semibold">{{ formatCurrency(emergencyFund.result.monthlyExpenses) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Target Coverage:</span>
                <span class="font-semibold">{{ emergencyFund.result.targetMonths }} months</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Target Fund:</span>
                <span class="font-bold text-blue-600">{{ formatCurrency(emergencyFund.result.targetAmount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Current Savings:</span>
                <span class="font-semibold">{{ formatCurrency(emergencyFund.result.currentSavings) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shortfall:</span>
                <span :class="emergencyFund.result.shortfall > 0 ? 'font-semibold text-red-600' : 'font-semibold text-green-600'">
                  {{ emergencyFund.result.shortfall > 0 ? formatCurrency(emergencyFund.result.shortfall) : 'Fully funded!' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Current Runway:</span>
                <span class="font-semibold">{{ emergencyFund.result.currentRunway.toFixed(1) }} months</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div :class="[
                'rounded-lg p-3',
                emergencyFund.result.adequacy === 'Good' ? 'bg-green-50 border border-green-200' :
                emergencyFund.result.adequacy === 'Adequate' ? 'bg-blue-50 border border-blue-200' :
                'bg-amber-50 border border-amber-200'
              ]">
                <p :class="[
                  'text-sm font-semibold',
                  emergencyFund.result.adequacy === 'Good' ? 'text-green-900' :
                  emergencyFund.result.adequacy === 'Adequate' ? 'text-blue-900' :
                  'text-amber-900'
                ]">
                  Status: {{ emergencyFund.result.adequacy }}
                </p>
                <p :class="[
                  'text-xs mt-1',
                  emergencyFund.result.adequacy === 'Good' ? 'text-green-700' :
                  emergencyFund.result.adequacy === 'Adequate' ? 'text-blue-700' :
                  'text-amber-700'
                ]">
                  {{ emergencyFund.result.message }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pension Growth Calculator -->
      <div v-if="activeCalculator === 'pension'" class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Pension Growth Calculator</h2>
        <p class="text-gray-600 mb-8">Project your pension pot at retirement with regular contributions.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Pension Value</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="pension.currentValue"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="50000"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Contribution</label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-500">£</span>
                <input
                  v-model.number="pension.monthlyContribution"
                  type="number"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Age</label>
              <input
                v-model.number="pension.currentAge"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="35"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Retirement Age</label>
              <input
                v-model.number="pension.retirementAge"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="65"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Expected Growth Rate (%)</label>
              <input
                v-model.number="pension.growthRate"
                type="number"
                step="0.1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="5.0"
              />
            </div>

            <button
              @click="calculatePension"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colours"
            >
              Calculate
            </button>
          </div>

          <div v-if="pension.result" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Projection</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Years to Retirement:</span>
                <span class="font-semibold">{{ pension.result.yearsToRetirement }} years</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Current Value:</span>
                <span class="font-semibold">{{ formatCurrency(pension.result.currentValue) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Contributions:</span>
                <span class="font-semibold">{{ formatCurrency(pension.result.totalContributions) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Projected Pot at {{ pension.retirementAge }}:</span>
                <span class="font-bold text-green-600">{{ formatCurrency(pension.result.projectedValue) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Investment Growth:</span>
                <span class="font-semibold text-blue-600">{{ formatCurrency(pension.result.investmentGrowth) }}</span>
              </div>
              <div class="border-t border-gray-300 pt-3"></div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-900">
                  <strong>At 4% withdrawal rate:</strong><br />
                  Annual income: {{ formatCurrency(pension.result.annualIncome) }}<br />
                  Monthly income: {{ formatCurrency(pension.result.monthlyIncome) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script>
import PublicLayout from '@/layouts/PublicLayout.vue';

export default {
  name: 'CalculatorsPage',

  components: {
    PublicLayout,
  },

  data() {
    return {
      activeCalculator: 'income-tax',
      calculators: [
        { id: 'income-tax', name: 'Income Tax' },
        { id: 'mortgage', name: 'Mortgage' },
        { id: 'loan', name: 'Loan Repayment' },
        { id: 'emergency-fund', name: 'Emergency Fund' },
        { id: 'pension', name: 'Pension Growth' },
      ],
      incomeTax: {
        income: null,
        pension: 0,
        result: null,
      },
      mortgage: {
        income: null,
        propertyValue: null,
        deposit: null,
        interestRate: 5.5,
        term: 25,
        result: null,
      },
      loan: {
        amount: null,
        rate: null,
        term: null,
        result: null,
      },
      emergencyFund: {
        monthlyExpenses: null,
        currentSavings: null,
        targetMonths: 6,
        result: null,
      },
      pension: {
        currentValue: null,
        monthlyContribution: null,
        currentAge: null,
        retirementAge: 65,
        growthRate: 5.0,
        result: null,
      },
    };
  },

  methods: {
    calculateIncomeTax() {
      const income = this.incomeTax.income || 0;
      const pension = this.incomeTax.pension || 0;
      const taxable = income - pension;

      // UK Tax Bands 2025/26
      const personalAllowance = 12570;
      const basicRateLimit = 50270;
      const higherRateLimit = 125140;

      let tax = 0;
      let remaining = taxable;

      // Personal allowance
      if (remaining > personalAllowance) {
        remaining -= personalAllowance;
      } else {
        remaining = 0;
      }

      // Basic rate (20%)
      if (remaining > 0) {
        const basicRateBand = Math.min(remaining, basicRateLimit - personalAllowance);
        tax += basicRateBand * 0.20;
        remaining -= basicRateBand;
      }

      // Higher rate (40%)
      if (remaining > 0) {
        const higherRateBand = Math.min(remaining, higherRateLimit - basicRateLimit);
        tax += higherRateBand * 0.40;
        remaining -= higherRateBand;
      }

      // Additional rate (45%)
      if (remaining > 0) {
        tax += remaining * 0.45;
      }

      // National Insurance (simplified)
      const niThreshold = 12570;
      const niUpperLimit = 50270;
      let ni = 0;
      if (income > niThreshold) {
        const niableIncome = Math.min(income - niThreshold, niUpperLimit - niThreshold);
        ni = niableIncome * 0.12;
        if (income > niUpperLimit) {
          ni += (income - niUpperLimit) * 0.02;
        }
      }

      const net = income - tax - ni;
      const effectiveRate = ((tax + ni) / income * 100).toFixed(1);

      this.incomeTax.result = {
        gross: income,
        pension: pension,
        taxable: taxable,
        tax: Math.round(tax),
        ni: Math.round(ni),
        net: Math.round(net),
        effectiveRate: effectiveRate,
      };
    },

    calculateMortgage() {
      const propertyValue = this.mortgage.propertyValue || 0;
      const deposit = this.mortgage.deposit || 0;
      const loanAmount = propertyValue - deposit;
      const monthlyRate = (this.mortgage.interestRate / 100) / 12;
      const numberOfPayments = this.mortgage.term * 12;

      // Monthly payment formula
      const monthlyPayment = loanAmount *
        (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
        (Math.pow(1 + monthlyRate, numberOfPayments) - 1);

      const totalRepayment = monthlyPayment * numberOfPayments;
      const totalInterest = totalRepayment - loanAmount;
      const ltv = ((loanAmount / propertyValue) * 100).toFixed(0);
      const maxAffordable = (this.mortgage.income || 0) * 4.5;

      this.mortgage.result = {
        propertyValue: propertyValue,
        deposit: deposit,
        loanAmount: loanAmount,
        ltv: ltv,
        monthlyPayment: Math.round(monthlyPayment),
        totalInterest: Math.round(totalInterest),
        totalRepayment: Math.round(totalRepayment),
        maxAffordable: maxAffordable,
      };
    },

    calculateLoan() {
      const amount = this.loan.amount || 0;
      const monthlyRate = (this.loan.rate / 100) / 12;
      const term = this.loan.term || 1;

      const monthlyPayment = amount *
        (monthlyRate * Math.pow(1 + monthlyRate, term)) /
        (Math.pow(1 + monthlyRate, term) - 1);

      const totalRepayment = monthlyPayment * term;
      const totalInterest = totalRepayment - amount;

      this.loan.result = {
        amount: amount,
        rate: this.loan.rate,
        term: term,
        monthlyPayment: Math.round(monthlyPayment),
        totalInterest: Math.round(totalInterest),
        totalRepayment: Math.round(totalRepayment),
      };
    },

    calculateEmergencyFund() {
      const monthlyExpenses = this.emergencyFund.monthlyExpenses || 0;
      const currentSavings = this.emergencyFund.currentSavings || 0;
      const targetMonths = this.emergencyFund.targetMonths;
      const targetAmount = monthlyExpenses * targetMonths;
      const shortfall = Math.max(0, targetAmount - currentSavings);
      const currentRunway = monthlyExpenses > 0 ? currentSavings / monthlyExpenses : 0;

      let adequacy = 'Low';
      let message = 'Build your emergency fund to cover unexpected expenses.';

      if (currentRunway >= 6) {
        adequacy = 'Good';
        message = 'Excellent! You have a strong emergency fund.';
      } else if (currentRunway >= 3) {
        adequacy = 'Adequate';
        message = 'Good progress. Consider building to 6 months for better security.';
      }

      this.emergencyFund.result = {
        monthlyExpenses: monthlyExpenses,
        targetMonths: targetMonths,
        targetAmount: targetAmount,
        currentSavings: currentSavings,
        shortfall: shortfall,
        currentRunway: currentRunway,
        adequacy: adequacy,
        message: message,
      };
    },

    calculatePension() {
      const currentValue = this.pension.currentValue || 0;
      const monthlyContribution = this.pension.monthlyContribution || 0;
      const currentAge = this.pension.currentAge || 0;
      const retirementAge = this.pension.retirementAge || 65;
      const yearsToRetirement = retirementAge - currentAge;
      const growthRate = (this.pension.growthRate / 100) / 12;
      const months = yearsToRetirement * 12;

      // Future value of current pot
      const futureValueOfCurrent = currentValue * Math.pow(1 + growthRate, months);

      // Future value of contributions
      const futureValueOfContributions = monthlyContribution *
        ((Math.pow(1 + growthRate, months) - 1) / growthRate);

      const projectedValue = futureValueOfCurrent + futureValueOfContributions;
      const totalContributions = monthlyContribution * months;
      const investmentGrowth = projectedValue - currentValue - totalContributions;
      const annualIncome = projectedValue * 0.04;
      const monthlyIncome = annualIncome / 12;

      this.pension.result = {
        currentValue: currentValue,
        yearsToRetirement: yearsToRetirement,
        totalContributions: totalContributions,
        projectedValue: Math.round(projectedValue),
        investmentGrowth: Math.round(investmentGrowth),
        annualIncome: Math.round(annualIncome),
        monthlyIncome: Math.round(monthlyIncome),
      };
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value || 0);
    },
  },
};
</script>
