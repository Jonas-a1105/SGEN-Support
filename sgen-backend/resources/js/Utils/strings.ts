/**
 * String and text manipulation utilities
 */

/**
 * Extracts initials from a full name (e.g. "John Doe" -> "JD").
 */
export function getInitials(name?: string | null, maxChars: number = 2): string {
    if (!name || !name.trim()) return '??';
    const clean = name.trim();
    const parts = clean.split(/\s+/);
    if (parts.length === 1) {
        return parts[0].substring(0, maxChars).toUpperCase();
    }
    return (parts[0][0] + parts[1][0]).substring(0, maxChars).toUpperCase();
}

/**
 * Truncates text with an ellipsis if it exceeds maxLength.
 */
export function truncate(text?: string | null, maxLength: number = 50): string {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Capitalizes the first letter of a string.
 */
export function capitalize(str?: string | null): string {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
