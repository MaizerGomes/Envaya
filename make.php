<?php

/*
 * Minifies javascript and CSS files, copying static files to www/_media/
 * and saving cache information in build/.
 *
 * Usage:
 *  php make.php [clean|js|css|media|lib_cache|path_cache|all]
 */     

chdir(__DIR__);
 
require_once "scripts/cmdline.php";

class Build
{
    private static function module_glob()
    {
        return "{".implode(',',Config::get('modules'))."}";
    }    
    
    private static function minify($srcFile, $destFile, $type='js')
    {
        $src = file_get_contents($srcFile);
        
        system("java -jar vendors/yuicompressor-2.4.2.jar --type $type -o ".escapeshellarg($destFile). " ".escapeshellarg($srcFile));
        
        $compressed = file_get_contents($destFile);

        echo strlen($src)." ".strlen($compressed)." $destFile\n";  
    }
    
    static function clean()
    {
        @unlink("build/lib_cache.php");
        @unlink("build/path_cache.php");
        @unlink("build/path_cache_info.php");
        
        system('rm -rf build/path_cache');
        system('rm -rf www/_media/*');        
    }
    
    /*
     * Generates a cache of all the paths of files in the lib/ directory.
     *
     * Uses relative paths so the same cache files work in any root directory
     * and can be copied to different systems without needing to regenerate them.
     */
    static function lib_cache()
    {
        @unlink("build/lib_cache.php");
        
        require_once "start.php";
        $paths = Engine::get_lib_paths();  
        static::write_file("build/lib_cache.php", static::get_array_php($paths));
    }
    
    /*
     * Generates a cache of all the paths of files in the engine, themes, languages, and views
     * directories, which might be referenced via virtual paths in calls to Engine::get_real_path() .
     * This cache allows Engine::get_real_path() to work in O(1) time instead of O(num_modules) time.
     *
     * Uses relative paths so the same cache files work in any root directory
     * and can be copied to different systems without needing to regenerate them.
     */    
    static function path_cache()
    {
        require_once "start.php";
                
        if (!is_dir('build/path_cache'))
        {
            mkdir('build/path_cache');
        }
                
        $dir_paths = array(
            // allows us to test if the path cache actually works like it should
            'views/default/admin' => array(            
                'views/default/admin/path_cache_test.php' => 'build/path_cache_info.php' 
            )
        );        
        
        $virtual_dirs = array('engine', 'themes', 'languages', 'views');
        
        foreach ($virtual_dirs as $virtual_dir)
        {        
            static::add_paths_in_dir('', $virtual_dir, $dir_paths);
        }
                
        $modules = Config::get('modules');
        foreach ($modules as $module)
        {
            foreach ($virtual_dirs as $virtual_dir)
            {        
                static::add_paths_in_dir("mod/{$module}/", $virtual_dir, $dir_paths);
            }
        }       
        
        static::add_nonexistent_view_paths($dir_paths);
                
        // create a cache file for each virtual directory
        foreach ($dir_paths as $dir => $paths)
        {
            $cache_name = str_replace('/','__', $dir);
            static::write_file("build/path_cache/$cache_name.php", static::get_array_php($paths));
        }
        
        // list of commonly used virtual directories whose paths will be included in
        // build/path_cache.php, rather than needing to open a cache file for each directory
        $default_dirs = array(
            'engine',
            'engine/controller',
            'engine/cache',
            'engine/query',            
            'engine/mixin',
            'engine/widget',            
            'languages/en',
            'themes',
            'views/default',
            'views/default/home',            
            'views/default/js',
            'views/default/layouts',
            'views/default/page_elements',
            'views/default/input',
            'views/default/object',            
            'views/default/output',
            'views/default/translation',
            'views/default/messages',
        );
        
        $default_paths = array();
        foreach ($default_dirs as $default_dir)
        {
            $default_paths = array_merge($default_paths, $dir_paths[$default_dir]);
        }
        
        static::write_file("build/path_cache.php", static::get_array_php($default_paths));
                
        // create a file with cache information to display on the admin statistics page
        // (which also verifies that the path cache is basically working)
        $num_default_paths = sizeof($default_paths);
        $num_files = sizeof($dir_paths);
        static::write_file("build/path_cache_info.php", 
            "<div>The path cache is enabled. ($num_default_paths default paths + $num_files files)</div>");
    }
    
    private static function add_nonexistent_view_paths(&$dir_paths)
    {
        // add cache entries for non-existent views in view types other than default
        // since the view() function will check if they exist before using the default view.
        
        $view_types = array();
        $default_views = array();

        foreach ($dir_paths as $dir => $paths)
        {
            if (preg_match('#^views/(\w+)#', $dir, $matches))
            {
                $view_type = $matches[1];
                if ($view_type == 'default')
                {            
                    foreach ($paths as $virtual_path => $real_path)
                    {
                        $default_views[] = substr($virtual_path, strlen('views/default/'));
                    }
                }
                else // collect names of all view types other than default
                {
                    $view_types[$view_type] = $view_type;
                }
            }
        }
                               
        foreach ($default_views as $view_path)
        {
            foreach ($view_types as $view_type)
            {            
                // is this view not defined for this view type?
                $virtual_path = "views/$view_type/$view_path";
                if (!Engine::filesystem_get_real_path($virtual_path)) 
                {
                    $dir = dirname($virtual_path);
                    
                    if (!isset($paths[$dir][$virtual_path]))
                    {
                        // 0 is sentinel for nonexistent keys in path cache
                        $dir_paths[$dir][$virtual_path] = 0; 
                    }                    
                }
            }
        }
    }
        
    static function write_file($filename, $contents)
    {
        echo strlen($contents) . " ".$filename."\n";
        file_put_contents($filename, $contents);            
    }

    private static function add_paths_in_dir($rel_base, $dir, &$paths)
    {
        $root = Config::get('root'); 
        $handle = @opendir("{$root}/{$rel_base}{$dir}");
        if ($handle)
        {
            while ($file = readdir($handle))
            {
                $virtual_path = "{$dir}/{$file}";
                $real_rel_path = "{$rel_base}{$virtual_path}";
                $real_path = "{$root}/{$real_rel_path}";

                if (preg_match('/\.php$/', $file))
                {
                    if (!isset($paths[$dir][$virtual_path]))
                    {
                        $paths[$dir][$virtual_path] = $real_rel_path;
                    }
                }
                if ($file[0] != '.' && is_dir($real_path))
                {
                    static::add_paths_in_dir($rel_base, $virtual_path, $paths);
                }
            }
        }
    }
    
    private static function get_array_php($arr)
    {
        return "<?php return ".var_export($arr, true).";";
    }
    
    /* 
     * Minifies all CSS files defined in each module's views/default/css/ directory, 
     * and copies to www/_media/css/.
     */       
    static function css($name = '*')
    {    
        require_once "start.php";
        
        $modules = static::module_glob();
        $css_paths = glob("{views/default/css/$name.php,mod/$modules/views/default/css/$name.php}", GLOB_BRACE);

        $output_dir = 'www/_media/css';
        
        if (!is_dir($output_dir))
        {
            mkdir($output_dir);
        }
        
        foreach ($css_paths as $css_path)
        {
            $pathinfo = pathinfo($css_path);
            $filename = $pathinfo['filename'];
            $css_temp = "scripts/$filename.tmp.css";
            $raw_css = view("css/$filename");
            
            if (preg_match('/http(s)?:[^\s\)\"\']*/', $raw_css, $matches))
            {
                throw new Exception("Absolute URL {$matches[0]} found in $css_path. In order to work on both dev/production without recompiling, CSS files must not contain absolute paths.");
            }
            
            file_put_contents($css_temp, $raw_css);
            static::minify($css_temp, "$output_dir/$filename.css", 'css');
            unlink($css_temp);
        }
    }
     
    private static function js_minify_dir($base, $name = '*', $dir = '')
    {    
        $js_src_files = glob("$base/js/{$dir}{$name}.js");
        foreach ($js_src_files as $js_src_file)
        {
            $basename = pathinfo($js_src_file,  PATHINFO_BASENAME);
            static::minify($js_src_file, "www/_media/{$dir}{$basename}");
        }
        
        $subdirs = glob("$base/js/{$dir}*", GLOB_ONLYDIR);
        foreach ($subdirs as $subdir)
        {
            $basename = pathinfo($subdir,  PATHINFO_BASENAME);
        
            if (!is_dir("www/_media/{$dir}{$basename}"))
            {
                mkdir("www/_media/{$dir}{$basename}");
            }
            static::js_minify_dir($base, $name, "{$dir}{$basename}/");
        }
    }

    /* 
     * Minifies Javascript in each module's js/ directory, and copie to www/_media/.
     */   
    static function js($name = '*')
    {    
        require_once "start.php";
        $modules = static::module_glob();
        
        static::js_minify_dir(".", $name);
        
        foreach (Config::get('modules') as $module)
        {            
            static::js_minify_dir("mod/$module", $name);
        }
    }
    
    static function system($cmd)
    {
        echo "$cmd\n";
        return system($cmd);
    }
    
    /* 
     * Copies static files from each module's _media/ directory to www/_media/.
     */
    static function media()
    {
        require_once "start.php";
        
        static::system("rsync -rp _media/ www/_media/");
        
        foreach (Config::get('modules') as $module)
        {            
            if (is_dir("mod/$module/_media"))
            {
                static::system("rsync -rp mod/$module/_media/ www/_media/");
            }
        }
    }

    static function all()
    {
        Build::clean();
        Build::media();
        Build::lib_cache();
        Build::path_cache();
        Build::css();
        Build::js();
    }
}

$target = @$argv[1] ?: 'all';
$arg = @$argv[2];
if (method_exists('Build', $target))
{
    if ($arg)
    {
        Build::$target($arg);
    }
    else
    {
        Build::$target();
    }
}
else
{
    echo "Build::$target is not defined\n";
}