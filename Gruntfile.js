module.exports = function(grunt) {

	var package_json = grunt.file.readJSON('package.json');
	
	var joe_path = 'Joe/';
	var joe_inc_path = joe_path + 'inc/';
	var build_path = 'build/';
	var app_path = 'App/';
	var app_slug = package_json.slug;
	var main_file_name = String(app_slug + '.php');

	var assets_path = 'assets/';
		
	var copy_plugin_files = {};
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
		copy_plugin_files[to_path] = [ from_path ];

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

	//Readme
	var to_path = String(build_path + 'readme.txt');
	var from_path = String('readme.txt');
	copy_plugin_files[to_path] = [ from_path ];
	
	//Main
	var to_path = String(build_path + main_file_name);
	var from_path = String(main_file_name);
	copy_plugin_files[to_path] = [ from_path ];
	replace_files[to_path] = [ to_path ];

	//Includes
	for(i in app_files) {
		var file_name = String(app_files[i]);
		var to_path = String(build_path + app_path + file_name);
		var from_path = String(app_path + file_name);

		copy_plugin_files[to_path] = [ from_path ];
		replace_files[to_path] = [ to_path ];
	}
	
	//Init
  grunt.option('stack', true);		
  grunt.initConfig({
    pkg: package_json,

		compress: {
			plugin: {
				options: {
					archive: app_slug + '.zip'
				},
				expand: true,
				cwd: 'build/',
				src: ['**/*']
			}
		},

		wp_readme_to_markdown: {
			plugin: {
				files: {
					'readme.md': 'readme.txt'
				},
// 				options: {
// 					screenshot_url: 'https://ps.w.org/{plugin}/assets/{screenshot}.jpg',
// 					post_convert: function(content) {
// 						//Remove unsupported Vimeo tags
// 						return content.replace(/\[vimeo(.*)\]\n*/g, '');
// 					}
// 				}				
			}
		},

		makepot: {
			plugin: {
				options: {
					cwd: 'build',
					mainFile: main_file_name,
					potHeaders: {
						poedit: true
					},
          updateTimestamp: false,
// 					processPot: null,
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
			plugin_files: {
				files: copy_plugin_files
			},
			joe_assets: {
				files: [{
					'assets/css/joe-admin.min.css': [ 'Joe/Assets/css/admin.min.css' ],
					'assets/css/joe-admin.css': [ 'Joe/Assets/css/admin.css' ],
					'assets/js/joe-admin.min.js': [ 'Joe/Assets/js/admin.min.js' ],				
					'assets/js/joe-admin.js': [ 'Joe/Assets/js/admin.js' ],				
				}]
			},
			app_assets: {
				files: [
					//App
					{
						expand: true,
						cwd: assets_path,
						src: [
							'css/*.css',
	// 						'css/images/**',
							'js/*.js',
							'img/**',
							'geo/**'
						],
						dest: 'build/assets/'
					}
				]
			}		
		},
		
		less: {
			wp_css: {
				files: {
					'assets/css/shared.css': 'assets/less/shared.less',
					'assets/css/front.css': 'assets/less/front.less',
// 					'assets/css/admin.css': 'assets/less/admin.less',
					'assets/css/shortcode.css': 'assets/less/shortcode.less'					
				}
			}		
		},
		
// 		concat: {
// 			wp_css: {
// 				files: {
// 					'assets/css/admin.css': [
// 						'assets/css/shared.css',
// 						'assets/css/admin.css'],					
// 				}
// 			},
// 			wp_js: {
// 				files: {
// 					'assets/js/admin.js': [
// 						'assets/js/admin.js'
// 					],					
// 				}
// 			}
// 		},	
		
		terser: {
			wp_js: {
				files: {
					'assets/js/leaflet.min.js': ['assets/js/leaflet.js'],
					'assets/js/shortcode.min.js': ['assets/js/shortcode.js'],
// 					'assets/js/admin.min.js': ['assets/js/admin.js']					
				}
			}			
		},
		
		cssmin: {
			wp_css: {
				files: {
					'assets/css/leaflet.min.css': 'assets/css/leaflet.css',
// 					'assets/css/front.min.css': 'assets/css/front.css',
// 					'assets/css/admin.min.css': 'assets/css/admin.css',
					'assets/css/shortcode.min.css': 'assets/css/shortcode.css'					
				}
			}	
		},
		
		watch: {
// 			options: {
// 				livereload: true,
// 			},						
			joe_assets: {
				files: ['Joe/assets/*/**'],
				tasks: ['copy:joe_assets']			
			},
			wp_css: {
				files: ['assets/less/*.less'],
				tasks: ['build_wp_css']
			},
			wp_js: {
				files: ['assets/js/*.js'],
				tasks: ['build_wp_js']
			},
			readme: {
				files: ['readme.txt'],
				tasks: ['wp_readme_to_markdown']			
			}		
		}
  });

 	grunt.loadNpmTasks('grunt-terser');
  grunt.loadNpmTasks('grunt-contrib-copy');  
//   grunt.loadNpmTasks('grunt-contrib-concat');  
	grunt.loadNpmTasks('grunt-contrib-cssmin');  
	grunt.loadNpmTasks('grunt-contrib-less');  
	grunt.loadNpmTasks('grunt-string-replace');  
  grunt.loadNpmTasks('grunt-contrib-watch');	
  grunt.loadNpmTasks('grunt-wp-i18n');
 	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
	grunt.loadNpmTasks('grunt-contrib-compress'); 	

  grunt.registerTask('build_wp_css', [
   	'less:wp_css',
//  		'concat:wp_css',   	
   	'cssmin:wp_css'
  ]); 

  grunt.registerTask('build_wp_js', [
//  		'concat:wp_js',
   	'terser:wp_js'
  ]);           
  
  grunt.registerTask('build_plugin', [
		'copy:plugin_files',
		'copy:joe_assets',
		'copy:app_assets',		
		'string-replace:php',
		'makepot',
		'compress',
		'wp_readme_to_markdown'
  ]); 
    
  grunt.registerTask('default', [
  	'build_plugin',
  	'build_wp_css',
   	'build_wp_js',  	
  	'watch'
  ]);   
};
