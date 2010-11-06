<?php

class Controller_Report extends Controller_Profile
{
    protected $report;

    function before()
    {
        parent::before();

        $reportId = $this->request->param('id');

        if ($reportId == 'new')
        {
            $this->request->action = 'new';
            return;
        }

        $report = $this->org->query_reports()->where('u.guid = ?', $reportId)->get();
        if ($report)
        {
            $this->report = $report;
            return;
        }
        else
        {
            $this->use_public_layout();
            $this->org_page_not_found();
        }
    }

    function show_next_steps()
    {
        return false;
    }
    
    function action_index()
    {
        $org = $this->org;
        $report = $this->report;

        $this->use_public_layout();

        if ($report->can_edit())
        {
            add_submenu_item(__("report:edit"), "{$report->get_url()}/edit", 'edit');
        }

        $title = $report->get_title();

        if (!$org->can_view())
        {
            $this->show_cant_view_message();
            $body = '';
        }
        else
        {
            $body = $this->org_view_body($title, view('reports/view', array('report' => $report)));
        }

        $this->page_draw($title,$body);
    }

    function use_editor_layout()
    {
        PageContext::set_theme('editor_wide');
    }    
    
    function require_editor()
    {
        parent::require_editor();

        $report = $this->report;

        if (!$report)
        {
            not_found();
        }
        else if (!$report->can_edit())
        {
            register_error(__('report:cantedit'));
            forward($report->get_url());
        }
    }

    function require_manager()
    {
        $report = $this->report;
        if (!$report->can_manage())
        {
            register_error(__('report:cantmanage'));
            force_login();
        }
        $this->use_editor_layout();
    }
    
    function action_edit()
    {
        $this->require_editor();
        $report = $this->report;

        $title = sprintf(__('report:edit_title'), $report->get_title());

        $cancelUrl = get_input('from') ?: $this->org->get_widget_by_name('reports')->get_edit_url();

        add_submenu_item(__("canceledit"), $cancelUrl, 'edit');

        $area1 = view('reports/edit', array('report' => $report));
        $body = view_layout("one_column", view_title($title), $area1);

        $this->page_draw($title,$body);
    }
    
    function action_preview()
    {
        $report = $this->report;
        $this->require_editor();
        
        add_submenu_item(__("report:cancel_preview"), $this->org->get_widget_by_name('reports')->get_edit_url(), 'edit');        
        
        $title = sprintf(__('report:preview'), $report->get_title());        
        $area1 = view('reports/preview', array('report' => $report));
        $body = view_layout("one_column", view_title($title), $area1);
        $this->page_draw($title,$body);
    }    
    
    function action_view_response()
    {
        $this->require_manager();
        $report = $this->report;        
        $report_def = $report->get_report_definition();
        
        add_submenu_item(__("report:cancel_preview"), $report_def->get_url()."/edit?section=manage", 'edit');        
        
        $title = sprintf(__('report:view_response_title'), $report->get_title());
        $area1 = view('reports/view_response', array('report' => $report));
        $body = view_layout("one_column", view_title($title), $area1);
        $this->page_draw($title,$body); 
    }
    
    function action_set_status()
    {
        $this->require_manager();
        $newStatus = (int)get_input('status');
        $report = $this->report;
        $report->status = $newStatus;
        $report->save();
        system_message(__('report:status_changed'));
        forward($report->get_report_definition()->get_url()."/edit?section=manage");
    }
    
    function action_confirm_submit()
    {
        $this->require_editor();
        $report = $this->report;
        
        add_submenu_item(__("report:cancel_submit"), $this->org->get_widget_by_name('reports')->get_edit_url(), 'edit');        
        
        $title = sprintf(__('report:confirm_submit'), $report->get_title());        
        $area1 = view('reports/preview', array('report' => $report, 'submit' => true));
        $body = view_layout("one_column", view_title($title), $area1);
        $this->page_draw($title,$body);
    }

    function action_submit()
    {
        $this->require_editor();
        $this->validate_security_token();
        $report = $this->report;
        $report->signature = get_input('signature');
        $report->status = ReportStatus::Submitted;
        $report->time_submitted = time();
        $report->save();
        
        system_message(__('report:submitted'));
        forward($this->org->get_widget_by_name('reports')->get_edit_url());
    }

    function action_save()
    {
        $this->require_editor();
        $this->validate_security_token();
        $report = $this->report;

        $field_names = get_input_array('fields');
        
        foreach ($field_names as $field_name)
        {        
            $field = $report->get_field($field_name);
        
            $input_name = "field_{$field_name}";
            $input_type = $field->get_arg('input_type');
        
            if ($input_type == 'input/checkboxes')
            {
                $field->value = get_input_array($input_name);
            }
            else
            {
                $field->value = get_input($input_name);
            }
        }
        
        if ($report->status == ReportStatus::Blank)
        {
            $report->status = ReportStatus::Draft;        
        }
        $report->save();
        
        $next_section = get_input('next_section');        
        if ($next_section)
        {            
            forward($report->get_edit_url()."?section=$next_section");
        }
        else
        {
            $org = $report->get_container_entity();            
            forward($report->get_url()."/confirm_submit");
        }
    }

    function action_new()
    {
        $this->require_editor();
        $this->validate_security_token();
    }
}