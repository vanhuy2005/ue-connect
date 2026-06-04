import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * UEConnect Tailwind Configuration
 *
 * Strategy: Extend Tailwind's theme to map CSS variable tokens.
 * Blade components are source of truth for variants; Tailwind provides
 * the utility class foundation.
 *
 * NOTE: Project runs tailwindcss@3.4.x (v3) with laravel-vite-plugin.
 * The @tailwindcss/vite@4 package is installed but NOT active in this pipeline.
 * Sticking with v3 config format. See design-implementation-notes.md.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        {
            pattern: /^ue-select/,
        },
    ],

    theme: {
        extend: {
            /* ----------------------------------------------------------------
               Font Families
               ---------------------------------------------------------------- */
            fontFamily: {
                sans: ['"Be Vietnam Pro"', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
                data: ['"Inter"', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
                mono: ['"JetBrains Mono"', '"SFMono-Regular"', 'Consolas', '"Liberation Mono"', 'monospace'],
            },

            /* ----------------------------------------------------------------
               Font Sizes — social product scale (not landing page)
               Paired with line-height and letter-spacing where defined.
               ---------------------------------------------------------------- */
            fontSize: {
                '2xs': ['0.6875rem', { lineHeight: '1rem' }],     // 11px
                'xs':  ['0.75rem',   { lineHeight: '1rem' }],     // 12px
                'sm':  ['0.8125rem', { lineHeight: '1.125rem' }], // 13px
                'md':  ['0.875rem',  { lineHeight: '1.25rem' }],  // 14px
                'base':['0.9375rem', { lineHeight: '1.4375rem' }],// 15px
                'lg':  ['1rem',      { lineHeight: '1.5rem' }],   // 16px
                'xl':  ['1.125rem',  { lineHeight: '1.75rem' }],  // 18px
                '2xl': ['1.25rem',   { lineHeight: '1.875rem' }], // 20px
                '3xl': ['1.5rem',    { lineHeight: '2rem' }],     // 24px
                '4xl': ['2rem',      { lineHeight: '2.5rem' }],   // 32px
                '5xl': ['2.5rem',    { lineHeight: '3rem' }],     // 40px
            },

            /* ----------------------------------------------------------------
               Colors — mapped to CSS variables for token consistency.
               Components should use semantic tokens (ue-brand, ue-text, etc.)
               not raw palette values.
               ---------------------------------------------------------------- */
            colors: {
                /* Brand blue scale */
                'ue-blue': {
                    50:  'var(--blue-50)',
                    100: 'var(--blue-100)',
                    200: 'var(--blue-200)',
                    300: 'var(--blue-300)',
                    400: 'var(--blue-400)',
                    500: 'var(--blue-500)',
                    600: 'var(--blue-600)',
                    700: 'var(--blue-700)',
                    800: 'var(--blue-800)',
                    900: 'var(--blue-900)',
                },

                /* Neutral scale */
                'ue-neutral': {
                    0:   'var(--neutral-0)',
                    25:  'var(--neutral-25)',
                    50:  'var(--neutral-50)',
                    100: 'var(--neutral-100)',
                    150: 'var(--neutral-150)',
                    200: 'var(--neutral-200)',
                    300: 'var(--neutral-300)',
                    400: 'var(--neutral-400)',
                    500: 'var(--neutral-500)',
                    600: 'var(--neutral-600)',
                    700: 'var(--neutral-700)',
                    800: 'var(--neutral-800)',
                    900: 'var(--neutral-900)',
                    950: 'var(--neutral-950)',
                },

                /* Semantic surface, text, border tokens */
                'ue-bg':              'var(--ue-bg)',
                'ue-surface':         'var(--ue-surface)',
                'ue-surface-subtle':  'var(--ue-surface-subtle)',
                'ue-surface-hover':   'var(--ue-surface-hover)',
                'ue-surface-pressed': 'var(--ue-surface-pressed)',

                'ue-text':           'var(--ue-text)',
                'ue-text-secondary': 'var(--ue-text-secondary)',
                'ue-text-muted':     'var(--ue-text-muted)',
                'ue-text-disabled':  'var(--ue-text-disabled)',
                'ue-text-inverse':   'var(--ue-text-inverse)',

                'ue-border':         'var(--ue-border)',
                'ue-border-strong':  'var(--ue-border-strong)',
                'ue-border-subtle':  'var(--ue-border-subtle)',

                'ue-brand':          'var(--ue-brand)',
                'ue-brand-hover':    'var(--ue-brand-hover)',
                'ue-brand-active':   'var(--ue-brand-active)',
                'ue-brand-soft':     'var(--ue-brand-soft)',
                'ue-brand-soft-hover': 'var(--ue-brand-soft-hover)',
                'ue-brand-border':    'var(--ue-brand-border)',
                'ue-brand-tint':      'var(--ue-brand-tint)',

                /* Semantic status */
                'ue-success':        'var(--success)',
                'ue-warning':        'var(--warning)',
                'ue-danger':         'var(--danger)',
                'ue-info':           'var(--info)',
                'ue-mentor':         'var(--mentor)',
            },

            /* ----------------------------------------------------------------
               Border Radius — from token scale
               ---------------------------------------------------------------- */
            borderRadius: {
                'none':  'var(--radius-none)',
                'xs':    'var(--radius-xs)',
                'sm':    'var(--radius-sm)',
                'md':    'var(--radius-md)',
                'lg':    'var(--radius-lg)',
                'xl':    'var(--radius-xl)',
                '2xl':   'var(--radius-2xl)',
                '3xl':   'var(--radius-3xl)',
                'full':  'var(--radius-full)',
            },

            /* ----------------------------------------------------------------
               Box Shadow — elevation system
               ---------------------------------------------------------------- */
            boxShadow: {
                'none':    'none',
                'xs':      'var(--shadow-xs)',
                'sm':      'var(--shadow-sm)',
                'md':      'var(--shadow-md)',
                'lg':      'var(--shadow-lg)',
                'xl':      'var(--shadow-xl)',
                'brand':   'var(--shadow-brand)',
                'focus':   'var(--focus-ring-brand)',
                'focus-danger': 'var(--focus-ring-danger)',
            },

            /* ----------------------------------------------------------------
               Z-index — from token scale
               ---------------------------------------------------------------- */
            zIndex: {
                'base':       'var(--z-base)',
                'raised':     'var(--z-raised)',
                'sticky':     'var(--z-sticky)',
                'header':     'var(--z-header)',
                'bottom-nav': 'var(--z-bottom-nav)',
                'dropdown':   'var(--z-dropdown)',
                'popover':    'var(--z-popover)',
                'tooltip':    'var(--z-tooltip)',
                'overlay':    'var(--z-overlay)',
                'modal':      'var(--z-modal)',
                'sheet':      'var(--z-sheet)',
                'toast':      'var(--z-toast)',
            },

            /* ----------------------------------------------------------------
               Screens — add xs breakpoint (360px minimum)
               ---------------------------------------------------------------- */
            screens: {
                'xs': '360px',
            },

            /* ----------------------------------------------------------------
               Transition Duration & Timing
               ---------------------------------------------------------------- */
            transitionDuration: {
                'instant': '0ms',
                'xs':      '75ms',
                'sm':      '120ms',
                'md':      '180ms',
                'lg':      '240ms',
                'xl':      '320ms',
            },

            transitionTimingFunction: {
                'standard':   'cubic-bezier(0.2, 0, 0, 1)',
                'out':        'cubic-bezier(0, 0, 0.2, 1)',
                'in':         'cubic-bezier(0.4, 0, 1, 1)',
                'in-out':     'cubic-bezier(0.4, 0, 0.2, 1)',
                'emphasized': 'cubic-bezier(0.16, 1, 0.3, 1)',
            },

            /* ----------------------------------------------------------------
               Min/Max Height for touch targets
               ---------------------------------------------------------------- */
            minHeight: {
                'touch': '44px',
            },

            minWidth: {
                'touch': '44px',
            },
        },
    },

    plugins: [forms],
};
