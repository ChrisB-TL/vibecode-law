/**
 * Convert a string to a URL-safe slug.
 *
 * - Lowercases the text
 * - Trims whitespace
 * - Removes non-word characters (except spaces and dashes)
 * - Converts spaces, underscores, and dashes to single dashes
 * - Removes leading and trailing dashes
 */
export function slugify(text: string): string {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
