/** @type {import('tailwindcss').Config} */
module.exports = {
	prefix: 'tw-',
	corePlugins: {
		preflight: false,
	},
	important: '#tailsignal-app',
	content: [
		'admin/partials/**/*.php',
		'admin/js/**/*.js',
	],
};
