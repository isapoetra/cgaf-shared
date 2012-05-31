<?php
use System\Web\UI\JQ\Wizard;

use System\Web\UI\Controls\TabItem;

use System\Web\UI\Controls\Tab;

use System\Web\Utils\HTMLUtils;

echo HTMLUtils::beginForm('',false);
$steps = array(
    array(
        'title'=>__('company.register.basic','Basic Information'),
        'content'=> $this->render('register/basic',true,false)
    ),
    array(
         'title'=>__('company.register.contact','Contacts'),
         'content'=> $this->render('register/contact',true,false)
    ),
    array(
        'title'=>__('company.register.privacy','Privacy'),
        'content'=> $this->render('register/privacy',true,false)
    )
);
$tab= new Wizard('register-company-wizard', $steps);
/*$tab->addClass('tabs-left');
$c = '<div>';
$c.=HTMLUtils::renderTextBox(__('company.company_name'), 'company_name');
$c.= '</div>';
$ti = new TabItem(__('company.register.basic'), $c);
$tab->addTab($ti);*/
echo $tab->Render(true);
echo HTMLUtils::endForm();
?>