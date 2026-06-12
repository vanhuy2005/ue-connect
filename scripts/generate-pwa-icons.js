import fs from 'fs';
import path from 'path';
import sharp from 'sharp';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const SOURCE_ICON = path.join(__dirname, '../public/images/brand/app-icon-nobg.png');
const OUTPUT_DIR = path.join(__dirname, '../public/icons');
const THEME_COLOR = '#124874';

const SIZES = [72, 96, 128, 144, 152, 180, 192, 384, 512];

async function generateIcons() {
    if (!fs.existsSync(SOURCE_ICON)) {
        console.error(`Source icon not found at ${SOURCE_ICON}`);
        process.exit(1);
    }

    if (!fs.existsSync(OUTPUT_DIR)) {
        fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }

    console.log('Generating standard icons...');
    for (const size of SIZES) {
        await sharp(SOURCE_ICON)
            .resize(size, size, { fit: 'contain' })
            .toFile(path.join(OUTPUT_DIR, `icon-${size}x${size}.png`));
        console.log(`Created icon-${size}x${size}.png`);
    }

    console.log('\nGenerating maskable icons...');
    // Maskable icons need safe padding so they aren't cropped by Android's adaptive icons.
    // The safe zone is a circle with radius 0.4 of the icon size (i.e. 80% of width).
    // We add padding and fill the background with the theme color.
    const maskableSizes = [192, 512];
    for (const size of maskableSizes) {
        const padding = Math.round(size * 0.15); // 15% padding
        await sharp(SOURCE_ICON)
            .resize(size - padding * 2, size - padding * 2, { fit: 'contain' })
            .extend({
                top: padding,
                bottom: padding,
                left: padding,
                right: padding,
                background: THEME_COLOR
            })
            .toFile(path.join(OUTPUT_DIR, `maskable-icon-${size}x${size}.png`));
        console.log(`Created maskable-icon-${size}x${size}.png`);
    }

    // Apple touch icon (180x180) typically requires a solid background, not transparent.
    console.log('\nGenerating apple-touch-icon...');
    await sharp(SOURCE_ICON)
        .resize(180, 180, { fit: 'contain' })
        .flatten({ background: '#ffffff' })
        .toFile(path.join(OUTPUT_DIR, 'apple-touch-icon.png'));
    console.log('Created apple-touch-icon.png');

    console.log('\nDone!');
}

generateIcons().catch(console.error);
