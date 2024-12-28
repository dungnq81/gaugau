import * as path from 'path';
import autoprefixer from 'autoprefixer';
import viteImagemin from '@vheemstra/vite-plugin-imagemin';
import imageminMozjpeg from 'imagemin-mozjpeg';
import imageminPngquant from 'imagemin-pngquant';
import imageminWebp from 'imagemin-webp';
import imageminSVGO from 'imagemin-svgo';

const isProduction = process.env.NODE_ENV === 'production';

export const sharedConfig = {
	base: './',
	resolve: {
		alias: {
			'@': path.resolve( __dirname ),
			'~': path.resolve( __dirname ),
		},
	},
	plugins: [
		viteImagemin( {
			plugins: {
				jpg: imageminMozjpeg( { quality: 80 } ),
				png: imageminPngquant( { strip: true, quality: [0.7, 0.9] } ),
				svg: imageminSVGO(),
			},
			// makeWebp: {
			//     plugins: {
			//         jpg: imageminWebp(),
			//         png: imageminWebp(),
			//     },
			// },
		} ),
	],
	css: {
		preprocessorOptions: {
			scss: {
				api: 'modern-compiler',
				quietDeps: true,
			},
		},
		postcss: {
			plugins: [
				autoprefixer( {
					remove: false,
					flexbox: 'no-2009',
				} ),
			],
		},
		devSourcemap: !isProduction,
	},
	optimizeDeps: { include: ['jQuery'] },
	define: {
		$: 'jQuery',
		jQuery: 'jQuery',
	},
	build: {
		sourcemap: !isProduction,
		target: 'modules',
		manifest: true,
		minify: isProduction ? 'terser' : false,
		watch: isProduction ? false : { exclude: 'node_modules/**' },
		cssCodeSplit: true,
		emptyOutDir: true,
		terserOptions: {
			compress: {
				drop_console: true,
				toplevel: true,
			},
			format: { comments: false },
		},
	},
};
