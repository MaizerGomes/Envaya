<?php

class Action_AddTranslationKeyComment extends Action
{
    function before()
    {
        Permission_RegisteredUser::require_any();
    }

    function process_input()
    {       
        $content = Input::get_string('content');        
        $content = preg_replace('/\n\s*\n/', '<br /><br />', $content);        
        $content = Markup::sanitize_html($content, array(
            'HTML.AllowedElements' => 'a,em,strong,br',
            'AutoFormat.RemoveEmpty' => true
        ));
        
        if ($content == '')
        {
            throw new ValidationException(__('comment:empty'));
        }
                
        $key = $this->param('key');
        if (!$key->guid)
        {
            $key->save();
        }
        
        if ($key->query_comments()->where('content = ?', $content)->exists())
        {
            throw new ValidationException(__('comment:duplicate'));
        }
        
        $user = Session::get_logged_in_user();
                
        $comment = new TranslationKeyComment();
        $comment->container_guid = $key->guid;
        $comment->owner_guid = $user->guid;
        if (Input::get_string('scope') == 'current')
        {
            $comment->language_guid = $key->language_guid;
        }
        $comment->key_name = $key->name;
        $comment->set_content($content, true);
		$comment->save();

        $key->update(true);
        
        SessionMessages::add(__('comment:success'));
        $this->redirect();
    }
}