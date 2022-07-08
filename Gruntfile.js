module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
		
		less: {
			wp_css: {
				files: {
					'App/Assets/css/shared.css': 'App/Assets/less/shared.less',
					'App/Assets/css/front.css': 'App/Assets/less/front.less',
					'App/Assets/css/admin.css': 'App/Assets/less/admin.less'
				}
			}		
		},
		
		concat: {
			wp_css: {
				files: {
					'App/Assets/css/front.css': ['App/Assets/css/shared.css', 'App/Assets/css/front.css'],
					'App/Assets/css/admin.css': ['App/Assets/css/shared.css', 'App/Assets/css/admin.css'],					
				}
			},
			wp_js: {
				files: {
					'App/Assets/js/front.min.js': ['App/Assets/js/shared.js', 'App/Assets/js/front.js'],
					'App/Assets/js/admin.min.js': ['App/Assets/js/shared.js', 'App/Assets/js/admin.js'],					
				}
			}			
		},	
		
		terser: {
			wp_js: {
				files: {
					'App/Assets/js/front.min.js': ['App/Assets/js/front.min.js'],
					'App/Assets/js/admin.min.js': ['App/Assets/js/admin.min.js']					
				}
			}			
		},
		
		cssmin: {
			wp_css: {
				files: {
					'App/Assets/css/front.min.css': 'App/Assets/css/front.css',
					'App/Assets/css/admin.min.css': 'App/Assets/css/admin.css'
				}
			}	
		},
		
		watch: {				
			wp_css: {
				files: ['App/Assets/less/*.less'],
				tasks: ['build_wp_css']
			},
			wp_js: {
				files: ['App/Assets/js/*.js'],
				tasks: ['build_wp_js']
			}		
		}
  });

 	grunt.loadNpmTasks('grunt-terser');
  grunt.loadNpmTasks('grunt-contrib-concat');  
	grunt.loadNpmTasks('grunt-contrib-cssmin');  
	grunt.loadNpmTasks('grunt-contrib-less');  
  grunt.loadNpmTasks('grunt-contrib-watch');	

  grunt.registerTask('default', [
  	'less',
   	'concat',
 		'terser',
  	'cssmin',
  	'watch'
  ]);

  grunt.registerTask('build_wp_css', [
   	'less:wp_css',
 		'concat:wp_css',   	
   	'cssmin:wp_css'
  ]); 

  grunt.registerTask('build_wp_js', [
 		'concat:wp_js',
   	'terser:wp_js'
  ]);           
};
