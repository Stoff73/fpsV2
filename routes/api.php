<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EstateController;
use App\Http\Controllers\Api\FamilyMembersController;
use App\Http\Controllers\Api\HolisticPlanningController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\MortgageController;
use App\Http\Controllers\Api\NetWorthController;
use App\Http\Controllers\Api\PersonalAccountsController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ProtectionController;
use App\Http\Controllers\Api\RecommendationsController;
use App\Http\Controllers\Api\RetirementController;
use App\Http\Controllers\Api\SavingsController;
use App\Http\Controllers\Api\SpousePermissionController;
use App\Http\Controllers\Api\UKTaxesController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// User Profile routes (Phase 2)
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // Profile endpoints
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
    Route::put('/profile/personal', [UserProfileController::class, 'updatePersonalInfo']);
    Route::put('/profile/income-occupation', [UserProfileController::class, 'updateIncomeOccupation']);

    // Family Members CRUD
    Route::prefix('family-members')->group(function () {
        Route::get('/', [FamilyMembersController::class, 'index']);
        Route::post('/', [FamilyMembersController::class, 'store']);
        Route::get('/{id}', [FamilyMembersController::class, 'show']);
        Route::put('/{id}', [FamilyMembersController::class, 'update']);
        Route::delete('/{id}', [FamilyMembersController::class, 'destroy']);
    });

    // Personal Accounts (P&L, Cashflow, Balance Sheet)
    Route::prefix('personal-accounts')->group(function () {
        Route::get('/', [PersonalAccountsController::class, 'index']);
        Route::post('/calculate', [PersonalAccountsController::class, 'calculate']);
        Route::post('/line-item', [PersonalAccountsController::class, 'storeLineItem']);
        Route::put('/line-item/{id}', [PersonalAccountsController::class, 'updateLineItem']);
        Route::delete('/line-item/{id}', [PersonalAccountsController::class, 'deleteLineItem']);
    });
});

// Spouse Permission routes
Route::middleware('auth:sanctum')->prefix('spouse-permission')->group(function () {
    Route::get('/status', [SpousePermissionController::class, 'status']);
    Route::post('/request', [SpousePermissionController::class, 'request']);
    Route::post('/accept', [SpousePermissionController::class, 'accept']);
    Route::post('/reject', [SpousePermissionController::class, 'reject']);
    Route::delete('/revoke', [SpousePermissionController::class, 'revoke']);
});

// Net Worth routes (Phase 3)
Route::middleware('auth:sanctum')->prefix('net-worth')->group(function () {
    Route::get('/overview', [NetWorthController::class, 'getOverview']);
    Route::get('/breakdown', [NetWorthController::class, 'getBreakdown']);
    Route::get('/trend', [NetWorthController::class, 'getTrend']);
    Route::get('/assets-summary', [NetWorthController::class, 'getAssetsSummary']);
    Route::get('/joint-assets', [NetWorthController::class, 'getJointAssets']);
    Route::post('/refresh', [NetWorthController::class, 'refresh']);
});

// Property routes (Phase 4)
Route::middleware('auth:sanctum')->prefix('properties')->group(function () {
    // Property CRUD
    Route::get('/', [PropertyController::class, 'index']);
    Route::post('/', [PropertyController::class, 'store']);
    Route::get('/{id}', [PropertyController::class, 'show']);
    Route::put('/{id}', [PropertyController::class, 'update']);
    Route::delete('/{id}', [PropertyController::class, 'destroy']);

    // Tax calculations
    Route::post('/calculate-sdlt', [PropertyController::class, 'calculateSDLT']);
    Route::post('/{id}/calculate-cgt', [PropertyController::class, 'calculateCGT']);
    Route::post('/{id}/rental-income-tax', [PropertyController::class, 'calculateRentalIncomeTax']);

    // Mortgages for a property
    Route::prefix('{propertyId}/mortgages')->group(function () {
        Route::get('/', [MortgageController::class, 'index']);
        Route::post('/', [MortgageController::class, 'store']);
    });
});

// Mortgage routes (Phase 4)
Route::middleware('auth:sanctum')->prefix('mortgages')->group(function () {
    Route::get('/{id}', [MortgageController::class, 'show']);
    Route::put('/{id}', [MortgageController::class, 'update']);
    Route::delete('/{id}', [MortgageController::class, 'destroy']);
    Route::get('/{id}/amortization-schedule', [MortgageController::class, 'amortizationSchedule']);
    Route::post('/calculate-payment', [MortgageController::class, 'calculatePayment']);
});

// Dashboard routes (aggregated data from all modules)
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/financial-health-score', [DashboardController::class, 'financialHealthScore']);
    Route::get('/alerts', [DashboardController::class, 'alerts']);
    Route::post('/alerts/{id}/dismiss', [DashboardController::class, 'dismissAlert']);
    Route::post('/invalidate-cache', [DashboardController::class, 'invalidateCache']);
});

// Protection module routes
Route::middleware('auth:sanctum')->prefix('protection')->group(function () {
    // Main protection data and analysis
    Route::get('/', [ProtectionController::class, 'index']);
    Route::post('/analyze', [ProtectionController::class, 'analyze']);
    Route::get('/recommendations', [ProtectionController::class, 'recommendations']);
    Route::post('/scenarios', [ProtectionController::class, 'scenarios']);

    // Protection profile
    Route::post('/profile', [ProtectionController::class, 'storeProfile']);

    // Life insurance policies
    Route::prefix('policies/life')->group(function () {
        Route::post('/', [ProtectionController::class, 'storeLifePolicy']);
        Route::put('/{id}', [ProtectionController::class, 'updateLifePolicy']);
        Route::delete('/{id}', [ProtectionController::class, 'destroyLifePolicy']);
    });

    // Critical illness policies
    Route::prefix('policies/critical-illness')->group(function () {
        Route::post('/', [ProtectionController::class, 'storeCriticalIllnessPolicy']);
        Route::put('/{id}', [ProtectionController::class, 'updateCriticalIllnessPolicy']);
        Route::delete('/{id}', [ProtectionController::class, 'destroyCriticalIllnessPolicy']);
    });

    // Income protection policies
    Route::prefix('policies/income-protection')->group(function () {
        Route::post('/', [ProtectionController::class, 'storeIncomeProtectionPolicy']);
        Route::put('/{id}', [ProtectionController::class, 'updateIncomeProtectionPolicy']);
        Route::delete('/{id}', [ProtectionController::class, 'destroyIncomeProtectionPolicy']);
    });

    // Disability policies
    Route::prefix('policies/disability')->group(function () {
        Route::post('/', [ProtectionController::class, 'storeDisabilityPolicy']);
        Route::put('/{id}', [ProtectionController::class, 'updateDisabilityPolicy']);
        Route::delete('/{id}', [ProtectionController::class, 'destroyDisabilityPolicy']);
    });

    // Sickness/Illness policies
    Route::prefix('policies/sickness-illness')->group(function () {
        Route::post('/', [ProtectionController::class, 'storeSicknessIllnessPolicy']);
        Route::put('/{id}', [ProtectionController::class, 'updateSicknessIllnessPolicy']);
        Route::delete('/{id}', [ProtectionController::class, 'destroySicknessIllnessPolicy']);
    });
});

// Savings module routes
Route::middleware('auth:sanctum')->prefix('savings')->group(function () {
    // Main savings data and analysis
    Route::get('/', [SavingsController::class, 'index']);
    Route::post('/analyze', [SavingsController::class, 'analyze']);
    Route::get('/recommendations', [SavingsController::class, 'recommendations']);
    Route::post('/scenarios', [SavingsController::class, 'scenarios']);

    // ISA allowance tracking
    Route::get('/isa-allowance/{taxYear}', [SavingsController::class, 'isaAllowance']);

    // Savings accounts
    Route::prefix('accounts')->group(function () {
        Route::post('/', [SavingsController::class, 'storeAccount']);
        Route::put('/{id}', [SavingsController::class, 'updateAccount']);
        Route::delete('/{id}', [SavingsController::class, 'destroyAccount']);
    });

    // Savings goals
    Route::prefix('goals')->group(function () {
        Route::get('/', [SavingsController::class, 'indexGoals']);
        Route::post('/', [SavingsController::class, 'storeGoal']);
        Route::put('/{id}', [SavingsController::class, 'updateGoal']);
        Route::delete('/{id}', [SavingsController::class, 'destroyGoal']);
        Route::patch('/{id}/progress', [SavingsController::class, 'updateGoalProgress']);
    });
});

// Investment module routes
Route::middleware('auth:sanctum')->prefix('investment')->group(function () {
    // Main investment data and analysis
    Route::get('/', [InvestmentController::class, 'index']);
    Route::post('/analyze', [InvestmentController::class, 'analyze']);
    Route::get('/recommendations', [InvestmentController::class, 'recommendations']);
    Route::post('/scenarios', [InvestmentController::class, 'scenarios']);

    // Monte Carlo simulation
    Route::post('/monte-carlo', [InvestmentController::class, 'startMonteCarlo']);
    Route::get('/monte-carlo/{jobId}', [InvestmentController::class, 'getMonteCarloResults']);

    // Investment accounts
    Route::prefix('accounts')->group(function () {
        Route::post('/', [InvestmentController::class, 'storeAccount']);
        Route::put('/{id}', [InvestmentController::class, 'updateAccount']);
        Route::delete('/{id}', [InvestmentController::class, 'destroyAccount']);
    });

    // Holdings
    Route::prefix('holdings')->group(function () {
        Route::post('/', [InvestmentController::class, 'storeHolding']);
        Route::put('/{id}', [InvestmentController::class, 'updateHolding']);
        Route::delete('/{id}', [InvestmentController::class, 'destroyHolding']);
    });

    // Investment goals
    Route::prefix('goals')->group(function () {
        Route::post('/', [InvestmentController::class, 'storeGoal']);
        Route::put('/{id}', [InvestmentController::class, 'updateGoal']);
        Route::delete('/{id}', [InvestmentController::class, 'destroyGoal']);
    });

    // Risk profile
    Route::post('/risk-profile', [InvestmentController::class, 'storeOrUpdateRiskProfile']);
});

// Estate Planning module routes
Route::middleware('auth:sanctum')->prefix('estate')->group(function () {
    // Main estate data and analysis
    Route::get('/', [EstateController::class, 'index']);
    Route::post('/analyze', [EstateController::class, 'analyze']);
    Route::get('/recommendations', [EstateController::class, 'recommendations']);
    Route::post('/scenarios', [EstateController::class, 'scenarios']);

    // IHT calculation and net worth
    Route::post('/calculate-iht', [EstateController::class, 'calculateIHT']);
    Route::get('/net-worth', [EstateController::class, 'getNetWorth']);
    Route::get('/cash-flow', [EstateController::class, 'getCashFlow']);

    // IHT Profile
    Route::post('/profile', [EstateController::class, 'storeOrUpdateIHTProfile']);

    // Assets
    Route::prefix('assets')->group(function () {
        Route::post('/', [EstateController::class, 'storeAsset']);
        Route::put('/{id}', [EstateController::class, 'updateAsset']);
        Route::delete('/{id}', [EstateController::class, 'destroyAsset']);
    });

    // Liabilities
    Route::prefix('liabilities')->group(function () {
        Route::post('/', [EstateController::class, 'storeLiability']);
        Route::put('/{id}', [EstateController::class, 'updateLiability']);
        Route::delete('/{id}', [EstateController::class, 'destroyLiability']);
    });

    // Gifts
    Route::prefix('gifts')->group(function () {
        Route::post('/', [EstateController::class, 'storeGift']);
        Route::put('/{id}', [EstateController::class, 'updateGift']);
        Route::delete('/{id}', [EstateController::class, 'destroyGift']);
    });

    // Trusts
    Route::prefix('trusts')->group(function () {
        Route::get('/', [EstateController::class, 'getTrusts']);
        Route::post('/', [EstateController::class, 'createTrust']);
        Route::put('/{id}', [EstateController::class, 'updateTrust']);
        Route::delete('/{id}', [EstateController::class, 'deleteTrust']);
        Route::get('/{id}/analyze', [EstateController::class, 'analyzeTrust']);
        Route::get('/{id}/assets', [EstateController::class, 'getTrustAssets']);
        Route::post('/{id}/calculate-iht-impact', [EstateController::class, 'calculateTrustIHTImpact']);
    });

    // Trust planning and tax returns
    Route::get('/trust-recommendations', [EstateController::class, 'getTrustRecommendations']);
    Route::get('/trusts/upcoming-tax-returns', [EstateController::class, 'getUpcomingTaxReturns']);

    // Will and Bequests
    Route::get('/will', [EstateController::class, 'getWill']);
    Route::post('/will', [EstateController::class, 'storeOrUpdateWill']);
    Route::prefix('bequests')->group(function () {
        Route::get('/', [EstateController::class, 'getBequests']);
        Route::post('/', [EstateController::class, 'storeBequest']);
        Route::put('/{id}', [EstateController::class, 'updateBequest']);
        Route::delete('/{id}', [EstateController::class, 'deleteBequest']);
    });
    Route::post('/calculate-discount', [EstateController::class, 'calculateDiscountedGiftDiscount']);
});

// Retirement module routes
Route::middleware('auth:sanctum')->prefix('retirement')->group(function () {
    // Main retirement data and analysis
    Route::get('/', [RetirementController::class, 'index']);
    Route::post('/analyze', [RetirementController::class, 'analyze']);
    Route::get('/recommendations', [RetirementController::class, 'recommendations']);
    Route::post('/scenarios', [RetirementController::class, 'scenarios']);

    // Annual allowance checking
    Route::get('/annual-allowance/{taxYear}', [RetirementController::class, 'checkAnnualAllowance']);

    // DC pensions
    Route::prefix('pensions/dc')->group(function () {
        Route::post('/', [RetirementController::class, 'storeDCPension']);
        Route::put('/{id}', [RetirementController::class, 'updateDCPension']);
        Route::delete('/{id}', [RetirementController::class, 'destroyDCPension']);
    });

    // DB pensions
    Route::prefix('pensions/db')->group(function () {
        Route::post('/', [RetirementController::class, 'storeDBPension']);
        Route::put('/{id}', [RetirementController::class, 'updateDBPension']);
        Route::delete('/{id}', [RetirementController::class, 'destroyDBPension']);
    });

    // State pension
    Route::post('/state-pension', [RetirementController::class, 'updateStatePension']);
});

// Holistic Planning routes (coordinating agent)
Route::middleware('auth:sanctum')->prefix('holistic')->group(function () {
    // Main holistic analysis and plan
    Route::post('/analyze', [HolisticPlanningController::class, 'analyze']);
    Route::post('/plan', [HolisticPlanningController::class, 'plan']);
    Route::get('/recommendations', [HolisticPlanningController::class, 'recommendations']);
    Route::get('/cash-flow-analysis', [HolisticPlanningController::class, 'cashFlowAnalysis']);

    // Recommendation tracking
    Route::post('/recommendations/{id}/mark-done', [HolisticPlanningController::class, 'markRecommendationDone']);
    Route::post('/recommendations/{id}/in-progress', [HolisticPlanningController::class, 'markRecommendationInProgress']);
    Route::post('/recommendations/{id}/dismiss', [HolisticPlanningController::class, 'dismissRecommendation']);
    Route::get('/recommendations/completed', [HolisticPlanningController::class, 'completedRecommendations']);
    Route::patch('/recommendations/{id}/notes', [HolisticPlanningController::class, 'updateRecommendationNotes']);
});

// Unified Recommendations routes (Phase 5)
Route::middleware('auth:sanctum')->prefix('recommendations')->group(function () {
    // Main recommendations endpoints
    Route::get('/', [RecommendationsController::class, 'index']);
    Route::get('/summary', [RecommendationsController::class, 'summary']);
    Route::get('/top', [RecommendationsController::class, 'top']);
    Route::get('/completed', [RecommendationsController::class, 'completed']);

    // Recommendation tracking actions
    Route::post('/{id}/mark-done', [RecommendationsController::class, 'markDone']);
    Route::post('/{id}/in-progress', [RecommendationsController::class, 'markInProgress']);
    Route::post('/{id}/dismiss', [RecommendationsController::class, 'dismiss']);
    Route::patch('/{id}/notes', [RecommendationsController::class, 'updateNotes']);
});

// UK Taxes & Allowances routes (Admin only)
Route::middleware(['auth:sanctum', 'admin'])->prefix('uk-taxes')->group(function () {
    Route::get('/', [UKTaxesController::class, 'index']);
});
