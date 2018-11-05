module.exports = function(grunt) {
    var targetDir = grunt.config.get('targetDir');
    var nodeModulesPath = grunt.config.get('nodeModulesPath');

    grunt.loadTasks(targetDir+'/modules/Core');
    grunt.config.merge({
        less: {
            demoJobboard: {
                options: {
                    compress: false,
                    modifyVars: {
                        "fa-font-path": "/dist/fonts",
                        "flag-icon-css-path": "/dist/flags"
                    }
                },
                files: [
                    {
                        src: [
                            targetDir+"/modules/Jobboard/less/Jobboard.less",
                            "./node_modules/select2/dist/css/select2.min.css",
                            "./node_modules/pnotify/dist/pnotify.css",
                            "./node_modules/pnotify/dist/pnotify.buttons.css",
                            "./node_modules/bootsrap3-dialog/dist/css/bootstrap-dialog.css"
                        ],
                        dest: targetDir+"/modules/Jobboard/dist/Jobboard.css"
                    }
                ]
            },
        },
        cssmin: {
            demoJobboard: {
                files: [
                    {
                        dest: targetDir+'/modules/Jobboard/dist/Jobboard.min.css',
                        src: targetDir+'/modules/Jobboard/dist/Jobboard.css'
                    }
                ]
            }
        }
    });

    grunt.registerTask('yawik:demoJobboard',['copy','less','concat','uglify','cssmin']);


};