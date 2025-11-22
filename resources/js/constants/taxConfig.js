/**
 * UK Tax Configuration Constants
 *
 * These values should ideally be fetched from the backend TaxConfigService
 * but are provided as constants for frontend use until a centralized
 * tax config API endpoint is implemented.
 *
 * Values for Tax Year 2025/26
 */

export const TAX_CONFIG = {
  ISA_ANNUAL_ALLOWANCE: 20000,
  LIFETIME_ISA_ALLOWANCE: 4000,
  JUNIOR_ISA_ALLOWANCE: 9000,

  // Add other tax constants as needed
  PERSONAL_ALLOWANCE: 12570,
  PENSION_ANNUAL_ALLOWANCE: 60000,
  CGT_ALLOWANCE: 3000,
};

export default TAX_CONFIG;
