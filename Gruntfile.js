module.exports = function(grunt) {

	/**
	 * Load tasks
	 */
	require('load-grunt-tasks')(grunt);

	/**
	 * Configuration
	 */
	grunt.initConfig({

		/**
		 * Load parameters
		 */
		pkg: grunt.file.readJSON('package.json'),
		tmp: [],

		/**
		 * Compile css
		 */
		less: {
			development: {
				options: {
					paths: ["css"],
					compress: false,
					ieCompat: false,
					plugins: [
						new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]}),
						new (require('less-plugin-clean-css'))({advanced:true})
					]
				},
				files: {
					"assets/css/style.css": "assets/css/style.less"
				}
			}
		},

		uglify: {
			options: {
				preserveComments: 'some',
				compress: {
					drop_console: true
				},
				mangle: {
					except: ['jQuery']
				}
			},
			my_target: {
				files: [{
					'assets/js/edit.min.js': ['assets/js/edit.js']
				}]
			}
		},

		watch: {
			css: {
				files: 'assets/css/*.less',
				tasks: ['less']
			},
			js: {
				files: ['js/*.js','!js/*.min.js'],
				tasks: ['uglify']
			}
		}
	});

	/**
	 * Register tasks
	 */
	grunt.registerTask('default', ['build']);
	grunt.registerTask('build', ['less','uglify']);

};