<?php

class Partnership extends Entity
{
    static $subtype_id = T_partnership;
    static $table_name = 'partnerships';

    static $table_attributes = array(
        'description' => '',
        'partner_guid' => 0,
        'date_formed' => '',
        'approval' => 0,
        'language' => '',
    );

    public function save()
    {
        if (!$this->language)
        {
            $this->language = GoogleTranslate::guess_language($this->description);
        }

        parent::save();
    }

    function getPartner()
    {
        return get_entity($this->partner_guid);
    }

    function isSelfApproved()
    {
        return ($this->approval & 1) != 0;
    }

    function setSelfApproved($approved)
    {
        if ($approved)
        {
            $this->approval = $this->approval | 1;
        }
        else
        {
            $this->approval = $this->approval & ~1;
        }
    }

    function isPartnerApproved()
    {
        return ($this->approval & 2) != 0;
    }

    function setPartnerApproved($approved)
    {
        if ($approved)
        {
            $this->approval = $this->approval | 2;
        }
        else
        {
            $this->approval = $this->approval & ~2;
        }
    }

    function getApproveUrl()
    {
        return "{$this->getContainerEntity()->getURL()}/confirm_partner";
    }
}
