import * as path from 'path';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import { sharedConfig } from '../../../../vite.config.shared.js';

const directory = path.basename( path.resolve( __dirname ) );
const dir = `./web/app/plugins/${ directory }`;
const resources = `${ dir }/resources`;
const assets = `${ dir }/assets`;
const node_modules = './node_modules';

// COPY
const directoriesToCopy = [
    { src: `${ resources }/img`, dest: '' },
    { src: `${ node_modules }/select2/dist/js/select2.full.min.js`, dest: 'js' },
    { src: `${ node_modules }/select2/dist/css/select2.min.css`, dest: 'css' },
];

// SASS
const sassFiles = [
    'admin_addons'
];

// JS
const jsFiles = [
    'custom_sorting',
    'lazyload',
    'recaptcha',
    'select2',
    'admin_addons2',
];

export default {
    ...sharedConfig,
    plugins: [
        ...sharedConfig.plugins,
        viteStaticCopy( {
            targets: directoriesToCopy,
        } ),
    ],
    build: {
        ...sharedConfig.build,
        outDir: `${ assets }`,
        rollupOptions: {
            input: [
                ...sassFiles.map( ( file ) => path.resolve( `${ resources }/sass/${ file }.scss` ) ),
                ...jsFiles.map( ( file ) => path.resolve( `${ resources }/js/${ file }.js` ) ),
            ],
            output: {
                entryFileNames: `js/[name].js`,
                chunkFileNames: `js/[name].js`,
                assetFileNames: ( assetInfo ) => {
                    if ( assetInfo.name.endsWith( '.css' ) ) {
                        return `css/[name].[ext]`;
                    }
                    return `img/[name].[ext]`;
                },
                manualChunks( id ) {
                    if ( id.includes( 'node_modules' ) ) {
                        return '_vendor';
                    }
                },
            },
        }
    }
}
