module.exports = function(grunt) {

	var package_json = grunt.file.readJSON('package.json');
	
	var joe_path = 'Joe/';
	var joe_inc_path = joe_path + 'inc/';
	var build_path = 'build/';
	var app_path = 'App/';
	var app_slug = package_json.slug;
	var main_file_name = String(app_slug + '.php');

	var assets_path = 'assets/';
		
	var copy_php_files = {};
	var replace_files = {};
	var joe_replacements = [];

	var joe_files = grunt.file.expand({ filter: "isFile",	cwd: joe_inc_path }, ["*.php"]);	
	var app_files = grunt.file.expand({	filter: "isFile",	cwd: app_path }, ["*.php"]);	

	if(! joe_files.length || ! app_files.length) {
		return;
	}
	
	//Joe
	for(i in joe_files) {
		var file_name = String(joe_files[i]);

		//Skip files starting with an underscore "_"
		if(file_name.indexOf('_') === 0) {
			continue;		
		}

		//Copy
		var to_path = String(build_path + joe_path + file_name);
		var from_path = String(joe_inc_path + file_name);
		copy_php_files[to_path] = [ from_path ];

		//String Replace
		var class_name = file_name.replace('.php', '');
		var joe_class_old = 'Joe_' + class_name;		
		var joe_class_new = 'Joe_v' + package_json.version.replace('.', '_') + '_' + class_name;
		joe_replacements.push({
			pattern: new RegExp(joe_class_old, 'g'),
			replacement: joe_class_new
		});		
		replace_files[to_path] = [ to_path ];		
	}
	
// 	console.log(joe_replacements);
// 	
// 	return false;

	//App
	
	//Main
	var to_path = String(build_path + main_file_name);
	var from_path = String(main_file_name);
	copy_php_files[to_path] = [ from_path ];
	replace_files[to_path] = [ to_path ];

	//Includes
	for(i in app_files) {
		var file_name = String(app_files[i]);
		var to_path = String(build_path + app_path + file_name);
		var from_path = String(app_path + file_name);

		copy_php_files[to_path] = [ from_path ];
		replace_files[to_path] = [ to_path ];
	}
	
	//Init
  grunt.option('stack', true);		
  grunt.initConfig({
    pkg: package_json,

		makepot: {
			target: {
				options: {
					cwd: 'build',                          // Directory of files to internationalize.
					domainPath: '',                   // Where to save the POT file.
					exclude: [],                      // List of files or directories to ignore.
					include: [],                      // List of files or directories to include.
					mainFile: main_file_name,                     // Main project file.
					potComments: '',                  // The copyright at the beginning of the POT file.
					potFilename: '',                  // Name of the POT file.
					potHeaders: {
							poedit: true,                 // Includes common Poedit headers.
							'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},                                // Headers to add to the generated POT file.
					processPot: null,                 // A callback function for manipulating the POT file.
					type: 'wp-plugin',                // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true,            // Whether the POT-Creation-Date should be updated without other changes.
					updatePoFiles: false              // Whether to update PO files in the same directory as the POT file.
				}
			}
		},

		'string-replace': {
			php: {
				files: replace_files,
				options: {
					replacements: joe_replacements
				}
			}
		},
		
		copy: {
			php: {
				files: copy_php_files
			},
			joe_assets: {
				files: [{
					'assets/css/joe-admin.min.css': [ 'Joe/Assets/css/admin.min.css' ],
					'assets/js/joe-admin.min.js': [ 'Joe/Assets/js/admin.min.js' ],				
				}]
			},
			app_assets: {
				files: [{
					expand: true,
					cwd: assets_path,
					src: [
						'css/*.min.css',
// 						'css/images/**',
						'js/*.min.js',
						'img/**',
						'geo/**'
					],
					dest: 'build/assets/'
				}]
			}		
		},
		
		less: {
			wp_css: {
				files: {
					'assets/css/shared.css': 'assets/less/shared.less',
					'assets/css/front.css': 'assets/less/front.less',
					'assets/css/admin.css': 'assets/less/admin.less',
					'assets/css/shortcode.css': 'assets/less/shortcode.less'					
				}
			}		
		},
		
		concat: {
			wp_css: {
				files: {
					'assets/css/admin.css': [
						'assets/css/shared.css',
						'assets/css/admin.css'],					
				}
			},
			wp_js: {
				files: {
					'assets/js/admin.js': [
						'assets/js/admin.js'
					],					
				}
			}
		},	
		
		terser: {
			wp_js: {
				files: {
					'assets/js/leaflet.min.js': ['assets/js/leaflet.js'],
					'assets/js/shortcode.min.js': ['assets/js/shortcode.js'],
					'assets/js/admin.min.js': ['assets/js/admin.js']					
				}
			}			
		},
		
		cssmin: {
			wp_css: {
				files: {
					'assets/css/leaflet.min.css': 'assets/css/leaflet.css',
// 					'assets/css/front.min.css': 'assets/css/front.css',
					'assets/css/admin.min.css': 'assets/css/admin.css',
					'assets/css/shortcode.min.css': 'assets/css/shortcode.css'					
				}
			}	
		},
		
		watch: {				
			wp_css: {
				files: ['assets/less/*.less'],
				tasks: ['build_wp_css']
			},
			wp_js: {
				files: ['assets/js/*.js'],
				tasks: ['build_wp_js']
			}		
		}
  });

 	grunt.loadNpmTasks('grunt-terser');
  grunt.loadNpmTasks('grunt-contrib-copy');  
  grunt.loadNpmTasks('grunt-contrib-concat');  
	grunt.loadNpmTasks('grunt-contrib-cssmin');  
	grunt.loadNpmTasks('grunt-contrib-less');  
	grunt.loadNpmTasks('grunt-string-replace');  
  grunt.loadNpmTasks('grunt-contrib-watch');	
  grunt.loadNpmTasks('grunt-wp-i18n');

  grunt.registerTask('build_wp_css', [
   	'less:wp_css',
 		'concat:wp_css',   	
   	'cssmin:wp_css'
  ]); 

  grunt.registerTask('build_wp_js', [
 		'concat:wp_js',
   	'terser:wp_js'
  ]);           
  
  grunt.registerTask('build_plugin', [
		'copy:php',
		'copy:joe_assets',
		'copy:app_assets',		
		'string-replace:php',
		'makepot'
  ]); 
    
  grunt.registerTask('default', [
  	'build_plugin',
  	'build_wp_css',
   	'build_wp_js',  	
  	'watch'
  ]);   
};
