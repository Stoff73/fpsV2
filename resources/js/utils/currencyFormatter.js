/**
 * Currency Formatter Utility
 * Provides functions for formatting and parsing UK currency values
 */

/**
 * Format a number as UK currency (£X,XXX.XX)
 * @param {number|string} value - The value to format
 * @param {number} decimals - Number of decimal places (default: 2)
 * @returns {string} Formatted currency string
 */
export function formatCurrency(value, decimals = 2) {
  // Handle null, undefined, or empty values
  if (value === null || value === undefined || value === '') {
    return '£0.00';
  }

  // Convert to number if string
  const numValue = typeof value === 'string' ? parseFloat(value) : value;

  // Check if valid number
  if (isNaN(numValue)) {
    return '£0.00';
  }

  // Format using UK locale
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'GBP',
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(numValue);
}

/**
 * Parse a currency string to a float number
 * Handles formats like: "£1,234.56", "1234.56", "£1234", etc.
 * @param {string} currencyString - The currency string to parse
 * @returns {number} Parsed float value
 */
export function parseCurrency(currencyString) {
  // Handle null, undefined, or empty values
  if (!currencyString || currencyString === '') {
    return 0;
  }

  // Convert to string if not already
  const str = String(currencyString);

  // Remove currency symbol, spaces, and commas
  const cleaned = str.replace(/[£\s,]/g, '');

  // Parse to float
  const value = parseFloat(cleaned);

  // Return 0 if invalid
  return isNaN(value) ? 0 : value;
}

/**
 * Format a number as a compact currency (e.g., £1.2M, £350K)
 * @param {number|string} value - The value to format
 * @returns {string} Compact formatted currency string
 */
export function formatCurrencyCompact(value) {
  // Handle null, undefined, or empty values
  if (value === null || value === undefined || value === '') {
    return '£0';
  }

  // Convert to number if string
  const numValue = typeof value === 'string' ? parseFloat(value) : value;

  // Check if valid number
  if (isNaN(numValue)) {
    return '£0';
  }

  // Format based on magnitude
  if (Math.abs(numValue) >= 1000000) {
    return `£${(numValue / 1000000).toFixed(1)}M`;
  } else if (Math.abs(numValue) >= 1000) {
    return `£${(numValue / 1000).toFixed(1)}K`;
  } else {
    return formatCurrency(numValue, 0);
  }
}

/**
 * Format a percentage value
 * @param {number|string} value - The value to format (e.g., 0.05 or 5)
 * @param {boolean} isDecimal - Whether the value is in decimal format (0.05 vs 5)
 * @param {number} decimals - Number of decimal places (default: 2)
 * @returns {string} Formatted percentage string
 */
export function formatPercentage(value, isDecimal = true, decimals = 2) {
  // Handle null, undefined, or empty values
  if (value === null || value === undefined || value === '') {
    return '0%';
  }

  // Convert to number if string
  const numValue = typeof value === 'string' ? parseFloat(value) : value;

  // Check if valid number
  if (isNaN(numValue)) {
    return '0%';
  }

  // Convert decimal to percentage if needed
  const percentValue = isDecimal ? numValue * 100 : numValue;

  return `${percentValue.toFixed(decimals)}%`;
}

export default {
  formatCurrency,
  parseCurrency,
  formatCurrencyCompact,
  formatPercentage,
};
