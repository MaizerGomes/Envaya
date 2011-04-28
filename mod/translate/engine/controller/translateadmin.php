<?php

class Controller_TranslateAdmin extends Controller
{
    static $routes = array(
        array(
            'regex' => '(/)?$', 
            'defaults' => array('action' => 'index'), 
        ),    
        array(
            'regex' => '/(?P<lang>\w+)(/)?$', 
            'defaults' => array('action' => 'manage_lang'), 
            'before' => 'init_language',
        ),        
        array(
            'regex' => '/(?P<lang>\w+)/(?P<action>export)\b', 
            'defaults' => array('action' => 'export'), 
            'before' => 'init_language',
        ),        
        
    );
    
    function init_language()
    {
        $lang = $this->param('lang');
        if (!$lang)
        {
            return $this->not_found();
        }
        
        $language = InterfaceLanguage::query()
            ->where('code = ?', $lang)
            ->show_disabled(true)
            ->get();
            
        if (!$language)
        {    
            $language = new InterfaceLanguage();
            $language->code = $lang;
        }
        $this->params['language'] = $language;            
    }
    
    function before()
    {
        $this->require_admin();
        $this->page_draw_vars['theme_name'] = 'editor';
    }
    
    function action_index()
    {
        return $this->page_draw(array(
            'title' => __('itrans:manage'),
            'header' => view('translate/admin/header'),
            'content' => view('translate/admin/index')
        ));
    }

    function action_export()
    {
        $language = $this->param('language');
        
        // from http://www.php.net/manual/en/function.ziparchive-open.php#84646
        
        $file = tempnam("tmp", "zip"); 
            
        $zip = new ZipArchive(); 
        $zip->open($file, ZipArchive::OVERWRITE); 

        $groups = $language->query_groups()->filter();
                
        foreach ($groups as $group)
        {
            $filename = "{$language->code}/{$language->code}_{$group->name}.php";
            $php = view('translate/admin/export_group', array('group' => $group));
            $zip->addFromString($filename, $php);
        }

        $zip->close(); 

        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($file));
        header("Content-Disposition: attachment; filename=\"{$language->code}.zip\""); 
        readfile($file); 
        unlink($file);        
    }
    
    function action_manage_lang()
    {
        $action = new Action_Admin_ManageLanguage($this);
        $action->execute();
    }
}