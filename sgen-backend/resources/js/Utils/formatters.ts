/**
 * Shared formatting utilities for SGEN-Support
 */

/**
 * Formats a numeric value as currency (e.g. $1,250.00).
 */
export function formatCurrency(
    val?: number | string | null,
    locale: string = 'en-US',
    currencySymbol: string = '$'
): string {
    if (val === null || val === undefined || val === '') {
        return 'N/A';
    }
    const num = typeof val === 'string' ? parseFloat(val) : val;
    if (isNaN(num)) {
        return 'N/A';
    }
    return `${currencySymbol}${num.toLocaleString(locale, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

/**
 * Formats an ISO or standard date string into a localized human-readable date.
 * Example: "24 sep 2026"
 */
export function formatDate(
    dateStr?: string | null,
    locale: string = 'es-ES',
    options?: Intl.DateTimeFormatOptions
): string {
    if (!dateStr) return 'No especificada';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;

    const defaultOptions: Intl.DateTimeFormatOptions = options ?? {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    };

    return d.toLocaleDateString(locale, defaultOptions);
}

/**
 * Formats an ISO or standard date string into date + time.
 * Example: "24 sep 2026, 08:30"
 */
export function formatDateTime(
    dateStr?: string | null,
    locale: string = 'es-ES'
): string {
    if (!dateStr) return 'No especificada';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;

    return d.toLocaleDateString(locale, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Formats numbers with thousand separators.
 */
export function formatNumber(
    val?: number | string | null,
    locale: string = 'es-ES'
): string {
    if (val === null || val === undefined || val === '') return '0';
    const num = typeof val === 'string' ? parseFloat(val) : val;
    if (isNaN(num)) return '0';
    return num.toLocaleString(locale);
}
