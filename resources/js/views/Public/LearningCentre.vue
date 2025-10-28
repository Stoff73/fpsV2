<template>
  <PublicLayout>
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-800 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Learning Centre
        </h1>
        <p class="text-xl text-indigo-100 max-w-3xl mx-auto">
          Educational resources to help you understand financial planning and make informed decisions
        </p>
      </div>
    </div>

    <!-- Category Navigation -->
    <div class="sticky top-16 bg-white border-b border-gray-200 shadow-sm z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex overflow-x-auto py-4 gap-4">
          <button
            v-for="category in categories"
            :key="category.id"
            @click="activeCategory = category.id"
            :class="[
              'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors',
              activeCategory === category.id
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ category.name }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Financial Planning Basics -->
      <div v-if="activeCategory === 'basics'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">What is Financial Planning?</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Financial planning is the process of taking a comprehensive look at your financial situation and building a specific financial plan to reach your goals. As a result, financial planning often delves into multiple areas of finance, including investing, taxes, savings, retirement, estate planning, insurance, and more.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">The Six Key Areas of Financial Planning</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                <h4 class="text-xl font-bold text-green-900 mb-3">1. Protection Planning</h4>
                <p class="text-green-800">
                  Protecting your income and family against unforeseen circumstances through life insurance, critical illness cover, and income protection.
                </p>
              </div>

              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                <h4 class="text-xl font-bold text-blue-900 mb-3">2. Cash Flow & Savings</h4>
                <p class="text-blue-800">
                  Managing your day-to-day finances, building emergency funds, and working towards short-term savings goals.
                </p>
              </div>

              <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                <h4 class="text-xl font-bold text-purple-900 mb-3">3. Investment Planning</h4>
                <p class="text-purple-800">
                  Growing your wealth over time through investments in stocks, bonds, funds, and other assets aligned with your risk tolerance.
                </p>
              </div>

              <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
                <h4 class="text-xl font-bold text-orange-900 mb-3">4. Retirement Planning</h4>
                <p class="text-orange-800">
                  Ensuring you have sufficient income in retirement through pensions, investments, and other retirement savings vehicles.
                </p>
              </div>

              <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                <h4 class="text-xl font-bold text-red-900 mb-3">5. Estate Planning</h4>
                <p class="text-red-800">
                  Planning for the efficient transfer of your wealth to beneficiaries while minimizing inheritance tax liabilities.
                </p>
              </div>

              <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200">
                <h4 class="text-xl font-bold text-indigo-900 mb-3">6. Tax Planning</h4>
                <p class="text-indigo-800">
                  Structuring your finances in a tax-efficient manner using allowances, reliefs, and legitimate tax planning strategies.
                </p>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Why Financial Planning Matters</h3>
            <ul class="list-disc pl-6 space-y-2">
              <li>Provides clarity and direction for your financial future</li>
              <li>Helps you make informed decisions about money</li>
              <li>Identifies potential risks and opportunities</li>
              <li>Ensures you're on track to meet your life goals</li>
              <li>Reduces financial stress and anxiety</li>
              <li>Maximizes the efficiency of your money</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Protection Planning -->
      <div v-if="activeCategory === 'protection'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Understanding Protection Planning</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Protection planning ensures your family and lifestyle are protected if something unexpected happens to you. It's about having the right types and levels of cover in place.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Types of Protection</h3>

            <div class="space-y-6">
              <div class="bg-green-50 border-l-4 border-green-600 p-6 rounded">
                <h4 class="text-xl font-bold text-green-900 mb-2">Life Insurance</h4>
                <p class="text-green-800 mb-3">
                  Pays out a lump sum if you die during the policy term. Essential for:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-green-800">
                  <li>Paying off the mortgage</li>
                  <li>Replacing your income for dependents</li>
                  <li>Covering funeral costs</li>
                  <li>Funding children's education</li>
                </ul>
                <div class="mt-4 bg-white rounded p-4">
                  <strong class="text-green-900">Rule of Thumb:</strong>
                  <p class="text-sm text-green-800 mt-1">Cover should be 10x your annual income, or enough to clear debts plus 5-10 years of income replacement.</p>
                </div>
              </div>

              <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded">
                <h4 class="text-xl font-bold text-blue-900 mb-2">Critical Illness Cover</h4>
                <p class="text-blue-800 mb-3">
                  Pays out a tax-free lump sum if you're diagnosed with a specified serious illness such as:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-blue-800">
                  <li>Cancer</li>
                  <li>Heart attack</li>
                  <li>Stroke</li>
                  <li>Multiple sclerosis</li>
                </ul>
                <div class="mt-4 bg-white rounded p-4">
                  <strong class="text-blue-900">Important:</strong>
                  <p class="text-sm text-blue-800 mt-1">You're statistically more likely to suffer a critical illness than die during working years. Consider this alongside life insurance.</p>
                </div>
              </div>

              <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded">
                <h4 class="text-xl font-bold text-purple-900 mb-2">Income Protection</h4>
                <p class="text-purple-800 mb-3">
                  Replaces a percentage of your income (typically 50-70%) if you can't work due to illness or injury. Key features:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-purple-800">
                  <li>Pays until you return to work or retirement age</li>
                  <li>Deferred period (4-52 weeks) before payments start</li>
                  <li>Own occupation or any occupation definitions</li>
                  <li>Tax-free benefits</li>
                </ul>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">How Much Cover Do You Need?</h3>
            <p>
              The right amount depends on your circumstances. Consider:
            </p>
            <ul class="list-disc pl-6 space-y-2">
              <li><strong>Debts:</strong> Mortgage, loans, credit cards</li>
              <li><strong>Dependents:</strong> Number and age of children</li>
              <li><strong>Income:</strong> Your annual earnings and lifestyle costs</li>
              <li><strong>Existing cover:</strong> Employer benefits, savings</li>
              <li><strong>Future needs:</strong> Children's education, care costs</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Retirement Planning -->
      <div v-if="activeCategory === 'retirement'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Retirement Planning in the UK</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Retirement planning involves building up sufficient savings and income sources to maintain your lifestyle when you stop working. With increasing life expectancy, this could be 20-30 years or more.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">UK Pension System</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
              <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h4 class="text-lg font-bold text-blue-900 mb-3">State Pension</h4>
                <p class="text-blue-800 text-sm mb-3">
                  Full state pension: £221.20/week (2025/26)
                </p>
                <p class="text-blue-800 text-sm">
                  You need 35 qualifying years of National Insurance contributions. Claim age is currently 66.
                </p>
              </div>

              <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                <h4 class="text-lg font-bold text-purple-900 mb-3">Workplace Pension</h4>
                <p class="text-purple-800 text-sm mb-3">
                  Auto-enrollment requires minimum 8% total contribution (5% employee + 3% employer).
                </p>
                <p class="text-purple-800 text-sm">
                  Defined Contribution (DC) pensions invest your money, with final pot depending on investment performance.
                </p>
              </div>

              <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                <h4 class="text-lg font-bold text-green-900 mb-3">Personal Pension</h4>
                <p class="text-green-800 text-sm mb-3">
                  Self-employed or additional savings beyond workplace pension.
                </p>
                <p class="text-green-800 text-sm">
                  SIPPs offer more investment control. Get 20-45% tax relief on contributions.
                </p>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Key Allowances & Limits</h3>

            <div class="bg-amber-50 border-l-4 border-amber-600 p-6 rounded my-6">
              <h4 class="text-xl font-bold text-amber-900 mb-4">Annual Allowance: £60,000</h4>
              <p class="text-amber-800 mb-3">
                The maximum you can contribute to pensions each year with tax relief. Includes employer contributions.
              </p>
              <ul class="list-disc pl-6 space-y-2 text-amber-800">
                <li><strong>Carry Forward:</strong> Use unused allowance from previous 3 years</li>
                <li><strong>Tapered:</strong> Reduced for high earners (income over £260,000)</li>
                <li><strong>MPAA:</strong> £10,000 limit if you've accessed pension flexibly</li>
              </ul>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded my-6">
              <h4 class="text-xl font-bold text-blue-900 mb-4">Tax-Free Lump Sum: 25%</h4>
              <p class="text-blue-800">
                You can take 25% of your pension pot as a tax-free lump sum from age 55 (rising to 57 in 2028). The rest is taxed as income when withdrawn.
              </p>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Retirement Income Options</h3>

            <div class="space-y-4">
              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">1. Pension Drawdown</h4>
                <p class="text-gray-700">
                  Keep your pension invested and withdraw income as needed. Flexible but investment risk remains with you. Potential for growth but also losses.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">2. Annuity</h4>
                <p class="text-gray-700">
                  Convert your pot into guaranteed income for life. Provides certainty but no flexibility. Rates depend on age, health, and interest rates.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">3. Combination</h4>
                <p class="text-gray-700">
                  Use part of your pot for an annuity (guaranteed income) and keep the rest in drawdown (flexibility and growth potential).
                </p>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">How Much Do You Need?</h3>
            <p>
              A comfortable retirement in the UK requires approximately:
            </p>
            <ul class="list-disc pl-6 space-y-2">
              <li><strong>Moderate lifestyle:</strong> £25,000-£35,000 per year per person</li>
              <li><strong>Comfortable lifestyle:</strong> £35,000-£50,000 per year per person</li>
              <li><strong>Luxurious lifestyle:</strong> £50,000+ per year per person</li>
            </ul>
            <p class="mt-4">
              These figures assume you own your home outright. Remember to factor in state pension income.
            </p>
          </div>
        </div>
      </div>

      <!-- Estate Planning -->
      <div v-if="activeCategory === 'estate'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Estate Planning & Inheritance Tax</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Estate planning ensures your assets pass to your chosen beneficiaries in a tax-efficient manner. Without proper planning, your estate could face a significant inheritance tax bill and may not be distributed as you wish.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Inheritance Tax (IHT) Basics</h3>

            <div class="bg-red-50 border-l-4 border-red-600 p-6 rounded my-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 class="text-lg font-bold text-red-900 mb-3">IHT Rate: 40%</h4>
                  <p class="text-red-800 text-sm">
                    Charged on estates above the nil rate band. Reduced to 36% if 10%+ left to charity.
                  </p>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-red-900 mb-3">When It's Charged</h4>
                  <p class="text-red-800 text-sm">
                    On death and on gifts made within 7 years of death (Potentially Exempt Transfers).
                  </p>
                </div>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Tax-Free Allowances</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
              <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h4 class="text-lg font-bold text-blue-900 mb-3">Nil Rate Band (NRB)</h4>
                <div class="text-4xl font-bold text-blue-600 mb-2">£325,000</div>
                <p class="text-blue-800 text-sm">
                  The standard IHT-free threshold. Transferable to surviving spouse/civil partner.
                </p>
              </div>

              <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                <h4 class="text-lg font-bold text-green-900 mb-3">Residence Nil Rate Band (RNRB)</h4>
                <div class="text-4xl font-bold text-green-600 mb-2">£175,000</div>
                <p class="text-green-800 text-sm">
                  Additional allowance when home left to direct descendants. Also transferable to spouse.
                </p>
              </div>
            </div>

            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 my-6">
              <h4 class="text-xl font-bold text-indigo-900 mb-3">Married Couples</h4>
              <p class="text-indigo-800 mb-3">
                Combined allowance: up to <strong>£1 million</strong> (£325k NRB + £175k RNRB for each spouse)
              </p>
              <p class="text-indigo-800 text-sm">
                Transfers between spouses are always IHT-free. Unused allowances transfer to the surviving spouse.
              </p>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">IHT Mitigation Strategies</h3>

            <div class="space-y-4">
              <div class="bg-white border-l-4 border-green-600 p-6 rounded">
                <h4 class="text-lg font-bold text-gray-900 mb-2">1. Gifting</h4>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 text-sm">
                  <li><strong>Annual Exemption:</strong> £3,000 per year (can carry forward 1 year)</li>
                  <li><strong>Small Gifts:</strong> £250 to unlimited people</li>
                  <li><strong>Wedding Gifts:</strong> £5,000 (child), £2,500 (grandchild), £1,000 (other)</li>
                  <li><strong>Normal Expenditure:</strong> Regular gifts from surplus income</li>
                  <li><strong>PETs:</strong> Gifts over allowances become exempt after 7 years</li>
                </ul>
              </div>

              <div class="bg-white border-l-4 border-blue-600 p-6 rounded">
                <h4 class="text-lg font-bold text-gray-900 mb-2">2. Trusts</h4>
                <p class="text-gray-700 text-sm">
                  Place assets in trust to remove them from your estate. Various trust types available with different tax treatments. Complex area requiring professional advice.
                </p>
              </div>

              <div class="bg-white border-l-4 border-purple-600 p-6 rounded">
                <h4 class="text-lg font-bold text-gray-900 mb-2">3. Life Insurance in Trust</h4>
                <p class="text-gray-700 text-sm">
                  Life insurance proceeds normally form part of your estate. Writing the policy in trust keeps it outside your estate and ensures quick payout to beneficiaries without probate.
                </p>
              </div>

              <div class="bg-white border-l-4 border-orange-600 p-6 rounded">
                <h4 class="text-lg font-bold text-gray-900 mb-2">4. Business Relief</h4>
                <p class="text-gray-700 text-sm">
                  Certain business assets and AIM shares qualify for 100% IHT relief after 2 years. Enterprise Investment Schemes (EIS) and Business Property Relief (BPR) investments can reduce IHT liability.
                </p>
              </div>

              <div class="bg-white border-l-4 border-red-600 p-6 rounded">
                <h4 class="text-lg font-bold text-gray-900 mb-2">5. Pension Death Benefits</h4>
                <p class="text-gray-700 text-sm">
                  Pensions are usually outside your estate for IHT. Nominate beneficiaries to ensure proceeds go where you intend. If you die before 75, benefits are usually tax-free.
                </p>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Intestacy Rules</h3>
            <p>
              If you die without a valid will, your estate is distributed according to intestacy rules:
            </p>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 my-6">
              <h4 class="text-lg font-bold text-amber-900 mb-3">⚠️ Without a Will:</h4>
              <ul class="list-disc pl-6 space-y-2 text-amber-800">
                <li>Unmarried partners receive nothing (even long-term cohabiting)</li>
                <li>Step-children have no automatic rights</li>
                <li>You can't choose guardians for children under 18</li>
                <li>Estate may not be distributed as you wish</li>
                <li>Could create family disputes and delays</li>
              </ul>
            </div>

            <p class="mt-6">
              <strong>Married with children and estate over £250,000?</strong> Spouse gets first £250,000 plus 50% of the remainder. Children share the other 50%.
            </p>
            <p>
              <strong>No spouse or children?</strong> Estate passes to parents, then siblings, then more distant relatives. If no relatives exist, everything goes to the Crown.
            </p>
          </div>
        </div>
      </div>

      <!-- Investment Planning -->
      <div v-if="activeCategory === 'investment'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Investment Planning Fundamentals</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Investment planning involves putting your money to work to achieve long-term financial goals. Understanding the basics of risk, return, and asset allocation is crucial for successful investing.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Risk & Return</h3>

            <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded my-6">
              <h4 class="text-lg font-bold text-blue-900 mb-3">Core Principle</h4>
              <p class="text-blue-800">
                <strong>Higher potential returns come with higher risk.</strong> There's no such thing as high returns without risk. Understanding your risk tolerance is the foundation of investment planning.
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
              <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                <h4 class="text-lg font-bold text-green-900 mb-3">Lower Risk</h4>
                <ul class="text-sm text-green-800 space-y-1">
                  <li>• Cash</li>
                  <li>• Government bonds</li>
                  <li>• Corporate bonds (high grade)</li>
                </ul>
                <p class="text-xs text-green-700 mt-3">
                  Expected return: 2-4% per year
                </p>
              </div>

              <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h4 class="text-lg font-bold text-blue-900 mb-3">Medium Risk</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                  <li>• Balanced funds</li>
                  <li>• Mixed asset portfolios</li>
                  <li>• Property funds</li>
                </ul>
                <p class="text-xs text-blue-700 mt-3">
                  Expected return: 4-7% per year
                </p>
              </div>

              <div class="bg-orange-50 rounded-lg p-6 border border-orange-200">
                <h4 class="text-lg font-bold text-orange-900 mb-3">Higher Risk</h4>
                <ul class="text-sm text-orange-800 space-y-1">
                  <li>• Individual stocks</li>
                  <li>• Emerging markets</li>
                  <li>• Small cap funds</li>
                </ul>
                <p class="text-xs text-orange-700 mt-3">
                  Expected return: 7-10%+ per year
                </p>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Asset Allocation</h3>
            <p>
              Spreading investments across different asset classes to balance risk and return. The right mix depends on your:
            </p>
            <ul class="list-disc pl-6 space-y-2">
              <li>Time horizon (when you need the money)</li>
              <li>Risk tolerance (ability and willingness to accept volatility)</li>
              <li>Financial goals and circumstances</li>
              <li>Age and life stage</li>
            </ul>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Tax-Efficient Wrappers</h3>

            <div class="space-y-4">
              <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded">
                <h4 class="text-lg font-bold text-purple-900 mb-2">ISAs (Individual Savings Accounts)</h4>
                <p class="text-purple-800 text-sm mb-3">
                  Annual allowance: £20,000 (2025/26)
                </p>
                <ul class="list-disc pl-6 space-y-1 text-purple-800 text-sm">
                  <li>Tax-free growth and withdrawals</li>
                  <li>No capital gains tax or income tax on returns</li>
                  <li>Can hold cash or investments (S&S ISA)</li>
                  <li>Flexible access to your money</li>
                </ul>
              </div>

              <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded">
                <h4 class="text-lg font-bold text-blue-900 mb-2">Pensions</h4>
                <p class="text-blue-800 text-sm mb-3">
                  Annual allowance: £60,000 (2025/26)
                </p>
                <ul class="list-disc pl-6 space-y-1 text-blue-800 text-sm">
                  <li>20-45% tax relief on contributions</li>
                  <li>Tax-free growth</li>
                  <li>25% tax-free lump sum at retirement</li>
                  <li>Locked until age 55 (rising to 57 in 2028)</li>
                </ul>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Diversification</h3>
            <p>
              "Don't put all your eggs in one basket." Diversification reduces risk by spreading investments across:
            </p>
            <ul class="list-disc pl-6 space-y-2">
              <li><strong>Asset classes:</strong> Stocks, bonds, property, commodities</li>
              <li><strong>Geographies:</strong> UK, US, Europe, emerging markets</li>
              <li><strong>Sectors:</strong> Technology, healthcare, finance, etc.</li>
              <li><strong>Individual holdings:</strong> Many companies, not just a few</li>
            </ul>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6 my-6">
              <h4 class="text-lg font-bold text-green-900 mb-3">💡 Investment Tips</h4>
              <ul class="list-disc pl-6 space-y-2 text-green-800">
                <li>Start early - compound growth is powerful</li>
                <li>Invest regularly (pound-cost averaging)</li>
                <li>Think long-term (5+ years minimum)</li>
                <li>Keep costs low (fees eat into returns)</li>
                <li>Rebalance periodically</li>
                <li>Don't try to time the market</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Tax Planning -->
      <div v-if="activeCategory === 'tax'" class="space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">UK Tax Planning Guide</h2>

          <div class="prose prose-lg max-w-none text-gray-600 space-y-4">
            <p>
              Tax planning involves arranging your financial affairs to minimize tax legally. Understanding allowances and using them effectively can save thousands of pounds each year.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Income Tax (2025/26)</h3>

            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-300 rounded-lg my-6">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Band</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Income Range</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Rate</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Personal Allowance</td>
                    <td class="px-6 py-4 text-sm text-gray-700">£0 - £12,570</td>
                    <td class="px-6 py-4 text-sm font-semibold text-green-600">0%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Basic Rate</td>
                    <td class="px-6 py-4 text-sm text-gray-700">£12,571 - £50,270</td>
                    <td class="px-6 py-4 text-sm font-semibold text-blue-600">20%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Higher Rate</td>
                    <td class="px-6 py-4 text-sm text-gray-700">£50,271 - £125,140</td>
                    <td class="px-6 py-4 text-sm font-semibold text-orange-600">40%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Additional Rate</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Over £125,140</td>
                    <td class="px-6 py-4 text-sm font-semibold text-red-600">45%</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-600 p-6 rounded my-6">
              <h4 class="text-lg font-bold text-amber-900 mb-2">⚠️ Personal Allowance Taper</h4>
              <p class="text-amber-800 text-sm">
                For every £2 earned over £100,000, you lose £1 of personal allowance. Between £100k-£125k, the effective tax rate is 60%.
              </p>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Key Tax Allowances</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
              <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h4 class="text-lg font-bold text-blue-900 mb-3">Personal Savings Allowance</h4>
                <ul class="text-sm text-blue-800 space-y-2">
                  <li>Basic rate: £1,000 tax-free interest</li>
                  <li>Higher rate: £500 tax-free interest</li>
                  <li>Additional rate: £0</li>
                </ul>
              </div>

              <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                <h4 class="text-lg font-bold text-green-900 mb-3">Dividend Allowance</h4>
                <ul class="text-sm text-green-800 space-y-2">
                  <li>£500 tax-free dividends (all taxpayers)</li>
                  <li>Reduced from £1,000 in previous years</li>
                  <li>Above this: taxed at 8.75%, 33.75%, or 39.35%</li>
                </ul>
              </div>

              <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                <h4 class="text-lg font-bold text-purple-900 mb-3">Capital Gains Tax Allowance</h4>
                <ul class="text-sm text-purple-800 space-y-2">
                  <li>Annual exemption: £3,000</li>
                  <li>Rate: 10% (basic rate), 20% (higher rate)</li>
                  <li>Property: 18% (basic), 24% (higher)</li>
                </ul>
              </div>

              <div class="bg-orange-50 rounded-lg p-6 border border-orange-200">
                <h4 class="text-lg font-bold text-orange-900 mb-3">Trading Allowance</h4>
                <ul class="text-sm text-orange-800 space-y-2">
                  <li>£1,000 tax-free self-employment income</li>
                  <li>£1,000 property income allowance</li>
                  <li>Useful for side hustles</li>
                </ul>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Tax-Efficient Strategies</h3>

            <div class="space-y-4">
              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">1. Maximize Pension Contributions</h4>
                <p class="text-gray-700 text-sm">
                  Get immediate tax relief at your marginal rate (20-45%). Reduces taxable income and can prevent personal allowance taper.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">2. Use ISA Allowances</h4>
                <p class="text-gray-700 text-sm">
                  £20,000 annual allowance for completely tax-free growth and withdrawals. Use it or lose it - doesn't carry forward.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">3. Split Income with Spouse</h4>
                <p class="text-gray-700 text-sm">
                  Transfer assets to lower-earning spouse to use their allowances and lower tax bands. Particularly effective for savings and investment income.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">4. Harvest Capital Gains</h4>
                <p class="text-gray-700 text-sm">
                  Use your £3,000 CGT allowance each year. Sell and repurchase investments to "crystallize" gains within your allowance.
                </p>
              </div>

              <div class="bg-white border border-gray-300 rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-2">5. Salary Sacrifice</h4>
                <p class="text-gray-700 text-sm">
                  Exchange salary for pension contributions or other benefits. Saves income tax and National Insurance for both employee and employer.
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
  name: 'LearningCentre',

  components: {
    PublicLayout,
  },

  data() {
    return {
      activeCategory: 'basics',
      categories: [
        { id: 'basics', name: 'Financial Planning Basics' },
        { id: 'protection', name: 'Protection Planning' },
        { id: 'retirement', name: 'Retirement Planning' },
        { id: 'estate', name: 'Estate Planning' },
        { id: 'investment', name: 'Investment Planning' },
        { id: 'tax', name: 'Tax Planning' },
      ],
    };
  },
};
</script>
